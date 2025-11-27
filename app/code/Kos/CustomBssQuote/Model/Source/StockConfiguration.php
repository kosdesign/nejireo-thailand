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
 * @package    Bss_MinQtyCP
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Kos\CustomBssQuote\Model\Source;

use Magento\Framework\Data\ValueSourceInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Class StockConfiguration
 *
 * @package Kos\CustomBssQuote\Model\Source
 */
class StockConfiguration implements ValueSourceInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Kos\CustomBssQuote\Helper\MaxQtyHelper
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * StockConfiguration constructor.
     * @param StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Kos\CustomBssQuote\Helper\MaxQtyHelper $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\App\Request\Http $request,
        \Kos\CustomBssQuote\Helper\MaxQtyHelper $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->request = $request;
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Get value max qty allowed in quote
     *
     * @param string $name
     * @return float|int|mixed
     */
    public function getValue($name)
    {
        try {
            $productStock = $this->getStockItem();
            $useConfig = $productStock->getData('use_config_bss_maximum_qty_quote');

            if ($useConfig) {
                $value = $this->helper->getUseConfigQuoteQty();
                $value = $value != 0 ? $value : null;
            } else {
                $value = $productStock->getData($name);
            }
        } catch (\Exception $e) {
            $value = 0;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Get product
     *
     * @return mixed
     */
    protected function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Get stock item
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    protected function getStockItem()
    {
        return $this->stockRegistry->getStockItem(
            $this->getProduct()->getId(),
            $this->getProduct()->getStore()->getWebsiteId()
        );
    }
}
