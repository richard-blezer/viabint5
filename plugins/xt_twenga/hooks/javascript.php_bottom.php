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
 # @version $Id: javascript.php_bottom.php 6060 2013-03-14 13:10:33Z mario $
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


global $currency, $db;

// customer is on page just before the payment
if ($page->page_name=='checkout' and $page->page_action=='confirmation') {

	// retrieve Shop ID - required
	$shopId_num = $_SESSION['customer']->customer_info['shop_id'];
	if ($shopId_num > 0) {
		// create twenga object
		require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_twenga/classes/class.xt_twenga.php');
		$xt_twenga = new xt_twenga();

		// set twenga ID via shop ID
		$idOkay_bol = $xt_twenga->setTwengaId(0, $shopId_num);
		$twengaId_num = $xt_twenga->getTwengaId();
		$twengaData_arr = $xt_twenga->getTwengaData($twengaId_num);

		// get cart content from SESSION
		$cart_obj = $_SESSION['cart'];
		$content_arr = $cart_obj->_getContent();
		$subContent_arr = $cart_obj->_getSubContent();
		
		// basket_id not always stored in cart products
		$basketId_bol = false;

		
		// set params for the twenga API call
		$twengaParams_arr = array(
			"PARTNER_AUTH_KEY" => $twengaData_arr['tw_partner_auth_key'],
			"key" => $twengaData_arr['tw_hash_key']
		);
		$twengaParams_arr['currency'] = $currency->code;
		
		$cartProducts_arr = $content_arr['products'];
		foreach ($cartProducts_arr as $productKey => $productValues) {
			if (isset($productValues['basket_id']) and $productValues['basket_id'] > 0) {
				$twengaParams_arr['basket_id'] = $productValues['basket_id'];
				$twengaParams_arr['user_id'] = $productValues['customers_id'];
				$twengaParams_arr['tax'] = $productValues['products_tax_rate'];
				$basketId_bol = true;
			}
			else {
				// remember values in case basket_id is missing
				$pId_num = $productValues['products_id'];
				$uId_num = $productValues['customers_id'];
				$tax_num = $productValues['products_tax_rate'];
			}
		}
		
		if (!$basketId_bol) {
			// basket_id not found in cart
			$basketId_res = $db->Execute("SELECT basket_id FROM ".TABLE_CUSTOMERS_BASKET." WHERE customers_id=".$uId_num." AND products_id=".$pId_num);
			$basketId_arr = $basketId_res->getArray();

			if (isset($basketId_arr[0]['basket_id']) and $basketId_arr[0]['basket_id'] > 0) {
				$twengaParams_arr['basket_id'] = $basketId_arr[0]['basket_id'];
				$twengaParams_arr['user_id'] = $uId_num;
				$twengaParams_arr['tax'] = $tax_num;
				$basketId_bol = true;
			}
		}
		
		
		$twengaParams_arr['total_ht'] = $content_arr['total_otax'];
		$twengaParams_arr['total_ttc'] = $content_arr['total'];
		$twengaParams_arr['tva'] = $content_arr['tax'][1];
		$twengaParams_arr['payment_method'] = $_SESSION['selected_payment'];
		$twengaParams_arr['shipping'] = $subContent_arr['total'];
		// END - set params for the twenga API call
		
		
		// get tracking script
		$twengaReply_str = $xt_twenga->getTrackingScript($twengaParams_arr);
		if ($twengaReply_str !== false) {
			echo $twengaReply_str;
		}
		
	} // endif $shopId_num
} // endif confirmation




// customer is on success page
if ($page->page_name=='checkout' and $page->page_action=='success') {

	// retrieve Shop ID - required
	$shopId_num = $_SESSION['customer']->customer_info['shop_id'];
	if ($shopId_num > 0) {
		// create twenga object
		require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_twenga/classes/class.xt_twenga.php');
		$xt_twenga = new xt_twenga();
		// set twenga ID via shop ID
		$idOkay_bol = $xt_twenga->setTwengaId(0, $shopId_num);
		$twengaId_num = $xt_twenga->getTwengaId();
		$twengaData_arr = $xt_twenga->getTwengaData($twengaId_num);
		
		$params_arr = array(
			"PARTNER_AUTH_KEY" => $twengaData_arr['tw_partner_auth_key'],
			"key" => $twengaData_arr['tw_hash_key'],
			"basket_id" => $_SESSION['twenga']['basket_id'],
			"user_id" => $_SESSION['twenga']['user_id']
		);
		
		$cart_obj = $_SESSION['cart'];
		$content_arr = $cart_obj->_getContent();
		
		
		// check for empty cart = order successful
		if (empty($content_arr['products']) and $content_arr['total'] == 0) {
			// validate order at twenga
			$xt_twenga->confirmOrder(true, $params_arr);
		}
		else {
			// cancel order at twenga
			$xt_twenga->confirmOrder(false, $params_arr);
		}
		
	} // endif shopId_num
	
} // endif success

?>