<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomQuote
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Kos\CustomBssQuote\Controller\Quote;

use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Helper\Mail;
use Bss\QuoteExtension\Helper\Json as JsonHelper;
use Bss\QuoteExtension\Helper\QuoteExtension\Address;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\QuoteItemFactory;
use Bss\QuoteExtension\Model\QuoteVersion;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class ViewSubmit
 *
 * @package Kos\CustomBssQuote\Controller\Quote
 */
class ViewSubmit extends \Bss\QuoteExtension\Controller\Quote\ViewSubmit
{

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRepository;

    /**
     * ViewSubmit constructor.
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param CartRepositoryInterface $quoteRepository
     * @param ManageQuote $manageQuote
     * @param QuoteItemFactory $quoteItemFactory
     * @param Data $helper
     * @param Mail $mailHelper
     * @param QuoteVersion $quoteVersion
     * @param JsonHelper $jsonHelper
     * @param Address $helperQuoteAddress
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRepository
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        CartRepositoryInterface $quoteRepository,
        ManageQuote $manageQuote,
        QuoteItemFactory $quoteItemFactory,
        Data $helper,
        Mail $mailHelper,
        QuoteVersion $quoteVersion,
        JsonHelper $jsonHelper,
        Address $helperQuoteAddress,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRepository
    ) {
        $this->stockRepository = $stockRepository;
        parent::__construct(
            $context,
            $formKeyValidator,
            $quoteRepository,
            $manageQuote,
            $quoteItemFactory,
            $helper,
            $mailHelper,
            $quoteVersion,
            $jsonHelper,
            $helperQuoteAddress
        );
    }

    /**
     * Excute Function
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/history');
        }

        $params = $this->getRequest()->getParams();
        if (isset($params['request_entity_id'])) {
            try {
                $manageQuote = $this->manageQuote->load($params['request_entity_id']);
                if (!$manageQuote->getQuoteId()) {
                    $this->messageManager->addErrorMessage(__('We can\'t find a quote.'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/history');
                }
                $status = $manageQuote->getStatus();
                $this->getStatusCanEdit($status, $params);
                $currentTime = $this->helper->getCurrentDateTime();
                $quote = $this->quoteRepository->get($manageQuote->getQuoteId());

                $this->manageQuote->load($params['request_entity_id']);

                if (isset($params['change_shipping_info'])
                    && $params['change_shipping_info']
                    && $this->helperQuoteAddress->isRequiredAddress()
                ) {
                    $this->saveShippingInformation($params, $quote);
                }

                $data = $this->updateItems($params['quote'], $quote, $manageQuote);
                if ($data['hasError'] == true) {
                    $this->messageManager->addErrorMessage(__('The requested qty exceeds the maximum qty allowed in quote cart'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/view/quote_id/' . $params['request_entity_id']);
                }
                $oldQuote = $this->processOldQuote();
                $this->manageQuote->setTargetQuote(null);
                $this->manageQuote->setBackendQuoteId(null);
                $this->manageQuote->setStatus(Status::STATE_RESUBMIT);
                $this->manageQuote->setVersion($manageQuote->getVersion() + 1);
                $this->manageQuote->setUpdatedAt($currentTime);
                $this->manageQuote->setOldQuote($oldQuote);
                $this->manageQuote->save();

                $data['comment'] = $params['customer_note'];
                $this->quoteVersion->setData($data);
                $this->quoteVersion->save();
                $quote->collectTotals();
                $this->quoteRepository->save($quote);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultRedirectFactory
                    ->create()
                    ->setPath('*/*/view/quote_id/' . $params['request_entity_id']);
            }
        }
        $this->messageManager->addSuccessMessage(__('You resubmit the quote'));
        return $this->resultRedirectFactory->create()->setPath('*/*/view/quote_id/' . $params['request_entity_id']);
    }

    /**
     * Function Update Items
     *
     * @param array $data
     * @param \Magento\Quote\Api\CartRepositoryInterface $quote
     * @param \Bss\QuoteExtension\Model\ManageQuote $requestQuote
     * @return array
     * @throws \Exception
     */
    protected function updateItems($data, $quote, $requestQuote)
    {
        $versionData = [];
        $errorQty = false;
        foreach ($data as $itemId => $info) {
            $dataItem = [];
            $item = $quote->getItemById($itemId);
            $productId = $item->getProductId();
            $maxQty = $this->stockRepository->getStockItem($productId)->getMaxSaleQty();
            $qty = $info['qty'];

            /**
             * Customize check max qty Quote
             */
            if ($maxQty < $qty) {
                $errorQty = true;
            }

            if (!$item) {
                continue;
            }

            if (!$item->getCustomPrice()) {
                $price  = [
                    'price'               => $item->getPrice(),
                    'base_price'          => $item->getBasePrice(),
                    'price_incl_tax'      => $item->getPriceInclTax(),
                    'base_price_incl_tax' => $item->getBasePriceInclTax()
                ];
            } else {
                $price  = [
                    'customprice'         => $item->getCustomPrice(),
                    'price'               => $item->getPrice(),
                    'base_price'          => $item->getBasePrice(),
                    'price_incl_tax'      => $item->getPriceInclTax(),
                    'base_price_incl_tax' => $item->getBasePriceInclTax()
                ];
            }
            /* Load request quote item */
            $requestQuoteItem = $this->getRequestQuoteItem($itemId);

            $dataItem['price'] = $price;
            $dataItem['name'] = $item->getName();
            $dataItem['sku'] = $item->getSku();
            $dataItem['comment'] = $requestQuoteItem->getComment();
            $dataItem['qty'] = $item->getQty();
            $versionData[$itemId] = $dataItem;

            $info['qty'] = (double)$info['qty'];
            $item->setQty($info['qty']);
            if (isset($info['description'])) {
                if ($requestQuoteItem->getId()) {
                    $requestQuoteItem->setComment($info['description']);
                    $this->saveRequestQuoteItem($requestQuoteItem);
                } else {
                    $data['item_id'] = $itemId;
                    $data['comment'] = $info['description'];
                    $requestQuoteItem->setData($data);
                    $this->saveRequestQuoteItem($requestQuoteItem);
                }
            }
        }

        $data = [
            'quote_id' => $requestQuote->getId(),
            'version' => $requestQuote->getVersion() + 1,
            'status' => $requestQuote->getStatus(),
            'log' => $this->jsonHelper->serialize($versionData),
            'hasError' => false
        ];

        if ($errorQty) {
            $data['hasError'] = true;
        }
        return $data;
    }

    /**
     * Save Shipping information
     *
     * @param array $data
     * @param \Magento\Quote\Api\CartRepositoryInterface $quote
     * @throws \Exception
     */
    protected function saveShippingInformation($data, $quote)
    {
        $address = $data['address'];
        if (isset($data['address']['customer_address_id'])) {
            $customerAddressId = $data['address']['customer_address_id'];
            $address = $this->helperQuoteAddress->getCustomerAddress($customerAddressId)->getData();
        }
        if ($quote) {
            $quote->getShippingAddress()->addData($address);
            if (isset($data['shipping_method'])) {
                $quote->getShippingAddress()
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($data['shipping_method']);
            }
        }
    }

    /**
     * Get Request Quote Item
     *
     * @param int $itemId
     * @return \Bss\QuoteExtension\Model\QuoteItem
     */
    protected function getRequestQuoteItem($itemId)
    {
        return $this->quoteItemFactory->create()->load($itemId, 'item_id');
    }

    /**
     * Save Request Quote Item
     *
     * @param \Bss\QuoteExtension\Model\QuoteItem $requestQuoteItem
     * @return mixed
     * @throws \Exception
     */
    protected function saveRequestQuoteItem($requestQuoteItem)
    {
        try {
            return $requestQuoteItem->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this;
    }

    /**
     * Clear draft quote
     *
     * @param int $targetQuote
     * @param int $backendQuote
     */
    private function clearDraftQuote($targetQuote, $backendQuote)
    {
        try {
            if ($targetQuote) {
                $targetQuote = $this->quoteRepository->get($targetQuote);
                $this->quoteRepository->delete($targetQuote);
            }

            if ($backendQuote) {
                $backendQuote = $this->quoteRepository->get($backendQuote);
                $this->quoteRepository->delete($backendQuote);
            }
        } catch (\Exception $e) {
            //nothing
        }
    }

    /**
     * Process old quote to request quote
     *
     * @return string
     */
    private function processOldQuote()
    {
        $oldQuote = $this->manageQuote->getOldQuote();
        $targetQuote = $this->manageQuote->getTargetQuote();
        $backendQuote = $this->manageQuote->getBackendQuoteId();
        if ($targetQuote) {
            $oldQuote = $oldQuote . ',' . $targetQuote;
        }
        if ($backendQuote) {
            $oldQuote = $oldQuote . ',' . $backendQuote;
        }
        return ltrim($oldQuote, ",");
    }

    /**
     * Get Status Can Edit
     *
     * @param string $status
     * @param array $params
     * @return $this|Redirect
     */
    private function getStatusCanEdit($status, $params)
    {
        $disableResubmit = $this->helper->disableResubmit();
        if (!$disableResubmit) {
            $statusCanEdit = [
                Status::STATE_UPDATED,
                Status::STATE_REJECTED,
                Status::STATE_EXPIRED
            ];
        } else {
            $statusCanEdit = [
                Status::STATE_UPDATED
            ];
        }
        if (!in_array($status, $statusCanEdit)) {
            $this->messageManager->addErrorMessage(__("This Quote can't re-submit."));
            return $this->resultRedirectFactory
                ->create()
                ->setPath('*/*/view/quote_id/' . $params['request_entity_id']);
        }
        return $this;
    }
}
