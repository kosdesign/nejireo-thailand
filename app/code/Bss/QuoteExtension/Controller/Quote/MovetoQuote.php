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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Controller\Quote;
use Bss\QuoteExtension\Helper\QuoteExtension\MoveToQuote as MoveToQuoteHelper;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Bss\QuoteExtension\Model\Session;
use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MovetoQuote
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class MovetoQuote extends Quote
{
    /**
     * @var MoveToQuoteHelper
     */
    protected $helper;

    /**
     * @var array $productMove
     */
    protected $productMove = [];

    /**
     * MovetoQuote constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $quoteExtensionSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param ManageQuote $manageQuote
     * @param PageFactory $resultPageFactory
     * @param MoveToQuoteHelper $helper
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $quoteExtensionSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        ManageQuote $manageQuote,
        PageFactory $resultPageFactory,
        MoveToQuoteHelper $helper
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $scopeConfig,
            $quoteExtensionSession,
            $storeManager,
            $formKeyValidator,
            $quoteExtension,
            $manageQuote,
            $resultPageFactory
        );
    }

    /**
     * Execute Function
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if ($this->quoteExtensionSession->getQuoteExtension()
            && $this->quoteExtensionSession->getQuoteExtension()->getId()
        ) {
            try {
                $quoteCart = $this->quoteExtensionSession->getQuote();
                $quoteExtension = $this->quoteExtensionSession->getQuoteExtension();
                foreach ($quoteCart->getAllVisibleItems() as $item) {
                    $product = $item->getProduct();
                    $productId = $product->getId();
                    $typeProduct = $item->getProductType();
                    if ($typeProduct == "grouped") {
                        $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
                        if (isset($options["super_product_config"]) && $options["super_product_config"]["product_id"]) {
                            $productId = $options["super_product_config"]["product_id"];
                        }
                    }
                    $productRepository = $this->helper->getProductRepository()->getById(
                        $productId,
                        false,
                        $this->storeManager->getStore()->getId()
                    );
                    if ($this->helper->isActiveRequest4Quote($productRepository)) {
                        $this->mergeQuotetoCart($quoteExtension, $item, $product);
                    }
                }
                if (empty($this->productMove)) {
                    $this->messageManager->addWarningMessage(
                        __(
                            'Can not moved to quote because there are item(s) that are not allowed to add to quote in cart'
                        )
                    );
                    return $this->_goBack();
                } else {
                    if (!$this->quoteExtensionSession->getQuoteExtensionId()) {
                        $this->quoteExtensionSession->setQuoteExtensionId($quoteExtension->getId());
                    }
                }
                $this->returnMessageSuccessMove();
                $quoteExtension->setTotalsCollectedFlag(false);
                $quoteExtension->collectTotals()->save();
                $this->helper->getQuoteRepository()->save($quoteExtension);
                $this->helper->getCart()->save();
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__("Can't move items to quote!"));
            }
        }
        $resultReidrect = $this->resultRedirectFactory->create();
        return $resultReidrect->setPath('quoteextension/quote/');
    }

    /**
     * @param $quoteExtension
     * @param $item
     * @param $product
     */
    protected function mergeQuotetoCart($quoteExtension, $item, $product)
    {
        $found = false;
        foreach ($quoteExtension->getAllItems() as $quoteItem) {
            if ($quoteItem->compare($item)) {
                $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                $found = true;
                break;
            }
        }
        $this->addItemToRequestQuote($found, $item, $quoteExtension);

        $this->productMove[] = $product->getName();
        $this->helper->getCart()->removeItem($item->getId());
    }

    /**
     * Add Item Form Cart To Request Quote
     *
     * @param bool $found
     * @param Item $item
     * @param Session $quoteExtension
     */
    protected function addItemToRequestQuote($found, $item, $quoteExtension)
    {
        if (!$found) {
            $newItem = clone $item;
            $quoteExtension->addItem($newItem);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $newChild = clone $child;
                    $newChild->setParentItem($newItem);
                    $quoteExtension->addItem($newChild);
                }
            }
        }
    }

    /**
     * Return Success Move Message
     */
    protected function returnMessageSuccessMove()
    {
        if (!empty($this->productMove)) {
            $this->messageManager->addSuccessMessage(
                __(
                    '%1 items has been move to quote %2',
                    count($this->productMove),
                    implode(', ', $this->productMove)
                )
            );
        }
    }
}
