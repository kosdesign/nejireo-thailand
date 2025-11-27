<?php
namespace Kos\CustomBssQuote\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SalesQuoteSaveAfterCustom implements ObserverInterface
{
    protected $helperCustomQuote;

    public function __construct(\Kos\CustomBssQuote\Helper\Data $helperCustomQuote)
    {
        $this->helperCustomQuote = $helperCustomQuote;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();

        foreach($quote->getAllVisibleItems() as $itemq){
            $itemQty = $itemq->getQty();
            $itemQty = $itemQty > 0 ? $itemQty : 1;
            if (!$itemq->getChangeDayToShip()) {
                $itemq->setDayToShip($this->helperCustomQuote->getDayToShip($itemQty,$itemq));
            }
            $itemq->save();
        }
    }
}
