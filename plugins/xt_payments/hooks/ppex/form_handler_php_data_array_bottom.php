<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true'){
    switch ($data_array['action']) {
        case XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT :
            require_once _SRV_WEBROOT.'plugins/xt_payments/classes/class.xt_payments.php';
            $paypalExpress = new xt_payments();
            $redirect = $paypalExpress->paypalAuthCall();
            $xtLink->_redirect($redirect);
        break;
    }
}
?>