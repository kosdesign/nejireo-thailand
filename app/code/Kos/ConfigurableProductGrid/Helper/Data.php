<?php
namespace Kos\ConfigurableProductGrid\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productLoader;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    protected $productOptionData = false;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productLoader
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->registry = $registry;
        $this->productLoader = $productLoader;
        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

        /**
     * @param $attribute
     * @param null $productIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOptionsArray($attribute, $productIds = null)
    {
        // ignore function in Bss\QuoteExtension\Observer\ApplyAddToQuoteCollection
        $this->registry->register('ignore_request_quote', true);
        if(!$productIds) {
            $key = 0;
        }else {
            $key = $this->jsonHelper->jsonEncode($productIds);
        }
        if(empty($this->productOptionData[$key])) {
            $product = $this->registry->registry('current_product');
            if (!$product) {
                $id = (int) $this->request->getParam('id');
                $product = $this->productLoader->create()->load($id);
            }

            $optionsData = [];
            if ($product->getTypeId() === 'configurable') {
                if (!$productIds) {
                    $productIds = $product->getTypeInstance()->getUsedProductIds($product);
                }

                $collection = $this->productCollectionFactory->create();
                // $collection->addAttributeToSelect($attribute);
                $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
                $collection->addFilterByRequiredOptions();
                $collection->addStoreFilter(
                    $this->storeManager->getStore()->getStoreId()
                );

                $joinAttributes = [
                    'diameter',
                    'length',
                    'material',
                    'plating'
                ];
                foreach($joinAttributes as $att) {
                    $collection->joinAttribute(
                        $att,
                        'catalog_product/'. $att,
                        'entity_id',
                        null,
                        'left',
                        $this->storeManager->getStore()->getStoreId()
                    );
                }

                $data = $collection->getData();

                $optionsData = [
                    'diameter' => array_column($data, 'diameter'),
                    'length' => array_column($data, 'length'),
                    'material' => array_column($data, 'material'),
                    'plating' => array_column($data, 'plating'),
                ];
            }

            $result = [];
            foreach($optionsData as $att => $data) {
                $attributeOption = $this->eavConfig->getAttribute('catalog_product', $att);
                $collectionOptions = $this->attrOptionCollectionFactory->create()->setPositionOrder(
                    'asc'
                )->setAttributeFilter(
                    $attributeOption->getSource()->getAttribute()->getId()
                )->setIdFilter(
                    $data
                )->setStoreFilter(
                    $this->storeManager->getStore()->getStoreId()
                )->load();

                $options = $collectionOptions->toOptionArray();
                $result[$att] = $options;
            }
            $this->productOptionData[$key] = $result;
        }
        $this->registry->unregister('ignore_request_quote');
        return $this->productOptionData[$key][$attribute] ? $this->productOptionData[$key][$attribute] : [];
    }
}
