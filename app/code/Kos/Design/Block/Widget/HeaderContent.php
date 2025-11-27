<?php
namespace Kos\Design\Block\Widget;

use Kos\Design\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class HeaderContent extends Template implements BlockInterface
{
    public function _toHtml()  
    { 
        $this->setTemplate(
            $this->getData('template') ?: 'Kos_design::widget/header_content.phtml'
        ); 

        return parent::_toHtml(); 
    }

    public function filterParameters(){ 
        $data = $this->getData();
        return $data; 
    }
} 
 