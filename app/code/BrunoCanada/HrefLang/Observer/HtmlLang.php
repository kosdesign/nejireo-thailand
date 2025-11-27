<?php

namespace BrunoCanada\HrefLang\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;

class HtmlLang implements ObserverInterface
{
    protected $pageConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct
    (
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageConfig $pageConfig
    )
    {
        $this->storeManager = $storeManager;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $this->pageConfig->setElementAttribute
        (
            PageConfig::ELEMENT_TYPE_HTML,
            PageConfig::HTML_ATTRIBUTE_LANG,
            $this->storeManager->getStore()->getCode() //Put here the value you want
        );
    }
}
