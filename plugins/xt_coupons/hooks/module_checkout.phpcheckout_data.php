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

if (!empty($_SESSION['coupon_info'])) {
    $info->_addInfo($_SESSION['coupon_info'], $_SESSION['coupon_info_type']);
    unset($_SESSION['coupon_info']);
    unset($_SESSION['coupon_info_type']);
}
///
if ($_SESSION['cart']->total['plain'] < 0 && $_SESSION['negtotalreload'] != 'true') {
    $_SESSION['negtotalreload'] = 'true';
//	$_SESSION['selected_payment_discount'][$_SESSION['selected_payment']] = 0;
//	global $xtLink;
    $info->_addInfoSession(TEXT_COUPON_INVALID_TOTAL, 'warning');
    $xtLink->_redirect($xtLink->_link(array('page' => 'checkout', 'paction' => 'shipping', 'conn' => 'SSL')));
} else {
    $_SESSION['negtotalreload'] = 'false';
}
?>