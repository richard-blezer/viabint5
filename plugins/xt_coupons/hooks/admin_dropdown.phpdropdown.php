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

if ($request['get'] == 'coupon') {
    require_once _SRV_WEBROOT . 'plugins/xt_coupons/classes/class.xt_coupons.php';
    $_coupons = new xt_coupons();
    $result = $_coupons->getCoupons();
    unset($_coupons);
}

if ($request['get'] == 'coupon_type') {
    $result = array();
    $result[] = array('id' => 'fix', 'name' => TEXT_COUPON_TYPE_FIX);
    $result[] = array('id' => 'percent', 'name' => TEXT_COUPON_TYPE_PERCENT);
    $result[] = array('id' => 'freeshipping', 'name' => TEXT_COUPON_TYPE_FREESHIPPING);
}
?>