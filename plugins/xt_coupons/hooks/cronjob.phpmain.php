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

if (isset($_GET['api'])) {
    if ($_GET['api'] == 'coupon_import') {
        include 'plugins/xt_coupons/classes/class.csvapi_coupons.php';
        include 'plugins/xt_coupons/classes/class.xt_coupons_token_im_export.php';
        $csv_export = new xt_coupons_token_im_export();
        $csv_export->run_import($_GET);
    }
    if ($_GET['api'] == 'coupon_export') {
        include 'plugins/xt_coupons/classes/class.csvapi_coupons.php';
        include 'plugins/xt_coupons/classes/class.xt_coupons_token_im_export.php';
        $csv_export = new xt_coupons_token_im_export();
        $csv_export->run_export($_GET);
    }
    if ($_GET['api'] == 'coupon_generator') {
        include 'plugins/xt_coupons/classes/class.xt_coupons_token_generator.php';
        $csv_export = new xt_coupons_token_generator();
        $csv_export->run_generator($_GET);
    }
}
?>