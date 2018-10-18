<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 * 
	 * This request should be used in case the merchant-side unique order ID for 
	 * the order is only known AFTER the capture request has already been sent. 
	 * In this case the billpay transation id (tx_id) that is obtained from the 
	 * result of the preauthorize request should be used as a "temporary order id" in
	 * the capture request. The update order request should be sent after the order 
	 * is created and therefor the order id is known. 
	 * 
	 * Please note that it is absolutely necessary that no user action takes place 
	 * between the capture request and the update order request!
	 */

	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php5/ipl_update_order_request.php';
	
	// This is the order reference from the capture request
	$tx_id = $_GET["tx_id"];
	$ref = $_GET["ref"];
	$refUpdated = $ref . '_foo';
	
	// The order ID must be unique -> generate a random number for this example
	$orderReference = rand(1, 15000000000);
	
	$req = new ipl_update_order_request("https://test-api.billpay.de/xml/offline");
	$req->set_default_params(2, 3, md5("test4testing"));
	$req->set_update_params($tx_id, $refUpdated);
	
	try {
		$req->send();
		
		if ($req->has_error()) {
			echo "Error code: 		" . $req->get_error_code() . "<br />";
			echo "Merchant msg: 	" . utf8_decode($req->get_merchant_error_message()) . "<br />";
			echo "Customer msg: 	" . utf8_decode($req->get_customer_error_message()) . "<br />";
		}
		else {
			echo "Update order successful!<br />";
			echo "<input type=\"button\" value=\"Invoice\" onclick=\"window.location.href='invoicecreated.php?ref=$refUpdated'\" />";
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

