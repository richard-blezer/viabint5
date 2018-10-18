<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
    global $template;
    $tpl = 'shipping_paypal.html';
    $template->getTemplatePath($tpl, 'xt_payments', '', 'plugin');
}
?>