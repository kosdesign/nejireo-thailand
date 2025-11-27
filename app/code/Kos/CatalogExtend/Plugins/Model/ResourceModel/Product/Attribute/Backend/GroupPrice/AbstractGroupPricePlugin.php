<?php
namespace Kos\CatalogExtend\Plugins\Model\ResourceModel\Product\Attribute\Backend\GroupPrice;

class AbstractGroupPricePlugin
{
    public function afterGetSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice $subject,
        $result
    ) {
        //add day_to_ship column to select
        $result->columns('day_to_ship');

        return $result;
    }
}
