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

if(is_data($_SESSION['xt_banktransfer_data'])){
    $temp_data = $_SESSION['xt_banktransfer_data'];
    $xt_banktransfer_temp = new xt_banktransfer;

    $data  = array('data' => $_SESSION['cart']->show_content,
        'payment_info' => $xt_banktransfer_temp->data,
        'shipping_info' => $shipping_info,
        'sub_total' => $_SESSION['cart']->content_total['formated'],
        'sub_data' => $_SESSION['cart']->show_sub_content,
        'tax' =>  $_SESSION['cart']->tax,
        'total' => $_SESSION['cart']->total['formated']
    );
	
	if (_STORE_DIGITALCOND_CHECK=='true' && (($_SESSION['cart']->type=='virtual') || ($_SESSION['cart']->type=='mixed'))) {
        $data['show_digital_checkbox']='true';
    } else {
        $data['show_digital_checkbox']='false';
    }
}
?>