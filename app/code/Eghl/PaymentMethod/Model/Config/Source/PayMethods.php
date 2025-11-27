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

class PayMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'ANY', 'label' => __('All Payment Methods')], ['value' => 'CC', 'label' => __('Credit Card')], ['value' => 'DD', 'label' => __('Direct Debit')], ['value' => 'WA', 'label' => __('e-Wallet')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['ANY' => __('All Payment Methods'), 'CC' => __('Credit Card'), 'DD' => __('Direct Debit'), 'WA' => __('e-Wallet')];
    }
}
