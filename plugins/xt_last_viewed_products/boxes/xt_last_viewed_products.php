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


if (!isset($xtPlugin->active_modules['xt_last_viewed_products'])){
// Box nicht anzeigen 
	$show_box = false;
} else{
	include_once 'plugins/xt_last_viewed_products/classes/class.xt_last_viewed_products.php';
	$last_viewed_products_obj = new last_viewed_products($current_category_id);
	$last_viewed_products = $last_viewed_products_obj->getLastViewedProductListing();
	if(count($last_viewed_products) != 0){
		$tpl_data = array('_last_viewed_products'=> $last_viewed_products);
		$show_box = true;
	}
	else {
		$show_box = false;
	}
}