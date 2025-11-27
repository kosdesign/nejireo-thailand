<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\Elasticsearch\Elasticsearch5\SearchAdapter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use \Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Request\Http;

class Adapter extends \Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Adapter
{
    /**
     * @var \Magento\Elasticsearch\SearchAdapter\QueryContainerFactory
     */
    protected $queryContainerFactory;

    /**
     * Empty response from Elasticsearch.
     *
     * @var array
     */
    protected static $emptyRawResponse = [
        "hits" =>
            [
                "hits" => []
            ],
        "aggregations" =>
            [
                "price_bucket" => [],
                "category_bucket" =>
                    [
                        "buckets" => []

                    ]
            ]
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductFactory
     */
    protected $productLoader;

    /**
     * @var Http
     */
    protected $request;

    protected $storeManager;
    protected  $productCollectionFactory;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param \Magento\Elasticsearch\SearchAdapter\QueryContainerFactory $queryContainerFactory
     * @param LoggerInterface $logger
     * @param ProductFactory $productLoader
     * @param Http $request
     */

    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        \Magento\Elasticsearch\SearchAdapter\QueryContainerFactory $queryContainerFactory,
        LoggerInterface $logger = null,
        ProductFactory $productLoader,
        Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {

        parent::__construct($connectionManager, $mapper, $responseFactory, $aggregationBuilder, $queryContainerFactory, $logger);
        $this->queryContainerFactory = $queryContainerFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
        $this->productLoader = $productLoader;
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;

    }

    /**
     * Custom Search query
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        $client = $this->connectionManager->getConnection();
        $aggregationBuilder = $this->aggregationBuilder;
        $query = $this->mapper->buildQuery($request);
        $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $query]));

        try {
            $rawResponse = $client->query($query);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            // return empty search result in case an exception is thrown from Elasticsearch
            $rawResponse = self::$emptyRawResponse;
        }

        $total = isset($rawResponse['hits']['total']) ? $rawResponse['hits']['total'] : 0;
        $ids = [];
        foreach ($rawResponse['hits']['hits'] as $key => $raw) {
            $ids[] = $raw['_id'];
        }
        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        $collection->addFieldToFilter('type_id', 'configurable');
        $removeId = [];
        foreach ($collection as $product) {
            $productIds = $product->getTypeInstance()->getUsedProductIds($product);
            $collectionChild = $this->_productCollectionFactory->create();
            $collectionChild->addFieldToFilter('entity_id', array('in' => $productIds));
            if(isset($this->request->getParams()['material'])) {
                $collectionChild->addFieldToFilter('material', $this->request->getParams()['material']);
            }
            if(isset($this->request->getParams()['plating'])) {
                $collectionChild->addFieldToFilter('plating', $this->request->getParams()['plating']);
            }
            if(isset($this->request->getParams()['diameter'])) {
                $collectionChild->addFieldToFilter('diameter', $this->request->getParams()['diameter']);
            }
            if(isset($this->request->getParams()['length'])) {
                $collectionChild->addFieldToFilter('length', $this->request->getParams()['length']);
            }
            $collectionChild->addStoreFilter(
                $this->_storeManager->getStore()->getStoreId()
            );

            if(count($collectionChild) == 0) {
                $removeId[] = $product->getId();
            }
        }
        if(!empty($removeId)) {
            foreach ($rawResponse['hits']['hits'] as $key => $raw) {
                if (in_array($raw['_id'], $removeId)) {
                    unset($rawResponse['hits']['hits'][$key]);
                    $total--;
                }
            }
        }

        $rawDocuments = isset($rawResponse['hits']['hits']) ? $rawResponse['hits']['hits'] : [];

        $queryResponse = $this->responseFactory->create(
            [
                'documents' => $rawDocuments,
                'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                'total' => $total
            ]
        );
        return $queryResponse;
    }
}
