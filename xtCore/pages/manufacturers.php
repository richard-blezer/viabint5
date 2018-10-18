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

if(isset($_GET['mnf']) && is_int($_GET['mnf'])){
  	$current_manufacturer_id = (int)$_GET['mnf'];
}
  	
if (empty($current_manufacturer_id)) {

$xtLink->_redirect($xtLink->_link(array('page'=>'index')));

} else {

    ($plugin_code = $xtPlugin->PluginCode('module_manufacturers.php:top')) ? eval($plugin_code) : false;
    
	$list = new products_list('');
  	
  	$man = array('manufacturers_id' => $current_manufacturer_id);
	$man_data = $manufacturer->buildData($man);
	if (!is_array($data)) $data=array();
	$data = array_merge($data, array('MANUFACTURER' => $man_data));

	$manufacturer_data = $data;
	
	$tpl_product_listing = $list->getProductListing();
	
	$heading_text= $man_data['manufacturers_name'];

	$brotkrumen->_addItem($xtLink->_link(array('page'=>'manufacturers', 'params'=>'mnf='.$current_manufacturer_id,'seo_url' => $man_data['url_text'])),$man_data['manufacturers_name']);

	$sort_dropdown = is_array($tpl_product_listing) ? $list->getSortDropdown():'';
  	if(isset($_GET['sorting']) && is_array($sort_dropdown) && $list->isSortDropdownDefault($sort_dropdown, $_GET['sorting'])){
    	$sort_default = $_GET['sorting'];
    }
    else {
    	$sort_default = '';
    }
   
	$tpl_data = array('categories' => $categories_listing,
					  'heading_text' => $heading_text,
					  'manufacturer'=>$data,
					  'current_manufacturer_id'=>$current_manufacturer_id,
					  'product_listing' => $tpl_product_listing,
					  'MANUFACTURER_DROPDOWN' => $manufacturers_dropdown,
					  'NAVIGATION_COUNT' => $list->navigation_count,
					  'NAVIGATION_PAGES' => $list->navigation_pages,
					  'mnf_page' => true,
					  'sort_default' => $sort_default,
					  'sort_dropdown' => $sort_dropdown);

	$listingtemplate = _STORE_TEMPLATE_PRODUCT_LISTING_MANUFACTURERS;

    ($plugin_code = $xtPlugin->PluginCode('module_manufacturers.php:tpl_data')) ? eval($plugin_code) : false;    
    
    $products_listing = $template->getTemplate('listing_smarty','/'._SRV_WEB_CORE.'pages/product_listing/'.$listingtemplate, $tpl_data);

  	$page_data = $products_listing;

}

?>