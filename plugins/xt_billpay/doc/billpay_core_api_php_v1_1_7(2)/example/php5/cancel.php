<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 */
	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php5/ipl_cancel_request.php';
	
	// This is the order reference from the capture request
	$ref = $_GET["ref"];
	
	$req = new ipl_cancel_request("https://test-api.billpay.de/xml/offline");
	$req->set_default_params(2, 3, md5("test4testing"));
	$req->set_cancel_params($ref, 3390, "EUR");
	
	try {
		$req->send();
	
		if ($req->has_error()) {
			echo "Error occured!<br />";
			echo "Error code: " . $req->get_error_code() . "<br />";
			echo "Merchant msg: " . utf8_decode($req->get_merchant_error_message()) . "<br />";
			echo "Customer msg: " . utf8_decode($req->get_customer_error_message()) . "<br />";
		}
		else {
			echo "Cancel successful<br />";
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

