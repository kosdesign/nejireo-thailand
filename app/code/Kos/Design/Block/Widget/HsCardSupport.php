<?php
namespace Kos\Design\Block\Widget;

use Kos\Design\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class HsCardSupport extends Template implements BlockInterface
{
    public function _toHtml()  
    { 
        $this->setTemplate(
            $this->getData('template') ?: 'Kos_design::widget/card_support.phtml'
        ); 

        return parent::_toHtml(); 
    }

    public function filterParameters(){ 
        $data = $this->getData();
        return $data; 
    }
} 
 