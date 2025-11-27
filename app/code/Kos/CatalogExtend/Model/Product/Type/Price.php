<?php
namespace Kos\CatalogExtend\Model\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;

/**
 * Product type price model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Sets list of product tier prices
     *
     * @param Product $product
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterface[] $tierPrices
     * @return $this
     */
    public function setTierPrices($product, array $tierPrices = null)
    {
        // null array means leave everything as is
        if ($tierPrices === null) {
            return $this;
        }

        $allGroupsId = $this->getAllCustomerGroupsId();
        $websiteId = $this->getWebsiteForPriceScope();

        // build the new array of tier prices
        $prices = [];
        foreach ($tierPrices as $price) {
            $extensionAttributes = $price->getExtensionAttributes();
            $priceWebsiteId = $websiteId;
            $priceDayToShip = "";
            if (isset($extensionAttributes) && is_numeric($extensionAttributes->getWebsiteId())) {
                $priceWebsiteId = (string)$extensionAttributes->getWebsiteId();
            }
            if (isset($extensionAttributes) && $extensionAttributes->getDayToShip()) {
                $priceDayToShip = (string)$extensionAttributes->getDayToShip();
            }
            $prices[] = [
                'website_id' => $priceWebsiteId,
                'cust_group' => $price->getCustomerGroupId(),
                'website_price' => $price->getValue(),
                'price' => $price->getValue(),
                'all_groups' => ($price->getCustomerGroupId() == $allGroupsId),
                'price_qty' => $price->getQty(),
                'percentage_value' => $extensionAttributes ? $extensionAttributes->getPercentageValue() : null,
                'day_to_ship' => $priceDayToShip
            ];
        }
        $product->setData('tier_price', $prices);

        return $this;
    }

    /**
     * Gets list of product tier prices
     *
     * @param Product $product
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]
     */
    public function getTierPrices($product)
    {
        $prices = [];
        $tierPrices = $this->getExistingPrices($product, 'tier_price');
        $tierPriceExtensionFactory = ObjectManager::getInstance()->get(ProductTierPriceExtensionFactory::class);
        foreach ($tierPrices as $price) {
            /** @var \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice */
            $tierPrice = $this->tierPriceFactory->create()
                ->setExtensionAttributes($tierPriceExtensionFactory->create());
            $tierPrice->setCustomerGroupId($price['cust_group']);
            if (array_key_exists('website_price', $price)) {
                $value = $price['website_price'];
            } else {
                $value = $price['price'];
            }
            $tierPrice->setValue($value);
            $tierPrice->setQty($price['price_qty']);
            $tierPrice->setDayToShip($price['day_to_ship']);
            if (isset($price['percentage_value'])) {
                $tierPrice->getExtensionAttributes()->setPercentageValue($price['percentage_value']);
            }

            if (isset($price['day_to_ship'])) {
                $tierPrice->getExtensionAttributes()->setDayToShip($price['day_to_ship']);
            }
            $websiteId = isset($price['website_id']) ? $price['website_id'] : $this->getWebsiteForPriceScope();
            $tierPrice->getExtensionAttributes()->setWebsiteId($websiteId);
            $prices[] = $tierPrice;
        }
        return $prices;
    }
}
