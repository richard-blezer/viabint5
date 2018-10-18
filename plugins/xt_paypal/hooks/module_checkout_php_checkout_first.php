<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: module_checkout_php_checkout_first.php 4816 2011-09-15 13:39:14Z dev_tunxa $
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(XT_PAYPAL_EXPRESS=='true' && $_GET['ppexpress']=='true'){
    require_once _SRV_WEBROOT.'plugins/xt_paypal/classes/class.paypal.php';
    $ppLogin = new paypal();
    $ppLogin->paypalGetCustomerData();	
}

if(XT_PAYPAL_EXPRESS=='true' && $_SESSION['paypalExpressCheckout']==true){
    $checkout = new checkout();
    $tmp_payment_data = $checkout->_getPayment();
    $payment_data = $tmp_payment_data['xt_paypal'];
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
    
    $_SESSION['selected_payment'] = 'xt_paypal';
}			

if(XT_PAYPAL_EXPRESS=='true' && $_SESSION['paypalExpressCheckout']==true && !$_SESSION['selected_shipping'] && $page->page_action!='success'){
    $pre_shipping = new shipping();
    $pre_shipping->_shipping();
    $pre_shipping_data = $pre_shipping->shipping_data;

    // Added check on array to see if ist empty rblezer 2018
	if (is_array($pre_shipping_data) || is_object($pre_shipping_data))
	{
		foreach ($pre_shipping_data as $key => $val)
		{
			$selected_shipping = $val['shipping_code'];
			break;
		}
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

    $tmp_link  = $xtLink->_link(array('page'=>'checkout', 'params'=>$xtLink->_getParams().'&'.session_name().'='.session_id(), 'conn'=>'SSL'));
    $xtLink->_redirect($tmp_link);

}		

if(XT_PAYPAL_EXPRESS=='true' && $_SESSION['paypalExpressCheckout']==1){	
    if($page->page_action=='shipping' || $page->page_action=='payment' || $page->page_action=='confirmation'|| $page->page_action=='success'){
        unset($_SESSION['reshash']);
        unset($_SESSION['nvpReqArray']);
        unset($_SESSION['paypalExpressCheckout']);	
        unset($_SESSION['conditions_accepted_paypal']);
    }		   
}
?>