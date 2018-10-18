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

if ( ! empty($_SESSION['sess_coupon']) && is_array($_SESSION['sess_coupon'])) {

	if ( ! empty($_SESSION['sess_coupon']['coupon_token_code'])) $key = 'coupon_token_code';
	else $key = empty($_SESSION['sess_coupon']['coupon_code']) ? null : 'coupon_code';

	if (isset($key))
	{
		global $xtLink,$currency, $price;

		$coupon_hash = md5($_SESSION['sess_coupon'][$key]);
		/*
		echo '<br /><b>'.TEXT_COUPON_AKTIV.': '.$_SESSION['sess_coupon'][$key].'</b>'
			.'<br /><a class="float-right underline" href="'
			.$xtLink->_link(array('page' => 'cart', 'params' => 'remove_coupon='.$coupon_hash)).'">'.TEXT_COUPON_REMOVE.'</a>';
		$show_field = false;
		*/
		if(XT_COUPONS_LOGIN == 'false'){
			$show_field = true;
		}elseif($_SESSION['registered_customer']){
			$show_field = true;
		}

		if($show_field == true){
			
	        $tpl = 'coupons_cart_total_bottom.html';
			$tpl_data['arr_coupon'] = $_SESSION['sess_coupon'];
			$new_amount = floatval($_SESSION['cart']->content_total['plain']) -  floatval($_SESSION['sess_coupon']['coupon_amount']);
			if($new_amount<0)
			{
				$new_amount = 0; // safe, cause view data only
			}
			
			if ($_SESSION['sess_coupon']["coupon_amount"]>0) 
				$tpl_data['coupon_amount'] =  $price->_StyleFormat($_SESSION['sess_coupon']["coupon_amount"]);
			else if ($_SESSION['sess_coupon']["coupon_free_shipping"]>0)
				$tpl_data['coupon_amount'] =  TEXT_COUPON_TYPE_FREESHIPPING;
				
			$tpl_data['currency'] = $currency->code;
			$tpl_data['coupon_percent'] = $_SESSION['sess_coupon']["coupon_percent"];
			$tpl_data['new_total'] = $price->_StyleFormat($new_amount);
			
			$coupon_hash = md5($_SESSION['sess_coupon'][$key]);
			$tpl_data['remove_coupon_link'] = $xtLink->_link(array('page' => 'cart', 'paction' => 'confirmation', 'params' => 'remove_coupon='.$coupon_hash));
			
	        $plugin_template = new Template();
	        $plugin_template->getTemplatePath($tpl, 'xt_coupons', '', 'plugin');
	        echo ($plugin_template->getTemplate('', $tpl, $tpl_data));
	    }
	}
}