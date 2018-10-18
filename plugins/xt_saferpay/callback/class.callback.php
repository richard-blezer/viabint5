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
 # @version $Id: class.callback.php 4495 2011-02-16 20:56:39Z mzanier $
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

class callback_xt_saferpay extends callback {

	var $version = '1.0';

	function process() {
		global $filter;


		if (!is_array($_POST)) return;

		$_postdata=array();
		
		$_postdata['DATA'] = $_POST['DATA'];
		$_postdata['SIGNATURE'] =$filter->_filter($_POST['SIGNATURE']);
		
		if($_GET['DATA'] && $_POST['DATA']==''){
			$_postdata['DATA'] = $_GET['DATA'];
		}
		
		if($_GET['SIGNATURE'] && $_GET['SIGNATURE']==''){
			$_postdata['SIGNATURE'] =$filter->_filter($_GET['SIGNATURE']);
		}		

		if (!isset($_GET['orders_id'])) return false;
		$this->orders_id = (int)$_GET['orders_id'];

		$response = $this->_callbackProcess($_postdata);


	}
	
	function _callbackProcess($_postdata) {

		// * catch magic_quotes_gpc is set to yes in PHP.ini
		if( substr($_postdata['DATA'], 0, 15) == "<IDP MSGTYPE=\\\"" ) {
			$_postdata['DATA'] = stripslashes($_postdata['DATA']);
		}

		if ($this->log_callback_data == true) {
			$log_data = array();
			$log_data['module'] = 'xt_saferpay';
			$log_data['class'] = 'callback_data';
			$log_data['orders_id'] = $this->orders_id;
			$log_data['callback_data'] = serialize(array('DATA'=>$_postdata['DATA'],'SIGNATURE'=>$_postdata['SIGNATURE']));
			$this->_addLogEntry($log_data);
		}

		$saferpay = new saferpay();


		if (!$saferpay->VerifyPayConfirm($_postdata)) {
			$log_data = array();
			$log_data['module'] = 'xt_saferpay';
			$log_data['class'] = 'error';
			$log_data['orders_id'] = $this->orders_id;
			$log_data['error_msg'] = 'verification error';
			$log_data['error_data'] = serialize(array('error'=>$saferpay->error));
			$this->_addLogEntry($log_data);
			return false;
		} else {
			$log_data = array();
			$log_data['module'] = 'xt_saferpay';
			$log_data['class'] = 'success';
			$log_data['orders_id'] = $this->orders_id;
			$this->transaction_id = $saferpay->transaction_id;
			$log_data['transaction_id'] = $saferpay->transaction_id;
			$log_data['error_msg'] = 'verification success';
			$log_data['error_data'] = serialize(array('success'=>$saferpay->error));
			$this->_addLogEntry($log_data);

			// set order status to athorized
			$this->_setStatus('auth');

			// try to complete payment if set
			if (XT_SAFERPAY_AUTH_ONLY=='false') {
				// complete payment
				if($saferpay->PayComplete()) {
						$this->_setStatus('completed');		
				} else {
					$log_data = array();
					$log_data['module'] = 'xt_saferpay';
					$log_data['class'] = 'error';
					$log_data['orders_id'] = $this->orders_id;
					$log_data['transaction_id'] = $this->transaction_id;
					$log_data['error_msg'] = 'paycomplete error';
					$log_data['error_data'] = serialize(array('error'=>$saferpay->error));
					$this->_addLogEntry($log_data);
				}
			}
			return true;
		}

	}

	function _setStatus($status) {
		switch ($status) {
			// Auth
			case 'auth' :
				$status = XT_SAFERPAY_AUTH;
				$mail = 'false';
				break;

				// canceled
			case 'completed' :
				$status = XT_SAFERPAY_COMPLETED;
				$mail = 'true';
				break;
		}
		// update order status
		$this->_updateOrderStatus($status,$mail,$this->transaction_id);
	}
}
?>