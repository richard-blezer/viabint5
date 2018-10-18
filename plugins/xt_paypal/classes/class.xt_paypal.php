<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

include_once _SRV_WEBROOT.'plugins/xt_paypal/classes/class.paypal.php';

class xt_paypal extends paypal{

	var $data=array();
	var $external = true;
	var $subpayments = false;
	var $iframe = false;

	public $url_data = array();

	function _getParams()
	{
		$params = array();
		return $params;
	}

	function setPosition($pos)
	{
	}

	function __construct(){
		parent::__construct();
		
	}

	function build_payment_info($data){

	}

	function pspRedirect($order_data) {
		global $xtLink,$filter,$order,$db;

		if($_SESSION['paypalExpressCheckout']==true){
			$url = $this->RETURN_URL;
			return $url;			
		}
		
		$orders_id = (int)$order_data['orders_id'];
		$this->_setOrderId($orders_id);
		$this->paypalAuthCall('checkout');
		
		$url = $this->payPalURL.'&useraction=commit';
		return $url;

	}

	function pspSuccess() {
		global $order,$db, $xtLink;
			
		$this->_setOrderId($_SESSION['last_order_id']);
		
		if($_SESSION['reshash']['REDIRECTREQUIRED']==true){
			$this->completeStandardCheckout();
			$url = $this->GIROPAY_URL.$_SESSION['reshash']['TOKEN'];
			$xtLink->_redirect($url);
			//return $url;				
		}else{		
			return $this->completeStandardCheckout();
		}
		
		die;
	}

	function testConnection()
	{
		$log_file = _SRV_WEBROOT ."xtLogs/ppp_test_connection.txt";
		$curl_log = fopen($log_file, 'w+');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://tlstest.paypal.com/");
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_STDERR, $curl_log);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// to ensure curls verify functions working in dev:
		// download http://curl.haxx.se/ca/cacert.pem
		// point _CURL_CURLOPT_CAINFO to cacert.pem, eg in config.php
		if (_CURL_CURLOPT_CAINFO !== '_CURL_CURLOPT_CAINFO')
		{
			curl_setopt($ch, CURLOPT_CAINFO, _CURL_CURLOPT_CAINFO);
		}
		$result = curl_exec($ch);

		fclose($curl_log);

		$result_class = 'success';
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_code!=200)
		{
			$result_class = 'warn';
		}

		$tpl_data = array(
			'curl_result' => $result,
			'curl_error_no' => curl_errno($ch),
			'curl_http_code' => $http_code,
			'curl_error_msg' => curl_error($ch),
			'result_class' => $result_class,
			'curl_out' => file_get_contents($log_file)
		);
		curl_close($ch);

		$tplFile = 'ppp_test_connection.html';
		$template = new Template();
		$template->getTemplatePath($tplFile, 'xt_paypal', 'admin', 'plugin');
		$html = $template->getTemplate('', $tplFile, $tpl_data);

		echo $html;
	}


}
?>