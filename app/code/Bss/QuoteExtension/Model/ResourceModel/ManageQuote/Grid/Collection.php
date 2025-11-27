<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\ResourceModel\ManageQuote\Grid;

use Bss\QuoteExtension\Helper\Data as HelperData;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 *
 * @package Bss\QuoteExtension\Model\ResourceModel\ManageQuote\Grid
 */
class Collection extends \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\Collection implements SearchResultInterface
{
    /**
     * @var Attribute
     */
    protected $eavAttribute;
    /**
     * @var
     */
    protected $helperData;
    /**
     * Main Table
     *
     * @var string $mainTable
     */
    protected $mainTable;

    /**
     * Resource Model
     *
     * @var string $resourceModel
     */
    protected $resourceModel;

    /**
     * Aggregations
     * @var \Magento\Framework\Search\AggregationInterface
     */
    private $aggregations;

    /**
     * Collection constructor.
     * @param Attribute $eavAttribute
     * @param HelperData $helperData
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        Registry $registry,
        Attribute $eavAttribute,
        HelperData $helperData,
        EntityFactoryInterface
        $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->mainTable = $mainTable;
        $this->resourceModel = $resourceModel;
        parent::__construct($registry, $helperData, $entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }
    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Bss\QuoteExtension\Model\ResourceModel\ManageQuote::class
        );
    }
    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['customer_grid_flat' => $this->getTable('customer_grid_flat')],
            'main_table.customer_id = customer_grid_flat.entity_id',
            ["customer_name"=> "customer_grid_flat.name"]
        );
        $this->addFilterToMap('customer_name', 'customer_grid_flat.name');
        if ($this->helperData->isEnableCompanyAccount()) {
            $attributeId = $this->eavAttribute->getIdByCode("customer", "bss_is_company_account");
            if ($attributeId) {
                $this->getSelect()->joinLeft(
                    ['customer_entity_int' => $this->getTable('customer_entity_int')],
                    'main_table.customer_id = customer_entity_int.entity_id AND customer_entity_int.attribute_id= ' . $attributeId,
                    ["bss_is_company_account" => "customer_entity_int.value"]
                );
            }
        }
    }
    /**
     * Get Aggregations
     *
     * @return \Magento\Framework\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     *
     * @param \Magento\Framework\Search\AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     *
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param array $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
