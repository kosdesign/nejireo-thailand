<?php
namespace Kos\ConfigurableProductGrid\Ui\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;

class ProductList extends ProductDataProvider
{
    protected $result = false;
    protected $registry;
    protected $productLoader;
    protected $request;
    protected $helper;
    protected $storeManager;
    protected $localeCurrency;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Kos\ConfigurableProductGrid\Helper\Data $helper,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->registry = $registry;
        $this->productLoader = $productLoader;
        $this->request = $request;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @return array|bool
     */
    public function getData()
    {
        // ignore function in Bss\QuoteExtension\Observer\ApplyAddToQuoteCollection
        $this->registry->register('ignore_request_quote', true);

        $product = $this->registry->registry('current_product');
        if (!$product) {
            $id = $this->request->getParam('id');
            $product = $this->productLoader->create()->load($id);
        }
        $filters = $this->request->getParam('filters', false);

        if ($product->getTypeId()==='configurable') {
            $ids = $product->getTypeInstance()->getUsedProductIds($product);
            $filterParams = [];
            $listAttributes = ['material', 'plating', 'diameter', 'length'];
            if (!$this->getCollection()->isLoaded()) {
                $this->getCollection()->addAttributeToSelect('*');
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $ids));
                $this->getCollection()->addFilterByRequiredOptions();

                if(empty($filters['auto_filter'])) {
                    foreach($this->request->getParams() as $attribute => $filter) {
                        if(in_array($attribute, $listAttributes)) {
                            $filter = explode(',', $filter);
                            $this->getCollection()->addFieldToFilter($attribute, array('in' => $filter));
                            $filterParams[$attribute] = $filter;
                        }
                    }
                }else {
                    $filterParams = false;
                }

                $this->getCollection()->addStoreFilter(
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );
                $this->getCollection()->load();
            }

            $items = $this->getCollection()->toArray();

            foreach ($this->getCollection() as $product) {
                $attr = $product->getResource()->getAttribute('material');
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($product->getMaterial());
                    if($optionText) {
                        $items[$product->getId()]['material'] = $optionText;
                    } else {
                        $items[$product->getId()]['material'] = '';
                    }
                }

                $attr = $product->getResource()->getAttribute('plating');
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($product->getPlating());
                    if($optionText) {
                        $items[$product->getId()]['plating'] = $optionText;
                    } else {
                        $items[$product->getId()]['plating'] = '';
                    }
                }

                $attr = $product->getResource()->getAttribute('diameter');
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($product->getDiameter());
                    if($optionText) {
                        $items[$product->getId()]['diameter'] = $optionText;
                    } else {
                        $items[$product->getId()]['diameter'] = '';
                    }
                }

                $attr = $product->getResource()->getAttribute('length');
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($product->getLength());
                    if($optionText) {
                        $items[$product->getId()]['length'] = $optionText;
                    } else {
                        $items[$product->getId()]['length'] = '';
                    }
                }

                $items[$product->getId()]['price'] = $this->formatPrice($product->getPrice());

                if ($product->getTierPrice()) {
                    $tierPrices = $product->getTierPrice();
                    $countTier = count($tierPrices);
                    $day_to_ship = "";
                    $tier_price = '';
                    $qty_old = '';

                    for ($i = 0; $i < $countTier; $i++) {
                        if (!empty($tierPrices[$i + 1])) {
                            $qty_old = (int)$tierPrices[$i]['price_qty'];
                            if($qty_old < ((int)$tierPrices[$i + 1]['price_qty'] - 1)) {
                                $tier_price = $tier_price . $qty_old . '-' . ((int)$tierPrices[$i + 1]['price_qty'] - 1) . ' : ' . $this->formatPrice($tierPrices[$i]['price']) . '</br>';
                            }else {
                                $tier_price = $tier_price . $qty_old . ' : ' . $this->formatPrice($tierPrices[$i]['price']) . '</br>';
                            }
                            $qty_old = (int)$tierPrices[$i + 1]['price_qty'];
                        } else {
				 if($qty_old == '') {
                                $qty_old = (int)$tierPrices[$i]['price_qty'];
                            }

                            $tier_price = $tier_price . $qty_old . '+ : ' . $this->formatPrice($tierPrices[$i]['price']);
                        }

                        if (empty($tierPrices[$i]['day_to_ship'])) {
                            $day = $product->getDayToShip();
                        } else {
                            $day = $tierPrices[$i]['day_to_ship'];
                        }
                        $day_to_ship = $day_to_ship . $day . '</br>';
                    }

                    $items[$product->getId()]['price'] = $tier_price;
                    $items[$product->getId()]['day_to_ship'] = $day_to_ship;
                }

            }
            $this->result = [
                'totalRecords' => $this->getCollection()->getSize(),
                'items' => array_values($items),
                'filterValue' => $filterParams
            ];

        } else {
            $this->result = [
                'totalRecords' => 0,
                'items' => [],
                'filterValue' => $filterParams
            ];
        }

        $this->registry->unregister('ignore_request_quote');

        if ($this->request->getParam('namespace', false)) {
            $this->result['filters'] = $this->getFilterOptions($this->getCollection()->getAllIds());
        }

        return $this->result;
    }

    /**
     * @param $price
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function formatPrice($price)
    {
        $store = $this->storeManager->getStore();
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        return $currency->toCurrency(sprintf("%f", $price));
    }

    /**
     * @param $productIds
     * @return array
     */
    private function getFilterOptions($productIds)
    {
        return [
            'diameter' => $this->helper->getOptionsArray('diameter', $productIds),
            'length' => $this->helper->getOptionsArray('length', $productIds),
            'material' => $this->helper->getOptionsArray('material', $productIds),
            'plating' => $this->helper->getOptionsArray('plating', $productIds),
        ];
    }
}
