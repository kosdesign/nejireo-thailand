<?php
namespace Eghl\PaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProcessShipment implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        
		$order->setStatus('complete');
		$order->setState('complete');
		$order->addStatusHistoryComment('Shipment Done', false);
    }
}