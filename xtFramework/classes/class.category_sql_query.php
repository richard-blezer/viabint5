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

class getCategorySQL_query extends SQL_query{

	public $filterFunctions; // sql filter functions array
	public $a_sql_table; 
	public $a_sql_where;
	public $a_sql_sort;
	public $a_sql_group;
	protected $_selectTables = array('c.*');
	
	function getCategorySQL_query() {
		global $xtPlugin;

		$this->setSQL_TABLE(TABLE_CATEGORIES . " c ");
		$this->setSQL_WHERE(" c.categories_status = '1'");
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':class_top')) ? eval($plugin_code) : false;
	}

	function getSQL_query($sql_cols = 'c.categories_id') {
		global $xtPlugin;
		
		if (USER_POSITION =='store') {
			$this->setFilter('GroupCheck');
			$this->setFilter('StoreCheck');
	    }
		
	    ($plugin_code = $xtPlugin->PluginCode('class.category_sql_query.php:getSQL_query_filter')) ? eval($plugin_code) : false;
	    
		$this->getFilter();
		$this->getHooks();

		$sql = 'SELECT '.$sql_cols.$this->a_sql_cols.' FROM '.$this->a_sql_table;
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
	
	/* F_StoreID
	 *
	 * return sql with store id if the store id field exists
	 *
	 * @param (string) ($table) - table to check into
	 * @param (string) ($column) - store id column in $table
	 * @param (string) ($sql_colum) - store id with prefix for sql string
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
        $add_to_sql2 = $this->F_StoreID(TABLE_CATEGORIES_CUSTOM_LINK_URL,'store_id','cl.store_id');
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_SEO_URL . " su ON (c.categories_id = su.link_id and su.link_type='2' ".$add_to_sql.")");
        $this->setSQL_TABLE("LEFT JOIN " . TABLE_CATEGORIES_CUSTOM_LINK_URL . " cl ON (cl.categories_id = c.categories_id ".$add_to_sql2.")");
		$this->setSQL_WHERE("and ((c.category_custom_link=0 and su.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql .") ||
		                          (c.category_custom_link=1 and cl.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql2 .")
		                          )
		");
		$this->_selectTables[] = 'su.*';
        $this->_selectTables[] = 'cl.link_url';

	}

	function F_Language($lang_code=''){
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;
		
		$add_to_sql = $this->F_StoreID(TABLE_CATEGORIES_DESCRIPTION,'categories_store_id','cd.categories_store_id');
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id ".$add_to_sql);
		$this->setSQL_WHERE("and cd.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);
		$this->_selectTables[] = 'cd.*';
	}

	function F_GroupCheck () {
		global $customers_status;

		if (_SYSTEM_GROUP_CHECK == 'true') {
			$perm_array = array(
				array(
					'type' => 'group_permission',
					'table' => TABLE_CATEGORIES_PERMISSION,
					'simple_permissions_table'=>TABLE_CATEGORIES_PERMISSION,
					'key' => 'categories_id',
					'simple_permissions' => 'true',
					'simple_permissions_key' => 'categories_id',
					'pref' => 'c'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
			
			$this->_selectTables[] = 'group_permission.*';
		}
	}

	function F_StoreCheck () {
		global $store_handler;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($store_handler->shop_id)) {
			$perm_array = array(
				array(
					'type'=>'shop',
					'table'=>TABLE_CATEGORIES_PERMISSION,
					'simple_permissions_table'=>TABLE_CATEGORIES_PERMISSION,
					'key'=>'categories_id',
					'simple_permissions' => 'true',
					'simple_permissions_key' => 'categories_id',
					'pref'=>'c'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
			$this->_selectTables[] = 'shop.*';
		}
	}	
	
	function F_MultiCheck ($params='') {
		global $xtPlugin;

	    ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':F_MultiCheck')) ? eval($plugin_code) : false;
	}	
	
	function F_Sorting($sort) {
			switch ($sort) {

			case 'name' :
				$this->setSQL_SORT(' cd.categories_name');
				break;

			case 'name-desc' :
				$this->setSQL_SORT(' cd.categories_name DESC');
				break;

			case 'sort_order' :
					$this->setSQL_SORT(' c.sort_order');
				break;

			case 'sort_order-desc' :
					$this->setSQL_SORT(' c.sort_order DESC');
				break;

			default:
				return false;
		}
	}
}