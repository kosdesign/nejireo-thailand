<?php
namespace Kos\Design\Block\Widget;

use Kos\Design\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class HsBannerHomePage extends Template implements BlockInterface
{
    public function _toHtml()  
    { 
        $this->setTemplate(
            $this->getData('template') ?: 'Kos_design::widget/hs_banner_homepage.phtml'
        ); 

        return parent::_toHtml(); 
    }

    public function filterParameters(){ 
        $data = $this->getData();
        return $data; 
    }
} 
 