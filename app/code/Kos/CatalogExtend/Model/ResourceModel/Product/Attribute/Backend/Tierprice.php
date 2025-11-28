<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kos\CatalogExtend\Model\ResourceModel\Product\Attribute\Backend;

/**
 * Catalog product tier price backend attribute model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tierprice extends \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
{
    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        $columns = parent::_loadPriceDataColumns($columns);
        $columns['price_qty'] = 'qty';
        $columns['percentage_value'] = 'percentage_value';
        $columns['day_to_ship'] = 'day_to_ship';
        $columns['ask_price'] = 'ask_price';
        return $columns;
    }
}
