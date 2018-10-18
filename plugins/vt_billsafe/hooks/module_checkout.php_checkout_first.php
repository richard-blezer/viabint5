<?php
	if(!$_SESSION['registered_customer'] && $page->page_action == "success"){
		$_SESSION['vt_billsafe_success_fix'] = $_SESSION['customer']->customer_info['account_type'];
		unset($_SESSION['customer']->customer_info['account_type']);
		$_SESSION['registered_customer'] = true;	
	}
?>