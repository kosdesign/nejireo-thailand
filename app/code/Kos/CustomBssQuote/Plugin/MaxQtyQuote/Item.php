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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Kos\CustomBssQuote\Plugin\MaxQtyQuote;

/**
 * Class Item
 *
 * @package Kos\CustomBssQuote\Plugin\MaxQtyQuote
 */
class Item
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperQuote;

    /**
     * @var \Kos\CustomBssQuote\Helper\MaxQtyHelper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;


    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $quote;

    /**
     * Item constructor.
     * @param \Bss\QuoteExtension\Helper\Data $helperQuote
     * @param \Kos\CustomBssQuote\Helper\MaxQtyHelper $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Backend\Model\Session\Quote $quote
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helperQuote,
        \Kos\CustomBssQuote\Helper\MaxQtyHelper $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Session\Quote $quote
    ) {
        $this->quote = $quote;
        $this->request = $request;
        $this->helperQuote = $helperQuote;
        $this->helper = $helper;
    }

    /**
     * Get Max qty allowed in quote cart
     *
     * @param object $subject
     * @param float $result
     * @return float
     */
    public function afterGetMaxSaleQty($subject, $result)
    {
        $qty = $subject->getBssMaxQtyQuote();
        $maxQtyQuote = empty($qty) ? 100000000 : $qty;

        $userConfig = $subject->getUseConfigBssMaximumQtyQuote();
        $applyConditions = $this->helperQuote->validateQuantity();
        $configMaxQty = $this->helper->maxQuoteQty();
        if ($applyConditions) {
            $maxQty = $userConfig == '1' ? $configMaxQty : (float)$maxQtyQuote;
        } else {
            $maxQty = $configMaxQty;
        }
        $action = $this->request->getFullActionName();
        $module = $this->request->getModuleName();
        if ($action == '__' || $module == 'quoteextension' || $module == 'bss_quote_extension' ) {
            return $maxQty;
        }
        $quoteExtension = $this->quote->getQuote();
        if (($action == 'sales_order_create_index' || $action == 'sales_order_create_loadBlock')
            && $this->isQuoteExtension($quoteExtension)) {
            return  $maxQty;
        }

        return $result;
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
