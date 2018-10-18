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
 # @version $Id: cart_bottom.php 4547 2011-03-09 09:13:47Z mzanier $
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

if (XT_COUPONS_CART_PAGE == 'true') {
    $arr_coupon = $_SESSION['sess_coupon'];

    if (!is_array($arr_coupon)) {

        $show_field = false;

        if (XT_COUPONS_LOGIN == 'false') {
            $show_field = true;
        } elseif ($_SESSION['registered_customer']) {
            $show_field = true;
        }

        if ($show_field == true) {
            $tpl = 'coupons_cart.html';

            $plugin_template = new Template();
            $plugin_template->getTemplatePath($tpl, 'xt_coupons', '', 'plugin');
            echo ($plugin_template->getTemplate('', $tpl, $tpl_data));
        }
    }
}
?>