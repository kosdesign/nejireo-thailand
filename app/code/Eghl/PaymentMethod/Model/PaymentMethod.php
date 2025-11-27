<?php
 
namespace Eghl\PaymentMethod\Model;
use Magento\Sales\Model\Order; 
/**
 * Pay In Store payment method model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
	protected $cwriter = '';
	protected $clogger = '';
	protected $helperData;
	
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'eghlpayment';
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = True;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = true;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;
	
	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;
	
	/**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @api
     */
	public function initialize($paymentAction, $stateObject)
    {
		$state = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
		$stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
        return $this;
    }
}
?>