<?php
	if($_SESSION['vt_billsafe_success_fix']){
		$_SESSION['customer']->customer_info['account_type'] = $_SESSION['vt_billsafe_success_fix'];
		unset($_SESSION['registered_customer']);
		unset($_SESSION['vt_billsafe_success_fix']);
	}
?>