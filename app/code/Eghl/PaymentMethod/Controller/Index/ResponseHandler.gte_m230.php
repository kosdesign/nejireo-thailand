<?php
namespace Eghl\PaymentMethod\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;

class ResponseHandler extends Action implements CsrfAwareActionInterface{

	protected $helperData;
	protected $request;
	protected $urlType;
	protected $_debug;
	protected $_order;
	protected $_OrderCommentSender;
	protected $_invoiceService;
	protected $_transaction;
	protected $_transactionBuilder;
	protected $invoiceSender;
	protected $_objectManager;

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

	public function __construct(
		Context $context
	)
	{
		parent::__construct($context);
		$this->_objectManager = ObjectManager::getInstance();
	}

	protected function initApp(){


		$this->helperData = $this->_objectManager->create('\Eghl\PaymentMethod\Helper\Data');
		$this->_debug = $this->helperData->getGeneralConfig('debug');
		$this->request = $this->_objectManager->create('\Magento\Framework\App\Request\Http');
		$this->urlType = $this->request->getParam('urlType');
		$this->_OrderCommentSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender');

		$this->_invoiceService = $this->_objectManager->create('\Magento\Sales\Model\Service\InvoiceService');
		$this->_transaction = $this->_objectManager->create('\Magento\Framework\DB\Transaction');
		$this->_transactionBuilder = $this->_objectManager->create('\Magento\Sales\Model\Order\Payment\Transaction\Builder');
		$this->invoiceSender = $this->_objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
	}

	protected function add_log($message){
		if($this->_debug){
			$this->helperData->add_log("EghlApp -> ".$this->urlType." -> ".$message);
		}
	}

	protected function createOrderInvoice($vars = array()){
		if( strtolower($this->urlType)=='callback' && !isset($vars['TokenType']) ){
			$this->helperData->add_log("EghlApp -> Invoice Debug [".$vars['TxnID']."]".$this->urlType." -> start sleep");
			sleep(2); // Delay for 2 second is applied
			$this->helperData->add_log("EghlApp -> Invoice Debug [".$vars['TxnID']."]".$this->urlType." -> end sleep");
		}
		$this->helperData->add_log("EghlApp -> Invoice Debug [".$vars['TxnID']."]".$this->urlType." -> before has invoice check");
		if($this->_order->canInvoice() && !$this->_order->hasInvoices() ) {
			$this->helperData->add_log("EghlApp -> Invoice Debug [".$vars['TxnID']."]".$this->urlType." -> has invoice check false");
			$invoice = $this->_invoiceService->prepareInvoice($this->_order);
			$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
			$invoice->register();
			$invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
			$invoice->save();
			$transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
			$transactionSave->save();
			$this->invoiceSender->send($invoice);
			$this->helperData->add_log("EghlApp -> Invoice Debug ".$this->urlType." -> Invoice generated");
			//send notification code
			$this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))->setIsCustomerNotified(true)->save();
		}
		else{

			$this->helperData->add_log("EghlApp -> Invoice Debug [".$vars['TxnID']."]".$this->urlType." -> has invoice check true");
		}
	}

	protected function createTransaction($order = null, $paymentData = array())
    {
        //get payment object from order object
		$payment = $order->getPayment();
		$payment->setLastTransId($paymentData['TxnID']);
		$payment->setTransactionId($paymentData['TxnID']);
		$payment->setAdditionalInformation(
			[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
		);
		$formatedPrice = $order->getBaseCurrency()->formatTxt(
			$order->getGrandTotal()
		);

		$message = __('The authorized amount is %1.', $formatedPrice);
		//get the object of builder class
		$trans = $this->_transactionBuilder;
		$transaction = $trans->setPayment($payment)
		->setOrder($order)
		->setTransactionId($paymentData['TxnID'])
		->setAdditionalInformation(
			[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
		)
		->setFailSafe(true)
		//build method creates the transaction and returns the object
		->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

		$payment->addTransactionCommentsToOrder(
			$transaction,
			$message
		);
		$payment->setParentTransactionId(null);
		$payment->save();
		$order->save();

		return  $transaction->save()->getTransactionId();
    }

	protected function orderStatusUpdate($status=NULL){
		// will update the status only if order status is 'pending_payment' and $status is not null
		if(!is_null($status) && "pending_payment"==$this->_order->getStatus()){
			$vars = $this->request->getParams();
			$this->_order->setStatus($status);
			$this->_order->setState($status);
			$bSentEmail = true;
			$comment = "[Payment Processed by eGHL] ". $vars['CurrencyCode'] . $vars['Amount'];
			if($vars['TxnStatus']=='1'){
				if($vars['TxnMessage']!="Buyer cancelled"){
					$comment .= " [Transaction ID:" . $vars['TxnID'] . "]" . " [Payment method: " . $vars['PymtMethod'] . "]"." [Order ID:" . $vars['OrderNumber'] . "]";
				}
				//$comment .= " [TxnMessage:".$vars['TxnMessage']."]";
				if(!$this->helperData->getGeneralConfig('fail_payment_email')){
					$bSentEmail = false;
				}
			}
			else{
				$comment .= " [Transaction ID:" . $vars['TxnID'] . "]" . " [Payment method: " . $vars['PymtMethod'] . "]"." [Order ID:" . $vars['OrderNumber'] . "]";
			}

			$history = $this->_order->addStatusHistoryComment($comment, false);
			if($bSentEmail){
				$history->setIsCustomerNotified(true);
			}
			$this->_order->save();

			if($status==$this->helperData->getGeneralConfig('payment_success_status')){
				$this->createOrderInvoice($vars);
				$this->createTransaction($this->_order,$vars);
			}

			if($bSentEmail){
				$this->_OrderCommentSender->send($this->_order, true, $comment);
			}

		}
	}

	protected function calculate_hash2($vars){
		$clear_string = $this->helperData->getGeneralConfig('hashpass');
		// Hash2 String before Hashing: TxnID.ServiceID.PaymentID.TxnStatus.Amount.CurrencyCode.AuthCode.OrderNumber

		$hashStrKeysOrder = array (
			'TxnID',
			'ServiceID',
			'PaymentID',
			'TxnStatus',
			'Amount',
			'CurrencyCode',
			'AuthCode',
			'OrderNumber',
		);

		//Here we construct the hash string according to the payment gateway's requirements
		foreach ($hashStrKeysOrder as $key)
		{
			if(isset($vars[$key])){
				$clear_string .= $vars[$key];
			}
		}

		$this->add_log("clear_string: $clear_string");
		return hash('sha256', $clear_string);
	}

	public function p_arr($arr, $prefix=NULL){
		if(is_null($prefix)){
			echo "<pre>".print_r($arr,1)."</pre>";
		}
		else{
			echo "<pre>$prefix: ".print_r($arr,1)."</pre>";
		}
	}

	public function execute()
	{
		try{

			$this->initApp();

			// get all request params
			$vars = $this->request->getParams();
			$this->add_log('vars: '.print_r($vars,1));

			if(strtolower($this->urlType)=='callback'){
				$this->add_log('vars: '.print_r($vars,1));
			}

			if(isset($vars['OrderNumber'])){

				// instanciate order object
				$this->_order = $this->_objectManager->create('\Magento\Sales\Model\Order');
				// load order by ID
				$this->_order->loadByIncrementId($vars['OrderNumber']);

				if(strtolower($this->urlType)=='ordershipping'){
					$output = array(
										'BaseShipping'=>number_format($this->_order->getBaseShippingAmount(),2,'.',''),
										'DisplayShipping'=>number_format($this->_order->getShippingAmount(),2,'.','')
									);
					echo json_encode($output);
					exit;
				}
				else{
					// Proceed only if TransactionType = SALE
					if($vars['TransactionType']=="SALE"){
						$hash2 = $this->calculate_hash2($vars);
						if (strcasecmp($hash2,$vars['HashValue2'])!=0) //Different hash between what we calculate and the hash sent by the payment platform so we do not do anything as we consider that the notification doesn't come from the payment platform.
						{
							$this->add_log('Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.')');
							$this->p_arr('Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.')');
							echo 'Hash2 error gateway('.$vars['HashValue2'].') - Calculated('.$hash2.')';
						}
						else{
							if($vars['TxnStatus']=='0') // Success
							{
								$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_success_status'));
								$this->add_log('Order Placed');
								if(strtolower($this->urlType)=='return'){
									//header("Location: ".$this->helperData->getBaseURL().'eghlgw?OrderNumber='.$vars['OrderNumber'].'&gwresp=success');
									header("Location: ".$this->helperData->getBaseURL().'checkout/onepage/success');
									die();
								}
							}
							elseif($vars['TxnStatus']=='1') //Return code different from success so we set the "invalid" status to the order
							{
								if($vars['TxnMessage']=='Buyer cancelled') //Buyer clicked cancel payement so donot treat it as failed transaction
								{
									$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_cancel_status'));
									$this->add_log('Order Canceled');
									if(strtolower($this->urlType)=='return'){
										header("Location: ".$this->helperData->getBaseURL().'eghlgw?OrderNumber='.$vars['OrderNumber'].'&gwresp=canceled');
										die();
									}
								}
								else{
									$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_fail_status'));
									$this->add_log('Payment Failed');
									if(strtolower($this->urlType)=='return'){
										header("Location: ".$this->helperData->getBaseURL().'eghlgw?OrderNumber='.$vars['OrderNumber'].'&gwresp=failed');
										die();
									}
								}
								if(strtolower($this->urlType)=='return'){
									header("Location: ".$this->helperData->getBaseURL().'checkout/onepage/failure');
									die();
								}
							}
							elseif($vars['TxnStatus']=='2') // Pending response
							{
								$this->orderStatusUpdate($this->helperData->getGeneralConfig('payment_pending_status'));
								$this->add_log('Payment Pending');
								if(strtolower($this->urlType)=='return'){
									header("Location: ".$this->helperData->getBaseURL().'eghlgw?OrderNumber='.$vars['OrderNumber'].'&gwresp=pending');
									die();
								}
							}

							if(strtolower($this->urlType)=='callback'){
								print "OK"; // acknowledgement sent to payment gateway
								$this->add_log('acknowledgement sent to payment gateway');
							}
						}
					}
					else{
						$this->add_log('Invalid TransactionType i.e. '.$vars['TransactionType']);
					}
				}

			}
			else{
				$this->p_arr('"OrderNumber" is missing');
				$this->add_log('"OrderNumber" is missing');
			}

		}
		catch (\Exception $e)
		{
			$this->add_log('Exception: '.print_r($e->getMessage(),1));
			echo "Exception: ".$e->getMessage();
		}
  }
}
?>