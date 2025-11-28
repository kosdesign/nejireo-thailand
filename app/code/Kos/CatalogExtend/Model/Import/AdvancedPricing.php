<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kos\CatalogExtend\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;

class AdvancedPricing extends \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing
{
    const COL_DAY_TO_SHIP = 'day_to_ship';
    const COL_ASK_PRICE = 'price_ask';

    /**
     * Valid column names.
     *
     * @array
     */
    protected $validColumnNames = [
        self::COL_SKU,
        self::COL_TIER_PRICE_WEBSITE,
        self::COL_TIER_PRICE_CUSTOMER_GROUP,
        self::COL_TIER_PRICE_QTY,
        self::COL_TIER_PRICE,
        self::COL_TIER_PRICE_TYPE,
        self::COL_DAY_TO_SHIP,
        self::COL_ASK_PRICE
    ];

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Save and replace advanced prices
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected function saveAndReplaceAdvancedPrices()
    {
        $behavior = $this->getBehavior();
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cachedSkuToDelete = null;
        }
        $listSku = [];
        $tierPrices = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowSku = $rowData[self::COL_SKU];
                $listSku[] = $rowSku;
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    // Map price_ask (CSV column) to ask_price (DB column)
                    $askPriceValue = isset($rowData[self::COL_ASK_PRICE]) ? $rowData[self::COL_ASK_PRICE] : null;
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]
                        ),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_FIXED
                            ? $rowData[self::COL_TIER_PRICE] : 0,
                        'percentage_value' => $rowData[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_PERCENT
                            ? $rowData[self::COL_TIER_PRICE] : null,
                        'website_id' => $this->getWebSiteId($rowData[self::COL_TIER_PRICE_WEBSITE]),
                        'day_to_ship' => $rowData[self::COL_DAY_TO_SHIP],
                        'ask_price' => $askPriceValue, // DB column name
                    ];
                }
            }

            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->processCountExistingPrices($tierPrices, self::TABLE_TIER_PRICE)
                    ->processCountNewPrices($tierPrices);

                $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                if ($listSku) {
                    $this->setUpdatedAt($listSku);
                }
            }
        }

        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            if ($listSku) {
                $this->processCountNewPrices($tierPrices);
                if ($this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE)) {
                    $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                    $this->setUpdatedAt($listSku);
                }
            }
        }

        return $this;
    }

    /**
     * Save product prices.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     * @throws \Exception
     */
    protected function saveProductPrices(array $priceData, $table)
    {
        if ($priceData) {
            $tableName = $this->_resourceFactory->create()->getTable($table);
            $priceIn = [];
            $entityIds = [];
            $oldSkus = $this->retrieveOldSkus();
            foreach ($priceData as $sku => $priceRows) {
                if (isset($oldSkus[$sku])) {
                    $productId = $oldSkus[$sku];
                    foreach ($priceRows as $row) {
                        $row[$this->getProductEntityLinkField()] = $productId;
                        $priceIn[] = $row;
                        $entityIds[] = $productId;
                    }
                }
            }
            if ($priceIn) {
                $this->_connection->insertOnDuplicate(
                    $tableName,
                    $priceIn,
                    ['value', 'percentage_value', 'day_to_ship', 'ask_price']
                );
            }
        }
        return $this;
    }

    /**
     * Get product entity link field
     *
     * @return string
     * @throws \Exception
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
