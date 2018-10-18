<?php

	/**
	 * @author Jan Wehrs (jan.wehrs@billpay.de)
	 * @copyright Copyright 2010 Billpay GmbH
	 * @license commercial 
	 */
	require_once '../../api/ipl_xml_api.php';
	require_once '../../api/php4/ipl_module_config_request.php';
	
	$req = new ipl_module_config_request("https://test-api.billpay.de/xml/offline");
	$req->set_default_params(2, 3, md5("test4testing"));
	
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
			echo "Merchant msg: " . utf8_decode($req->get_merchant_error_message()) . "<br />";
			echo "Customer msg: " . utf8_decode($req->get_customer_error_message()) . "<br />";
		}
		else {
			echo "Module config request successful<br />";
			echo "Direct debit allowed: " . $req->is_direct_debit_allowed() . "<br />";
			echo "Invoice allowed: " . $req->is_invoice_allowed() . "<br />";
			echo "Limit direct debit: " . $req->get_static_limit_direct_debit() . "<br />";
			echo "Limit invoice: " . $req->get_static_limit_invoice() . "<br />";
		}
	
		echo "<br /><br />";
		echo "<strong>Request XML:</strong> " . htmlentities($req->get_request_xml());
		echo "<br /><br />";
		echo "<strong>Response XML:</strong> " . htmlentities($req->get_response_xml());
	}
?>

