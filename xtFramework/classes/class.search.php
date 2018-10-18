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

class search{

	public $search_data = array();

	function search(){
		global $xtPlugin ;

		($plugin_code = $xtPlugin->PluginCode('class.search.php:search_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->sql_products = new search_query();
	}

	function _search($data){
		global $db, $xtPlugin, $current_category_id, $xtLink, $category;

		if(!empty($data['mnf'])) $data['mnf']=(int)$data['mnf'];
		if(!empty($data['cat'])) $data['cat']=(int)$data['cat'];
		
		($plugin_code = $xtPlugin->PluginCode('class.search.php:_search_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
        
		$this->sql_products->setPosition('getSearchData');
		$this->sql_products->setFilter('Language');

		($plugin_code = $xtPlugin->PluginCode('class.search.php:_search_filter')) ? eval($plugin_code) : false;

		$this->sql_products->setFilter('Keywords', $data, '', 'array');

		if(!empty($data['mnf']))
		$this->sql_products->setFilter('Manufacturer', $data['mnf']);

		if(!empty($data['cat']) && $data['subkat']=='on'){
			$rc = $category->getChildCategoriesIDs($data['cat']);
			$this->sql_products->setSQL_TABLE("INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id LEFT JOIN ".TABLE_CATEGORIES." c ON p2c.categories_id = c.categories_id");
			
            
			if(count($rc) > 0){			
				$this->sql_products->setSQL_WHERE("and p2c.categories_id in (".$data['cat'].", ".implode(',',$rc).")");
			}
			else{
				$this->sql_products->setSQL_WHERE("and p2c.categories_id in (".$data['cat'].")");
			}
            
           
		}elseif(!empty($data['cat'])){
			$this->sql_products->setFilter('Categorie', $data['cat']);
		}else{
            
            if( _SYSTEM_SIMPLE_GROUP_PERMISSIONS=='true'){
                $cat_list = $category->getAllCategoriesList();
                $cat_listIDs = array();
                foreach($cat_list as $k=>$v){
                    $cat_listIDs[] = $v['categories_id'];
                }
                $this->sql_products->setSQL_TABLE("INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id LEFT JOIN ".TABLE_CATEGORIES." c ON p2c.categories_id = c.categories_id");

                $this->sql_products->setSQL_WHERE("and p2c.categories_id in (".implode(',',$cat_listIDs).")");
            }
        }
        
        
        
		($plugin_code = $xtPlugin->PluginCode('class.search.php:_search_SQL')) ? eval($plugin_code) : false;
		$query = "".$this->sql_products->getSQL_query()."";	

		$pages = new split_page($query, _STORE_SEARCH_RESULTS, $xtLink->_getParams(array ('next_page', 'info')));

		$this->navigation_count = $pages->split_data['count'];
		$this->navigation_pages = $pages->split_data['pages'];

		$_count = count($pages->split_data['data']);
		$this->saveSearchResults($_count, $data['keywords']);
		
		for ($i = 0; $i < $_count;$i++) {
			$size = 'default';
			($plugin_code = $xtPlugin->PluginCode('class.search.php:_search_data')) ? eval($plugin_code) : false;
			$product = new product($pages->split_data['data'][$i]['products_id'],$size);
			$module_content[] = $product->data;
		}

		($plugin_code = $xtPlugin->PluginCode('class.search.php:_search_bottom')) ? eval($plugin_code) : false;
		
		$this->search_data = $module_content;
		return $module_content;

	}
	
	public function saveSearchResults($count, $keyword)
	{
		global $db, $store_handler;

		$count = (int)$count;
		$keyword = trim((string)$keyword);
		
		if($keyword != 'Suchbegriff eingeben' && $keyword != '')
		{
			$sql = 'INSERT INTO ' . TABLE_SEARCH . '(keyword, result_count, request_count, last_date, shop_id) 
					VALUES (?, ?, "1", DATE(NOW()), ?)
					ON DUPLICATE KEY UPDATE
					request_count = (request_count + 1),
					last_date = (DATE(NOW())),
					result_count = ?;';
			
			$db->Execute($sql, array($keyword, $count, $store_handler->shop_id, $count));
		}
	}
}