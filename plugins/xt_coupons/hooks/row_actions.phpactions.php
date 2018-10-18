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

if ($_GET['type'] == 'coupons_token_im_export') {

    $id = $_GET['id'];
    require_once _SRV_WEBROOT . 'plugins/xt_coupons/classes/class.csvapi_coupons.php';
    $csv_api = new csv_api_coupons();
    $csv_api->getDetails($id);
    if ($csv_api->_recordData['ei_type'] == 'export') {
        $params = 'api=coupon_export&id=' . $id;
    } else {
        $params = 'api=coupon_import&id=' . $id;
    }

    $iframe_target = $xtLink->_adminlink(array('default_page' => 'cronjob.php','conn'=>'SSL', 'params' => $params.'&seckey='._SYSTEM_SECURITY_KEY));
    echo '<iframe src="' . $iframe_target . '" frameborder="0" width="100%" height="500"></iframe>';
}


if ($_GET['type'] == 'coupons_token_generator') {
    $id = $_GET['id'];
    $params = 'api=coupon_generator&id=' . $id;

    $iframe_target = $xtLink->_adminlink(array('default_page' => 'cronjob.php','conn'=>'SSL', 'params' => $params));
    echo '<iframe src="' . $iframe_target . '" frameborder="0" width="100%" height="500"></iframe>';
}
?>