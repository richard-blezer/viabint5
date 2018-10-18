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

$payment = new payment();
$payment->_payment();
$data_array = $payment->_getPossiblePayment();
$show_button = false;
foreach($data_array as $k=>$v){
    if($v['payment_code']=='xt_banktransfer' && $v['status'] = 1){
        $show_button = true;
    }
}

if($show_button){
    $tmp_data = '';
    $tpl = 'account_link.html';
    $template = new Template();
    $template->getTemplatePath($tpl, 'xt_banktransfer', '', 'plugin');
    $tpl_data= array('show_button'=>$show_button);

    $tmp_data = $template->getTemplate('xt_banktransfer_account_smarty', $tpl, $tpl_data);
    echo $tmp_data;
}

?>