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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Search;

/**
 * Class Grid
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Search
 */
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'bss_quote_extension/*/loadBlock',
            ['block' => 'search_grid', '_current' => true, 'collapse' => null]
        );
    }
}
