<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product tier price backend attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Kos\CatalogExtend\Model\Product\Attribute\Backend;

/**
 * Backend model for Tierprice attribute
 */
class Tierprice extends \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice
{
    /**
     * @inheritdoc
     */
    protected function getAdditionalFields($objectArray)
    {
        $percentageValue = $this->getPercentage($objectArray);
        $dayToShipValue = $this->getDayToShip($objectArray);
        $askPriceValue = $this->getAskPrice($objectArray);
        return [
            'value' => $percentageValue ? null : $objectArray['price'],
            'percentage_value' => $percentageValue ?: null,
            'day_to_ship' => $dayToShipValue ? $dayToShipValue : null,
            'ask_price' => $askPriceValue ? $askPriceValue : null,
        ];
    }

    /**
     * Update Price values in DB
     *
     * Updates price values in DB from array comparing to old values. Returns bool if updated
     *
     * @param array $valuesToUpdate
     * @param array $oldValues
     * @return boolean
     */
    protected function updateValues(array $valuesToUpdate, array $oldValues)
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
            if ((!empty($value['value']) && $oldValues[$key]['price'] != $value['value'])
                || $this->getPercentage($oldValues[$key]) != $this->getPercentage($value)
                || $this->getDayToShip($oldValues[$key]) != $this->getDayToShip($value)
                || $this->getAskPrice($oldValues[$key]) != $this->getAskPrice($value)
            ) {
                $price = new \Magento\Framework\DataObject(
                    [
                        'value_id' => $oldValues[$key]['price_id'],
                        'value' => $value['value'],
                        'percentage_value' => $this->getPercentage($value),
                        'day_to_ship' => $this->getDayToShip($value),
                        'ask_price' => $this->getAskPrice($value)
                    ]
                );
                $this->_getResource()->savePriceData($price);
                $isChanged = true;
            }
        }
        return $isChanged;
    }

    /**
     * @inheritdoc
     */
    protected function modifyPriceData($object, $data)
    {
        /** @var \Magento\Catalog\Model\Product $object */
        $data = parent::modifyPriceData($object, $data);
        $price = $object->getPrice();
        foreach ($data as $key => $tierPrice) {
            $percentageValue = $this->getPercentage($tierPrice);
            if ($percentageValue) {
                $data[$key]['price'] = $price * (1 - $percentageValue / 100);
                $data[$key]['website_price'] = $data[$key]['price'];
            }
        }
        return $data;
    }

    /**
     * Check whether price has percentage value.
     *
     * @param array $priceRow
     * @return null
     */
    private function getPercentage($priceRow)
    {
        return isset($priceRow['percentage_value']) && is_numeric($priceRow['percentage_value'])
            ? $priceRow['percentage_value']
            : null;
    }

    /**
     * Check whether price has title value.
     *
     * @param $priceRow
     * @return null
     */
    private function getDayToShip($priceRow)
    {
        return isset($priceRow['day_to_ship'])
            ? $priceRow['day_to_ship']
            : null;
    }

        /**
     * Check whether price has ask price value.
     *
     * @param $priceRow
     * @return null
     */
    private function getAskPrice($priceRow)
    {
        return isset($priceRow['ask_price'])
            ? $priceRow['ask_price']
            : null;
    }
}
