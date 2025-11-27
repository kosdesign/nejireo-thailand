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
namespace Kos\CustomBssQuote\Model;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class ProductSaveAfter
 *
 * @package Bss\CustomQuote\Model
 */
class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->request = $request;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Save value config max qty allowed quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $params = $this->request->getParams();
            $minQtyCp = $params['product']['qty_data']['bss_max_qty_quote'];
            $useConfigMinQtyCp = $params['product']['qty_data']['use_config_bss_maximum_qty_quote'];
            $product = $observer->getEvent()->getProduct();
            if ($product->getStockData() === null) {
                return;
            }
            $stockItemData = $product->getStockData();
            $stockItemData['product_id'] = $product->getId();
            if (!isset($stockItemData['website_id'])) {
                $stockItemData['website_id'] = $this->stockConfiguration->getDefaultScopeId();
            }
            $stockItem = $this->stockRegistry->getStockItem($stockItemData['product_id'], $stockItemData['website_id']);
            $stockItemData['bss_max_qty_quote'] = empty($minQtyCp) ? null : $minQtyCp;
            $stockItemData['use_config_bss_maximum_qty_quote'] = $useConfigMinQtyCp;
            $stockItem->addData($stockItemData);
            $this->stockItemRepository->save($stockItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('The stock item was unable to be saved. Please try again.'), $exception);
        }
    }
}
