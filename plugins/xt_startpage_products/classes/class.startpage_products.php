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

class startpage_products extends products_list {


	function getStartPageProductListing ($data) {
		global $xtPlugin, $xtLink, $db;

		($plugin_code = $xtPlugin->PluginCode('plugin_startpage_products:getStartPageProductListing_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->sql_products->setPosition('startpage_products');
		$this->sql_products->setFilter('Language');
		
		if (is_data($_GET['filter_id']))
			$this->sql_products->setFilter('Manufacturer', (int)$_GET['filter_id']);

		if (is_data($_GET['sorting'])) {
			$this->sql_products->setFilter('Sorting', $_GET['sorting']);
		}elseif(is_data($data['sorting'])){
			$this->sql_products->setSQL_SORT($data['sorting']);
		} else {
			$this->sql_products->setSQL_SORT("p.products_startpage_sort");
		}

		$this->sql_products->setSQL_WHERE("and p.products_startpage = '1'");

		($plugin_code = $xtPlugin->PluginCode('plugin_startpage_products:getStartPageProductListing_query')) ? eval($plugin_code) : false;

		$query = $this->sql_products->getSQL_query();

		$pages = new split_page($query, $data['limit'], $xtLink->_getParams(array ('next_page', 'info')));

		$this->navigation_count = $pages->split_data['count'];
		$this->navigation_pages = $pages->split_data['pages'];

		$count = count($pages->split_data['data']);
		for ($i = 0; $i < $count;$i++) {
			$size = 'default';
			($plugin_code = $xtPlugin->PluginCode('plugin_startpage_products:getStartPageProductListing_size')) ? eval($plugin_code) : false;
			$product = new product($pages->split_data['data'][$i]['products_id'],$size);
			($plugin_code = $xtPlugin->PluginCode('plugin_startpage_products:getStartPageProductListing_data')) ? eval($plugin_code) : false;
			$module_content[] = $product->data;
		}

		($plugin_code = $xtPlugin->PluginCode('plugin_startpage_products:getStartPageProductListing_bottom')) ? eval($plugin_code) : false;

		return $module_content;
	}
}
?>