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

class getProductSQL_query extends SQL_query{
	public $filterFunctions; // sql filter functions array
	public $a_sql_table, $a_sql_where, $a_sql_sort, $a_sql_group;

	function __construct() {
		global $xtPlugin;

		$this->setSQL_TABLE(TABLE_PRODUCTS . " p ");
		$this->setSQL_WHERE(" p.products_id != '' ");
        $this->user_position=USER_POSITION; 
		($plugin_code = $xtPlugin->PluginCode('class.getProductSQL_query.php:getProductSQL_query_bottom')) ? eval($plugin_code) : false;
	}

	function getSQL_query($sql_cols = 'p.products_id', $filter_type='string') {

	    if ($this->user_position =='store') {
			$this->setFilter('GroupCheck');
			$this->setFilter('StoreCheck');
			$this->setFilter('Fsk18');
			$this->setFilter('Status');
			$this->setFilter('Seo');
			$this->setFilter('Listing');
			if (_STORE_STOCK_CHECK_DISPLAY=='false' && _SYSTEM_STOCK_HANDLING=='true') {
				$this->setFilter('Stock');
			}
	    }
		$this->getFilter();
		$this->getHooks();

		if( _SYSTEM_SIMPLE_GROUP_PERMISSIONS=='true') $sql = 'SELECT DISTINCT '.$sql_cols.$this->a_sql_cols.' FROM '.$this->a_sql_table;
		else $sql = 'SELECT '.$sql_cols.$this->a_sql_cols.' FROM '.$this->a_sql_table;
		if (is_data($this->a_sql_where))
			$sql.=' WHERE '.$this->a_sql_where;
		if (is_data($this->a_sql_group))
			$sql.=' GROUP BY '.$this->a_sql_group;
		if (is_data($this->a_sql_sort))
			$sql.=' ORDER BY '.$this->a_sql_sort;
		if (is_data($this->a_sql_limit))
			$sql.=' LIMIT '.$this->a_sql_limit;
		return $sql;
	}


	//////////////////////////////////////////////////////
	// products filter function

	function F_Status () {
		$this->setSQL_WHERE(" and p.products_status = '1'");
	}

	function F_Stock () {
		$this->setSQL_WHERE(" and p.products_quantity > 0");
	}

/* F_StoreID
 *
 * return part of string with store_id based on current store_id
 *
 * @param (string) ($table) - table to check for column into
 * @param (string) ($column) - column to be checked
 * @param (string) ($sql_colum) - column with prefix to be added in sql
 * @return string
 */
	function F_StoreID($table,$column,$sql_colum){
		global $db,$store_handler;
		$add_to_sql='';
		if ($sql_colum=='') $sql_colum = $column;
		
		$rs=$db->Execute(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = ? ",
			array(_SYSTEM_DATABASE_DATABASE, $table, $column)
		);
		if ($rs->RecordCount()>0)
		{
			$add_to_sql =  " and ".$sql_colum."='".$store_handler->shop_id."'";
		}
		return $add_to_sql;
	}
	
	function F_Seo($lang_code='') {
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;

		$add_to_sql = $this->F_StoreID(TABLE_SEO_URL,'store_id','su.store_id');
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_SEO_URL . " su ON (p.products_id = su.link_id and su.link_type='1' ".$add_to_sql.")");
		$this->setSQL_WHERE("and su.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);
	}

	function F_Language($lang_code=''){
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;

		$add_to_sql = $this->F_StoreID(TABLE_PRODUCTS_DESCRIPTION,'products_store_id','pd.products_store_id');
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id");
		$this->setSQL_WHERE("and pd.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);
	}

	function F_Manufacturer ($array) {
		$this->setSQL_WHERE("and p.manufacturers_id = '".$array."'");
	}
	function F_Startpage () {
		global $store_handler;
		$this->setSQL_WHERE("and p.products_startpage_" . $store_handler->shop_id ."=1 ");
	}
	function F_GroupCheck () {
		global $customers_status;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($customers_status->customers_status_id)) {

			$perm_array = array(array(
				'type'=>'group_permission',
				'table'=>TABLE_PRODUCTS_PERMISSION,
				'simple_permissions_table'=>TABLE_CATEGORIES_PERMISSION,
				'key'=>'products_id',
				'simple_permissions' => _SYSTEM_SIMPLE_GROUP_PERMISSIONS,
				'simple_permissions_key' => 'products_id',
				'pref'=>'p'
			));

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}
	
	function F_StoreCheck () {
		global $store_handler;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($store_handler->shop_id)) {
			$perm_array = array(array(
				'type'=>'shop',
				'table'=>TABLE_PRODUCTS_PERMISSION,
				'simple_permissions_table'=>TABLE_CATEGORIES_PERMISSION,
				'key'=>'products_id',
				'simple_permissions' => _SYSTEM_SIMPLE_GROUP_PERMISSIONS,
				'simple_permissions_key' => 'products_id',
				'pref'=>'p'
			));

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}		
	
	function F_MultiCheck ($params='') {
		global $xtPlugin;

	    ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':F_MultiCheck')) ? eval($plugin_code) : false;
	}		
	
	function F_Fsk18 () {
		global $customers_status;

		if ($customers_status->customers_fsk18_display == '0') {
			$this->setSQL_WHERE("and p.products_fsk18!=1");
		}
	}

	function F_Listing () {
		global $xtPlugin;

		if($this->position != 'product_info')
		($plugin_code = $xtPlugin->PluginCode('class.getProductSQL_query.php:F_Listing')) ? eval($plugin_code) : false;

	}

	function F_Categorie ($data = 0) {
		
		$add_to_sql = $this->F_StoreID(TABLE_PRODUCTS_TO_CATEGORIES,'store_id','p2c.store_id');
		
		$this->setSQL_TABLE("INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id ".$add_to_sql);
		$this->setSQL_WHERE("and p2c.categories_id = ".$data.$add_to_sql);
	}
	function F_Categorie_Recursive ($data = 0) {
		
		$add_to_sql = $this->F_StoreID(TABLE_PRODUCTS_TO_CATEGORIES,'store_id','p2c.store_id');
		
		$this->setSQL_TABLE("INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id = p.products_id ".$add_to_sql." LEFT JOIN ".TABLE_CATEGORIES." c ON p2c.categories_id = c.categories_id");
		$this->setSQL_WHERE("and ".$data." in (c.categories_id, c.parent_id) ".$add_to_sql);
	}
	function F_Sorting($sort) {
		global $customers_status,$xtPlugin;
			switch ($sort) {


			case 'price' :
				$data = date("Y-m-d H:i:s", mktime(date(H), date(i), date(s), date(m), date(d), date(Y)));
				if (_SYSTEM_GROUP_CHECK == 'true') {
					$this->setSQL_TABLE('LEFT JOIN '.TABLE_PRODUCTS_PRICE_SPECIAL.' pps ON p.products_id = pps.products_id'." and (pps.date_available <= '" . $data . "' or pps.date_available = 0) and (pps.date_expired >= '" . $data . "'  or pps.date_expired = 0) and (pps.group_permission_" . $customers_status->customers_status_id . "=1 or pps.group_permission_all=1)");
				}else{
					$this->setSQL_TABLE('LEFT JOIN '.TABLE_PRODUCTS_PRICE_SPECIAL.' pps ON p.products_id = pps.products_id'." and (pps.date_available <= '" . $data . "' or pps.date_available = 0) and (pps.date_expired >= '" . $data . "'  or pps.date_expired = 0)");
				}
				
				$plugin_code = $xtPlugin->PluginCode('class.product_sql_query.php:F_Sorting_price');
				if ($plugin_code) eval($plugin_code);
				else $this->setSQL_COLS(', IF(pps.specials_price>0,pps.specials_price,p.products_price) as sort_price');
				
				$this->setSQL_SORT('sort_price');
				break;

			case 'price-desc' :
				$data = date("Y-m-d H:i:s", mktime(date(H), date(i), date(s), date(m), date(d), date(Y)));
			if (_SYSTEM_GROUP_CHECK == 'true') {
					$this->setSQL_TABLE('LEFT JOIN '.TABLE_PRODUCTS_PRICE_SPECIAL.' pps ON p.products_id = pps.products_id'." and (pps.date_available <= '" . $data . "' or pps.date_available = 0) and (pps.date_expired >= '" . $data . "'  or pps.date_expired = 0) and (pps.group_permission_" . $customers_status->customers_status_id . "=1 or pps.group_permission_all=1)");
				}else{
					$this->setSQL_TABLE('LEFT JOIN '.TABLE_PRODUCTS_PRICE_SPECIAL.' pps ON p.products_id = pps.products_id'." and (pps.date_available <= '" . $data . "' or pps.date_available = 0) and (pps.date_expired >= '" . $data . "'  or pps.date_expired = 0)");
				}
				$plugin_code = $xtPlugin->PluginCode('class.product_sql_query.php:F_Sorting_price_desc');
				if ($plugin_code) eval($plugin_code);
				else $this->setSQL_COLS(', IF(pps.specials_price>0,pps.specials_price,p.products_price) as sort_price');
				$this->setSQL_SORT('sort_price DESC');
				break;

			case 'name' :
				$this->setFilter('Language');
				$this->setSQL_SORT(' pd.products_name');
				break;

			case 'name-desc' :
				$this->setFilter('Language');
				$this->setSQL_SORT(' pd.products_name DESC');
				break;
				
			case 'sort' :
				$this->setSQL_SORT(' p.products_sort');
				break;

			case 'sort-desc' :
				$this->setSQL_SORT(' p.products_sort DESC');
				break;
				
			case 'order' :
				$plugin_code = $xtPlugin->PluginCode('class.product_sql_query.php:F_Sorting_order');
				if ($plugin_code) eval($plugin_code);
				else $this->setSQL_COLS(', p.products_ordered as sort_ordered');
				
				$this->setSQL_SORT(' sort_ordered,p.products_id');
				break;

			case 'order-desc' :
				$plugin_code = $xtPlugin->PluginCode('class.product_sql_query.php:F_Sorting_order_desc');
				if ($plugin_code) eval($plugin_code);
				else $this->setSQL_COLS(', p.products_ordered as sort_ordered');
				
				$this->setSQL_SORT(' sort_ordered DESC,p.products_id'); //$this->setSQL_SORT(' p.products_ordered DESC');
				break;

			case 'date' :
				if ($this->position == 'products_specials')
					$this->setSQL_SORT(' s.date_added');
				else
					$this->setSQL_SORT(' p.date_added');
				break;

			case 'date-desc' :
				if ($this->position == 'products_specials')
					$this->setSQL_SORT(' s.date_added DESC');
				else
					$this->setSQL_SORT(' p.date_added DESC');
				break;

			default:
				$this->setSQL_SORT(' p.products_sort');
				break;
				//return false;
		}
		
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.getProductSQL_query.php:F_Sorting')) ? eval($plugin_code) : false;				
	}

	function F_Date ($date_limit) {
		if ($date_limit != '0') {
			$date = date("Y.m.d", mktime(1, 1, 1, date(m), date(d) - $date_limit, date(Y)));
			$this->setSQL_WHERE(" and p.date_added > '" . $date . "' ");
		}
	}
}