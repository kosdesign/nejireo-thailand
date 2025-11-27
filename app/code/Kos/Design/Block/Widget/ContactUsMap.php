<?php
namespace Kos\Design\Block\Widget;
class ContactUsMap extends \Magento\Framework\View\Element\Template
{        
    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,  
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    public function getContactUsMapAddressBlock()
    {        
        return $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId('contact-us-map-address')->toHtml();
    }
}