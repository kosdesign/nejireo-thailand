<?php
namespace Eghl\PaymentMethod\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;

class Logo extends Action
{
    protected $_objectManager;
    protected $response;
    protected $_resultPageFactory;
    protected $moduleReader;
    
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
        $this->_objectManager = ObjectManager::getInstance();
        $this->response = $this->_objectManager->get('Magento\Framework\App\ResponseInterface');
        $this->moduleReader = $this->_objectManager->get('Magento\Framework\Module\Dir\Reader');
    }

    public function execute()
    {
        $imgPath = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'Eghl_PaymentMethod'
        )."/frontend/web/img/eghl_logo.png";
        
        $this->response->setHeader('content-type', 'image/png', $overwriteExisting = true);
        $this->response->setContent(file_get_contents($imgPath));
    }
}
?>