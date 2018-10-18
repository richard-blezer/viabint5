<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 */
	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php5/ipl_validation_request.php';
		
	$req = new ipl_validation_request("https://test-api.billpay.de/xml/offline");
	$req->set_default_params(2, 3, md5("test4testing"));
	$req->set_customer_details("123456", "e", "Herr", "", "Tamlin", "Wallder", 
		"Wanderweg", "6", "", "24857", "Borgwedel", 
		"DEU", "demo@billpay.de", "0302333459", "01775112389", 
		"19850911", "de", "85.214.7.22");
		
	$req->set_shipping_details(true);
	
	try {
		$req->send();
		
		if ($req->has_error()) {
			echo "Error occured!<br />";
			echo "Error code: 	" . $req->get_error_code() . "<br />";
			echo "Customer msg: " . utf8_decode($req->get_customer_error_message()) . "<br />";
			echo "Merchant msg: " . utf8_decode($req->get_merchant_error_message()) . "<br />";
		}
		
		echo "<br /><br />";
		echo "<strong>Request XML:</strong> " . htmlentities($req->get_request_xml());
		echo "<br /><br />";
		echo "<strong>Response XML:</strong> " . htmlentities($req->get_response_xml());
	}
	catch(Exception $e) {
		echo $e->getMessage();	
	}
		
?>
