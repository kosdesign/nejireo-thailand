<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Totals;

class Creditmemo extends \Magento\Sales\Block\Order\Creditmemo\Totals
{
    private $__mainNode = null;

    public function getVarModel()
    {
        return $this->getMainNode()->getVarModel();
    }

    public function getMainNode()
    {
        if ($this->__mainNode == null) {
            $this->__mainNode = $this->getParentBlock()->getParentBlock();
        }

        return $this->__mainNode;
    }
}
