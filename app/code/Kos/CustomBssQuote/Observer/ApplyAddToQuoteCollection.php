<?php

namespace Kos\CustomBssQuote\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Bss\QuoteExtension\Observer\ApplyAddToQuoteCollection as BaseApplyAddToQuoteCollection;

class ApplyAddToQuoteCollection extends BaseApplyAddToQuoteCollection
{
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Set Request4quote for product in collection
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if($this->registry->registry('ignore_request_quote')) {
            return $this;
        }

        return parent::execute($observer);
    }
}
