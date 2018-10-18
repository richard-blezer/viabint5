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

class currency{

	public $default_currency = _STORE_CURRENCY;

	protected $_table = TABLE_CURRENCIES;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'currencies_id';

	function currency($code = ''){
		global $xtPlugin, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:currency_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->getPermission();

		$this->_getCurrency($code);

	}

	function _getCurrency($force_code = '')
	{
		global $xtPlugin, $order_edit_controller;
		$order_edit_controller->hook_getCurrency_top($force_code);

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_getCurrency_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		if(!empty($_SESSION['selected_currency'])){
			$code = $_SESSION['selected_currency'];
		}elseif(!empty($_SESSION['customer']->customer_info['customers_default_currency'])){
			$code = $_SESSION['customer']->customer_info['customers_default_currency'];
		}elseif(!empty($_SESSION['language']->default_currency)){
			$code = $_SESSION['language']->default_currency;
		}else{
			$code = $this->default_currency;
		}
		if ($force_code!='') $code = $force_code;

		if($this->_checkStore($code, 'store')){
			$code = $code;
		}else{
			$code = $this->default_currency;
		}

		$data = $this->_buildData($code);

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_getCurrency_bottom')) ? eval($plugin_code) : false;

		if(is_array($data)){
			while (list ($key, $value) = each($data)) {
				$this->$key = $value;
			}
		}
	}

	function _getCurrencyList($list_type = 'store',$index=''){
		global $xtPlugin, $db, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_getCurrencyList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($list_type=='store'){
			$table = $this->permission->_table;
			$where = $this->permission->_where;
		}

		$qry_where = " where c.currencies_id != '' ".$where."";

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_getCurrencyList_qry')) ? eval($plugin_code) : false;

		$qry =  "SELECT * FROM " . TABLE_CURRENCIES." c ".$table." ".$qry_where." ";

		$record = $db->CacheExecute($qry);
		while(!$record->EOF){

			$record->fields['id'] = $record->fields['code'];
			$record->fields['text'] = $record->fields['title'];

            if ($index=='') $data[] = $record->fields;
            if ($index=='code') $data[$record->fields['code']] = $record->fields;   
			$record->MoveNext();
		}$record->Close();

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_getCurrencyList_bottom')) ? eval($plugin_code) : false;
		return $data;
	}


	function _buildData($code){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->CacheExecute("SELECT * FROM " . TABLE_CURRENCIES . " where code = ?", array($code));
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					$data = $record->fields;
					$record->MoveNext();
				}$record->Close();
				($plugin_code = $xtPlugin->PluginCode('class.currency.php:_buildData_bottom')) ? eval($plugin_code) : false;
				return $data;
			}else{
				return false;
			}
	}

	function _checkStore($code, $list_type='store'){
		global $xtPlugin, $db, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_checkStore_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($list_type=='store'){
			$table = $this->permission->_table;
			$where = $this->permission->_where;
		}

		$record = $db->CacheExecute("SELECT c.currencies_id FROM " . TABLE_CURRENCIES." c ".$table." where c.code = ? ".$where."", array($code));
		if($record->RecordCount() > 0){
			return true;
		}else{
			return false;
		}
	}

	function _checkCurrencyData(){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.currency.php:_checkCurrencyData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($_SESSION['selected_currency'])){

			if($_SESSION['customer']->customer_info['customers_default_currency'] != $this->code)
			$this->_getCurrency();
		}
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'shop_perm' => array('type'=>'shop',
				'key'=>$this->_master_key,
				'value_type'=>'currency',
				'pref'=>'c'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);

		return $this->perm_array;
	}

	function _getParams() {
		$params = array();

		$header['currencies_id'] = array('type' => 'hidden');
		$header['code'] = array('max' => '3','min'=>'3');
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
 		$params['languageTab']    = false;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('currencies_id', 'title', 'code', 'value_multiplicator');
		}else{
			$params['exclude'] = array ('last_updated');
		}

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $store_handler, $customers_status, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);

		if ($this->url_data['get_data']){
        $data = $table_data->getData();
		}elseif($ID){
            $data = $table_data->getData($ID);
            $data[0]['shop_permission_info']=_getPermissionInfo();
		}else{
		$data = $table_data->getHeader();
		}

		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter;

		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		return $obj;
	}


	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

		$query = "SELECT code FROM " . $this->_table . " where currencies_id = ?";

		$record = $db->Execute($query, array($id));
		if($record->RecordCount() > 0){
			$current_code = $record->fields['code'];
		}else{
			return false;
		}

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));

	    $query = "SELECT code FROM " . $this->_table . " LIMIT 1";
		$rs = $db->Execute($query);
		$new_code=$rs->fields['code'];
		$db->Execute(
			"UPDATE ".TABLE_CUSTOMERS." SET customers_default_currency=? WHERE customers_default_currency=?",
			array($new_code, $current_code)
		);

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_deleteData($id);
	}
}