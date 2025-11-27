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
namespace Bss\QuoteExtension\Plugin;

/**
 * Class AddtoQuoteButtonCategory
 *
 * @package Bss\QuoteExtension\Plugin
 */
class AddtoQuoteButtonCategory
{
    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtensionCart
     */
    protected $quoteExtensionHelper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postHelper;

    /**
     * @var \Bss\QuoteExtension\Helper\Admin\ConfigShow
     */
    protected $helperConfig;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * AddtoQuoteButtonCategory constructor.
     * @param \Bss\QuoteExtension\Helper\QuoteExtensionCart $quoteExtensionHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postHelper
     * @param \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig
     * @param \Bss\QuoteExtension\Helper\Data $helper
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\QuoteExtensionCart $quoteExtensionHelper,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig,
        \Bss\QuoteExtension\Helper\Data $helper
    ) {
        $this->quoteExtensionHelper = $quoteExtensionHelper;
        $this->postHelper = $postHelper;
        $this->helperConfig = $helperConfig;
        $this->helper = $helper;
    }

    /**
     * Add Add to quote Button
     *
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        $product = $subject->getSaleableItem();
        if($product && $product->getDisableButtonAddToQuote()){
            return $result;
        }
        if ($this->helper->isEnable() && $this->helper->isActiveRequest4Quote($product)) {
            $product->setIsActiveRequest4Quote(true);
        }
        $isEnableOtherPage = $this->helperConfig->isEnableOtherPage();
        if ($product->getIsActiveRequest4Quote()
            && $isEnableOtherPage
            && $product->getIsInCollection()
            && $product->isSaleable()
        ) {
            $additional = [];
            if (!$this->helper->validateQuantity()) {
                $additional = ['qty' => 1];
            }
            $postData = $this->postHelper->getPostData(
                $this->quoteExtensionHelper->getAddUrl($product, $additional),
                ['product' => $product->getEntityId(), 'quoteextension' => 1]
            );
            $isQuoteExtension = '<input type="hidden" name="quote_extension" class="' . $product->getId() . '_quote_extension"  value="' . $product->getId() . '">';
            $buttonTitle = $this->helperConfig->getOtherPageText()
                ? $this->helperConfig->getOtherPageText()
                : __('Add to Quote');
            $button = '<div class="quote_extension' . $product->getId() . ' quote-category">
            ' . $isQuoteExtension . '
            <button
                class="action toquote primary"
                type="submit" title="' . $buttonTitle . '"
                data-post=\'' . $postData . '\'"
                >
                    ' . $buttonTitle . '
                 </button>
            </div>
            <script type="text/x-magento-init">
                {
                    ".quote_extension' . $product->getId() . '": {
                        "addButtonCategory": {
                        }
                    }
                }
            </script>';
            return $result . $button;
        }

        return $result;
    }
}
