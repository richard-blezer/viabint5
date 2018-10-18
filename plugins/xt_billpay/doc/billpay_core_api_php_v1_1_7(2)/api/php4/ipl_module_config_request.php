<?php

require_once 'ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_module_config_request extends ipl_xml_request {
	
	var $invoicestatic = 0;
	var $directdebitstatic = 0;
	var $active = false;
	var $invoiceallowed = false;
	var $directdebitallowed = false;
	
	function is_active() {
		return $this->active;
	}
	function is_invoice_allowed() {
		return $this->invoiceallowed;
	}
	function is_direct_debit_allowed() {
		return $this->directdebitallowed;	
	}
	function get_static_limit_invoice() {
		return $this->invoicestatic;
	}
	function get_static_limit_direct_debit() {
		return $this->directdebitstatic;
	}

	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	function _send() {
		return ipl_core_send_module_config_request($this->_ipl_request_url, $this->_default_params);
	}
}

?>