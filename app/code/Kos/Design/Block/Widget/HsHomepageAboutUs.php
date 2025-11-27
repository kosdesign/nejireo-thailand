<?php
namespace Kos\Design\Block\Widget;

use Kos\Design\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class HsHomepageAboutUs extends Template implements BlockInterface
{
    public function _toHtml()  
    { 
        $this->setTemplate(
            $this->getData('template') ?: 'Kos_design::widget/homepage_about_us.phtml'
        ); 

        return parent::_toHtml(); 
    }

    public function filterParameters(){ 
        $data = $this->getData();
        return $data; 
    }
} 
 