<?php
 
include_once 'class.XTPaymentsConnector.php';

class PaymentMethodPPPUrl extends XTPaymentsConnector {
	
	function PaymentMethodPPPUrl(){
	
		parent::__construct();
	}
	
	public function getPPPUrl($RETURN_URL, $CANCEL_URL, $BACK_URL, $NOTIFY_URL){
	
		$redirectUrl = parent::getPPPUrl($RETURN_URL, $CANCEL_URL, $BACK_URL, $NOTIFY_URL);
		return $redirectUrl;
	}
}