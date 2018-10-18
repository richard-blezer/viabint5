<?php

require_once 'ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_capture_request extends ipl_xml_request {
	
	private $_capture_params = array();
	
	// bank account
	private $account_holder;
	private $account_number;
	private $bank_code;
	private $bank_name;
	private $invoice_reference;
	private $invoice_duedate;
	
	public function get_account_holder() {
		return $this->account_holder;
	}
	public function get_account_number() {
		return $this->account_number;
	}
	public function get_bank_code() {
		return $this->bank_code;
	}
	public function get_bank_name() {
		return $this->bank_name;
	}
	public function get_invoice_reference() {
		return $this->invoice_reference;
	}
	public function get_invoice_duedate() {
		return $this->invoice_duedate;
	}
	
	public function set_capture_params($bptid, $cart_total_gross, $currency, $reference, $customer_id) {
		$this->_capture_params['bptid'] = $bptid;
		$this->_capture_params['carttotalgross'] = $cart_total_gross;
		$this->_capture_params['currency'] = $currency;
		$this->_capture_params['reference'] = $reference;
		$this->_capture_params['customerid'] = $customer_id;
	}
	
	protected function _send() {
		return ipl_core_send_capture_request($this->_ipl_request_url, $this->_default_params, $this->_capture_params);
	}
	
	protected function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}

?>