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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\QuoteExtension;

use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

/**
 * Class Submit
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Submit extends \Bss\QuoteExtension\Block\QuoteExtension\AbstractQuoteExtension
{
    /**
     * @var LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Bss\QuoteExtension\Block\QuoteExtension\Submit\LayoutProcessor
     */
    protected $layoutProcessor;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\Address
     */
    protected $helperAddress;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var \Bss\QuoteExtension\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Submit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param Submit\LayoutProcessor $layoutProcessor
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Address $helperAddress
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice
     * @param array $layoutProcessors
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        Submit\LayoutProcessor $layoutProcessor,
        \Bss\QuoteExtension\Helper\QuoteExtension\Address $helperAddress,
        CartTotalRepositoryInterface $cartTotalRepository,
        \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->configProvider = $configProvider;
        $this->layoutProcessor = $layoutProcessor;
        $this->helperAddress = $helperAddress;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * Json Encode Layout
     *
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        $this->jsLayout = $this->layoutProcessor->process($this->jsLayout);
        if (!$this->customerSession->getCustomerId()) {
            unset($this->jsLayout['components']
                ['block-submit']['children']['steps']['children']["shipping-step"]['children']['step-config']);
            unset($this->jsLayout['components']
                ['block-submit']
                ['children']['steps']['children']["shipping-step"]['children']['quote-comment-fieldset']);
            unset($this->jsLayout['components']
                ['block-submit']['children']['steps']['children']["shipping-step"]['children']['shippingAddress']);
            unset($this->jsLayout['components']
                ['block-submit']['children']['steps']['children']["shipping-step"]['children']['block-totals']);
        } else {
            if (!$this->canShowSubtotal()) {
                unset($this->jsLayout['components']
                    ['block-submit']['children']['steps']['children']["shipping-step"]['children']['block-totals']
                    ['children']['subtotal']);
                unset($this->jsLayout['components']
                    ['block-submit']['children']['steps']['children']["shipping-step"]['children']['block-totals']
                    ['children']['grand-total']);
            }
        }
        return $this->helperAddress->jsonEncodeDataConfig($this->jsLayout);
    }

    /**
     * Get checkout config
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCheckoutConfig()
    {
        $output = $this->configProvider->getConfig();
        $output['quoteData'] = $this->getQuoteData();
        $output['quoteItemData'] = $this->getQuoteItemData();
        $output['selectedShippingMethod'] = $this->getSelectedShippingMethod();
        $output['storeCode'] = $this->getStoreCode();
        $output['staticBaseUrl'] = $this->getStaticBaseUrl();
        $output['totalsData'] = $this->getTotalsData();
        if (isset($output['checkoutUrl'])) {
            $output['checkoutUrl'] = $this->getUrl('quoteextension/quote/');
        }
        if (isset($output['isGuestCheckoutAllowed'])) {
            $output['isGuestCheckoutAllowed'] = false;
        }
        if (isset($output['defaultSuccessPageUrl'])) {
            $output['defaultSuccessPageUrl'] = $this->getUrl('quoteextension/quote/success');
        }
        $output['isRequiredAddress'] = $this->isRequiredAddress();
        $output['inValidAmount'] = $this->quoteExtensionSession->getInvalidRequestQuoteAmount();
        $output["addToQuote"] = true;
        return $output;
    }

    /**
     * Address is required
     *
     * @return bool
     */
    /**
     * Get quote Data
     *
     * @return array
     */
    protected function getQuoteData()
    {
        $quoteData = [];
        if ($this->getQuoteExtension()->getId()) {
            $quoteData = $this->getQuoteExtension()->toArray();
            $quoteData['is_virtual'] = $this->getQuoteExtension()->getIsVirtual();
        }
        return $quoteData;
    }

    /**
     * Get Quote item data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuoteItemData()
    {
        $quoteItemData = [];
        $quoteId = $this->getQuoteExtension()->getId();
        if ($quoteId) {
            $quoteItems = $this->helperAddress->getListItemsById($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                $quoteItemData[$index] = $quoteItem->toArray();
                $quoteItemData[$index]['options'] = $this->getFormattedOptionValue($quoteItem);
            }
        }
        return $quoteItemData;
    }

    /**
     * Get shipping methods
     *
     * @return array|null
     */
    protected function getSelectedShippingMethod()
    {
        $shippingMethodData = null;
        try {
            $quoteId = $this->getQuoteExtension()->getId();
            $shippingMethod = $this->helperAddress->getShippindMethods($quoteId);
            if ($shippingMethod) {
                $shippingMethodData = $shippingMethod->__toArray();
            }
        } catch (\Exception $exception) {
            $shippingMethodData = null;
        }
        return $shippingMethodData;
    }

    /**
     * Retrieve store code
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function getStoreCode()
    {
        return $this->getQuoteExtension()->getStore()->getCode();
    }

    /**
     * Get Static Base Url
     *
     * @return string
     */
    protected function getStaticBaseUrl()
    {
        return $this->getQuoteExtension()->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
    }

    /**
     * Shipping Address is required\
     *
     * @return bool
     */
    public function isRequiredAddress()
    {
        return $this->helperAddress->isRequiredAddress();
    }

    /**
     * Return quote totals data
     *
     * @return array
     */
    protected function getTotalsData()
    {
        /** @var \Magento\Quote\Api\Data\TotalsInterface $totals */
        $totals = $this->cartTotalRepository->get($this->getQuoteExtension()->getId());
        $items = [];
        /** @var  \Magento\Quote\Model\Cart\Totals\Item $item */
        foreach ($totals->getItems() as $item) {
            $items[] = $item->__toArray();
        }
        $totalSegmentsData = [];
        /** @var \Magento\Quote\Model\Cart\TotalSegment $totalSegment */
        foreach ($totals->getTotalSegments() as $totalSegment) {
            $totalSegmentArray = $totalSegment->toArray();
            if (is_object($totalSegment->getExtensionAttributes())) {
                $totalSegmentArray['extension_attributes'] = $totalSegment->getExtensionAttributes()->__toArray();
            }
            $totalSegmentsData[] = $totalSegmentArray;
        }
        $totals->setItems($items);
        $totals->setTotalSegments($totalSegmentsData);
        $totalsArray = $totals->toArray();
        if (is_object($totals->getExtensionAttributes())) {
            $totalsArray['extension_attributes'] = $totals->getExtensionAttributes()->__toArray();
        }
        return $totalsArray;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function canShowSubtotal()
    {
        foreach ($this->getQuoteExtension()->getAllVisibleItems() as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if ($item->getProductType() == 'configurable') {
                $parentProductId = $item->getProductId();
                $childProductSku = $item->getSku();
                $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku);
            } else {
                $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false);
            }
            if (!$canShowPrice) {
                return false;
            }
        }
        return true;
    }
}
