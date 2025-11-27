<?php
namespace Eghl\PaymentMethod\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;

class Copyquote extends Action {
	protected $_resultPageFactory;
	protected $quoteFactory;
	protected $formKey;  
	protected $cart;
	protected $product;
	protected $order;
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		QuoteFactory $quoteFactory,
		Cart $cart,
		ProductFactory $product
	)
	{
		$this->_resultPageFactory = $resultPageFactory;
		$this->quoteFactory = $quoteFactory;
		$this->cart = $cart;
		$this->product = $product;
		parent::__construct($context);
	}
	
	public function execute()
	{
		try
		{
			
			$orderInfo  = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order')->loadByIncrementId($_REQUEST['order_id']);
			//$couponCode = $orderInfo->getCouponCode();
			$quote_id = $orderInfo->getQuoteId(); //Your Quote ID
			
			if($quote_id > 0)
			{
				$quote = $this->quoteFactory->create()->load($quote_id);
				$items = $quote->getAllVisibleItems();
				
				foreach ($items as $item)
				{
					$productId =$item->getProductId();
					$_product = $this->product->create()->load($productId); 
					
					$options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
					
					$info = $options['info_buyRequest'];
					$request1 = new \Magento\Framework\DataObject();
					$request1->setData($info);
					 
					$this->cart->addProduct($_product, $request1);
				}
				$this->cart->save();
			}
			
			// Redirect to checkout page
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setPath('checkout');
			return $resultRedirect;
		}
		catch (\Exception $e)
		{
			$this->messageManager->addError( __($e->getMessage()) );
		}
    }
}
?>