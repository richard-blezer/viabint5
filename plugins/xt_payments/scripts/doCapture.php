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
 # @version $Id: order_edit.php 5674 2013-01-18 16:32:24Z stefan $
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


include '../../../xtFramework/admin/main.php';

if (!$xtc_acl->isLoggedIn()) {
    die('action requires login');
}

$orders_id = (int)$_GET['order_id'];


$query = "SELECT * FROM ".TABLE_ORDERS." WHERE orders_id=?";
$rs = $db->GetRow($query, array($orders_id));


if (count($rs)>0) {
    include_once ('/../classes/class.paypal.php');
    $paypal = new paypal_express();
    $success = $paypal->doCaptureRequest($orders_id);

    if ($success=='true') {
		$order = new order($orders_id,-1);
		$order->_updateOrderStatus(XT_PAYMENTS_APPROVED,'','true','true','IPN','','');
		
        echo XT_PAYMENTS_TEXT_PAYPAL_CAPTURE_SUCCESS;
    } else {
        echo XT_PAYMENTS_TEXT_PAYPAL_CAPTURE_ERROR.' '.$success;
    }
} else {
    echo XT_PAYMENTS_TEXT_PAYPAL_CAPTURE_ERROR;
}
?>