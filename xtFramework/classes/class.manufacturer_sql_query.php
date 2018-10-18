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

class getManufacturerSQL_query extends SQL_query{
	var $filterFunctions; // sql filter functions array
	var $a_sql_table, $a_sql_where, $a_sql_sort, $a_sql_group;

	function getManufacturerSQL_query() {
		global $xtPlugin;
		
		$this->setSQL_TABLE(TABLE_MANUFACTURERS . " m ");
		$this->setSQL_WHERE(" m.manufacturers_id != '0'");
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':class_top')) ? eval($plugin_code) : false;

	}

	function getSQL_query($sql_cols = 'm.manufacturers_id') {
		
		if(USER_POSITION=='store')
		$this->setFilter('StoreCheck');
		
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
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_SEO_URL . " su ON (m.manufacturers_id = su.link_id and su.link_type='4' ".$add_to_sql.")");
		$this->setSQL_WHERE("and su.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);

	}

	function F_Language($lang_code=''){
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;
		
		$add_to_sql = $this->F_StoreID(TABLE_MANUFACTURERS_DESCRIPTION,'manufacturers_store_id','mi.manufacturers_store_id');
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_MANUFACTURERS_DESCRIPTION . " mi ON m.manufacturers_id = mi.manufacturers_id ".$add_to_sql." ");
		$this->setSQL_WHERE("and mi.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);
	}

	function F_Sorting($sort='') {
			switch ($sort) {
			default:
			case 'name' :
				$this->setSQL_SORT(' m.manufacturers_name');
				break;

			case 'name-desc' :
				$this->setSQL_SORT(' m.manufacturers_name DESC');
				break;
		}
	}

	function F_StoreCheck () {
		global $store_handler;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($store_handler->shop_id)) {
			$perm_array = array(
				array(
					'type'=>'shop',
					'table'=>TABLE_MANUFACTURERS_PERMISSION,
					'key'=>'manufacturers_id',
					'pref'=>'m'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}	
	
	function F_MultiCheck ($params='') {
		global $xtPlugin;

	    ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':F_MultiCheck')) ? eval($plugin_code) : false;
	}		
	
	function F_GroupCheck () {
	 global $customers_status;
	
	 if (_SYSTEM_GROUP_CHECK == 'true' && isset($customers_status->customers_status_id)) {
	
	  $perm_array = array(
		  array(
			  'type'=>'group_permission',
	          'table'=>TABLE_PRODUCTS_PERMISSION,
	          'simple_permissions_table'=>TABLE_CATEGORIES_PERMISSION,
	          'key'=>'products_id',
	          'simple_permissions' => 'true',
	          'simple_permissions_key' => 'permission_id',
	          'pref'=>'p'
		  )
	  );
	
	  $perm = new item_permission($perm_array);
	
	  $this->setSQL_TABLE($perm->_table);
	  $this->setSQL_WHERE($perm->_where);
	 }
	}
}