<?php

require_once 'ipl_xml_request.php';

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_partialcancel_request extends ipl_xml_request {
	
	private $_cancel_params = array();
	private $_canceled_articles = array();
	
	public function set_cancel_params($reference, $rebatedecrease, $rebatedecreasegross) {
		$this->_cancel_params['reference'] = $reference;
		$this->_cancel_params['rebatedecrease'] = $rebatedecrease;
		$this->_cancel_params['rebatedecreasegross'] = $rebatedecreasegross;
	}
	
	public function add_canceled_article($articleid, $articlequantity) {
		$article = array();
		$article['articleid'] = $articleid;
		$article['articlequantity'] = $articlequantity;
		
		$this->_canceled_articles[] = $article;
	}
	
	protected function _send() {
		return ipl_core_send_partialcancel_request($this->_ipl_request_url, $this->_default_params, $this->_cancel_params, $this->_canceled_articles);
	}
	
	protected function _process_response_xml($data) {
	}
	
}

?>