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
 # @version $Id: class.xt_ClickandBuy.php 4611 2011-03-30 16:39:15Z mzanier $
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

require_once 'plugins/xt_ClickandBuy/classes/class.ClickandBuy.php';

class xt_ClickandBuy extends ClickandBuy {

 	// Veyton Payment Settings
	public $external = true;
	public $subpayments = false;
	public $iframe = false;
	public $data = array();

	public $TARGET_URL;
	public $TARGET_PARAMS = array();

	function pspRedirect($order_data) {
		global $xtLink,$filter,$order,$db;

		$this->ClickandBuyUrl = XT_CLICKANDBUY_URL;

		$orders_id = (int)$order_data['orders_id'];
		$this->_setOrderId($orders_id);
		$url = $this->buildLink();
		/*
		echo  ($url . "\n\n");
		foreach (split ('&', $url) as $x) {
		  __debug ($x);
		}
		die;
		*/
		return $url;

	}

	function debug ($s, $txt = null) {
    return; // no debugging
    $f = fopen (_SRV_WEBROOT.'plugins/xt_ClickandBuy/classes/debug.txt', "a+");
    $date = date("dS of F Y h:i:s A");
    $dbg = print_r ($s, true);
    @fprintf ($f, ($txt ? "$txt: " : '') . "$date\n");
    fprintf ($f, $dbg . "\n\n");
    fclose ($f);
  } // debug()

	function pspSuccess() {
		return true;
	}
}
?>