<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.'); 

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_invoice_created_request extends ipl_xml_request {
	
	private $_invoice_params = array();
	
	// bank account
	private $account_holder;
	private $account_number;
	private $bank_code;
	private $bank_name;
	private $invoice_reference;
	private $invoice_duedate;
	private $activation_performed;
	
	private $dues = array();
	
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
	public function get_activation_performed() {
		return $this->activation_performed;
	}
	
	public function get_dues() {
		return $this->dues;
	}
	 	
	public function set_invoice_params($carttotalgross, $currency, $reference, $delayindays = 0) {
		$this->_invoice_params['carttotalgross'] = $carttotalgross;
		$this->_invoice_params['currency'] = $currency;
		$this->_invoice_params['reference'] = $reference;
		$this->_invoice_params['delayindays'] = $delayindays;
	}
	
	protected function _send() {
		return ipl_core_send_invoice_request($this->_ipl_request_url, $this->_default_params, $this->_invoice_params);
	}
	
	protected function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
}

?>