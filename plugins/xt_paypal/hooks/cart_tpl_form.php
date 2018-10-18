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
 # @version $Id: cart_tpl_form.php 4816 2011-09-15 13:39:14Z dev_tunxa $
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

if (XT_PAYPAL_EXPRESS == 'true') {

    $payment = new payment();

    $payment->_payment();
    $data_array = $payment->_getPossiblePayment();

    $show_paypalexpress = false;
    foreach ($data_array as $k => $v) {
        if ($v['payment_code'] == 'xt_paypal' && $v['status'] = 1 && $v['plugin_installed'] != 0) {
            $show_paypalexpress = true;
        }
    }

    if ($show_paypalexpress) {
        global $xtPlugin;
        require_once _SRV_WEBROOT . 'plugins/xt_paypal/classes/class.paypal.php';
        $ppExpressButton = new paypal();
        // fbo : update für xt_facebook_shop : link geht auf target=_top
        $button_str = '<span class="float-right paypalexpress"><a target="_top" class="paypal_checkout" href="' . $ppExpressButton->buildLink() . '">' . $ppExpressButton->buildButton() . '</a></span>';
        ($plugin_code = $xtPlugin->PluginCode('paypal_hooks:cart_tpl_form_bottom')) ? eval($plugin_code) : false;
        echo $button_str;
    }
}

?>