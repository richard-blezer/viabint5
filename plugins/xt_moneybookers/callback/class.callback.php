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

class callback_xt_moneybookers extends callback {

	var $version = '1.0';


	function process() {
		global $filter;


		if (!is_array($_POST)) return;

		$this->data = array();
		foreach ($_POST as $key => $val) {
			$this->data[$key] = $filter->_filter($val);
		}

		$response = $this->_callbackProcess();

		if ($response->repost) {
			header('HTTP/1.0 404 Not Found');
		} else {
			header("HTTP/1.0 200 OK");
		}

	}


	function _callbackProcess() {

		if ($this->log_callback_data == true) {
		$log_data = array();
		$log_data['module'] = 'xt_moneybookers';
		$log_data['class'] = 'callback_data';
		$log_data['transaction_id'] = $this->data['transaction_id'];
		$log_data['callback_data'] = serialize($this->data);
		$this->_addLogEntry($log_data);
		}

		// order ID already inserted ?
		$err = $this->_getOrderID();
		if (!$err)
		return false;

		// check if merchant ID matches
		$err = $this->_checkMerchantMail();

		if (!$err)
		return false;

		// validate md5signature
		$err = $this->_checkMD5Signature();
		if (!$err)
		return false;

		// validate Amount
		$err = $this->_checkAmount();
		if (!$err)
		return false;


		$this->_setStatus();

	}

	/**
	 * compare orders total amount with ipn amount
	 *
	 * @return boolean
	 */
	function _checkAmount() {
		global $db;

		$order = new order($this->orders_id,$this->customers_id);

		if ($order->order_total['total']['plain']==$this->data['mb_amount']) return true;

		$log_data = array();
		$log_data['module'] = 'xt_moneybookers';
		$log_data['class'] = 'error';
		$log_data['error_msg'] = 'amount conflict';
		$log_data['error_data'] = serialize(array('detail'=>'Amount SEND:' . $this->data['mb_amount'] . ' Amount STORED:' . $order->order_total['total']['plain']));
		$this->_addLogEntry($log_data);

		return true;
	}

	/**
	 * Find order ID for given MB transaction_id
	 *
	 * @return boolean
	 */
	function _getOrderID() {
		global $db;

		$order_query = "SELECT orders_id,customers_id FROM ".TABLE_ORDERS." WHERE orders_data = ?";
		$rs = $db->Execute($order_query, array($this->data['transaction_id']));

		if ($rs->RecordCount() == 1) {
			$this->orders_id = $rs->fields['orders_id'];
			$this->customers_id = $rs->fields['customers_id'];
			return true;
		}

		$log_data = array();
		$log_data['module'] = 'xt_moneybookers';
		$log_data['class'] = 'error';
		$log_data['error_msg'] = 'order id not found';
		$log_data['error_data'] = serialize(array('transaction_id'=>$this->data['transaction_id']));
		$this->_addLogEntry($log_data);
		$this->repost = true;
		return false;

	}

	/**
	 * check if merchant ID and mechant mail matches to DB
	 *
	 * @return boolean
	 */
	function _checkMerchantMail() {

		// does merchant ID exists ?
		if (!isset ($this->data['merchant_id']) || $this->data['merchant_id'] != XT_MONEYBOOKERS_MERCHANT_ID) {
			$log_data = array();
			$log_data['module'] = 'xt_moneybookers';
			$log_data['class'] = 'error';
			$log_data['error_msg'] = 'merchant id conflict';
			$log_data['error_data'] = serialize(array('detail'=>'Merchant ID SEND:' . $this->data['merchant_id'] . ' Merchant ID STORED:' . XT_MONEYBOOKERS_MERCHANT_ID));
			$this->_addLogEntry($log_data);
			return false;
		}
		// merchant mail ?
		if (!isset ($this->data['pay_to_email']) || $this->data['pay_to_email'] != XT_MONEYBOOKERS_EMAILID) {
			$log_data = array();
			$log_data['module'] = 'xt_moneybookers';
			$log_data['class'] = 'error';
			$log_data['error_msg'] = 'merchant email conflict';
			$log_data['error_data'] = serialize(array('Merchant EMAIL SEND:' . $this->data['pay_to_email'] . ' Merchant EMAIL STORED:' . XT_MONEYBOOKERS_EMAILID));
			$this->_addLogEntry($log_data);
			return false;
		}

		return true;
	}

	/**
	 * Calculate and check MD5 Signature of Callback
	 *
	 * @return boolean
	 */
	function _checkMD5Signature() {

		if (XT_MONEYBOOKERS_MERCHANT_SECRET == '')
		return true;

		$secret = XT_MONEYBOOKERS_MERCHANT_SECRET;
		$md5sec = strtoupper(md5($secret));
		$hash = $this->data['merchant_id'] . $this->data['transaction_id'] . $md5sec . $this->data['mb_amount'] . $this->data['mb_currency'] . $this->data['status'];
		$hash = strtoupper(md5($hash));
		if ($hash != $this->data['md5sig']) {
			$this->Error = '1004';
			$log_data['module'] = 'xt_moneybookers';
			$log_data['class'] = 'error';
			$log_data['error_msg'] = 'md5 check failed';
			$this->_addLogEntry($log_data);
			return false;
		}

		return true;

	}

	function _setStatus() {


		switch ($this->data['status']) {

			// processed
			case 2 :
				$status = XT_MONEYBOOKERS_PROCESSED;
				$log_data = array();
				$log_data['orders_id'] = $this->orders_id;
				$log_data['module'] = 'xt_moneybookers';
				$log_data['class'] = 'success';
				$log_data['transaction_id'] = $this->data['transaction_id'];
				$log_data['callback_data'] = array('message'=>'OK','error'=>'200','transaction_id'=>$this->data['mb_transaction_id']);
				$txn_log_id = $this->_addLogEntry($log_data);
				break;

				// canceled
			case -2 :
			case -1 :
				$status = XT_MONEYBOOKERS_CANCELED;
				$log_data = array();
				$log_data['orders_id'] = $this->orders_id;
				$log_data['module'] = 'xt_moneybookers';
				$log_data['class'] = 'success';
				$log_data['transaction_id'] = $this->data['transaction_id'];
				$log_data['callback_data'] = array('message'=>'FAILED','error'=>'999','transaction_id'=>$this->data['mb_transaction_id']);
				$txn_log_id = $this->_addLogEntry($log_data);
				break;

				// pending
			case 0 :
			case 1 :
				$status = XT_MONEYBOOKERS_PENDING;
				$log_data = array();
				$log_data['orders_id'] = $this->orders_id;
				$log_data['module'] = 'xt_moneybookers';
				$log_data['class'] = 'success';
				$log_data['transaction_id'] = $this->data['transaction_id'];
				$log_data['callback_data'] = array('message'=>'PENDING','error'=>'200','transaction_id'=>$this->data['mb_transaction_id']);
				$txn_log_id = $this->_addLogEntry($log_data);
				break;
				
			// chargeback
			case -3:
				
				break;

		}

		// update order status
		$this->_updateOrderStatus($status,'true',$txn_log_id);
	}
}