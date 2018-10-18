<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 */
	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php5/ipl_invoice_created_request.php';
	
	// This is the order reference from the capture request
	$ref = $_GET["ref"];
	
	$req = new ipl_invoice_created_request("https://test-api.billpay.de/xml/offline");
	$req->set_default_params(2, 3, md5("test4testing"));
	$req->set_invoice_params(3390, "EUR", $ref, 5);
	
	try {
		$req->send();
		
		if (!$req->has_error()) {
			echo "Account holder: 	" . $req->get_account_holder() . "<br />";
			echo "Account number: 	" . $req->get_account_number() . "<br />";
			echo "Bank code: 		" . $req->get_bank_code() . "<br />";
			echo "Bank name: 		" . $req->get_bank_name() . "<br />";
			echo "Invoice reference: 	" . $req->get_invoice_reference() . "<br />";
			echo "Invoice due date: 	" . $req->get_invoice_duedate() . "<br />";
			
			echo "<input type=\"button\" value=\"Partial cancel\" onclick=\"window.location.href='partialcancel.php?ref=" . $ref . "'\" />";
			echo "<input type=\"button\" value=\"Full cancel\" onclick=\"window.location.href='cancel.php?ref=" . $ref . "'\" />";
			
		}
		else {
			echo "Error occured!<br />";
			echo "Error code: " . $req->get_error_code() . "<br />";
			echo "Merchant msg: " . utf8_decode($req->get_merchant_error_message()) . "<br />";
			echo "Customer msg: " . utf8_decode($req->get_customer_error_message()) . "<br />";
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