<?php
  /*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.ClickandBuy.php 4611 2011-03-30 16:39:15Z mzanier $
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

class ClickandBuy {

	protected $ClickandBuyUrl;
	protected $orders_id;

	function __construct(){
	}

	function _str_shuffle($str){

	  	for ($i = 0; $i <= strlen($str); $i++){
	   	 	$nary[] = substr($str, $i, 1);
	  	}
	  	shuffle ($nary);
	  	$output = '';
	  	while (list (, $number) = each ($nary)) { $output .= $number; }

	  	return $output;
	}

	function _setOrderId($orders_id) {
		$this->orders_id = (int)$orders_id;
	}

	static function _buildPrice($price){
		return sprintf('%03d', round($price * 100));
	}

	function _buildID ($order){
		return $order->order_data['orders_id'] . '0x0' . substr(md5($this->_str_shuffle(rand('100000', '999999'))),1,10);
	}


	function buildLink(){
		global $order;

		$BDRID = $this->_buildID($order);

		// alle utf8 nach iso wandeln?!
		/*
		foreach ($order->order_data as $k => $v) {
		  if (is_string($v) && ! is_numeric($v)) {
		    $order->order_data[$k] = utf8_decode($v);
		  }
		}
		*/

  		$url_host = rtrim($this->ClickandBuyUrl, '/');

  		$url_path .= '&price='.urlencode(self::_buildPrice($order->order_total['total']['plain'])); // total
  		// $url_path .= '&userid='.urlencode($order->order_data['customers_id']); // shop userid
  		// $url_path .= '&TransactionID='.urlencode($order->order_data['orders_id']); // session cartid and ClickandBuy TransactionID
  		// $url_path .= '&check='.rand('1000', '9999'); // first value for redirection-check
  		// $url_path .= '&b_check='.rand('100', '999'); // second value for redirection-check
  		$url_path .= '&externalBDRID='.urlencode($BDRID); // ClickandBuy ExternalBDRID

  		// OPTIONAL
  		$url_path .= '&cb_currency='.urlencode($order->order_data['currency_code']); // set currency
  		$url_path .= '&lang=' . strtolower($order->order_data['language_code']);
  		$url_path .= '&Nation='.strtoupper($order->order_data['billing_country_code']);

  		$url_path .= '&Email='.urlencode($order->order_data['customers_email_address']);
  		$url_path .= '&FirstName='.urlencode($order->order_data['billing_firstname']);
  		$url_path .= '&LastName='.urlencode($order->order_data['billing_lastname']);
  		$url_path .= '&Street='.urlencode($order->order_data['billing_street_address']);
  		$url_path .= '&ZIP='.urlencode($order->order_data['billing_postcode']);
  		$url_path .= '&City='.urlencode($order->order_data['billing_city']);
  		$url_path .= '&Phone='.urlencode($order->order_data['billing_phone']);
  		$url_path .= '&Gender='.urlencode(strtoupper($order->order_data['billing_gender']));
  		$url_path .= '&Company='.urlencode($order->order_data['billing_company']);
      /* */
  		// SHIPPING DATA:
  		$url_path .= '&cb_shipping_FirstName='.urlencode($order->order_data['delivery_firstname']);
  		$url_path .= '&cb_shipping_LastName='.urlencode($order->order_data['delivery_lastname']);
  		$url_path .= '&cb_shipping_Street='.urlencode($order->order_data['delivery_street_address']);
  		$url_path .= '&cb_shipping_Street2='.urlencode($order->billing['delivery_suburb']);
  		$url_path .= '&cb_shipping_ZIP='.urlencode($order->order_data['delivery_postcode']);
  		$url_path .= '&cb_shipping_City='.urlencode($order->order_data['delivery_city']);
  		$url_path .= '&cb_shipping_Nation='.urlencode(strtoupper($order->order_data['delivery_country_code']));

  		// PAYMENT DATA:
  		$url_path .= '&cb_billing_FirstName='.urlencode($order->order_data['billing_firstname']);
  		$url_path .= '&cb_billing_LastName='.urlencode($order->order_data['billing_lastname']);
  		$url_path .= '&cb_billing_Street='.urlencode($order->order_data['billing_street_address']);
  		$url_path .= '&cb_billing_Street2='.urlencode($order->billing['billing_suburb']);
  		$url_path .= '&cb_billing_ZIP='.urlencode($order->order_data['billing_postcode']);
  		$url_path .= '&cb_billing_City='.urlencode($order->order_data['billing_city']);
  		$url_path .= '&cb_billing_Nation='.urlencode(strtoupper($order->order_data['billing_country_code']));

  		// OTHER:
  		$url_path .= '&cb_content_name_utf=' . urlencode ('Ihre Bestellung bei ' . _SRV_WEB);
  		$url_path .= '&cb_content_info_utf=' . urlencode ('Beschreibung von ' . _SRV_WEB);
  		$url_path .= '&' . (session_name().'='.urlencode(session_id()));

  		$_SESSION['externalBDRID'] = $BDRID;
  		$_SESSION['TransactionID'] = $_SESSION['cartID'];

  		$return = $url_host . $url_path;
  		if (XT_CLICKANDBUY_DYNKEY) {
  		   $return .= '&fgkey='.md5(XT_CLICKANDBUY_DYNKEY . '/' . basename ($return));
  		}

  		return $return;

	}
}
?>