<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if (XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED == 'true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT] == true) {
// only for checked attribute in conditions checkbox
    if ($_POST['conditions_accepted'] == 'on') {
        $_SESSION['XT_PPEXPRESS__conditions_accepted'] = 1;
    }

// prepare additional comments when using expresscheckout
	unset($_SESSION['order_comments']);
	if (is_data($_POST['comments'])) {
		
		$_SESSION['order_comments']=$filter->_filter($_POST['comments']);
	}

// redirect on any error. that's why this hook-code hang in last order 99.
    if ($_check_error === true) {
        $tmp_link = $xtLink->_link(array('page' => 'checkout', 'params' => $xtLink->_getParams() . '&' . session_name() . '=' . session_id(), 'conn' => 'SSL'));
        $xtLink->_redirect($tmp_link);
    }
}
?>