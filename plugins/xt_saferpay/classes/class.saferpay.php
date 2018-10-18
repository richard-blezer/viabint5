<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ldt. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.saferpay.php 4495 2011-02-16 20:56:39Z mzanier $
 # @copyright xt:Commerce International Ldt., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ldt., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class saferpay {

	var $gatewayPayInit = 'https://www.saferpay.com/hosting/CreatePayInit.asp';
	var $gatewayPayConfirm = 'https://www.saferpay.com/hosting/VerifyPayConfirm.asp';
	var $gatewayPayComplete = 'https://www.saferpay.com/hosting/PayComplete.asp';
	var $error;

	function saferpay() {
		global $xtLink;

		if (XT_SAFERPAY_API_MODE=='true') {
			$this->account_id ='95479-17760819';
			$this->password='So_Sicher';
		} else {
			$this->account_id =XT_SAFERPAY_API_ACCOUNT_ID;
		}

		$this->SUCCESS_URL  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment_process'));
		$this->CANCEL_URL  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment','params'=>'error=ERROR_PAYMENT'));
	}

	/**
	 * query saferpay webservice to get gateway url
	 *
	 * @param array $data
	 * @return string agteway url
	 */
	function createPayInit($data) {
		global $xtLink,$language;

		$description = 'Bestellung '.$data['orders_id'];

		$attributes = "?ACCOUNTID=" . $this->account_id;
		$attributes .= "&AMOUNT=" . $data['amount'];
		$attributes .= "&CURRENCY=" . $data['currency'];
		$attributes .= "&DESCRIPTION=" . urlencode($description);
		$attributes .= "&SUCCESSLINK=" . urlencode($this->SUCCESS_URL);
		$attributes .= "&FAILLINK=" . urlencode($this->CANCEL_URL);
		$attributes .= "&BACKLINK=" . urlencode($this->CANCEL_URL);

		$this->NOTIFY_URL  = $xtLink->_link(array('page'=>'callback', 'paction'=>'xt_saferpay','params'=>'orders_id='.$data['orders_id']));

		$attributes .= "&NOTIFYURL=" . urlencode($this->NOTIFY_URL);

		$attributes .= "&DELIVERY=no";
		$attributes .= "&CCCVC=yes";
		$attributes .= "&CCNAME=yes";

		$attributes .= "&AUTOCLOSE=0";
		
		// language, see https://www.saferpay.com/vt/xml/language.xml
        $attributes .= "&LANGID=".$language->code;  
		/*
        switch ($language->code) {
			case 'de':
				$attributes .= "&LANGID=2055";
				break;
			default:
				$attributes .= "&LANGID=1033";
				break;
		}
        */
		
		//styling options
		if (XT_SAFERPAY_HEADCOLOR !='')
		$attributes .= "&HEADCOLOR=".XT_SAFERPAY_HEADCOLOR;
		if (XT_SAFERPAY_HEADLINECOLOR !='')
		$attributes .= "&HEADLINECOLOR=".XT_SAFERPAY_HEADLINECOLOR;
		if (XT_SAFERPAY_HEADFONTCOLOR !='')
		$attributes .= "&HEADFONTCOLOR=".XT_SAFERPAY_HEADFONTCOLOR;
		if (XT_SAFERPAY_MENUCOLOR !='')
		$attributes .= "&MENUCOLOR=".XT_SAFERPAY_MENUCOLOR;
		if (XT_SAFERPAY_BODYFONTCOLOR !='')
		$attributes .= "&BODYFONTCOLOR=".XT_SAFERPAY_BODYFONTCOLOR;
		if (XT_SAFERPAY_FONT !='')
		$attributes .= "&FONT=".XT_SAFERPAY_FONT;
		if (XT_SAFERPAY_BODYCOLOR !='')
		$attributes .= "&BODYCOLOR=".XT_SAFERPAY_BODYCOLOR;
				
		$attributes .= "&ORDERID=" . $data['orders_id'];

		$payinit_url = $this->gatewayPayInit.$attributes;

		// initiate curl session
		$cs = curl_init($payinit_url);

		curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
		curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
		curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
		curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true); 	// receive returned characters

		$payment_url = curl_exec($cs);

		$ce = curl_error($cs);
		curl_close($cs);

		if( strtolower( substr( $payment_url, 0, 24 ) ) != "https://www.saferpay.com" ) {
			$msg = "<h1>PHP-CURL is not working correctly for outgoing SSL-calls on your server</h1>\r\n";
			$msg .= "<h2><font color=\"red\">".htmlentities($payment_url)."&nbsp;</font></h2>\r\n";
			$msg .= "<h2><font color=\"red\">".htmlentities($ce)."&nbsp;</font></h2>\r\n";
			echo $payinit_url;
			die($msg);

			// TODO LOG
		}
		return $payment_url;

	}

	/**
	 * verify payment against saferpay webservice
	 *
	 * @param array $data
	 * @return boolean
	 */
	function VerifyPayConfirm($data) {

		require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/phpxml/xml.php';

		$payconfirm_url = $this->gatewayPayConfirm."?DATA=" . urlencode($data['DATA']) . "&SIGNATURE=" . urlencode($data['SIGNATURE']);
		$cs = curl_init($payconfirm_url);

		curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
		curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
		curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
		curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true); 	// receive returned characters

		$verification = curl_exec($cs);

		curl_close($cs);

		if( strtoupper( substr( $verification, 0, 3 ) ) != "OK:" ) {
			$this->error=$verification;
			return false;
		} else {
			$xml = $data['DATA'];
			$array = XML_unserialize($xml);
			$this->transaction_id = $array['IDP attr']['ID'];
			$this->error=$verification;
			return true;
		}
	}

	function PayComplete($data) {

		$paycomplete_url = $this->gatewayPayComplete."?ACCOUNTID=" . $this->account_id . "&ID=" . urlencode($this->transaction_id);

		if (XT_SAFERPAY_API_MODE=='true') {
			$paycomplete_url .= "&spPassword=".$this->password;
		}

		$cs = curl_init($paycomplete_url);

		curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
		curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
		curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
		curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true); 	// receive returned characters

		$answer = curl_exec($cs);

		curl_close($cs);
		
		if( strtoupper( $answer ) != "OK" ) {
			$this->error = $answer;
			return false;
		} else {
			return true;
		}

	}
}
?>