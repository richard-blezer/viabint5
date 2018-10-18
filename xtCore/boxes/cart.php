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

if (is_object($_SESSION['cart']) && count($_SESSION['cart']->show_content) > 0){
	
	$tpl_data = array('cart_data' => $_SESSION['cart']->show_content,
					  'data_count' => count($_SESSION['cart']->show_content),
					  'content_count' => $_SESSION['cart']->content_count,
					  'cart_tax' =>  $_SESSION['cart']->content_tax,
					  'cart_total' => $_SESSION['cart']->content_total['formated'],
					  'cart_total_weight' => $_SESSION['cart']->weight,
					  'show_cart_content'=>true);

	global $system_shipping_link;
	$shipping_link = $system_shipping_link->shipping_link;
	if ($shipping_link!='') {
		$tpl_data = array_merge($tpl_data,array('shipping_link'=>$shipping_link));
	}

}else{

	$tpl_data = array('show_cart_content'=>false);

}

$show_box = true;

?>