<?php
namespace Kos\Design\Block\Widget;
class LandingBanner extends \Magento\Framework\View\Element\Template
{        
    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,  
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }
    
    public function getPopupLandingBannerCMSBlock()
    {        
        return $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId('popup-landing-banner')->toHtml();
    }
    
    public function getCookieTimeout()
    {        
        return $this->scopeConfig->getValue(
                'landingbanner/general/cookie_timeout',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}