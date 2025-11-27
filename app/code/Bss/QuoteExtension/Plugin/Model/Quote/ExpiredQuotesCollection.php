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
namespace Bss\QuoteExtension\Plugin\Model\Quote;

use Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection as ExpiredQuote;

/**
 * Class ExpiredQuotesCollection
 *
 * @package Bss\QuoteExtension\Plugin\Model\Quote
 */
class ExpiredQuotesCollection
{
    /**
     * Get expired quote
     *
     * @param ExpiredQuote $subject
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $quotes
     * @return mixed
     */
    public function afterGetExpiredQuotes(
        ExpiredQuote $subject,
        $quotes
    ) {
        $quotes->addFieldToFilter('quote_extension', ['null' => true]);
        return $quotes;
    }
}
