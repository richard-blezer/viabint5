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
 # @version $Id: class.cart.php_getContent_data.php 6618 2013-12-06 16:56:41Z andreya $
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

if (isset($_GET['remove_coupon'], $_SESSION['sess_coupon']))
{
	// sanity check
	if (isset($_SESSION['sess_coupon']['coupon_token_code'])) $key = 'coupon_token_code';
	else $key = empty($_SESSION['sess_coupon']['coupon_code']) ? null : 'coupon_code';

	if ($_GET['remove_coupon'] === md5($_SESSION['sess_coupon'][$key]))
	{
		global $info, $xtLink;
		$info->_addInfoSession(TEXT_COUPON_REMOVE_SUCCESS.': '.$_SESSION['sess_coupon'][$key], 'success');
		unset(
			$_SESSION['sess_coupon'],
			$_SESSION['cart']->sub_content['xt_coupon'],
			$_SESSION['cart']->show_sub_content['xt_coupon'],
			$_SESSION['cart']->sub_content_total,
			$_SESSION['cart']->sub_content_tax,
			$_SESSION['cart']->coupon_product_discount,
			$_SESSION['cart']->coupon_fix_discount
		);
		$xtLink->_redirect(preg_replace('#(\?|&)remove_coupon=[a-z0-9]{32}#i', '', $xtLink->getCurrentUrl()));
	}
}