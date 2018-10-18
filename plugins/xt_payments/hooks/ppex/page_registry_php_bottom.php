<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
    define('PAGE_XT_PAYPAL_CHECKOUT', _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/pages/xt_paypal_checkout.php'); // TodO  den pfad gibt auch im orig pp plg nicht
}

if (isset($xtPlugin->active_modules['xt_payments'])) // todo was wenn die tabellen nicht existieren
{
	define('TABLE_PAYPAL_REFUNDS', DB_PREFIX . '_plg_paypal_refunds');
}
?>