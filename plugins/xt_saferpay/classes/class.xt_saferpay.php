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
 # @version $Id: class.xt_saferpay.php 4495 2011-02-16 20:56:39Z mzanier $
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

include 'plugins/xt_saferpay/classes/class.saferpay.php';

class xt_saferpay {

	var $data=array();
	var $external = true;
	var $subpayments = false;
	var $iframe = false;

	function build_payment_info($data){

	}

	function pspRedirect($processed_data = array()) {
		global $xtLink,$filter,$db,$countries,$language;
		
		$orders_id = (int)$_SESSION['last_order_id'];
		$rs = $db->Execute("SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id='".$orders_id."'");
		$order = new order($orders_id,$rs->fields['customers_id']);
		
		$data = array();
		$data['amount'] = round($order->order_total['total']['plain']*100,0);
		$data['currency'] = $order->order_data['currency_code'];
		$data['orders_id'] = $orders_id;
		
		$saferpay = new saferpay();
		$target_url = $saferpay->createPayInit($data);
		return $target_url;
	}
	
	function pspSuccess() {
		return true;
	}


}

?>