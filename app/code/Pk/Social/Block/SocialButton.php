<?php

namespace Pk\Social\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class SocialButton extends Template
{
    public $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function getWorkingTime(): ?string{
        return $this->scopeConfig->getValue('social_contact/group_a/working_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTelInfo(){
        $phoneNumbers = $this->scopeConfig->getValue('social_contact/group_a/tel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return explode(",",$phoneNumbers);
    }

    public function getMessenger(): ?string{
        return $this->scopeConfig->getValue('social_contact/group_a/messenger', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLineInfo(): ?string{
        return $this->scopeConfig->getValue('social_contact/group_a/line', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
