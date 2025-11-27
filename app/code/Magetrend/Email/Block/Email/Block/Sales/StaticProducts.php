<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

class StaticProducts extends \Magetrend\Email\Block\Email\Block\Sales\RelatedProducts
{
    private $collection = null;

    public $productRepository;

    public $searchCriteriaBuilder;

    public $productStatus;

    public $registry;

    public function getItems()
    {
        if ($this->collection == null) {
            $this->collection = $this->getProductBySku();

            if ($this->registry->registry('mt_editor_edit_mode') == 1 && empty($this->collection)) {
                $this->collection = $this->getDemoProducts();
            }
        }

        return $this->collection;
    }

    public function getProductBySku()
    {
        $skuList = [
            $this->getSku(1) => null,
            $this->getSku(2) => null,
            $this->getSku(3) => null,
            $this->getSku(4) => null,
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', array_keys($skuList), 'in')
            ->setPageSize(4)
            ->setCurrentPage(1)
            ->create();
        $products = $this->productRepository->getList($searchCriteria);

        $relatedProducts = [];
        if ($products->getTotalCount() > 0) {
            $productCollection = $products->getItems();
            foreach ($productCollection as $product) {
                $skuList[$product->getSku()] = $product;
            }
        }
        $productList = [];
        foreach ($skuList as $product) {
            $productList[] = $product;
        }

        return $productList;
    }

    public function getSku($index)
    {
        $storeId = null;

        if ($order = $this->getOrder()) {
            $storeId = $order->getStoreId();
        }

        return $this->_scopeConfig->getValue(
            'mtemail/product_block/sku_'.$index,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

}
