<?php

namespace Eghl\PaymentMethod\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;
    protected $objectManager;
	protected $_logger;

    const XML_PATH_EGHL = 'payment/eghlpayment/';



    public function __construct(Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager  = $storeManager;
		$this->_logger = $context->getLogger();
        parent::__construct($context);
    }
	
	public function add_log($message){
		$this->_logger->addDebug("eGHL_Logs: ".$message);
	}

    public function getConfigValue($field, $storeId = null)
    {
       return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }
	
	public function getBaseURL(){
		list($lang,$locale) =  explode('_',$this->getCurrentLocale());
		return str_replace('/'.$lang.'/','/',$this->storeManager->getStore()->getBaseUrl());
	}

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_EGHL . $code, $storeId);
    }
	
	public function getCurrentLocale(){
		$resolver = $this->objectManager->get('Magento\Framework\Locale\Resolver');
		return $resolver->getLocale();
	}

	public function GUID($length)
	{
		$output = '';	
		$pseudoBytesLen = 64;
		
		$bytes = openssl_random_pseudo_bytes($pseudoBytesLen);
		$hex_array = str_split(bin2hex($bytes));
		
		for($i=0;$i<$length;$i++){
			$output .= $hex_array[rand ( 0 , ($pseudoBytesLen-1) )];
		}
		return $output;
	}

	public function genPaymentID($maxLength = 20){
		$timeStamp = time();
		$guid_len = $maxLength - strlen((string)$timeStamp);
		return $timeStamp.$this->GUID($guid_len);
	}

}