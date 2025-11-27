<?php
namespace Kos\CustomBssQuote\Plugin\Quote;

use Magento\Framework\Event\Observer;

/**
 * Class QuantityValidator
 *
 * @package Kos\CustomBssQuote\Plugin\Quote
 */
class QuantityValidator
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected  $collectionFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
    }

    public function aroundValidate($subject, callable $proceed, Observer $observer)
    {
        if ($this->request->getParam('quote_extension')) {
           $quote = $observer->getEvent()->getItem()->getQuote();
           if ($quote && $quote->getQuoteExtension() == null) {
               return ;
           }
        }
        return $proceed($observer);
    }
}
