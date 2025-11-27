<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Shipping;

class Tracking extends \Magetrend\Email\Block\Email\Block\Template
{
    public function getShipment()
    {
        return $this->getParentBlock()->getShipment();
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }
}
