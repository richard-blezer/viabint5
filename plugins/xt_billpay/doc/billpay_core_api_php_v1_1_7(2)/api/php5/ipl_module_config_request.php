<?php

require_once 'ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_module_config_request extends ipl_xml_request {
	
	private $invoicestatic = 0;
	private $directdebitstatic = 0;
	private $active = false;
	private $invoiceallowed = false;
	private $directdebitallowed = false;
	
	public function is_active() {
		return $this->active;
	}
	public function is_invoice_allowed() {
		return $this->invoiceallowed;
	}
	public function is_direct_debit_allowed() {
		return $this->directdebitallowed;	
	}
	public function get_static_limit_invoice() {
		return $this->invoicestatic;
	}
	public function get_static_limit_direct_debit() {
		return $this->directdebitstatic;
	}

	protected function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	protected function _send() {
		return ipl_core_send_module_config_request($this->_ipl_request_url, $this->_default_params);
	}
}

?>