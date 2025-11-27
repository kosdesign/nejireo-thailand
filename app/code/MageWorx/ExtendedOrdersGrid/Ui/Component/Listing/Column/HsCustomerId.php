<?php
namespace MageWorx\ExtendedOrdersGrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\InvoiceRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Customer\Api\CustomerRepositoryInterface;


class HsCustomerId extends Column
{

    protected $_invoiceRepository;
    protected $_searchCriteria;
    protected $_customerRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [], array $data = [])
    {
        $this->_invoiceRepository = $invoiceRepository;
        $this->_customerRepository = $customerRepository;
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
            $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
            $websiteId = $storeManager->getStore()->getWebsiteId();

            foreach ($dataSource['data']['items'] as & $item) {

                $customerHsID = 'NULL';
                if(isset($item['customer_email'])){
                    if($item['customer_group_id']!=0){

                        $customerRepo = $this->_customerRepository->get($item['customer_email'], $websiteId);
                        if($customerRepo && $customerRepo->getCustomAttribute('hs_customer_id')){
                            $customerHsID = $customerRepo->getCustomAttribute('hs_customer_id')->getValue();
                        }
                    }
                }
                $item[$this->getData('name')] = $customerHsID;
                $sql = "UPDATE " . $tableName . " SET hs_customer_id = '$customerHsID' WHERE parent_id = " .$item['entity_id'];
                $connection->query($sql);
            }
        }
        return $dataSource;
    }
}
