<?php
ini_set('display_errors',1);
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https:/api.zaakpay.com/transact',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {		
		return false;
	}

	public function fixedVariables() {
	
	require (CC_ROOT_DIR.CC_DS.'modules'.CC_DS.'gateway'.CC_DS.'Zaakpay'.CC_DS.'ref'.CC_DS.'checksum.php');
		if($this->_module['testMode']=="Y") {
			$mode = '0';	
		}
		else{
			$mode = '1';
		}

		$txntype='1';
		$zpayoption='1';
		$currency="INR";
		$purpose="1";
		$merchantIdentifier = $this->_module['merchantIdentifier'];
		$ip=$_SERVER['REMOTE_ADDR'];
		$merchantIpAddress = $ip;
		
		$secret_key = $this->_module['secret_key'];
		$amount = (int) (100 * $this->_basket['total']);
		$orderId = $this->_basket['cart_order_id'];
		$returnUrl = $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=Zaakpay';
		//$hash = $secret_key."|".$merchantIdentifier."|".$amount."|".$ref_no."|".$returnUrl."|".$mode;
		//$securehash = md5($hash);
		$txnDate=date('Y-m-d');	
		$post_variables	= array(
			'merchantIdentifier' => $merchantIdentifier,
			'orderId' => str_replace("-", "A", $orderId),
			'returnUrl'=> $returnUrl,
			'buyerEmail' => $this->_basket['billing_address']['email'],
			'buyerFirstName' => $this->_basket['billing_address']['first_name'],
			'buyerLastName' => $this->_basket['delivery_address']['last_name'],
			'buyerAddress' => $this->_basket['billing_address']['line1'].' '.$this->_basket['billing_address']['line2'],
			'buyerCity' => $this->_basket['billing_address']['town'],
			'buyerState' => $this->_basket['billing_address']['state'],
			'buyerCountry' => $this->_basket['billing_address']['country_iso'],
			'buyerPincode' => $this->_basket['billing_address']['postcode'],
			'buyerPhoneNumber' => $this->_basket['billing_address']['phone'],
			'txnType' => $txntype,
			'zpPayOption' => $zpayoption,
			'mode' => $mode,
			'currency' => $currency,
			'amount' => $amount,//Amount should be in paisa
			'merchantIpAddress' => $merchantIpAddress,
			'purpose' => $purpose,
			'productDescription' => 'productDescription',
			'ShipToAddress' => $this->_basket['delivery_address']['line1'].' '.$this->_basket['delivery_address']['line2'],
			'ShipToCity' => $this->_basket['delivery_address']['town'],
			'ShipToState' => $this->_basket['delivery_address']['state'],
			'ShipToCountry' => $this->_basket['delivery_address']['country_iso'],			
			'ShipToPincode' => $this->_basket['delivery_address']['postcode'],
			'ShipToPhoneNumber' => $this->_basket['billing_address']['phone'],	
			'ShipToFirstname' => $this->_basket['delivery_address']['first_name'],
			'ShipToLastname' => $this->_basket['delivery_address']['last_name'],
			'txnDate' => $txnDate,
		);	
		$all = '';
		foreach($post_variables as $name => $value) {
		if($name != 'checksum') {
		$all .= "'";
		if ($name == 'returnUrl') {
		$all .= Checksum::sanitizedURL($value);
		} else {

		$all .= Checksum::sanitizedParam($value);
		}
		$all .= "'";
		}
		}
		if($this->_module['logging']=="Y") {
			error_log("AllParams : ".$all);
			error_log("Secret Key : ".$secret_key);	
		}
		//print $all;
		$checksum = Checksum::calculateChecksum($this->_module['secret_key'], $all);
		$PostToZaakpay	= array(
			'merchantIdentifier' => Checksum::sanitizedParam($merchantIdentifier),
			'orderId' => Checksum::sanitizedParam(str_replace("-", "A", $orderId)),
			'returnUrl'=> Checksum::sanitizedURL($returnUrl),
			'buyerEmail' => Checksum::sanitizedParam($this->_basket['billing_address']['email']),
			'buyerFirstName' => Checksum::sanitizedParam($this->_basket['billing_address']['first_name']),
			'buyerLastName' => Checksum::sanitizedParam($this->_basket['delivery_address']['last_name']),
			'buyerAddress' => Checksum::sanitizedParam($this->_basket['billing_address']['line1'].' '.$this->_basket['delivery_address']['line2']),
			'buyerCity' => Checksum::sanitizedParam($this->_basket['billing_address']['town']),
			'buyerState' => Checksum::sanitizedParam($this->_basket['billing_address']['state']),
			'buyerCountry' => Checksum::sanitizedParam($this->_basket['billing_address']['country_iso']),
			'buyerPincode' => Checksum::sanitizedParam($this->_basket['billing_address']['postcode']),
			'buyerPhoneNumber' => Checksum::sanitizedParam($this->_basket['billing_address']['phone']),
			'txnType' => Checksum::sanitizedParam($txntype),
			'zpPayOption' => Checksum::sanitizedParam($zpayoption),
			'mode' => Checksum::sanitizedParam($mode),
			'currency' => Checksum::sanitizedParam($currency),
			'amount' => Checksum::sanitizedParam($amount),//Amount should be in paisa
			'merchantIpAddress' => Checksum::sanitizedParam($ip),
			'purpose' => Checksum::sanitizedParam($purpose),
			'productDescription' => Checksum::sanitizedParam('productDescription'),
			'ShipToAddress' => Checksum::sanitizedParam($this->_basket['delivery_address']['line1'].' '.$this->_basket['delivery_address']['line2']),
			'ShipToCity' => Checksum::sanitizedParam($this->_basket['delivery_address']['town']),
			'ShipToState' => Checksum::sanitizedParam($this->_basket['delivery_address']['state']),
			'ShipToCountry' => Checksum::sanitizedParam($this->_basket['delivery_address']['country_iso']),			
			'ShipToPincode' => Checksum::sanitizedParam($this->_basket['delivery_address']['postcode']),
			'ShipToPhoneNumber' => Checksum::sanitizedParam($this->_basket['billing_address']['phone']),	
			'ShipToFirstname' => Checksum::sanitizedParam($this->_basket['delivery_address']['first_name']),
			'ShipToLastname' => Checksum::sanitizedParam($this->_basket['delivery_address']['last_name']),
			'txnDate' => Checksum::sanitizedParam($txnDate),
			'checksum' => $checksum,
		);	
		
		return (isset($PostToZaakpay)) ? $PostToZaakpay : false;
	}

	public function call() {
		return false;
	}

	public function process() {
		$order = Order::getInstance();	
		require (CC_ROOT_DIR.CC_DS.'modules'.CC_DS.'gateway'.CC_DS.'Zaakpay'.CC_DS.'ref'.CC_DS.'checksum.php');
		$secret_key = $this->_module['secret_key'];
			if(isset($_REQUEST['orderId']) && isset($_REQUEST['responseCode'])){
			/*$response = array();
			            $response['responseCode'] = Checksum::sanitizedParam($_REQUEST['responseCode']);
						$response['responseDescription'] = Checksum::sanitizedParam($_REQUEST['responseDescription']);
						$response['orderId'] = Checksum::sanitizedParam(str_replace("A", "-", $orderId));	
						$response['recd_checksum'] = Checksum::sanitizedParam($_REQUEST['checksum']);
						*/
						$responseCode = Checksum::sanitizedParam($_REQUEST['responseCode']);
						$responseDescription = Checksum::sanitizedParam($_REQUEST['responseDescription']);
						$orderId = Checksum::sanitizedParam(str_replace("A", "-", $_REQUEST['orderId']));	
						$recd_checksum = Checksum::sanitizedParam($_REQUEST['checksum']);
						
						}
						//echo "<pre>";print_r($response);echo "</pre>";
				
				//$orderId = Checksum::sanitizedParam($_REQUEST['orderId']);	
		$order_summary = $order->getSummary($orderId);				
		$cart_order_id = $orderId;		
		//echo "<pre>";print_r($response);echo "</pre>";
		
		//$responseDescription = $responseDescription;
		$all = Checksum::getAllParams();
		if($this->_module['logging']=="Y") {
							error_log("AllParams : ".$all);
							error_log("Secret Key : ".$secret_key);	
						}
		$bool = 0;
		$bool = Checksum::verifyChecksum($recd_checksum, $all, $this->_module['secret_key']);
		if($bool==1)
		{
		if($responseCode==100){
				$action = "Complete";
				$notes 	= $responseDescription;
				$status = 'Received';
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);				
			}
			else {
				$action = "Error";
				$notes = $responseDescription;
				$status = 'Failed';	
				$order->paymentStatus(Order::PAYMENT_FAILED, $cart_order_id);
				$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			}
		}
		else{
			$action = "Error";
			$notes 	= $responseDescription;
			$status = 'Failed';
			$order->paymentStatus(Order::PAYMENT_FAILED, $cart_order_id);
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);			
		}		
		
		$transData['notes']			= $notes;
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);
		
		//httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		if($action == "Complete"){
			$url = $GLOBALS['storeURL'].'/index.php?_a=complete';
			httpredir($url);
		}		else{
			$GLOBALS['gui']->setError("Transaction Failed. Please try again!!!");
			$url = $GLOBALS['storeURL'].'/index.php?_a=gateway';
			httpredir($url);			
		}
		
	}

	public function form() {
		return false;
	}
}
