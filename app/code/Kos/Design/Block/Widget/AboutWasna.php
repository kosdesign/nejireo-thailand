<?php
/**
 * A Magento 2 module named Toptal/CustomWidget
 * Copyright (C) 2017  
 * 
 * This file is part of Toptal/CustomWidget.
 * 
 * Toptal/CustomWidget is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


namespace Kos\Design\Block\Widget;

use Kos\Design\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class AboutWasna extends Template implements BlockInterface
{ 
    public function _toHtml()  
    { 
        $this->setTemplate(  
            $this->getData('template') ?: 'Kos_Design::widget/about_wasna.phtml'
        ); 
 
        return parent::_toHtml();  
    }
    public function getTitle()  
    {
        return $this->getData('title');
    }

    public function filterParameters(){ 
        $data = $this->getData();
        return $data; 
    }
} 
 