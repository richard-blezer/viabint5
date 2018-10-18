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
 # @version $Id: module_checkout.php_checkout_process_check.php stefan.a $
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

if (XT_PAYPAL_EXPRESS == 'true' && $_SESSION['paypalExpressCheckout'] == true) {
// only for checked attribute in conditions checkbox
    if ($_POST['conditions_accepted'] == 'on') {
        $_SESSION['XT_PPEXPRESS__conditions_accepted'] = 1;
    }
// redirect on any error. that's why this hook-code hang in last order 99.
    if ($_check_error === true) {
        $tmp_link = $xtLink->_link(array('page' => 'checkout', 'params' => $xtLink->_getParams() . '&' . session_name() . '=' . session_id(), 'conn' => 'SSL'));
        $xtLink->_redirect($tmp_link);
    }
}
?>