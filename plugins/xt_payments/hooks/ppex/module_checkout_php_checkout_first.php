<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

/*
Check if payment method is Regular PayPal via xt_payments module
*/
if($_GET[XT_PAYMENTS_PARAM_PPREGULAR]=='true'){
	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';
    $ppLogin = new paypal_express();
	/*
	Skip checking address (pass isRegularPayPal parameter as true)
	Call paypalGetCustomerData to make logs if getexpresscheckout fails
	*/
    $ppLogin->paypalGetCustomerData(true, "checkout");
	//$_SESSION[XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT] = true;
	//redirect directly to processing page, no need of confirmation if the paypal method is the regular one and not expresscheckout
	$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment_process', 'conn'=>'SSL'));
	$xtLink->_redirect($tmp_link);
}

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_GET[XT_PAYMENTS_PARAM_PPEXPRESS]=='true'){
    require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';
    $ppLogin = new paypal_express();
    $ppLogin->paypalGetCustomerData();	
}

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){

    $checkout = new checkout();
    $tmp_payment_data = $checkout->_getPayment();
    $payment_data = $tmp_payment_data['xt_payments'];
    if ($payment_data['payment_price']['discount']!=1){
        $payment_data_array = array('customer_id' => $_SESSION['registered_customer'],
                                                'qty' => $payment_data['payment_qty'],
                                                'name' => $payment_data['payment_name'],
                                                'model' => $payment_data['payment_code'],
                                                'key_id' => $payment_data['payment_id'],
                                                'price' => $payment_data['payment_price']['plain_otax'],
                                                'tax_class' => $payment_data['payment_tax_class'],
                                                'sort_order' => $payment_data['payment_sort_order'],
                                                'type' => $payment_data['payment_type']
                    );
        $_SESSION['cart']->_deleteSubContent('payment');
        $_SESSION['cart']->_addSubContent($payment_data_array);
        $_SESSION['cart']->_refresh();
    }
    
    if($payment_data['payment_price']['plain_otax']<=0){
        $_SESSION['cart']->_deleteSubContent('payment');
        $_SESSION['cart']->_refresh();
    }
    
    $_SESSION['selected_payment'] = 'xt_payments';
}			

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true && !$_SESSION['selected_shipping'] && $page->page_action!='success'){
    $pre_shipping = new shipping();
    $pre_shipping->_shipping();
    $pre_shipping_data = $pre_shipping->shipping_data;

    foreach ($pre_shipping_data as $key=>$val){
        $selected_shipping = $val['shipping_code'];
        break;
    }


    $shipping_data = $pre_shipping_data[$selected_shipping];

    $shipping_data_array = array('customer_id' => $_SESSION['registered_customer'],
                                 'qty' => $shipping_data['shipping_qty'],
                                 'name' => $shipping_data['shipping_name'],
                                 'model' => $shipping_data['shipping_code'],
                                 'key_id' => $shipping_data['shipping_id'],
                                 'price' => $shipping_data['shipping_price']['plain_otax'],
                                 'tax_class' => $shipping_data['shipping_tax_class'],
                                 'sort_order' => $shipping_data['shipping_sort_order'],
                                 'type' => $shipping_data['shipping_type']
                                );
    $_SESSION['cart']->_deleteSubContent($shipping_data_array['type']);
    $_SESSION['cart']->_addSubContent($shipping_data_array);
    $_SESSION['selected_shipping'] = $shipping_data['shipping_code'];		

// if selected_shipping not set in $_SESSION PayPalExpres crashes
    if (!$_SESSION['selected_shipping'] || $_SESSION['cart']->type == 'virtual') {
        $_SESSION['cart']->_deleteSubContent($shipping_data_array['type']);
        $_SESSION['selected_shipping']='NOSHIPPING';
    }

    $tmp_link  = $xtLink->_link(array('page'=>'checkout', 'params'=>$xtLink->_getParams(array(XT_PAYMENTS_PARAM_PPEXPRESS)).'&'.session_name().'='.session_id(), 'conn'=>'SSL'));
	 $xtLink->_redirect($tmp_link);

}		

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==1){
    if($page->page_action=='shipping' || $page->page_action=='payment' || $page->page_action=='confirmation'|| $page->page_action=='success'){
        unset($_SESSION['reshash']);
        unset($_SESSION['nvpReqArray']);
        unset($_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]);
        unset($_SESSION['conditions_accepted_paypal']);
    }		   
}

/*
Unset session variables for regular paypal method
*/
if($_SESSION[XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT]==1){
    if($page->page_action=='shipping' || $page->page_action=='payment' || $page->page_action=='confirmation'|| $page->page_action=='success'){
        unset($_SESSION['reshash']);
        unset($_SESSION['nvpReqArray']);
        unset($_SESSION[XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT]);
        unset($_SESSION['conditions_accepted_paypal']);
    }		   
}
?>