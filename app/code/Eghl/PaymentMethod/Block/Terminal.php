<?php

namespace Eghl\PaymentMethod\Block;

class Terminal extends \Magento\Framework\View\Element\Template
{
	protected $helperData;
	protected $request;
	protected $_store;
	protected $debug_html;
	protected $_objectManager;
	protected $_order;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Eghl\PaymentMethod\Helper\Data $helperData,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Store\Api\Data\StoreInterface $store,
		\Magento\Framework\ObjectManagerInterface $objectmanager
	)
	{
		$this->helperData = $helperData;
		$this->request = $request;
		$this->_store = $store;
		$this->_objectManager = $objectmanager;
		$this->_order = $this->_objectManager->create('\Magento\Sales\Model\Order');
		
		$this->debug_html = "";
		parent::__construct($context);
	}
	
	public function _prepareLayout()
	{
		//set page title
		$gwresp = $this->getParam('gwresp');
		if(!is_null($gwresp) && $gwresp!=""){
			$this->pageConfig->getTitle()->set(__($this->getTextTitle()));
		}else{
			$this->pageConfig->getTitle()->set(__('Redirecting to eGHL Payment Gateway'));
		}
		
		return parent::_prepareLayout();
	}  
	
	public function getEghlConfig($config){
		return $this->helperData->getGeneralConfig($config);
	}
	
	public function getPost($param, $default=NULL){
		return $this->request->getPost($param, $default);
	}
	
	public function getParam($param, $default=NULL){
		return $this->request->getParam($param, $default);
	}
	
	public function getParams($params, $default=NULL){
		return $this->request->getParams($params, $default);
	}
	
	public function eghl_acceptable_locale(){
		list($first,$second) =  explode('_',$this->helperData->getCurrentLocale());
		return $first;
	}
	
	protected function calculateHashValue(&$pgw_params){
		$clearString	=	$this->getEghlConfig('hashpass');
		$hashStrKeysOrder = array (
			'ServiceID',
			'PaymentID',
			'MerchantReturnURL',
			'MerchantCallBackURL',
			'Amount',
			'CurrencyCode',
			'CustIP',
			'PageTimeout',
		);
		foreach($hashStrKeysOrder as $ind){
			$clearString	.=	$pgw_params[$ind];
		}
		$pgw_params["HashValue"]	=	hash('sha256', $clearString);
	}
	
	public function content_controller(){
		
		$gwresp = $this->getParam('gwresp');
		$order_id = $this->getParam('OrderNumber');
		$content = "";
		$reorder = "";
		if(!is_null($gwresp) && $gwresp!=""){
			if("success"==$gwresp){
				$content .= "<p class='text-center'>".__("Thanks for purchasing with us.")."</p>";
				$content .= "<div class='action-button'>
					<a class='eGHL_btn button action continue' href='".$this->helperData->getBaseURL()."sales/order/view/order_id/$order_id'>View Order</a>
				</div>";
			}
			elseif("failed"==$gwresp){
				$content .= "<p class='text-center'>".__("Something went wrong. Your order is under review with us.")."</p>";
				$reorder = '<button type="button" onclick="window.location=`'.$this->helperData->getBaseURL().'eghlgw/Index/Copyquote?order_id='.$order_id.'`"><span>'.__('Reorder').'</span></button>';
				$content .= "<div class='action-button'>
					".$reorder."
					<a class='eGHL_btn button action continue' href='".$this->helperData->getBaseURL()."sales/order/view/order_id/$order_id'>View Order</a>
				</div>";
			}
			elseif("pending"==$gwresp){
				$content .= "<p class='text-center'>".__("Your order status is pending with us.")."</p>";
				$content .= "<div class='action-button'>
					<a class='eGHL_btn button action continue' href='".$this->helperData->getBaseURL()."sales/order/view/order_id/$order_id'>View Order</a>
				</div>";
			}
			elseif("canceled"==$gwresp){
				$content .= "<p class='text-center'>".__("Your payment has been cancelled.")."</p>";
				$content .= "<div class='action-button'>
					<button type='button' onclick='window.location=`".$this->helperData->getBaseURL()."`'><span>".__("Visit Homepage")."</span></button>
				</div>";
			}
		}
		else{
			$content .= $this->eGHLPaymentForm();
		}
		
		return $content;
	}
	
	public function eGHLPaymentForm(){
		// load order by ID
		if(is_numeric($this->getPost('OrderNumber'))){
			// in case of logged in user
			$this->_order->loadByAttribute('quote_id',$this->getPost('OrderNumber'));
		}
		else{
			// in case of guest checkout
			$checkoutSession = $this->_objectManager->create('\Magento\Checkout\Model\Session');
			$this->_order = $checkoutSession->getLastRealOrder();
		}
		
		$CurrencyCode = '';
		$amount = 0;
		$shipping = 0;
		
		/*if($this->getEghlConfig('currency_type')=="Base"){
			$CurrencyCode = $this->getPost('BaseCurrencyCode');
			$amount = number_format($this->getPost('BaseAmount'), 2, '.','');
			$shipping = $this->getPost('BaseShipping');
		}
		elseif($this->getEghlConfig('currency_type')=="Display"){
			$CurrencyCode = $this->getPost('DisplayCurrencyCode');
			$amount = number_format($this->getPost('DisplayAmount'), 2, '.','');
			$shipping = $this->getPost('DisplayShipping');
		}*/
		$CurrencyCode = $this->_order->getOrderCurrencyCode();
		$amount = number_format($this->_order->getGrandTotal(), 2, '.','');
		
		$pgw_params	=	array(
							"TransactionType"	=>	"SALE",
							"PymtMethod"	=>	$this->getEghlConfig('pay_method'),
							"ServiceID"	=>	$this->getEghlConfig('mid'),
							"PaymentID"	=>	$this->helperData->genPaymentID(),
							"OrderNumber"	=>	$this->_order->getIncrementId(),
							"PaymentDesc"	=>	$this->getPost('PaymentDesc'),
							"Amount"	=>	number_format(($amount+$shipping), 2, '.',''),
							"CurrencyCode"	=>	$CurrencyCode,
							"CustIP"	=>	$this->getPost('CustIP'),
							"CustName"	=>	$this->getPost('CustName'),
							"CustEmail"	=>	$this->getPost('CustEmail'),
							"CustPhone"	=>	$this->getPost('CustPhone'),
							"LanguageCode"	=> $this->eghl_acceptable_locale(),
							"PageTimeout"	=>	$this->getEghlConfig('page_timeout'),
							"MerchantReturnURL"	=>	$this->helperData->getBaseURL()."eghlapi/Index/ResponseHandler/?urlType=return",
							"MerchantCallBackURL"	=>	$this->helperData->getBaseURL()."eghlapi/Index/ResponseHandler/?urlType=callback"
						);
		$this->calculateHashValue($pgw_params);
		$this->add_log($pgw_params);
		$this->redirect($this->getEghlConfig('payment_url'),$pgw_params);
		exit;
		/*$output = 	"	<p>
							You will be redirected automatically to eGHL payment gateway shortly...
						</p>
						<p>
							Or you can manually click the 'Pay Now' button
						</p>
						<form id='frm_eGHL_payment' method='post' action='".$this->getEghlConfig('payment_url')."'>";
		foreach($pgw_params as $ind=>$val){
			$output .= 	"	<input type='hidden' name='".$ind."' id='".$ind."' value='".$val."'/>";
		}
		$output .= 	"	<center><input class='eGHL_btn' type='submit' value='Pay Now'/></center>
						<div class='loader'>Loading...</div>";
		$output .= 	"</form>";
		
		return $output;*/

	}

	private function redirect($URL, $data){
		$URL;
		$data = http_build_query($data);

		header("Location: $URL?$data");
	}
	
	protected function add_log($message){
		if($this->getEghlConfig('debug')){
			$this->helperData->add_log(print_r($message,1));
		}
	}
	
	public function getTextTitle()
	{
		$gwresp = $this->getParam('gwresp');
		$return = '';
		if (!is_null($gwresp) && $gwresp!="") {
			if ("success"==$gwresp) {
				$return = __("Payment Successful!");
			} else if ("failed"==$gwresp) {
				$return = __("Payment Failed!");
			} else if ("pending"==$gwresp) {
				$return = __("Payment Pending!");
			} else if ("canceled"==$gwresp) {
				$return = __("Payment Canceled!");
			}
		} else {
			$return = __('Redirecting to eGHL Payment Gateway');
		}
		return $return;
	}
}
?>