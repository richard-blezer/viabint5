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

if($_SESSION['selected_payment']=='xt_banktransfer'){
    if(is_data($_SESSION['xt_banktransfer_data'])){
        $data = $_SESSION['xt_banktransfer_data'];

        $banktransferValidationReturnValue = $payment_module_data->_banktransferValidation($data);
        $data = $banktransferValidationReturnValue['data'];
        $error_data = $banktransferValidationReturnValue['error'];

        if (count($error_data) > 0) {
            $error = true;
            $checkout_data['page_action'] = 'payment';
        } else {
            $_SESSION['xt_banktransfer_data'] = $data;
        }
    }
}
?>