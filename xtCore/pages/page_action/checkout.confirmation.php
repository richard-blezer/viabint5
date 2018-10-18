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
 # @version $Id$
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
  
  if (count($_SESSION['cart']->content)==0) {
        $xtLink->_redirect($xtLink->_link(array('page'=>'cart')));
    }
    $_SESSION['cart']->_checkCustomersStatusRange('confirmation');
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'cart')),TEXT_CART);
    if($_SESSION['cart']->type != 'virtual'){
    	$brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout','paction'=>'shipping', 'conn'=>'SSL')),TEXT_SHIPPING_METHOD);
    }
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout','paction'=>'payment', 'conn'=>'SSL')),TEXT_PAYMENT_METHOD);
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout','paction'=>'confirmation', 'conn'=>'SSL')),TEXT_CONFIRMATION);
	
	if(empty($_SESSION['selected_shipping']) && ($_SESSION['cart']->type != 'virtual')){
        $info->_addInfo(ERROR_NO_SHIPPING_SELECTED);
        $checkout_data['page_action'] = 'shipping';
    } 
	else
    if(empty($_SESSION['selected_payment'])){
        $info->_addInfo(ERROR_NO_PAYMENT_SELECTED);
        $checkout_data['page_action'] = 'payment';
    }

    // sub payment ?
    if (strpos($_SESSION['selected_payment'],':')) {
        $_payments = explode(':',$_SESSION['selected_payment']);
        $_SESSION['selected_payment'] = $_payments[0];
        $_SESSION['selected_payment_sub'] = $_payments[1];
    }
    // if isset payment discount, add to card
    /*
    if (_STORE_TERMSCOND_CHECK == 'true') {
        if($_SESSION['conditions_accepted'] != 'true'){
            $info->_addInfo(ERROR_CONDITIONS_ACCEPTED);
            $checkout_data['page_action'] = 'confirmation';
        }
    }
    */


    $p_data = $checkout->_getPayment();
    $payment_info = $p_data[$_SESSION['selected_payment']];

    $s_data = $checkout->_getShipping();
    $shipping_info = $s_data[$_SESSION['selected_shipping']];

    // Shipping
    $shipping_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$shipping_info['shipping_dir'].'/classes/';
    $shipping_class_file = 'class.'.$shipping_info['shipping_code'].'.php';

    if (file_exists($shipping_class_path . $shipping_class_file)) {
        require_once($shipping_class_path.$shipping_class_file);
        $shipping_module_data = new $shipping_info['shipping_code']();
    }

    // Payment
    $payment_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$payment_info['payment_dir'].'/classes/';
    $payment_class_file = 'class.'.$payment_info['payment_code'].'.php';

    if (file_exists($payment_class_path . $payment_class_file)) {
        require_once($payment_class_path.$payment_class_file);
        $payment_module_data = new $payment_info['payment_code']();
    }

    ($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_pre_data')) ? eval($plugin_code) : false;

    if ($payment_module_data->subpayments===true) {
        // check if subpayment is allowed
        if (!in_array($_SESSION['selected_payment_sub'],$payment_module_data->allowed_subpayments)) {
            $info->_addInfo(ERROR_NO_PAYMENT_SELECTED);
            $checkout_data['page_action'] = 'payment';
        }
        $_SESSION['cart']->_refresh();
    }

    if (isset($_SESSION['selected_payment_sub'])) $payment_info['payment_name']=constant('TEXT_PAYMENT_'.strtoupper($_SESSION['selected_payment_sub']));

    // post form ?
	
	  // refresh cart with new shiipping price based on currency 
   // if ($shipping_info["shipping_price"]["plain"]!=$_SESSION['cart']->show_sub_content["shipping"]["products_price"]["plain"]) // if current shipping price is diffrent from the saved in the session
	if ($shipping_info['shipping_code']!=$_SESSION['cart']->show_sub_content["shipping"]["products_model"])
	{
		$shipping_data = $checkout->_getShipping();
		$shipping_data2 = $shipping_data[$_SESSION['selected_shipping']];
		$shipping_data_array = array('customer_id' => $_SESSION['registered_customer'],
											 'qty' => $shipping_data2['shipping_qty'],
											 'name' => $shipping_data2['shipping_name'],
											 'model' => $shipping_data2['shipping_code'],
											 'key_id' => $shipping_data2['shipping_id'],
											 'price' => $shipping_data2['shipping_price']['plain_otax'],
											 'tax_class' => $shipping_data2['shipping_tax_class'],
											 'sort_order' => $shipping_data2['shipping_sort_order'],
											 'type' => $shipping_data2['shipping_type']
			);
		$_SESSION['cart']->_deleteSubContent($shipping_data_array['type']);
		$_SESSION['cart']->_addSubContent($shipping_data_array);
		$_SESSION['cart']->_refresh();
	}
    // end of -----refresh cart with new shiipping price based on currency
	
    $post_form = 0;
    if (isset($payment_module_data->post_form) && $payment_module_data->post_form==true) $post_form = 1;
    $data  = array('data' => $_SESSION['cart']->show_content,
                   'payment_info' => $payment_info,
                   'shipping_info' => $shipping_info,
                   'post_form' => $post_form,
                   'sub_total' => $_SESSION['cart']->content_total['formated'],
                   'sub_data' => $_SESSION['cart']->show_sub_content,
                   'tax' =>  $_SESSION['cart']->tax,
                   'total' => $_SESSION['cart']->total['formated']
    );

    if (_STORE_DIGITALCOND_CHECK=='true' && ($_SESSION['cart']->type=='virtual' || $_SESSION['cart']->type=='mixed')) {
        $data['show_digital_checkbox']='true';
    } else {
        $data['show_digital_checkbox']='false';
    }

    ($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_data')) ? eval($plugin_code) : false; // deprecated bacause of duplicate use
	($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_confirmation_data')) ? eval($plugin_code) : false;
    $checkout_data = array_merge($checkout_data, $data);
?>