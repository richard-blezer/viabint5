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

class customers_status{

	var $default_status = _STORE_CUSTOMERS_STATUS_ID_GUEST;

	protected $_table = TABLE_CUSTOMERS_STATUS;
	protected $_table_lang = TABLE_CUSTOMERS_STATUS_DESCRIPTION;
	protected $_table_seo = null;
	protected $_master_key = 'customers_status_id';

	function customers_status($c_status = '')
	{
		global $db, $xtPlugin, $order_edit_controller;

		require_once _SRV_WEBROOT.'xtFramework/classes/class.order_edit_controller.php';
		require_once _SRV_WEBROOT.'xtFramework/classes/class.orderCouponInfo.php';
		if ($order_edit_controller)
		{
			$order_edit_controller->hook_customersStatus_top($c_status);
		}

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:customers_status_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		$this->getPermission();

		if(isset($_SESSION['customer']->customers_id)&&$_SESSION['customer']->customers_id!=0){
			$c_status = $_SESSION['customer']->customer_info['customers_status'];
		}else{
			if ($c_status!='') {
				$c_status = (int)$c_status;
			} else {
				$c_status = $this->default_status;
			}
		}

		if($this->_checkStore($c_status, 'store')){
			$c_status = $c_status;
		}else{
			$c_status = $this->default_status;
		}

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:customers_status_bottom')) ? eval($plugin_code) : false;

		$this->_getStatus($c_status);

	}

	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'shop_perm' => array(
				'type'=>'shop',
				'key'=>$this->_master_key,
				'value_type'=>'customers_status',
				'pref'=>'cs'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);

		return $this->perm_array;
	}

	function _getStatus($c_status){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_getStatus_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$data = $this->_buildData($c_status);

		while (list ($key, $value) = each($data)) {
			$this->$key = $value;
		}
	}

	function _getStatusList($list_type = 'store', $show_all = 'false'){
		global $db, $xtPlugin, $store_handlder, $language;

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_getStatuslist_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($list_type=='store'){
			$table = $this->permission->_table;
			$where = $this->permission->_where;
		}

		if($show_all=='true'){
			$data[] = array('id'=>'all', 'text'=>TEXT_SHOW_ALL);
		}

		$record = $db->Execute(
			"SELECT cs.customers_status_id as id, csd.customers_status_name as text FROM " . TABLE_CUSTOMERS_STATUS . " cs, ".TABLE_CUSTOMERS_STATUS_DESCRIPTION." csd ".$table." where cs.customers_status_id = csd.customers_status_id and csd.language_code=? ".$where,
			array($language->code)
		);
		while(!$record->EOF){
			$data[] = $record->fields;
			$record->MoveNext();
		}$record->Close();

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_getStatuslist_bottom')) ? eval($plugin_code) : false;
		return $data;

	}

	function _buildData($data){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		if (isset($data)){
			$record = $db->Execute("SELECT * FROM " . TABLE_CUSTOMERS_STATUS . " where customers_status_id=?", array($data));
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					$data = $record->fields;
					$record->MoveNext();
				}$record->Close();
				($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_buildData_bottom')) ? eval($plugin_code) : false;
				return $data;
			}else{
				return false;
			}
		}else{
           return false;
        }
	}

	/**
	 * define group permission names for TEXT_GROUP_PERMISSION_n
	 *
	 */
	function _defineGroupPermissionNames() {
		global $db,$language;

		$rs = $db->Execute(
			"SELECT cd.customers_status_name,cs.customers_status_id FROM " . TABLE_CUSTOMERS_STATUS." cs, ".TABLE_CUSTOMERS_STATUS_DESCRIPTION." cd where cs.customers_status_id = cd.customers_status_id and cd.language_code = ? ",
			array($language->code)
		);
		if($rs->RecordCount() > 0){
			while (!$rs->EOF) {
				define('TEXT_GROUP_PERMISSION_'.$rs->fields['customers_status_id'],$rs->fields['customers_status_name']);
				$rs->MoveNext();

			}
		}
	}

	function getGroupName($id) {
		global $db, $language;
		$record = $db->Execute(
			"SELECT customers_status_name FROM " . TABLE_CUSTOMERS_STATUS." cs, ".TABLE_CUSTOMERS_STATUS_DESCRIPTION." cd where cs.customers_status_id = cd.customers_status_id and cs.customers_status_id = ? and language_code = ? ",
			array($id, $language->code)
		);
		if($record->RecordCount() > 0){
			return $record->fields['customers_status_name'];
		}
	}

	function _checkStore($status, $list_type='store'){
		global $xtPlugin, $db, $language;

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_checkStore_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($list_type=='store'){
			$table = $this->permission->_table;
			$where = $this->permission->_where;
		}
		if (isset($status)){
			$record = $db->Execute(
				"SELECT cs.customers_status_id FROM " . TABLE_CUSTOMERS_STATUS . " cs ".$table." where cs.customers_status_id =? ".$where." ",
				array($status)
			);
			if($record->RecordCount() > 0){
				return true;
			}else{
				return false;
			}
		}else{
            return false;
        }
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language,$xtPlugin;

		$params = array();
		foreach ($language->_getLanguageList() as $key => $val) {
			$header['customers_status_name_'.$val['code']] = array('type' => '');
			$header['customers_status_image_'.$val['code']] = array('type' => '');
		}

		$header['customers_status_master'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=customers_status'
		);

		$header['customers_status_template'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=templateSets'
		);

		$header['customers_status_mobile_template'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=templateSets'
		);

		$header['customers_status_min_order'] = array('type' => '');
		$header['customers_status_max_order'] = array('type' => '');
		$header['customers_status_image'] = array('type' => '');

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_getParams')) ? eval($plugin_code) : false;

		if (isset($rowActions) && is_array($rowActions) && isset($rowActionsFunctions)) {
			$params['rowActions']             = $rowActions;
			$params['rowActionsFunctions']    = $rowActionsFunctions;
		}

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol']  = true;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ($this->_master_key, 'customers_status_name_'.$language->code,'customers_count');
		}

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();

            if (count($data) > 0) {
                foreach ($data as $key => $arr) {
                    $query = "SELECT count(*) as count FROM " . TABLE_CUSTOMERS . " WHERE customers_status=?";
                    $rs = $db->Execute($query, array($arr['customers_status_id']));
                    $data[$key]['customers_count']=$rs->fields['count'];
                }
            }

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

	function _set($data, $set_type = 'edit'){
		global $db,$language,$filter, $xtPlugin;

		$obj = new stdClass;

		foreach ($data as $key => $val) {

			if($val == 'on')
			$val = 1;

			$data[$key] = $val;

		}

		$oC = new adminDB_DataSave(TABLE_CUSTOMERS_STATUS, $data, false, __CLASS__);
		$objC = $oC->saveDataSet();

		if ($set_type=='new') {	// edit existing
			$obj->new_id = $objC->new_id;
			$data = array_merge($data, array($this->master_id=>$objC->new_id));

			$db->Execute("CREATE TABLE " . TABLE_PRODUCTS_PRICE_GROUP . $obj->new_id . " LIKE " . TABLE_PRODUCTS_PRICE_GROUP . "all");

			$db_check = new database_check();
			$db_check->GroupCheckTable(TABLE_PRODUCTS_PRICE_SPECIAL);
			$db_check->PriceCheckTable(TABLE_PRODUCTS);

		}

		$oCD = new adminDB_DataSave(TABLE_CUSTOMERS_STATUS_DESCRIPTION, $data, true, __CLASS__);
		$objCD = $oCD->saveDataSet();

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_set_bottom')) ? eval($plugin_code) : false;

		if ($objC->success && $objCD->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		return $obj;
	}

	function _unset($id = 0) {
		global $db, $xtPlugin;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;

        $query = "SELECT count(*) as count FROM " . TABLE_CUSTOMERS . " WHERE customers_status=?";
        $rs = $db->Execute($query, array($id));
        if ($rs->fields['count']>0) return false;

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_deleteData($id);

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
		if ($this->_table_lang !== null)
			$db->Execute("DELETE FROM ". $this->_table_lang ." WHERE ".$this->_master_key." = ?", array($id));

		$db->Execute("DROP TABLE " . TABLE_PRODUCTS_PRICE_GROUP . $id . "");
		
		$db->Execute("ALTER TABLE " . TABLE_PRODUCTS ." DROP COLUMN price_flag_graduated_".$id);

		($plugin_code = $xtPlugin->PluginCode('class.customers_status.php:_unset_bottom')) ? eval($plugin_code) : false;
	}
}