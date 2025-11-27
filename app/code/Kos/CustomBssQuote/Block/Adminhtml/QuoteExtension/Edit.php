<?php
namespace Kos\CustomBssQuote\Block\Adminhtml\QuoteExtension;

use Bss\QuoteExtension\Model\Config\Source\Status;

class Edit extends \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit
{
    protected function canShowButtonAction($quoteStatus)
    {
        $ignore = [
            Status::STATE_CANCELED,
            Status::STATE_ORDERED ,
            Status::STATE_REJECTED,
            Status::STATE_EXPIRED
        ];
        if (in_array($quoteStatus, $ignore)) {
            $this->buttonList->remove('save');
            return false;
        }
        return true;
    }
}
