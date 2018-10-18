<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
    echo '<link rel="stylesheet" type="text/css" href="'._SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_PLUGINS.'xt_payments/xt_paypal_shop.css'.'" />';
}	
?>