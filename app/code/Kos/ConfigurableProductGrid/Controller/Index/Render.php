<?php

namespace Kos\ConfigurableProductGrid\Controller\Index;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Render
 */
class Render extends \Magento\Framework\App\Action\Action
{
    protected $_productloader;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\View\Element\UiComponentFactory $factory,
        \Magento\Catalog\Model\ProductFactory $_productloader
    )
    {
        $this->pageFactory = $pageFactory;
        $this->factory = $factory;
        $this->_productloader = $_productloader;
        return parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->isAjax();
        if ($isAjax) {
            $component = $this->factory->create($this->_request->getParam('namespace'));
            $this->prepareComponent($component);
            $this->_response->appendBody((string)$component->render());
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $id = $this->_request->getParam('id');
            $product = $this->_productloader->create()->load($id);
            $resultRedirect->setUrl($product->getProductUrl());
            return $resultRedirect;
        }
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
