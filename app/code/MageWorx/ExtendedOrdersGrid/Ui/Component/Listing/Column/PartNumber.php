<?php
namespace MageWorx\ExtendedOrdersGrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\InvoiceRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class PartNumber extends Column
{

    protected $_invoiceRepository;
    protected $_searchCriteria;
    protected $_customfactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [], array $data = [])
    {
        $this->_invoiceRepository = $invoiceRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('sales_invoice_item');
            $productObj = $objectManager->get('Magento\Catalog\Model\Product');

            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->_invoiceRepository->get($item['entity_id']);
                $productCollection = $order->getItemsCollection();
                $partNumbers = [];
               foreach ($productCollection as $product){
                   if($product['base_price']!=0.0000){
                       $product = $productObj->loadbyAttribute('sku',$product->getSku());
                       $partNumbers[] = $product->getPartNumber();
                       $PN = $product->getPartNumber();
                       $sql = "UPDATE " . $tableName . " SET part_number = '$PN' WHERE parent_id = " .$item['entity_id']. " AND product_id = ".$product->getId();
                       $connection->query($sql);
                   }
               }
                $item[$this->getData('name')] = implode(',',$partNumbers);
            }
        }
        return $dataSource;
    }
}
