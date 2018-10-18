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

if (isset ($_POST['action']) || isset ($_GET['action'])) {

	$post_data = array();
	$post_data = $_POST;
	$get_data = array();
	$get_data = $_GET;
		
	$data_array = array_merge($post_data, $get_data);

	($plugin_code = $xtPlugin->PluginCode('form_handler.php:data_array_top')) ? eval($plugin_code) : false;

	switch ($data_array['action']) {

		case 'change_currency' :

			if($currency->_checkStore($data_array['new_currency'], $store_handler->shop_id)==true)
			$_SESSION['selected_currency'] = $data_array['new_currency'];

			$link_array = array('page'=>$page->page_name, 'params'=>$xtLink->_getParams(array('action', 'new_currency')));
			if(isset($_POST['paction'])){
				$link_array['paction']=$_POST['paction'];
			}
            if (($page->page_name == 'checkout' || $page->page_name == 'customer' ) && _SYSTEM_SSL == true) {
                $link_array['conn']='SSL';
            }
            ($plugin_code = $xtPlugin->PluginCode('form_handler.php:change_currency_bottom')) ? eval($plugin_code) : false;
            $xtLink->_redirect($xtLink->_link($link_array));

		break;

		case 'change_lang' :
			if($language->_checkStore($data_array['new_lang'], $store_handler->shop_id)==true)
			$_SESSION['selected_language'] = $data_array['new_lang'];

			$link_array = array('page'=>$page->page_name, 'paction'=>$page->page_action,'params'=>$xtLink->_getParams(array('action', 'new_lang')), 'lang_code' => $data_array['new_lang']);

            if (($page->page_name == 'checkout' || $page->page_name == 'customer' ) && _SYSTEM_SSL == true) {
                $link_array['conn']='SSL';
            }
            ($plugin_code = $xtPlugin->PluginCode('form_handler.php:change_lang_bottom')) ? eval($plugin_code) : false;
			$xtLink->_redirect($xtLink->_link($link_array));

		break;

		case 'add_product' :
			($plugin_code = $xtPlugin->PluginCode('form_handler.php:add_product_top')) ? eval($plugin_code) : false;
            //negative quantity
            if($data_array['qty'] < 0){
                $data_array['qty'] = -$data_array['qty'];
            }
            $_SESSION['cart']->_addCart($data_array);

			// $link_array = array('page'=>$page->page_name, 'params'=>$xtLink->_getParams());
			$link_array = array('page'=>'cart');
			$cart_product = new product($data_array['product']);

			$info->_addInfoSession(sprintf(SUCCESS_PRODUCT_ADDED,$cart_product->data['products_name']),'success');

			($plugin_code = $xtPlugin->PluginCode('form_handler.php:add_product_bottom')) ? eval($plugin_code) : false;
			$xtLink->_redirect($xtLink->_link($link_array));

		break;

		case 'update_product' :
			($plugin_code = $xtPlugin->PluginCode('form_handler.php:update_product_top')) ? eval($plugin_code) : false;

			for ($i = 0, $n = sizeof($data_array['cart_delete']); $i < $n; $i++) {
				$_SESSION['cart']->_deleteContent($data_array['cart_delete'][$i]);
			}

			if(!is_data($data_array['cart_delete']))
			$data_array['cart_delete'] = array();

			for ($i = 0, $n = sizeof($data_array['products_key']); $i < $n; $i++) {

					($plugin_code = $xtPlugin->PluginCode('form_handler.php:update_product_update')) ? eval($plugin_code) : false;

					if(!in_array($data_array['products_key'][$i], $data_array['cart_delete'])){
						$data = array('products_key'=>$data_array['products_key'][$i], 'qty'=>$data_array['qty'][$i]);
						$_SESSION['cart']->_updateCart($data);
					}

			}

			$link_array = array('page'=>$page->page_name);
			($plugin_code = $xtPlugin->PluginCode('form_handler.php:update_product_bottom')) ? eval($plugin_code) : false;
			$xtLink->_redirect($xtLink->_link($link_array));

		break;

		case 'select_address' :
			($plugin_code = $xtPlugin->PluginCode('form_handler.php:select_address_top')) ? eval($plugin_code) : false;
			$_SESSION['customer']->_setAdress($data_array['adID'],$data_array['adType']);

			if($data_array['adType']=='payment')
			unset($_SESSION['selected_payment']);

			if($data_array['adType']=='shipping')
			unset($_SESSION['selected_shipping']);

			$link_array = array('page'=>$page->page_name, 'paction'=>$data_array['adType'], 'conn'=>'SSL');
			($plugin_code = $xtPlugin->PluginCode('form_handler.php:select_address_bottom')) ? eval($plugin_code) : false;
			$xtLink->_redirect($xtLink->_link($link_array));

		break;

		default:

	    ($plugin_code = $xtPlugin->PluginCode('form_handler.php:data_array_bottom')) ? eval($plugin_code) : false;
	}
}

?>