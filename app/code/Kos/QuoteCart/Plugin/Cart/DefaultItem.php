<?php

namespace Kos\QuoteCart\Plugin\Cart;

use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    /**
     * @param $subject
     * @param \Closure $proceed
     * @param Item $item
     * @return mixed
     */
    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $product = $item->getProduct();
        if ($product->getTypeId() == 'configurable') {
            $data['configure_url'] = $product->getProductUrl();
        }
        return $data;
    }
}