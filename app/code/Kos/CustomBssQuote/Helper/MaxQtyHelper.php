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
namespace Kos\CustomBssQuote\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class MaxQtyHelper
 *
 * @package Kos\CustomBssQuote\Helper
 */
class MaxQtyHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Path config maximum qty in quote cart
     */
    const PATH_MAX_QTY_QUOTE = 'bss_request4quote/request4quote_global/bss_max_quote_qty';

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperQuote;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Bss\QuoteExtension\Helper\Data $helperQuote
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Context $context,
        \Bss\QuoteExtension\Helper\Data $helperQuote,
        StockRegistryInterface $stockRegistry
    ){
        $this->stockRegistry = $stockRegistry;
        $this->helperQuote = $helperQuote;
        parent::__construct($context);
    }

    /**
     * Get value config maximum qty in quote cart
     *
     * @param int $store
     * @return float
     */
    public function maxQuoteQty($store = null)
    {
        $maxQty = (float) $this->scopeConfig->getValue(
            self::PATH_MAX_QTY_QUOTE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return empty($maxQty) ? 10000000 : $maxQty;
    }

    /**
     * Get value config maximum qty in quote cart
     *
     * @param int $store
     * @return float
     */
    public function getUseConfigQuoteQty($store = null)
    {
        return (float) $this->scopeConfig->getValue(
            self::PATH_MAX_QTY_QUOTE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $product
     * @return float|int
     */
    public function getValidateQuote($product)
    {
        $id = $product->getId();
        $stockItem = $this->stockRegistry->getStockItem($id);
        $qty = $stockItem->getBssMaxQtyQuote();
        $maxQtyQuote = empty($qty) ? 100000000 : $qty;
        $userConfig = $stockItem->getUseConfigBssMaximumQtyQuote();
        $applyConditions = $this->helperQuote->validateQuantity();
        $configMaxQty = $this->maxQuoteQty();
        if ($applyConditions) {
            $maxQty = $userConfig == '1' ? $configMaxQty : $maxQtyQuote;
        } else {
            $maxQty = $configMaxQty;
        }
        return $maxQty;
    }
}
