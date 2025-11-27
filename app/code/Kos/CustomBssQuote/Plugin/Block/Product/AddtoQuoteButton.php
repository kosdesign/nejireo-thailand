<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\CustomBssQuote\Plugin\Block\Product;

use Magento\Catalog\Block\Product\View as MagentoView;

/**
 * Class AddtoQuoteButton
 *
 * @package Bss\QuoteExtension\Plugin\Block\Product
 */
class AddtoQuoteButton
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\QuoteExtension\Helper\Admin\ConfigShow
     */
    protected $helperConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $helperCustom;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * AddtoQuoteButton constructor.
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig
     */
    public function __construct(
        \Kos\CustomBssQuote\Helper\MaxQtyHelper $helperCustom,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Bss\QuoteExtension\Helper\Admin\ConfigShow $helperConfig,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Url $customerUrl
    )
    {
        $this->helperCustom = $helperCustom;
        $this->helper = $helper;
        $this->helperConfig = $helperConfig;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->customerUrl = $customerUrl;
    }

    /**
     * Add AddtoQuote Button
     *
     * @param MagentoView $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        MagentoView $subject,
        $result
    )
    {
        $matchedNames = [
            'product.info.addtocart.additional',
            'product.info.addtocart',
            'product.info.addtocart.bundle'
        ];
        $product = $subject->getProduct();
        $qtyQuoteMax = $this->helperCustom->getValidateQuote($product);
        if (in_array($subject->getNameInLayout(), $matchedNames) && $product->getIsActiveRequest4QuoteProductPage()) {
            $buttonTitle = $this->helperConfig->getProductPageText()
                ? $this->helperConfig->getProductPageText()
                : __('Add to Quote');
            $pattern = '#<button([^>]*)product-addtocart-button([^*]*)<\/button>#';
            preg_match_all($pattern, $result, $_matches);
            $buttonCart = implode('', $_matches[0]);
            if ($product->getDisableAddToCart()) {
                $buttonCart = '';
            }
            $disable = "";
            if (!$this->isLoggedIn()) {
                $disable = 'disable';
            }
            $button = '<div class="box-tocart box-toquote simple quote_extension' . $product->getId() . '"><button type="button"
                            title="' . $buttonTitle . '"
                            class="action primary toquote ' . $disable . '"
                            id="product-addtoquote-button">
                            <span>' . $buttonTitle . '</span>
                        </button>';
            if (!$this->isLoggedIn()) {
                $button .= '<div class="popup-login">
                            <div class="content"> ' . __("Please %1 Sign In %2 to add to quote", "<a href='" . $this->getLoginUrl() . "'>", "</a>") . '</div>
                            </div>';
            }
            $button .= '</div><script type="text/x-magento-init">
                        {
                            "#product-addtoquote-button": {
                                "Kos_CustomBssQuote/js/catalog-add-to-quote": {
                                    "validateQty" : "' . $this->helper->validateQuantity() . '",
                                    "addToQuoteButtonTextDefault" : "'.$buttonTitle.'",
                                    "maxQtyQuote" : "'.$qtyQuoteMax.'"
                                }
                            }
                        }
                        </script>';

            $result = preg_replace($pattern, $buttonCart . $button, $result);
        }

        return $result;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }
}
