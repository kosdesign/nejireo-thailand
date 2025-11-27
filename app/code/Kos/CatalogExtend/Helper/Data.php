<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\CatalogExtend\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Html output
 */
class Data extends AbstractHelper
{
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Check whether price has title value.
     *
     * @param $priceRow
     * @return null
     */
    public function getDayToShip($priceRow)
    {
        return isset($priceRow['day_to_ship'])
            ? $priceRow['day_to_ship']
            : null;
    }

    public function getOptionsFilterAttribute($attribute)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attribute);
        $options = $attribute->getSource()->getAllOptions();
        $optionsArray = [];
        foreach ($options as $option) {
            $optionsArray[] = ['label' => $option['label'], 'value' => $option['value']];
        }

        return $optionsArray;
    }
}
