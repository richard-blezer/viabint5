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

class products_list {

	// constructor load
	function products_list($catID) {
		$this->current_categorey_id = $catID;
		$this->sql_products = new getProductSQL_query();
	}

	function getProductListing () {
		global $xtPlugin, $db, $xtLink,$current_manufacturer_id,$current_category_id,$category, $manufacturer_data;

		($plugin_code = $xtPlugin->PluginCode('products_list:getProductListing_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if (empty($current_manufacturer_id)) {
			$this->sql_products->setPosition('product_listing');

			if ($this->current_categorey_id == 0)
				$this->sql_products->setFilter('Startpage');
				$this->sql_products->setFilter('Categorie', $this->current_categorey_id);

			if (is_data($_GET['filter_id']))
				$this->sql_products->setFilter('Manufacturer', (int)$_GET['filter_id']);
		} else {
			$this->sql_products->setPosition('product_manufacturer_listing');
			$this->sql_products->setFilter('Manufacturer', (int)$current_manufacturer_id);
			if (is_int($current_category_id))
				$this->sql_products->setFilter('Categorie', $current_category_id);		}
		if (is_data($_GET['sorting'])) {
			$this->sql_products->setFilter('Sorting', $_GET['sorting']);
		} else {
			if(empty($current_manufacturer_id))
			{
				if (is_object($category)) {
					if ($category->current_category_data['products_sorting']!='') {
						$sorting = $category->current_category_data['products_sorting'];
						if ($category->current_category_data['products_sorting2']=='DESC') $sorting.='-desc';
						$this->sql_products->setFilter('Sorting', $sorting);
					}
	
				}
			} else {
				if(!empty($manufacturer_data['MANUFACTURER']['products_sorting']))
				{
					$sorting = $manufacturer_data['MANUFACTURER']['products_sorting'];
					if ($manufacturer_data['MANUFACTURER']['products_sorting2']=='DESC') $sorting.='-desc';
					$this->sql_products->setFilter('Sorting', $sorting);					
				}
			}
		}

		$listResult =  _STORE_PRODUCT_LIST_RESULTS;
		($plugin_code = $xtPlugin->PluginCode('products_list:getProductListing_filter')) ? eval($plugin_code) : false;

	    $query = $this->sql_products->getSQL_query();

		$pages = new split_page($query, $listResult, $xtLink->_getParams(array ('next_page', 'info')));

		$this->navigation_count = $pages->split_data['count'];
		$this->navigation_pages = $pages->split_data['pages'];
 		$this->navigation_total = $pages->split_data['total'];
		
		$_count = count($pages->split_data['data']);
		for ($i = 0; $i < $_count;$i++) {
			$size = 'default';
			($plugin_code = $xtPlugin->PluginCode('products_list:getProductListing_size')) ? eval($plugin_code) : false;
			$product = new product($pages->split_data['data'][$i]['products_id'],$size);
			$module_content[] = $product->data;
		}

		($plugin_code = $xtPlugin->PluginCode('products_list:getProductListing_bottom')) ? eval($plugin_code) : false;
		return $module_content;
	}
	/**
	 * 
	 * Get Sort Dropdown List
	 * @return array $data List of Options
	 */
	function getSortDropdown() {
		global $xtPlugin,$_GET;		
		
		$options = array();
		$options[] = array('id'=>'','text'=>TEXT_PLEASE_SELECT);
		$options[] = array('id'=>'price','text'=>SORT_PRICE);
		$options[] = array('id'=>'price-desc','text'=>SORT_PRICE_DESC);
		$options[] = array('id'=>'name','text'=>SORT_NAME);
		$options[] = array('id'=>'name-desc','text'=>SORT_NAME_DESC);
		$options[] = array('id'=>'date','text'=>SORT_DATE);
		$options[] = array('id'=>'date-desc','text'=>SORT_DATE_DESC);

		$data['options'] = $options;
		return $data;
	}
        
        /**
	 * 
	 * Get Filter Dropdown List
	 * @return array $data List of Options
	 */
	function getFilterDropdown() {
		global $xtPlugin, $_GET;		
		
		$options = array();
		$options[] = array('id'=>'free','text'=>FILTER_FREE);
		$options[] = array('id'=>'free-desc','text'=>FILTER_FREE_DESC);
		$options[] = array('id'=>'paid','text'=>FILTER_PAID);
		$options[] = array('id'=>'paid-desc','text'=>FILTER_PAID_DESC);

		$data['options'] = $options;
		return $data;
	}
	/**
	 * 
	 * Check default value of Sort Dropdown
	 * 
	 * @param array $data List of Sort Options
	 * @param string $sort_default default value of Sort Dropdown
	 * @return bool $is_def TRUE if $sort_default is one id of $data
	 */
	public function isSortDropdownDefault($data, $sort_default)
	{
		$is_def = false;
		if(is_array($data))
		{
			foreach($data['options'] as $value){
				if($value['id'] == $sort_default){
					$is_def = true;
					break;
				}
			}
		}
		return $is_def;
	}
}