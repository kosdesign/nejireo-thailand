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

namespace Bss\QuoteExtension\Block\QuoteExtension\Email\ViewModel;

/**
 * Class Helper
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension\Email\ViewModel
 */
class Helper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\QuoteExtension\Helper\HidePriceEmail
     */
    protected $hidePriceEmail;

    /**
     * Helper constructor.
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Bss\QuoteExtension\Helper\HidePriceEmail $hidePriceEmail
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helper,
        \Bss\QuoteExtension\Helper\HidePriceEmail $hidePriceEmail
    ) {
        $this->helper = $helper;
        $this->hidePriceEmail = $hidePriceEmail;
    }

    /**
     * @return \Bss\QuoteExtension\Helper\Data
     */
    public function getModuleHelper()
    {
        return $this->helper;
    }

    /**
     * Check visible of price
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function canShowPrice($item, $quote = null)
    {
        if ($item->getProductType() == 'configurable') {
            $parentProductId = $item->getProductId();
            $childProductSku = $item->getSku();
            $canShowPrice = $this->hidePriceEmail
                ->canShowPrice($parentProductId, $childProductSku, false, null, $quote);
        } else {
            $canShowPrice = $this->hidePriceEmail->canShowPrice($item->getProductId(), false, false, null, $quote);
        }
        return $canShowPrice;
    }
}
