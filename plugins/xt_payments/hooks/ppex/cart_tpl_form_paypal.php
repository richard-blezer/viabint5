<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true'){
    
    $payment = new payment();

    $payment->_payment();
    $data_array = $payment->_getPossiblePayment();

    $show_paypalexpress = false;
    foreach($data_array as $k=>$v){
        if($v['payment_code']=='xt_payments' && $v['status'] = 1 && $v['plugin_installed'] != 0){
            $show_paypalexpress = true;
        }
    }
    
    if($show_paypalexpress){
        require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';
        $ppExpressButton = new paypal_express();
        echo '<span class="float-right paypalexpress"><a target="_top" class="paypal_checkout" href="' . $ppExpressButton->buildLink() . '">' . $ppExpressButton->buildButton() . '</a></span>';
        // before xt_grid the btn was rendered in cart_tpl_form
        // to signal thats no need to render there we set paypal_btn_rendered = true
        $_REQUEST['paypal_btn_rendered'] = true;
    }
}

?>