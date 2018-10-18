<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// xt_grid (4.1) renders the btn in cart_tpl_form_paypal
// if already rendered there, $_REQUEST['paypal_btn_rendered'] will be true
if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_REQUEST['paypal_btn_rendered'] != true){
    
    $payment = new payment();

    $payment->_payment();
    $data_array = $payment->_getPossiblePayment();

    $show_paypalexpress = false;
    if (is_array($data_array)){
        foreach($data_array as $k=>$v){
            if($v['payment_code']=='xt_payments' && $v['status'] = 1 && $v['plugin_installed'] != 0){
                $show_paypalexpress = true;
            }
        }
    }
    
    if($show_paypalexpress){
            require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';
            $ppExpressButton = new paypal_express();
            echo '<br /><a class="paypal_checkout" href="'.$ppExpressButton->buildLink().'">'.$ppExpressButton->buildButton().'</a><br />'.XT_PAYMENTS_TEXT_EXPRESS_CART;
    }
}

?>