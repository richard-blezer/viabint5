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
 # @version $Id: module_checkout_php_checkout_page_actions.php 4816 2011-09-15 13:39:14Z dev_tunxa $
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

if (XT_PAYPAL_EXPRESS == 'true' && $_SESSION['paypalExpressCheckout'] == true && $page->page_action != 'success' && $page->page_action != 'process') {

    include _SRV_WEBROOT . 'xtCore/pages/page_action/checkout.payment.php';

    if ($_SESSION['conditions_accepted_paypal'] == 'true')
        $_SESSION['conditions_accepted'] = 'true';

    if ($_SESSION['rescission_accepted_paypal'] == 'true')
        $_SESSION['rescission_accepted'] = 'true';

    include _SRV_WEBROOT . 'xtCore/pages/page_action/checkout.confirmation.php';

}
?>