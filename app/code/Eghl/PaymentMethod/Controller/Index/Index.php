<?php 
namespace Eghl\PaymentMethod\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory; 
 
class Index extends Action {
    	
    protected $resultPageFactory;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * 
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPageFactory = $this->resultPageFactory->create();
		$gwresp = $this->_request->getParam('gwresp');
		if(!is_null($gwresp) && $gwresp!=""){
            // Add breadcrumb
            /** @var \Magento\Theme\Block\Html\Breadcrumbs */
            $breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]);
            $breadcrumbs->addCrumb('Eghl_PaymentMethod', [
                'label' => $this->getTextTitle($gwresp),
                'title' => $this->getTextTitle($gwresp)
            ]);
        }
        return $resultPageFactory;
    }

    public function getTextTitle($gwresp = '') {
        if ("success"==$gwresp) {
            $return = __("Payment Successful!");
        } else if ("failed"==$gwresp) {
            $return = __("Payment Failed!");
        } else if ("pending"==$gwresp) {
            $return = __("Payment Pending!");
        } else if ("canceled"==$gwresp) {
            $return = __("Payment Canceled!");
        } else {
            $return = __("Redirecting to eGHL Payment Gateway");
        }
        return $return;
    }
}