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

class special_products {

	function special_products($catID) {
		$this->current_categorey_id = $catID;
		$this->sql_products = new special_products_query();
	}

	function getSpecialProductListing ($data) {
		global $xtPlugin, $xtLink, $db;

		($plugin_code = $xtPlugin->PluginCode('plugin_special_products:getSpecialProductListing_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->sql_products->setPosition('special_products');

		if ($this->current_categorey_id != 0)
		$this->sql_products->setFilter('Categorie', $this->current_categorey_id);

		if (is_data($_GET['filter_id']))
			$this->sql_products->setFilter('Manufacturer', (int)$_GET['filter_id']);

        
		$this->sql_products->setSQL_TABLE(" RIGHT JOIN ". TABLE_PRODUCTS_PRICE_SPECIAL." s ON (s.products_id = p.products_id) ");
		$this->sql_products->setSQL_WHERE(" and s.status = '1'");
		
		if(isset($xtPlugin->active_modules['xt_master_slave']) &&  $xtPlugin->active_modules['xt_master_slave']== 'true')
		{
			if (XT_SPECIAL_PRODUCTS_SHOW_TYPE == 'nothing') {
				$this->sql_products->setSQL_WHERE(" and (p.products_master_flag ='0' OR p.products_master_flag IS NULL) ");
			}
			
			if (XT_SPECIAL_PRODUCTS_SHOW_TYPE == 'slave') {
				$this->sql_products->setSQL_WHERE(" and (p.products_master_flag ='0' OR p.products_master_flag IS NULL)and (p.products_master_model!='' or p.products_master_model IS NOT NULL) ");
				$this->sql_products->setPosition('special_products_plugin_ms');
			}
			
			if (XT_SPECIAL_PRODUCTS_SHOW_TYPE == 'master') {
				$this->sql_products->setSQL_WHERE(" and (p.products_master_flag ='1' OR p.products_master_model = '') ");
			}
        }else{
			if (is_data($_GET['sorting'])) {
				$this->sql_products->setFilter('Sorting', $_GET['sorting']);
			}elseif(is_data($data['sorting'])){
				$this->sql_products->setSQL_SORT($data['sorting']);
			}
		}
		$this->sql_products->setFilter('SpecialGroupCheck', '');
		$this->sql_products->setFilter('DateFrom', '');
		$this->sql_products->setFilter('DateTo', '');

		$this->sql_products->setSQL_GROUP('p.products_id');

		($plugin_code = $xtPlugin->PluginCode('plugin_special_products:getSpecialProductListing_query')) ? eval($plugin_code) : false;

		$query = $this->sql_products->getSQL_query();

		$pages = new split_page($query, $data['limit'], $xtLink->_getParams(array ('next_page', 'info')), '', 'false');

		$this->navigation_count = $pages->split_data['count'];
		$this->navigation_pages = $pages->split_data['pages'];

		$count = count($pages->split_data['data']);
		for ($i = 0; $i < $count;$i++) {
			$size = 'default';
			($plugin_code = $xtPlugin->PluginCode('plugin_special_products:getSpecialProductListing_size')) ? eval($plugin_code) : false;
			$product = new product($pages->split_data['data'][$i]['products_id'],$size);
			($plugin_code = $xtPlugin->PluginCode('plugin_special_products:getSpecialProductListing_data')) ? eval($plugin_code) : false;
			$module_content[] = $product->data;
		}

		($plugin_code = $xtPlugin->PluginCode('plugin_special_products:getSpecialProductListing_bottom')) ? eval($plugin_code) : false;

		return $module_content;
	}

}

class special_products_query extends getProductSQL_query{
	
	function F_DateFrom(){
		$timestamp = time();
		$data = date("Y-m-d H:i:s", $timestamp);
		$this->setSQL_WHERE(" and (s.date_available <= '" . $data . "' or s.date_available = 0)");

	}	
	
	function F_DateTo(){
		$timestamp = time();
		$data = date("Y-m-d H:i:s", $timestamp);
		$this->setSQL_WHERE(" and (s.date_expired >= '" . $data . "'  or s.date_expired = 0)");

	}	
	

	function F_SpecialGroupCheck () {
		global $customers_status;

		if (_SYSTEM_GROUP_CHECK == 'true') {
			$this->setSQL_WHERE("and (s.group_permission_" . $customers_status->customers_status_id . "=1 or s.group_permission_all=1) ");
		}
	}

}

?>