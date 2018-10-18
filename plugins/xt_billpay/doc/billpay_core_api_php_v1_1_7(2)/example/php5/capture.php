<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 */
	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php5/ipl_capture_request.php';
	
	// This is the transaction ID obtained from the preauthorization response
	$tx_id = $_GET["tx_id"];
	
	// The order ID must be unique -> generate a random number for this example
	$orderReference = rand(1, 15000000000);
	
	$req = new ipl_capture_request("https://test-api.billpay.de/xml/offline");
	$req->set_default_params(2, 3, md5("test4testing"));
	$req->set_capture_params($tx_id, 3390, "EUR", $orderReference, "12345612");
	
	try {
		$req->send();
		
		if (!$req->has_error()) {
			echo "Account holder: 		" . $req->get_account_holder() . "<br />";
			echo "Account number: 		" . $req->get_account_number() . "<br />";
			echo "Bank code: 			" . $req->get_bank_code() . "<br />";
			echo "Bank name: 			" . $req->get_bank_name() . "<br />";
			echo "Invoice reference: 	" . $req->get_invoice_reference() . "<br />";
			
			echo "<input type=\"button\" value=\"Update order\" onclick=\"window.location.href='updateorder.php?tx_id=$tx_id&ref=$orderReference'\" />";
			echo "<input type=\"button\" value=\"Invoice\" onclick=\"window.location.href='invoicecreated.php?ref=$orderReference'\" />";
		}
		else {
			echo "Error occured!<br />";
			echo "Error code: 		" . $req->get_error_code() . "<br />";
			echo "Merchant msg: 	" . utf8_decode($req->get_merchant_error_message()) . "<br />";
			echo "Customer msg: 	" . utf8_decode($req->get_customer_error_message()) . "<br />";
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

