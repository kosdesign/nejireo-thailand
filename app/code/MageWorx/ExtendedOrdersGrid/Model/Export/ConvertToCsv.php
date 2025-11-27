<?php
namespace MageWorx\ExtendedOrdersGrid\Model\Export;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        if($component->getName()=='sales_order_invoice_grid') {
            $headers = ['ORDER N0.', 'DATE', 'HS CUSTOMER ID', 'PART NUMBER', 'QTY', 'PRICE', 'SHIPPING', 'DISCOUNT'];
            $stream->writeCsv($headers);
        }else{
            $stream->writeCsv($this->metadataProvider->getHeaders($component));
        }
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {
                $this->metadataProvider->convertDate($item, $component->getName());
                if($component->getName()=='sales_order_invoice_grid') {
                    $fields = ['order_increment_id','created_at','hs_customer_id','part_number','qty','price','shipping_and_handling','discount_amount'];
                }else{
                    $fields = $this->metadataProvider->getFields($component);
                }
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

}
