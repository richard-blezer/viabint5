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

class getContentSQL_query extends SQL_query{

	public $filterFunctions; // sql filter functions array
	public $a_sql_table, $a_sql_where, $a_sql_sort, $a_sql_group;

	function getContentSQL_query() {
		global $xtPlugin;

		$this->setSQL_TABLE(TABLE_CONTENT . " c ");
		$this->setSQL_WHERE(" c.content_id != '' ");

		($plugin_code = $xtPlugin->PluginCode('class.getContentSQL_query.php:getContentSQL_query_bottom')) ? eval($plugin_code) : false;
	}

	function getSQL_query($sql_cols = 'c.content_id', $filter_type='string') {

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


	//////////////////////////////////////////////////////

	function F_Status () {
		$this->setSQL_WHERE(" and c.content_status = '1'");
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
			$add_to_sql =  " and ".$sql_colum."='" . (int)$store_handler->shop_id."'";
		}
		return $add_to_sql;
	}
	
	function F_Seo($lang_code='') {
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;
		
		$add_to_sql = $this->F_StoreID(TABLE_SEO_URL,'store_id','su.store_id');
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_SEO_URL . " su ON (c.content_id = su.link_id and su.link_type='3' ".$add_to_sql.")");
		$this->setSQL_WHERE("and su.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);
	}

	function F_Language($lang_code=''){
		global $db, $language;

		if(empty($lang_code))
			$lang_code = $language->code;
		
		$add_to_sql = $this->F_StoreID(TABLE_CONTENT_ELEMENTS,'content_store_id','cd.content_store_id');
		
		$this->setSQL_TABLE("LEFT JOIN " . TABLE_CONTENT_ELEMENTS . " cd ON c.content_id = cd.content_id");
		$this->setSQL_WHERE("and cd.language_code = " . $db->Quote($lang_code) . " ".$add_to_sql);
	}

	function F_GroupCheck () {
		global $customers_status;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($customers_status->customers_status_id)) {

			$perm_array = array(
				array(
					'type'=>'group_permission',
					'key'=>'content_id',
					'simple_permissions' => 'true',
					'simple_permissions_key' => 'permission_id',
					'pref'=>'c'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}

	function F_StoreCheck () {
		global $store_handler;

		if (_SYSTEM_GROUP_CHECK == 'true' && isset($store_handler->shop_id)) {
			$perm_array = array(
				array(
					'type'=>'shop',
					'key'=>'content_id',
					'simple_permissions' => 'true',
					'simple_permissions_key' => 'permission_id',
					'pref'=>'c'
				)
			);

			$perm = new item_permission($perm_array);

			$this->setSQL_TABLE($perm->_table);
			$this->setSQL_WHERE($perm->_where);
		}
	}

	function F_Block ($data = 0) {
		$this->setSQL_TABLE("INNER JOIN " . TABLE_CONTENT_TO_BLOCK . " c2b ON c2b.content_id = c.content_id");
		$this->setSQL_WHERE("and c2b.content_id = ".$data);
	}

	function F_Block_Recursive ($data = 0) {
		$this->setSQL_TABLE("INNER JOIN " . TABLE_CONTENT_TO_BLOCK . " c2b ON c2b.content_id = c.content_id");
		$this->setSQL_WHERE("and ".$data." in (c.content_id, c.content_parent)");
	}

	function F_MultiCheck ($params='') {
		global $xtPlugin;

	    ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':F_MultiCheck')) ? eval($plugin_code) : false;
		
	}		
	
	function F_Sorting($sort) {
			switch ($sort) {
			case 'name' :
				$this->setFilter('Language');
				$this->setSQL_SORT(' cd.content_title');
				break;

			case 'name-desc' :
				$this->setFilter('Language');
				$this->setSQL_SORT(' cd.content_title DESC');
				break;

			default:
				return false;
		}
	}
}