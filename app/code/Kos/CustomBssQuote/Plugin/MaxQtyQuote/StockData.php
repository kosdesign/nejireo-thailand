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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Kos\CustomBssQuote\Plugin\MaxQtyQuote;

use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Class StockData
 *
 * @package Kos\CustomBssQuote\Plugin\MaxQtyQuote
 */
class StockData
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * StockData constructor.
     * @param LocatorInterface $locator
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        LocatorInterface $locator,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->locator = $locator;
        $this->stockItemRepository = $stockItemRepository;
        $this->coreRegistry = $coreRegistry;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param \Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\StockData $subject
     * @param $meta
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyMeta(
        \Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\StockData $subject,
        $meta
    ) {
        $config = [];
        $productStock = $this->getStockItem();
        $useConfig = $productStock->getData('use_config_bss_maximum_qty_quote');

        $config['children']['use_config_bss_maximum_qty_quote']['arguments']['data']['config'] = [
            'value' => $useConfig
        ];
        $meta['advanced_inventory_modal'] = [
            'children' => [
                'stock_data' => [
                    'children' => [
                        'container_max_qty_quote' => $config
                    ],
                ],
            ],
        ];
        return $meta;
    }

    /**
     * Get Product
     *
     * @return mixed|null
     */
    protected function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Get Stock Item
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
