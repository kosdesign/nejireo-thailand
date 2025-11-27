<?php
namespace Kos\CustomBssQuote\Plugin\MaxQtyQuote;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\ProductSalabilityError;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

class CheckQuoteItemQtyPlugin
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BackOrderNotifyCustomerCondition
     */
    private $backOrderNotifyCustomerCondition;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var \Bss\QuoteExtension\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Bss\QuoteExtension\Model\Session $session
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $collectionFactory
     * @param Quote $quote
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ObjectFactory $objectFactory
     * @param FormatInterface $format
     * @param IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     * @param BackOrderNotifyCustomerCondition $backOrderNotifyCustomerCondition
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        \Bss\QuoteExtension\Model\Session $session,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $collectionFactory,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Framework\App\RequestInterface $request,
        ObjectFactory $objectFactory,
        FormatInterface $format,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        BackOrderNotifyCustomerCondition $backOrderNotifyCustomerCondition
    ) {
        $this->session = $session;
        $this->collectionFactory = $collectionFactory;
        $this->quote = $quote;
        $this->request = $request;
        $this->objectFactory = $objectFactory;
        $this->format = $format;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
        $this->backOrderNotifyCustomerCondition = $backOrderNotifyCustomerCondition;
    }

    /**
     * Replace legacy quote item check
     *
     * @param StockStateInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int|null $scopeId
     *
     * @return DataObject
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckQuoteItemQty(
        StockStateInterface $subject,
        \Closure $proceed,
        $productId,
        $itemQty,
        $qtyToCheck,
        $origQty,
        $scopeId = null
    ) {
        $result = $this->objectFactory->create();
        $result->setHasError(false);

        $qty = max($this->getNumber($itemQty), $this->getNumber($qtyToCheck));

        $skus = $this->getSkusByProductIds->execute([$productId]);
        $productSku = $skus[$productId];

        $websiteCode = $this->storeManager->getWebsite($scopeId)->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();

        $isSalableResult = $this->isProductSalableForRequestedQty->execute($productSku, (int)$stockId, $qty);

        if ($isSalableResult->isSalable() === false) {
            /** @var ProductSalabilityError $error */
            foreach ($isSalableResult->getErrors() as $error) {
                $result->setHasError(true)->setMessage($error->getMessage())->setQuoteMessage($error->getMessage())
                    ->setQuoteMessageIndex('qty');
            }
        } else {
            $productSalableResult = $this->backOrderNotifyCustomerCondition->execute($productSku, (int)$stockId, $qty);
            if ($productSalableResult->getErrors()) {
                /** @var ProductSalabilityError $error */
                foreach ($productSalableResult->getErrors() as $error) {
                    $result->setMessage($error->getMessage());
                }
            }
        }

        if (($quoteId = $this->request->getParam('quote_id')) && $result->getData('has_error') == true) {
            $quote = $this->quote->load($quoteId);
            if ($this->isQuoteExtension($quote)) {
                $quoteMessage = str_replace("shopping","quote",$result->getMessage());
                $result->setMessage($quoteMessage);
            }
        }
        if ($this->request->getParam('quoteextension') &&
            $this->request->getParam('quoteextension') == '1' &&
            $result->getData('has_error') == true
        ) {
            $quoteMessage = str_replace("shopping","quote",$result->getMessage());
            $result->setMessage($quoteMessage);
            $result->setQuoteMessage($quoteMessage);
        }
        if ($this->request->getParam('quote_extension') &&
            $this->request->getParam('quote_extension') == '1' &&
            $result->getData('has_error') == true
        ) {
            $quoteMessage = str_replace("shopping","quote",$result->getMessage());
            $result->setMessage($quoteMessage);
            $result->setQuoteMessage($quoteMessage);
        }
        return $result;
    }

    /**
     * Convert quantity to a valid float
     *
     * @param string|float|int|null $qty
     *
     * @return float|null
     */
    private function getNumber($qty)
    {
        if (!is_numeric($qty)) {
            return $this->format->getNumber($qty);
        }

        return $qty;
    }

    /**
     * Check quote is quote extension
     *
     * @param object $quote
     * @return bool
     */
    public function isQuoteExtension($quote)
    {
        return $quote->getQuoteExtension() == null ? false : true;
    }
}
