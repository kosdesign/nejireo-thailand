<?php
/**
 * Copyright � 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Payment URL config value selection
 *
 */
namespace Eghl\PaymentMethod\Model\Config\Source;

class CurrencyType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'Base', 'label' => __('Base Currency')], ['value' => 'Display', 'label' => __('Display Currency')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['Base' => __('Base Currency'), 'Display' => __('Display Currency')];
    }
}
