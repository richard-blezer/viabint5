<?php

// todo wird nicht als hook definiert ??
defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
    require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/javascript/paypal-js.php';
}	
?>