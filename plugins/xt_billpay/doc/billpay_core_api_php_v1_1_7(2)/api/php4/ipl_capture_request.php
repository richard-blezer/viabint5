<?php

require_once 'ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_capture_request extends ipl_xml_request {
	
	var $_capture_params = array();
	
	// bank account
	var $account_holder;
	var $account_number;
	var $bank_code;
	var $bank_name;
	var $invoice_reference;
	var $invoice_duedate;
	
	function get_account_holder() {
		return $this->account_holder;
	}
	function get_account_number() {
		return $this->account_number;
	}
	function get_bank_code() {
		return $this->bank_code;
	}
	function get_bank_name() {
		return $this->bank_name;
	}
	function get_invoice_reference() {
		return $this->invoice_reference;
	}
	function get_invoice_duedate() {
		return $this->invoice_duedate;
	}
	
	function set_capture_params($bptid, $cart_total_gross, $currency, $reference, $customer_id) {
		$this->_capture_params['bptid'] = $bptid;
		$this->_capture_params['carttotalgross'] = $cart_total_gross;
		$this->_capture_params['currency'] = $currency;
		$this->_capture_params['reference'] = $reference;
		$this->_capture_params['customerid'] = $customer_id;
	}
	
	function _send() {
		return ipl_core_send_capture_request($this->_ipl_request_url, $this->_default_params, $this->_capture_params);
	}
	
	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}

?>