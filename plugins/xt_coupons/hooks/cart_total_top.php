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
		global $xtLink,$currency;

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
	        $tpl = 'coupons_cart_total_top.html';
			$tpl_data['arr_coupon'] = $_SESSION['sess_coupon'];
			
			if ($_SESSION['sess_coupon']["coupon_percent"]>0)
			{
				$coupon_total_before_discount = 0;
				foreach ($_SESSION['cart']->show_content as $pr)
				{
					$coupon_total_before_discount += ($pr['_original_products_price']['plain']*$pr['products_quantity']);
				}
				$tpl_data['coupon_amount'] =  ' - '.$_SESSION['cart']->discount["formated"].' ('.$_SESSION['sess_coupon']["coupon_percent"].' % )';
				
				$tpl_data['currency'] = $currency->code;
				
				$tpl_data['new_total'] = $currency->code.' '.number_format($new_amount, 2, ',', ' ');
				$coupon_hash = md5($_SESSION['sess_coupon'][$key]);
				$tpl_data['remove_coupon_link'] = $xtLink->_link(array('page' => 'cart', 'paction' => 'confirmation', 'params' => 'remove_coupon='.$coupon_hash));
				$tpl_data['coupon_total_before_discount'] = $currency->code.' '.number_format($coupon_total_before_discount, 2, ',', ' ');
		        $plugin_template = new Template();
		        $plugin_template->getTemplatePath($tpl, 'xt_coupons', '', 'plugin');
		        echo ($plugin_template->getTemplate('', $tpl, $tpl_data));
		   }
	    }
	}
}