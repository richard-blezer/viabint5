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

if (ACTIVATE_XT_SPECIAL_PRODUCTS_PAGE == 'true'){

	require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/xt_special_products/classes/class.special_products.php';

	$limit = XT_SPECIAL_PRODUCTS_PAGE_LIMIT;
	
	$order_by = 'p.date_added DESC';
	if ($params['order_by'])
	$order_by = $params['order_by'];
	
	($plugin_code = $xtPlugin->PluginCode('plugin_xt_special_products.php:data_array')) ? eval($plugin_code) : false;

	$special_products_page_data_array = array('limit'=> $limit,
										 	  	  'sorting' => $order_by);

	$special_products_page = new special_products($current_category_id);
	$special_products_page_list = $special_products_page->getSpecialProductListing($special_products_page_data_array);

	$tpl_data = array('heading_text' => TEXT_SPECIAL_PRODUCTS,
						  'product_listing' => $special_products_page_list,
						  'NAVIGATION_COUNT' => $special_products_page->navigation_count,
						  'NAVIGATION_PAGES' => $special_products_page->navigation_pages);


	$tpl = XT_SPECIAL_PRODUCTS_PAGE_TPL;

	if(!empty($params['tpl'])){
		$tpl = $params['tpl'];
	}else{
		$params['tpl'] = $tpl;
	}

	if(is_object($brotkrumen))
	$brotkrumen->_addItem($xtLink->_link(array('page'=>'xt_special_products')),TEXT_SPECIAL_PRODUCTS);

	$template = new Template();
	($plugin_code = $xtPlugin->PluginCode('plugin_xt_special_products.php:tpl_data')) ? eval($plugin_code) : false;
	$page_data = $template->getTemplate('xt_special_products_smarty', '/'._SRV_WEB_CORE.'pages/product_listing/'.$tpl, $tpl_data);
}else{
	$show_page = false;
}
?>