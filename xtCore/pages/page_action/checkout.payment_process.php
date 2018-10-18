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

$shop_id = $store_handler->shop_id;

$shipping_code = $_SESSION['selected_shipping'];
$payment_code = $_SESSION['selected_payment'];

// Shipping
$tmp_shipping_data = $checkout->_getShipping();
$shipping_data = $tmp_shipping_data[$shipping_code];
$shipping_class_path = _SRV_WEBROOT . _SRV_WEB_PLUGINS . $shipping_data['shipping_dir'] . '/classes/';
$shipping_class_file = 'class.' . $shipping_data['shipping_code'] . '.php';

if (file_exists($shipping_class_path . $shipping_class_file)) {
    require_once($shipping_class_path . $shipping_class_file);
    $shipping_module_data = new $shipping_data['shipping_code']();
}


// Payment
$tmp_payment_data = $checkout->_getPayment();
$payment_data = $tmp_payment_data[$payment_code];

$payment_class_path = _SRV_WEBROOT . _SRV_WEB_PLUGINS . $payment_data['payment_dir'] . '/classes/';
$payment_class_file = 'class.' . $payment_data['payment_code'] . '.php';

if (file_exists($payment_class_path . $payment_class_file)) {
    require_once($payment_class_path . $payment_class_file);
    $payment_module_data = new $payment_data['payment_code']();
}

$currency_code = $currency->code;
$currency_value = $currency->value;

$account_type = $_SESSION['customer']->customer_info['account_type'];

$order = new order($_SESSION['last_order_id'], $_SESSION['customer']->customers_id);

if ($payment_module_data->external == true) {
    $res = $payment_module_data->pspSuccess();
    if ($res !== true) {
        $xtLink->_redirect($res);
    }
}


$tmp_link = $xtLink->_link(array('page' => 'checkout', 'paction' => 'success', 'conn' => 'SSL'));
$_SESSION['success_order_id'] = $_SESSION['last_order_id'];
($plugin_code = $xtPlugin->PluginCode('module_checkout.php:payment_proccess_bottom')) ? eval($plugin_code) : false;
$order->_sendOrderMail($_SESSION['last_order_id']);

unset($_SESSION['last_order_id']);
unset($_SESSION['selected_shipping']);
unset($_SESSION['selected_payment']);
unset($_SESSION['conditions_accepted']);
$_SESSION['cart']->_resetCart();
//CORE-424
//last article bug - find a better solution.
unset($_SESSION['info_handler']);
$xtLink->_redirect($tmp_link);