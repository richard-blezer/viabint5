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

if (ACTIVATE_XT_UPCOMING_PRODUCTS_BOX == 'true'  && isset($xtPlugin->active_modules['xt_upcoming_products'])){

	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/xt_upcoming_products/classes/class.upcoming_products.php';

	if($params['limit']){
		$limit = $params['limit'];
	}else{
		$limit = XT_UPCOMING_PRODUCTS_BOX_LIMIT;
	}

	$upcoming_products_data_array = array('limit'=> $limit,
										 'sorting' => $params['order_by']);

	$upcoming_products_box = new upcoming_products($current_category_id);
	$upcoming_products_list = $upcoming_products_box->getUpcomingProductListing($upcoming_products_data_array);

	if(count($upcoming_products_list) != 0){

		if(ACTIVATE_XT_UPCOMING_PRODUCTS_PAGE==true){
			$show_more_link = true;
		}else{
			$show_more_link = false;
		}

		$tpl_data = array('_upcoming_products'=> $upcoming_products_list, '_show_more_link'=>$show_more_link);
		$show_box = true;
	}else{
		$show_box = false;
	}

}else{
	$show_box = false;
}
?>