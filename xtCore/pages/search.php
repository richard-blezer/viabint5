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
$search_flag = false;

($plugin_code = $xtPlugin->PluginCode('module_search.php:top')) ? eval($plugin_code) : false;

$manufacturers_list = $manufacturer->getManufacturerList('default','box');
$cat_list = $category->getAllCategoriesList();

$tmp_data[] = array('id'=>'', 'text'=>TEXT_LOOK_FOR_ALL);
$cat_list = array_merge($tmp_data, $cat_list);
if (is_array($manufacturers_list)) {
	$manufacturers_list = array_merge($tmp_data, $manufacturers_list);
} else {
	$manufacturers_list = $tmp_data;
}

$search = new search();
$search_result = array();

($plugin_code = $xtPlugin->PluginCode('module_search.php:search')) ? eval($plugin_code) : false;

$template = new Template();
$search_flag = true;
if(isset($_GET['keywords']) && $_GET['keywords']=='') {
    $search_flag = false;
    $info->_addInfo(ERROR_NO_KEYWORDS);
}

if (strlen($_GET['keywords'])<=_SYSTEM_SEARCH_MIN_CHARS) {
    $search_flag = false;
    $info->_addInfo(ERROR_NO_KEYWORDS);
}

if(!empty($_GET['keywords']) && $search_flag==true){
	$search_flag = true;
	while (list ($key, $value) = each($_GET)) {
		$search_array[$filter->_filter($key)] = $filter->_filter($value);
	}

	$search->_search($search_array);
}

($plugin_code = $xtPlugin->PluginCode('module_search.php:search_data')) ? eval($plugin_code) : false;

if(count($search->search_data)>0){
	$search_result = array('product_listing' => $search->search_data,
					       'NAVIGATION_COUNT' => $search->navigation_count,
					       'NAVIGATION_PAGES' => $search->navigation_pages);

	$tpl = 'product_listing/'._STORE_TEMPLATE_PRODUCT_SEARCH_RESULT;

}else{
	if($search_flag==true){
		$info->_addInfo(ERROR_NO_SEARCH_RESULT, 'warning');
	}
	$tpl = 'search.html';
}

$brotkrumen->_addItem($xtLink->_link(array('page'=>'search')),TEXT_SEARCH);

//GET Params for Templates
if(isset($_GET['cat'])){
	$default_cat = (int)$_GET['cat'];
}
else{
	$default_cat = '';
} 

if(isset($_GET['mnf'])){
	$default_mnf = (int)$_GET['mnf'];
}
else{
	$default_mnf = '';
}

if(isset($_GET['subkat']) && $_GET['subkat'] == 'on'){
	$checked_subcat = 'checked';
}
else{
	$checked_subcat = '';
}

if(isset($_GET['sdesc']) && $_GET['sdesc'] == 'on'){
	$checked_sdesc = 'checked';
}
else{
	$checked_sdesc = '';
}

if(isset($_GET['desc']) && $_GET['desc'] == 'on'){
	$checked_desc = 'checked';
}
else{
	$checked_desc = '';
}

$tpl_data = array(	'message'=>$info->info_content, 
					'mnf_data'=>$manufacturers_list, 
					'default_mnf'=>$default_mnf, 
					'cat_data'=>$cat_list, 
					'default_cat'=>$default_cat, 
					'checked_subcat'=>$checked_subcat,
					'checked_desc'=>$checked_desc,
					'checked_sdesc'=>$checked_sdesc);
$tpl_data = array_merge($tpl_data, $search_result);

($plugin_code = $xtPlugin->PluginCode('module_search.php:default_tpl_data')) ? eval($plugin_code) : false;
$page_data = $template->getTemplate('smarty', '/'._SRV_WEB_CORE.'pages/'.$tpl, $tpl_data);
?>