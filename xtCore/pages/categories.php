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

if (empty($current_category_id)) {

$xtLink->_redirect($xtLink->_link(array('page'=>'index')));

} else {
	
	//simple chmod of subcategories
	if( _SYSTEM_SIMPLE_GROUP_PERMISSIONS=='true' && count($category->level)>2){
		$root_cat_id = $category->level[1];
		$root_cat = new category($root_cat_id);
		// redirect to 404 page if category dont exists
		if (!is_array($root_cat->current_category_data)) {
			if (_SYSTEM_MOD_REWRITE_404 == 'true') header("HTTP/1.0 404 Not Found");
			$tmp_link  = $xtLink->_link(array('page'=>'404'));
			$xtLink->_redirect($tmp_link);
		}
	}
	
	// redirect to 404 page if category dont exists
	if (!is_array($category->current_category_data)) {
		if (_SYSTEM_MOD_REWRITE_404 == 'true') header("HTTP/1.0 404 Not Found");
		$tmp_link  = $xtLink->_link(array('page'=>'404'));
		$xtLink->_redirect($tmp_link);
	}
	
	$list = new products_list($current_category_id);

  if (!$template->isTemplateCache('/'._SRV_WEB_CORE.'pages/product_listing/'.$category->current_category_data['listing_template'])) {
	// nocache
	if (!$template->isTemplateCache('/'._SRV_WEB_CORE.'pages/categorie_listing/'.$category->current_category_data['categories_template'])) {
		// nocache
		$data = array_merge($category->buildData($category->current_category_data),array('categorie_listing' => $category->getCategoryListing('',1)));

	if (isset($_GET['filter_id']))
		$manID = (int)$_GET['filter_id'];
	if (isset($_GET['mnf']))
		$manID = (int)$_GET['mnf'];
	if ($manID) {
		$man = array('manufacturers_id' => $manID);
		$man_data = $manufacturer->buildData($man);
		$data = array_merge($data, array('MANUFACTURER' => $man_data));
	}

     if ($category->current_category_data['categories_template'])
			$categories_listing = $template->getTemplate('cat_listing_smarty','/'._SRV_WEB_CORE.'pages/categorie_listing/'.$category->current_category_data['categories_template'], $data);
	} else {
		// cached
		$categories_listing = $template->getCachedTemplate('/'._SRV_WEB_CORE.'pages/categorie_listing/'.$category->current_category_data['categories_template']);
	}

	$category->getBreadCrumbNavigation($current_category_id);

	$tpl_product_listing = $list->getProductListing();

	if (count($tpl_product_listing) > 0) {
		$manufacturer->setPosition('product_listing');
		$manufacturers_dropdown = $manufacturer->getManufacturerSortDropdown((int)$_GET['filter_id']);
 
		if (isset($_GET['mnf'])) {
			$heading_text= sprintf(HEADING_PRODUCTS_MANUFACTURERS, $man_data['NAME']) ;
			$manufacturers_dropdown = $category->getCategoriesDropDown($manID);
            
		}
	}

	$sort_dropdown = is_array($tpl_product_listing) ? $list->getSortDropdown():'';
  	if(isset($_GET['sorting']) && is_array($sort_dropdown) && $list->isSortDropdownDefault($sort_dropdown, $_GET['sorting'])){
    	$sort_default = $_GET['sorting'];
    }
    else {
    	$sort_default = '';
    }
    
	$tpl_data = array('categories' => $categories_listing,
                      'category_data'=>$category->current_category_data,
					  'heading_text' => $heading_text,
					  'product_listing' => $tpl_product_listing,
					  'current_category_id'=>$current_category_id,
					  'MANUFACTURER_DROPDOWN' => $manufacturers_dropdown,
					  'NAVIGATION_COUNT' => $list->navigation_count,
					  'NAVIGATION_PAGES' => $list->navigation_pages,
					  'sort_default' => $sort_default,
					  'sort_dropdown' => $sort_dropdown);

	($plugin_code = $xtPlugin->PluginCode('module_categories.php:tpl_data')) ? eval($plugin_code) : false;	
	if ($error_product_listing)
		$tpl_data = array_merge($tpl_data, array('error_listing' =>$error_product_listing));
    
	$products_listing = $template->getTemplate('listing_smarty','/'._SRV_WEB_CORE.'pages/product_listing/'.$category->current_category_data['listing_template'],$tpl_data);

  } else {
	// cached
	$products_listing = $template->getCachedTemplate('/'._SRV_WEB_CORE.'pages/product_listing/'.$category->current_category_data['listing_template']);
  }

  $page_data = $products_listing;

}

?>