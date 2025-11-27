<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ExtendedOrdersGrid\Plugin;

use Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection as OrderGridCollection;

/**
 * Class AddDataToOrdersGrid
 */
class AddDataToOrdersGrid
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AddDataToOrdersGrid constructor.
     *
     * @param \Psr\Log\LoggerInterface $customLogger
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $customLogger,
        array $data = []
    ) {
        $this->logger = $customLogger;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param OrderGridCollection $collection
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_invoice_grid_data_source') {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_invoice_grid')) {
            try {
                $this->addCustomColumns($collection);
            } catch (\Zend_Db_Select_Exception $selectException) {
                // Do nothing in that case
                $this->logger->log(100, $selectException);
            }
        }
        return $collection;
    }

    private function addCustomColumns(OrderGridCollection $collection): OrderGridCollection
    {
        $invoiceItemsTableName = $collection->getResource()->getTable('sales_invoice_item');
        $itemsTableSelectGrouped = $collection->getConnection()->select();
        $itemsTableSelectGrouped->from(
            $invoiceItemsTableName,
            [
                'price' => new \Zend_Db_Expr('GROUP_CONCAT(ROUND(price,2) SEPARATOR \', \')'),
                'qty' => new \Zend_Db_Expr('GROUP_CONCAT(ROUND(qty,0) SEPARATOR \', \')'),
                'discount_amount'     => new \Zend_Db_Expr('GROUP_CONCAT(ROUND(discount_amount,0) SEPARATOR \', \')'),
                'hs_customer_id' => 'hs_customer_id',
                'part_number' => new \Zend_Db_Expr('GROUP_CONCAT(part_number SEPARATOR \', \')'),
                'parent_id' => 'parent_id'
            ]
        )->where('part_number IS NOT NULL');
        $itemsTableSelectGrouped->group('parent_id');
        $collection->getSelect()
            ->joinLeft(
                ['soi' => $itemsTableSelectGrouped],
                'soi.parent_id = main_table.entity_id',
                ['qty','price','discount_amount','hs_customer_id','part_number']
            );

        return $collection;
    }

}
