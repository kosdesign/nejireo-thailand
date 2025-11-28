<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kos\CatalogExtend\Model\Product\Attribute\Backend\TierPrice;

use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\AbstractHandler;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;
use Kos\CatalogExtend\Helper\Data as CatalogKosHelper;

/**
 * Process tier price data for handled new product
 */
class SaveHandler extends AbstractHandler
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPoll;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    private $tierPriceResource;

    /**
     * @var CatalogKosHelper
     */
    protected $catalogKosHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $tierPriceResource
     * @param CatalogKosHelper $catalogKosHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $attributeRepository,
        GroupManagementInterface $groupManagement,
        MetadataPool $metadataPool,
        Tierprice $tierPriceResource,
        CatalogKosHelper $catalogKosHelper
    ) {
        parent::__construct($groupManagement);

        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->metadataPoll = $metadataPool;
        $this->catalogKosHelper = $catalogKosHelper;
        $this->tierPriceResource = $tierPriceResource;
    }

    /**
     * Set tier price data for product entity
     *
     * @param object $entity
     * @param array $arguments
     * @return bool|object
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $attribute = $this->attributeRepository->get('tier_price');
        $priceRows = $entity->getData($attribute->getName());
        if (null !== $priceRows) {
            if (!is_array($priceRows)) {
                throw new \Magento\Framework\Exception\InputException(
                    __('Tier prices data should be array, but actually other type is received')
                );
            }
            $websiteId = $this->storeManager->getStore($entity->getStoreId())->getWebsiteId();
            $isGlobal = $attribute->isScopeGlobal() || $websiteId === 0;
            $identifierField = $this->metadataPoll->getMetadata(ProductInterface::class)->getLinkField();
            $priceRows = array_filter($priceRows);
            $productId = (int) $entity->getData($identifierField);

            // prepare and save data
            foreach ($priceRows as $data) {
                $isPriceWebsiteGlobal = (int)$data['website_id'] === 0;
                if ($isGlobal === $isPriceWebsiteGlobal
                    || !empty($data['price_qty'])
                    || isset($data['cust_group'])
                ) {
                    $tierPrice = $this->prepareTierPrice($data);
                    $price = new \Magento\Framework\DataObject($tierPrice);
                    $price->setData(
                        $identifierField,
                        $productId
                    );
                    $this->tierPriceResource->savePriceData($price);
                    $valueChangedKey = $attribute->getName() . '_changed';
                    $entity->setData($valueChangedKey, 1);
                }
            }
        }

        return $entity;
    }

    /**
     * Get additional tier price fields.
     *
     * @param array $objectArray
     * @return array
     */
    protected function getAdditionalFields(array $objectArray): array
    {
        $percentageValue = $this->getPercentage($objectArray);
        $dayToShipValue = $this->catalogKosHelper->getDayToShip($objectArray);
        $askPriceValue = $this->catalogKosHelper->getAskPrice($objectArray);
        return [
            'value' => $percentageValue ? null : $objectArray['price'],
            'percentage_value' => $percentageValue ?: null,
            'day_to_ship' => $dayToShipValue ? $dayToShipValue : null,
            'ask_price' => $askPriceValue ? $askPriceValue : null,
        ];
    }
}
