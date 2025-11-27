<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Kos\QuoteCart\Helper;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product;

/**
 * Kos QuoteCart data helper
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected  $configurableProductType;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductType
     */
    public function __construct(
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductType,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->configurableProductType = $configurableProductType;
        $this->objectManager = $objectmanager;
    }

    /**
     * @param $price
     * @return mixed
     */
    public function formatPrice($price)
    {
        $store = $this->storeManager->getStore();
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        return $currency->toCurrency(sprintf("%f", $price));
    }

    /**
     * @param $id
     * @return bool
     */
    public function getProductParentByChild($id)
    {
        $product = $this->configurableProductType->getParentIdsByChild($id);
        if ($product) {
            return $this->objectManager->create('Magento\Catalog\Model\Product')->load($product);
        }

        return false;
    }

    /**
     * @param $product
     * @param $qty
     * @return string
     */
    public function getDaytoShip($product, $qty)
    {
        $dayToShip = "";
        if ($product->getTierPrice()) {
            $tierPrices = $product->getTierPrice();
            $countTier = count($tierPrices);
            $qtyMin = '';
            $qtyMax = '';
            for ($i = 0; $i < $countTier; $i++) {
                if (!empty($tierPrices[$i + 1])) {
                    $qtyMin = (int)$tierPrices[$i]['price_qty'];
                    $qtyMax = (int)$tierPrices[$i + 1]['price_qty'];
                    for ($j = $qtyMin; $j < $qtyMax; $j++){
                        if($j == $qty){
                            if (empty($tierPrices[$i]['day_to_ship'])) {
                                $day = $product->getDayToShip();
                            } else {
                                $day = $tierPrices[$i]['day_to_ship'];
                            }
                            $dayToShip = $dayToShip . $day;
                        }
                    }
                } else {
                    if($qty >= $qtyMax) {
                        if (empty($tierPrices[$i]['day_to_ship'])) {
                            $day = $product->getDayToShip();
                        } else {
                            $day = $tierPrices[$i]['day_to_ship'];
                        }
                        $dayToShip = $dayToShip . $day;
                    }
                    if($qty < $qtyMin) {
                        $day = $product->getDayToShip();
                        $dayToShip = $dayToShip . $day;
                    }
                }
            }
        } else {
            $dayToShip = $product->getDayToShip();
        }

        return $dayToShip;
    }

    /**
     * Get item configurable child product.
     *
     * @param ItemInterface $item
     * @return Product | null
     */
    public function getChildProduct(ItemInterface $item): ?Product
    {
        /** @var \Magento\Quote\Model\Quote\Item\Option $option */
        $option = $item->getOptionByCode('simple_product');
        return $option ? $option->getProduct() : null;
    }


}
