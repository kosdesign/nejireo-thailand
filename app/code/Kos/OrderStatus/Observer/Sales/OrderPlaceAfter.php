<?php
namespace Kos\OrderStatus\Observer\Sales;

/**
 * Class OrderPlaceAfter
 *
 * @package Kos\OrderStatus\Observer\Sales
 */
class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $groupRepository;
    
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->groupRepository = $groupRepository;
    }

    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $statuscode = $order->getStatus();
        $group = "";
        if($order->getCustomerGroupId()) {
            $group = $this->getGroupName($order->getCustomerGroupId());
        }
        if($group == "B2B" && $statuscode == "pending") {
            $order->setState('new')->setStatus('order');
            $order->save();
        }
    }

    public function getGroupName($groupId){
        $group = $this->groupRepository->getById($groupId);
        return $group->getCode();
    }
}

