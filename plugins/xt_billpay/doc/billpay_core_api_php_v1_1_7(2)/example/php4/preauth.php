<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 */
	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php4/ipl_preauthorize_request.php';
	
	// Generate a random suffix to create unique customer
	$randSuffix = rand(1, 15000000000);
	
	$req = new ipl_preauthorize_request("https://test-api.billpay.de/xml/offline", IPL_CORE_PAYMENT_TYPE_INVOICE);
	// UN-COMMENT THIS FOR A DIRECT DEBIT REQUEST
	//$req = new ipl_preauthorize_request("http://ipaylater.net:8211/xml/offline", IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT);
	
	$req->set_default_params(2, 3, md5("test4testing"));
	$req->set_customer_details("123456", "e", "Herr", "", "Herbert_$randSuffix", "Tester_$randSuffix",
		"Teststrasse_$randSuffix", "123", "", "12345", "Teststadt_$randSuffix", "DEU", "testing@billpay.de", 
	       "0302333459", "01775112389", "19850911", "de", "85.214.7.22");
	$req->set_shipping_details(true);
	// UN-COMMENT THIS FOR A DIRECT DEBIT REQUEST
	//$req->set_bank_account("Herbert_$randSuffix Tester_$randSuffix", "0000000", "00000000");
	$req->set_total(500, 500, "Express-Versand", 500, 650, 2280, 3390, "EUR", "");
	$req->add_article("article001", 12, "Kaffetasse 08/15", "Eine Kaffetasse aus Porzellan", 120, 195);
	$req->add_article("article002", 4, "Kuchenteller 07-11", "Kuchenteller - weiss", 85, 100);
	$req->add_order_history("1233343321", "20080101 10:23:30", 12300, "EUR", 4, 0);
	$req->set_terms_accepted(true);
	
	$internalError = $req->send();

	if ($internalError) {
		echo "Internal error occured!<br />";
		echo "Error code: " . $internalError['error_code'] . "<br />";
		echo "Error message: " . $internalError['error_message'];
	}
	else {
		if ($req->has_error()) {
			echo "Error occured!<br />";
			echo "Error code: " . $req->get_error_code() . "<br />";
			echo "Customer msg: " . utf8_decode($req->get_customer_error_message()) . "<br />";
			echo "Merchant msg: " . utf8_decode($req->get_merchant_error_message()) . "<br />";
		}

		echo "Status: " . $req->get_status() . "<br />";
	
		if (!$req->has_error()) {
			echo "TX ID: 	" . $req->get_bptid() . "<br />";
			echo "<input type=\"button\" value=\"Capture\" onclick=\"window.location.href='capture.php?tx_id=" . $req->get_bptid() . "'\" />";
		}
		
		echo "<br /><br />";
		echo "<strong>Request XML:</strong> " . htmlentities($req->get_request_xml());
		echo "<br /><br />";
		echo "<strong>Response XML:</strong> " . htmlentities($req->get_response_xml());
	}

?>
