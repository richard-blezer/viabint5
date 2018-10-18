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
    
    
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout','paction'=>'success')),TEXT_SUCCESS);

    $success_order_id = (int)$_SESSION['success_order_id'];
    $success_order = new order($_SESSION['success_order_id'],$_SESSION['customer']->customers_id);
    $_show = 'true';
    ($plugin_code = $xtPlugin->PluginCode('module_checkout.php:success')) ? eval($plugin_code) : false;

    // Payment
    $payment_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$success_order->order_data['payment_code'].'/classes/';
    $payment_class_file = 'class.'.$success_order->order_data['payment_code'].'.php';

    if (file_exists($payment_class_path . $payment_class_file)) {
        require_once($payment_class_path.$payment_class_file);
        $payment_module_data = new $success_order->order_data['payment_code']();
    }

    $checkout_data  = array('page_action'=>$page_data,'show_next_button'=>$_show, 'success_order' => $success_order);
    if($_SESSION['customer']->customers_status == _STORE_CUSTOMERS_STATUS_ID_GUEST)
    session_destroy();
?>