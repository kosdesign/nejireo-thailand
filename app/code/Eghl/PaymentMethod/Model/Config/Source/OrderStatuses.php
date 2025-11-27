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

class OrderStatuses implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'pending_payment', 'label' => __('Pending Payment')], ['value' => 'processing', 'label' => __('Processing')], ['value' => 'complete', 'label' => __('Complete')], ['value' => 'closed', 'label' => __('Closed')], ['value' => 'canceled', 'label' => __('Canceled')], ['value' => 'holded', 'label' => __('Holded')], ['value' => 'payment_review', 'label' => __('Payment Review')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['pending_payment' => __('Pending Payment'), 'processing' => __('Processing'), 'complete' => __('Complete'), 'closed' => __('Closed'), 'canceled' => __('Canceled'), 'holded' => __('Holded'), 'payment_review' => __('Payment Review')];
    }
}
