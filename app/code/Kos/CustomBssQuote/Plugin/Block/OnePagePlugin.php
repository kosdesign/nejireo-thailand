<?php
namespace Kos\CustomBssQuote\Plugin\Block;

use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Helper\QuoteExtension\Address;
use Magento\Catalog\Model\ProductRepository as ProductRepository;
/**
* Class Address
*
* @package Kos\CustomBssQuote\Plugin\Block
*/
class OnePagePlugin
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Address
     */
    protected $helperAddress;

    /**
     * OnePagePlugin constructor.
     * @param Data $helper
     * @param Address $helperAddress
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Data $helper,
        Address $helperAddress,
        ProductRepository $productRepository
    ) {
        $this->helper = $helper;
        $this->helperAddress = $helperAddress;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetCheckoutConfig($subject, $result)
    {
        $quote = $subject->getQuoteExtension();
        $token = $subject->getQuoteExtensionToken();
        $manageQuote = $subject->getManageQuoteExtension();
        if ($quote && $token && $manageQuote) {
            $result['quoteItemData'] = $this->getQuoteItemData($quote);
            $result['isQuoteCheckout'] = true;
        }
        return $result;
    }

    /**
     * @param $quote
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuoteItemData($quote)
    {
        $quoteItemData = [];
        $quoteId = $quote->getId();
        if ($quoteId) {
            $quoteItems = $this->helperAddress->getListItemsById($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                $quoteItemData[$index] = $quoteItem->toArray();
                $product = $this->productRepository->getById($quoteItem->getProduct()->getId());
                if ($quoteItem->getChildren() && $product->getTypeId() == 'configurable') {
                    foreach ($quoteItem->getChildren() as $child) {
                        $product = $this->productRepository->getById($child->getProductId());
                        $quoteItemData[$index]['name'] = $child->getName();
                        $quoteItemData[$index]['material'] = $product->getResource()->getAttribute('material')->getFrontend()->getValue($product);
                        $quoteItemData[$index]['part_number'] = $product->getResource()->getAttribute('part_number')->getFrontend()->getValue($product);
                        $quoteItemData[$index]['plating'] = $product->getResource()->getAttribute('plating')->getFrontend()->getValue($product);
                        $quoteItemData[$index]['diameter'] = $product->getResource()->getAttribute('diameter')->getFrontend()->getValue($product);
                        $quoteItemData[$index]['length'] = $product->getResource()->getAttribute('length')->getFrontend()->getValue($product);
                    }
                } else {
                    $quoteItemData[$index]['material'] = $product->getResource()->getAttribute('material')->getFrontend()->getValue($product);
                    $quoteItemData[$index]['part_number'] = $product->getResource()->getAttribute('part_number')->getFrontend()->getValue($product);
                    $quoteItemData[$index]['plating'] = $product->getResource()->getAttribute('plating')->getFrontend()->getValue($product);
                    $quoteItemData[$index]['diameter'] = $product->getResource()->getAttribute('diameter')->getFrontend()->getValue($product);
                    $quoteItemData[$index]['length'] = $product->getResource()->getAttribute('length')->getFrontend()->getValue($product);
                }
            }
        }
        return $quoteItemData;
    }
}