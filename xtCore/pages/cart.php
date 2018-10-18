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
$no_index_tag = true; 

($plugin_code = $xtPlugin->PluginCode('module_cart.php:cart_top')) ? eval($plugin_code) : false;


if (is_object($_SESSION['cart']) && count($_SESSION['cart']->show_content) > 0){
	
	$_SESSION['cart']->_checkCustomersStatusRange();
	$tpl_data = array('cart_data' => $_SESSION['cart']->show_content,
						  'cart_tax' =>  $_SESSION['cart']->content_tax,
						  'cart_total' => $_SESSION['cart']->content_total['formated'],
						  'cart_total_weight' => $_SESSION['cart']->weight,
						  'message'=>$info->info_content,
						  'show_cart_content'=>true);

	$shipping_link = $system_shipping_link->shipping_link;
	if ($shipping_link!='') {
		$tpl_data = array_merge($tpl_data,array('shipping_link'=>$shipping_link));
	}
	
	($plugin_code = $xtPlugin->PluginCode('module_cart.php:tpl_data')) ? eval($plugin_code) : false;
	if(isset($plugin_return_value))
	return $plugin_return_value;

}else{

	if (_CUST_STATUS_SHOW_PRICE=='1') {
		$info->_addInfo(WARNING_EMTPY_CART,'warning');
	} else {
		$info->_addInfo(WARNING_NO_PRICE_ALLOWED,'warning');
	}

	$tpl_data = array('show_cart_content'=>false,'message'=>$info->info_content);

}
$brotkrumen->_addItem($xtLink->_link(array('page'=>'cart')),TEXT_CART);
$template = new Template();
$tpl = 'cart.html';
($plugin_code = $xtPlugin->PluginCode('module_cart.php:tpl_data')) ? eval($plugin_code) : false;
if(isset($plugin_return_value))
return $plugin_return_value;
$page_data = $template->getTemplate('smarty', '/'._SRV_WEB_CORE.'pages/'.$tpl, $tpl_data);
?>