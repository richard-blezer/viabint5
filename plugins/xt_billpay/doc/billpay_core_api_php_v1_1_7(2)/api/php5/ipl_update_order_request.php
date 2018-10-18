<?php

require_once 'ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_update_order_request extends ipl_xml_request {
	
	private $_update_params = array();
	
	public function set_update_params($bptid, $reference) {
		$this->_update_params['bptid'] = $bptid;
		$this->_update_params['reference'] = $reference;
	}
	
	protected function _send() {
		return ipl_core_send_update_order_request($this->_ipl_request_url, $this->_default_params, $this->_update_params);
	}
	
	protected function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}

?>