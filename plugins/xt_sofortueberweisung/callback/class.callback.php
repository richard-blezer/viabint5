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

class callback_xt_sofortueberweisung extends callback {

	var $version = '1.0';


	function process() {
		global $filter;
		
		if (!is_array($_POST)) return;

		$this->data = array();
		foreach ($_POST as $key => $val) {
			$this->data[$key] = $filter->_filter($val);
		}
		//	$data['hash'] = $filter->_filter($_POST['hash']);
			
		$hash_array = array(
 			'transaction' => $this->data['transaction'], 
 			'user_id' => $this->data['user_id'], 
 			'project_id' => $this->data['project_id'], 
 			'sender_holder' => $this->data['sender_holder'], 
 			'sender_account_number' => $this->data['sender_account_number'], 
 			'sender_bank_code' => $this->data['sender_bank_code'], 
 			'sender_bank_name' => $this->data['sender_bank_name'], 
 			'sender_bank_bic' => $this->data['sender_bank_bic'], 
 			'sender_iban' => $this->data['sender_iban'], 
 			'sender_country_id' => $this->data['sender_country_id'], 
 			'recipient_holder' => $this->data['recipient_holder'], 
 			'recipient_account_number' => $this->data['recipient_account_number'], 
 			'recipient_bank_code' => $this->data['recipient_bank_code'], 
 			'recipient_bank_name' => $this->data['recipient_bank_name'], 
 			'recipient_bank_bic' => $this->data['recipient_bank_bic'], 
			'recipient_iban' => $this->data['recipient_iban'], 
			'recipient_country_id' => $this->data['recipient_country_id'], 
			'international_transaction' => $this->data['international_transaction'], 
			'amount' => $this->data['amount'], 
			'currency_id' => $this->data['currency_id'], 
			'reason_1' => $this->data['reason_1'], 
			'reason_2' => $this->data['reason_2'], 
			'security_criteria' =>$this->data['security_criteria'], 
			'user_variable_0' => $this->data['user_variable_0'], 
			'user_variable_1' => $this->data['user_variable_1'], 
			'user_variable_2' => $this->data['user_variable_2'], 
			'user_variable_3' => $this->data['user_variable_3'], 
			'user_variable_4' => $this->data['user_variable_4'], 
			'user_variable_5' => $this->data['user_variable_5'],
			'created' => $this->data['created'], 
			'project_password' => XT_SOFORTUEBERWEISUNG_PROJECT_PASSWORD 
		);

		$user_1 = explode(':',$this->data['user_variable_0']);
		$this->customers_id = (int)$user_1[1];
		$this->orders_id = (int)$user_1[0]; 
		$this->amount = number_format($this->data['amount'],'2','.','');

		$data_implode = implode('|', $hash_array);
		$this->hash_calulated = md5($data_implode);

		$return = $this->_callbackProcess();

        header("HTTP/1.1 200 OK");

	}


	function _callbackProcess() {


		
		// check hash codes
		$err = $this->_checkHashSignature();
		if (!$err)
		return false;

		// check if order exists
		$err = $this->_checkOrder();
		if (!$err)
		return false;

		// validate order amount
		$err = $this->_checkAmount();
		if (!$err)
		return false;


		// transaction ID is correct,
		$this->_setStatus('success');
		
		//echo '-OK-';
     //   header("HTTP/1.1 200 OK");

	}
	
	/**
	 * check currency and order amount
	 *
	 * @return boolean
	 */
	function _checkAmount() {
		global $db;
		
		
		$order = new order($this->orders_id,$this->customers_id);
		
		$amount = $order->order_total['total']['plain'];
		$currency = $order->order_data['currency_code'];
		if ($this->data['currency_id']!=$currency) {
			$log_data = array();
			$log_data['module'] = 'xt_sofortueberweisung';
			$log_data['orders_id'] = $this->orders_id;
			$log_data['error_msg'] = 'currency check failed ('.$currency.') vs ('.$this->data['currency_id'].')';
			$log_data['error_data'] = serialize($this->data);
			$this->_addLogEntry($log_data);
			return false;
		}
		
		if (round($this->amount,2) != round($amount,2)) {
			$log_data = array();
			$log_data['module'] = 'xt_sofortueberweisung';
			$log_data['orders_id'] = $this->orders_id;
			$log_data['error_msg'] = 'amount check failed ('.$amount.') vs ('.$this->amount.')';
			$log_data['error_data'] = serialize($this->data);
			$this->_addLogEntry($log_data);
			return false;
		}
		return true;
	}
	
	/**
	 * check hash signature from callback with calculated local hash
	 *
	 * @return boolean
	 */
	function _checkHashSignature() {
		
		if ($this->hash_calulated!=$this->data['hash'] or !isset($this->data['hash'])) {
			$log_data = array();
			$log_data['module'] = 'xt_sofortueberweisung';
			$log_data['orders_id'] = $this->orders_id;
			$log_data['error_msg'] = 'hash check failed';
			$log_data['error_data'] = serialize($this->data);
			$this->_addLogEntry($log_data);
			return false;
		}
		
		return true;
		
	}
	
	/**
	 * check if orders_id/customers_id combination exists
	 *
	 * @return boolean
	 */
	function _checkOrder() {
		global $db;
		$error = false;
		
		if ($this->orders_id=='' or $this->customers_id=='') $error = true;
		
		$rs = $db->Execute(
			"SELECT orders_id FROM ".TABLE_ORDERS." WHERE orders_id=? and customers_id=?",
			array($this->orders_id,$this->customers_id)
		);
		if ($rs->RecordCount()==0) {
			$error = true;
		}
		if ($error) {
			$log_data = array();
			$log_data['module'] = 'xt_sofortueberweisung';
			$log_data['orders_id'] = $this->orders_id;
			$log_data['error_msg'] = 'orders_id/customers_id check failed';
			$log_data['error_data'] = serialize($this->data);
			$this->_addLogEntry($log_data);
		}
		return true;
		
		
	}


	/**
	 * Find order ID for given MB transaction_id
	 *
	 * @return boolean
	 */
	function _getOrderID() {
		global $db;

		$order_query = "SELECT orders_id FROM ".TABLE_ORDERS." WHERE orders_data = ?";
		$rs = $db->Execute($order_query, array($this->data['transaction_id']));

		if ($rs->RecordCount() == 1) {
			return true;
		}

		$this->orders_id = $rs->fields['orders_id'];
		$log_data = array();
		$log_data['module'] = 'moneybookers';
		$log_data['orders_id'] = $this->orders_id;
		$log_data['error_msg'] = 'orders_id not found';
		$log_data['error_data'] = serialize(array('transaction_id'=>$this->data['transaction_id']));
		$this->_addLogEntry($log_data);

		$this->repost = true;
		return false;

	}


	function _setStatus($status = 'success') {
		global $store_handler;

		switch ($status) {

			case 'success' :
				$new_status = XT_SOFORTUEBERWEISUNG_STATUS_SUCCESS;
				$log_data = array();
				$log_data['orders_id'] = $this->orders_id;
				$log_data['class'] = 'success';
				$log_data['module'] = 'xt_sofortueberweisung';
				$log_data['transaction_id'] = $this->data['transaction'];
				$log_data['callback_data'] = array('message'=>'OK','error'=>'200','transaction_id'=>$this->data['transaction'],'shop_id'=>$store_handler->shop_id);
				$callback_id = $this->_addLogEntry($log_data);
				break;

				// error
			default:
				$new_status = XT_SOFORTUEBERWEISUNG_STATUS_ERROR;
				$log_data = array();
				$log_data['orders_id'] = $this->orders_id;
				$log_data['class'] = 'error';
				$log_data['module'] = 'xt_sofortueberweisung';
				$log_data['transaction_id'] = $this->data['transaction'];
				$log_data['callback_data'] = array('message'=>'FAILED','error'=>'999','transaction_id'=>$this->data['transaction'],'shop_id'=>$store_handler->shop_id);
				$callback_id = $this->_addLogEntry($log_data);
				break;


		}

		// update order status
		$this->_updateOrderStatus($new_status,XT_SOFORTUEBERWEISUNG_STATUS_NOTIFY,$this->data['transaction']);
	}
}