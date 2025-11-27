<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

class Totals extends \Magetrend\Email\Block\Email\Block\Template
{

    public function getTotalsHtml()
    {
        $blockHtml = '';
        if ($this->getParentBlock()->hasData('invoice')) {
            $blockHtml = $this->getChildHtml('invoice_totals');
        } elseif ($this->getParentBlock()->hasData('creditmemo')) {
            $blockHtml = $this->getChildHtml('creditmemo_totals');
        } elseif ($this->getParentBlock()->hasData('order')) {
            $blockHtml = $this->getChildHtml('order_totals');
        } elseif ($this->getParentBlock()->hasData('quote')) {
            $blockHtml = $this->getChildHtml('quote_totals');
        }
        return $blockHtml;
    }
}
