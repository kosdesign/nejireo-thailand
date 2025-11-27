<?php

namespace Kos\BssOneStepCheckout\Plugin\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductRepository as ProductRepository;

class ConfigProviderPlugin extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {

        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            $product = $this->productRepository->getById($quoteItem->getProduct()->getId());
            if ($quoteItem->getChildren() && $product->getTypeId() == 'configurable') {
                foreach ($quoteItem->getChildren() as $child) {
                    $product = $this->productRepository->getById($child->getProductId());
                    $result['quoteItemData'][$index]['name'] = $child->getName();
                    $result['quoteItemData'][$index]['material'] = $product->getResource()->getAttribute('material')->getFrontend()->getValue($product);
                    $result['quoteItemData'][$index]['part_number'] = $product->getResource()->getAttribute('part_number')->getFrontend()->getValue($product);
                    $result['quoteItemData'][$index]['plating'] = $product->getResource()->getAttribute('plating')->getFrontend()->getValue($product);
                    $result['quoteItemData'][$index]['diameter'] = $product->getResource()->getAttribute('diameter')->getFrontend()->getValue($product);
                    $result['quoteItemData'][$index]['length'] = $product->getResource()->getAttribute('length')->getFrontend()->getValue($product);
                }
            } else {
                $result['quoteItemData'][$index]['material'] = $product->getResource()->getAttribute('material')->getFrontend()->getValue($product);
                $result['quoteItemData'][$index]['part_number'] = $product->getResource()->getAttribute('part_number')->getFrontend()->getValue($product);
                $result['quoteItemData'][$index]['plating'] = $product->getResource()->getAttribute('plating')->getFrontend()->getValue($product);
                $result['quoteItemData'][$index]['diameter'] = $product->getResource()->getAttribute('diameter')->getFrontend()->getValue($product);
                $result['quoteItemData'][$index]['length'] = $product->getResource()->getAttribute('length')->getFrontend()->getValue($product);
            }
        }
        return $result;
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
}