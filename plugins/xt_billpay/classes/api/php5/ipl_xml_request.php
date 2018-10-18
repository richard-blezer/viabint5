<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.'); 

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_xml_request {

	const TERMS_AND_CONDITION_URL 		= 'http://www.billpay.de/kunden/agb';
	
	private $request_xml = '';
	private $response_xml = '';
	
	protected $_ipl_request_url = '';
	protected $_default_params 	= array();
	protected $_status_info 	= array();
	
	private $_username;
	private $_password;
	
	
	public function has_error() {
		return $this->_status_info['error_code'] > 0;
	}
	
	public function get_error_code() {
		return $this->_status_info['error_code'];
	}
	
	public function get_customer_error_message() {
		return $this->_status_info['customer_message'];
	}
	
	public function get_merchant_error_message() {
		return $this->_status_info['merchant_message'];
	}
	
	public function get_request_xml() {
		return $this->request_xml;
	}
	
	public function get_response_xml() {
		return $this->response_xml;
	}
	
	function __construct($ipl_request_url) {
		$this->_ipl_request_url	= $ipl_request_url;
	}
	
	public function set_default_params($mid, $pid, $bpsecure) {
		$this->_default_params['mid'] = $mid;
		$this->_default_params['pid'] = $pid;
		$this->_default_params['bpsecure'] = $bpsecure;
	}
	
	public function set_basic_auth_params($username, $password) {
		$this->_username = $username;
		$this->_password = $password;
	}
	
	/**
	 * This must be overridden in deriving class
	 *
	 * @return unknown
	 */
	protected function _send() {
		return false;
	}
	
	/**
	 * This must be overridden in deriving class
	 * @return unknown
	 */
	protected function _process_response_xml($data) {
	}
	
	/**
	 * This must be overridden in deriving class
	 * @return unknown
	 */
	protected function _process_error_response_xml($data) {
	}
	
	
	public function send() {
		$res = $this->_send();

		if (!$res || ipl_core_has_internal_error()) {
			$errorMsg = ipl_core_get_internal_error_msg();
			
			if (!empty($errorMsg)) {
				throw new Exception($errorMsg);
			}
			else {
				throw new Exception('Internal error with unkown cause occured.');
			}
		}

		// Get status info data structure
		$this->_status_info = ipl_core_get_api_error_info();
		
		$this->request_xml = $res[0];
		$this->response_xml = $res[1];

		if (!ipl_core_has_api_error()) {
			$this->_process_response_xml($res[2]);
		}
		else {
			$this->_process_error_response_xml($res[2]);
		}
	}
	
}
?>