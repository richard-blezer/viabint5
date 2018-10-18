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

class permission {

	protected $_type = null;
	protected $_master_table = null;
	protected $_master_key = null;
	protected $_master_pref = null;
	protected $_master_status = null;

	protected $_value_pref = null;
	protected $_value_type = null;

	protected $_value_data= null;
	protected $_value_id= null;

	protected $_default_pref = 'perm';
	protected $_default_table = TABLE_CONTENT_PERMISSION;

	function __construct($data = '') {

		  if(!is_array($data)) return false;

		  if (!isset($data['type'])) $data['type']='';
		  $this->_setType($data['type']);

		  if (!isset($data['table'])) $data['table']='';
		  $this->_setTable($data['table']);
		  
		  if (!isset($data['simple_permissions_table'])) $data['simple_permissions_table']='';
		  $this->_setSimpleTable($data['simple_permissions_table']);		  

		  if (!isset($data['key'])) $data['key']='';
		  $this->_setKey($data['key']);

		  if (!isset($data['master_pref'])) $data['master_pref']='';
     	  $this->_setPref($data['master_pref']);

     	  if (!isset($data['value_type'])) $data['value_type']='';
		  $this->_setValueType($data['value_type']);

		  if (!isset($data['pref'])) $data['pref']='';
		  $this->_setValuePref($data['pref']);

		  if (!isset($data['status'])) $data['status']='';
     	  $this->_setStatus($data['status']);

     	  if (isset($data['value_data']))
     	  $this->_setValueData($data['value_data']);

     	  if(isset($data['simple_permissions']))
     	  $this->_setSimplePermissions($data['simple_permissions']);

     	  if(isset($data['simple_permissions_key']))
     	  $this->_setSimplePermissionsKey($data['simple_permissions_key']);

     	  if (!isset($data['id'])) $data['id']='';
		  $this->_setID($data['id']);

		  $this->_getPerm();

	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _setType ($data=''){
		$this->_type = $data;
	}

	function _setTable ($data=''){

		if($data){
			$this->_master_table = $data;
		}else{
			$this->_master_table = $this->_default_table;
		}
	}

	function _setSimpleTable ($data=''){

		if($data){
			$this->_simple_table = $data;
		}else{
			$this->_simple_table = $this->_default_table;
		}
	}
	
	
	function _setKey ($data=''){
		$this->_master_key = $data;
	}

	function _setPref ($data=''){

		if($data){
			$this->_master_pref = $data;
		}else{
			if (!isset($this->default_pref)) {
				$this->_master_pref = $this->_type;
			} else {
				$this->_master_pref = $this->_type.$this->default_pref;
			}
		}
	}

	function _setValueType ($data=''){
		$this->_value_type = $data;
	}

	function _setValuePref ($data=''){
		$this->_value_pref = $data;
	}

	function _setStatus ($data=''){

		if($data){
			$this->_master_status = $data;
		}else{
			$this->_master_status = $this->_getAutoStatus();
		}
	}

	function _setValueData ($data=''){

		if($data){
			$this->value_data = $data;
		}else{
			$this->value_data = $this->_getAutoData();
		}
	}

	function _setSimplePermissions ($data=''){
		$this->_simple_permissions = $data;
	}

	function _setSimplePermissionsKey ($data=''){
		$this->_simple_permissions_key = $data;
	}

	function _setID ($data=''){
		$this->value_id = $data;
	}

	function _getAutoStatus (){
		global $customers_status, $store_handler, $xtPlugin;

		if ($this->_type) {
		    switch ($this->_type) {
		        case "shop":
		            return $store_handler->shop_id;
		            break;
		        case "group_permission":
		            return $customers_status->customers_status_id;
		            break;
		        case "shipping_permission":
		        //	$dropdown = new getAdminDropdownData();
		        //	return $dropdown->getShippingMethods();
		        return $_SESSION['selected_shipping'];
		        	break;
				default:
					($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getAutoStatus')) ? eval($plugin_code) : false;

					if(isset($plugin_return_value))
					return $plugin_return_value;
		    }
		}

	}

	function _getAutoData (){
		global $customers_status, $store_handler, $xtPlugin;

		if ($this->_type) {
		    switch ($this->_type) {
		        case "shop":
		        	$stores = $store_handler->getStores();
		        	($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getAutoData_shop')) ? eval($plugin_code) : false;
		            return $stores;
		            break;
		        case "group_permission":
		        	$groups = $customers_status->_getStatusList('admin');
		        	($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getAutoData_group')) ? eval($plugin_code) : false;
		            return $groups;
		            break;
		        case "shipping_permission":
		        	$dropdown = new getAdminDropdownData();
		        	$shippingMethods = $dropdown->getShippingMethods();
		        	($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getAutoData_shipping')) ? eval($plugin_code) : false;
		        	return $shippingMethods;
		        	break;
				default:
                ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getAutoData')) ? eval($plugin_code) : false;
                if(isset($plugin_return_value))
                    return $plugin_return_value;
		    }
		}

	}

	function _getPerm(){

		if (_SYSTEM_GROUP_CHECK == 'true') {

			$value = '';
			if($this->_value_type)
			$value = "and ".$this->_master_pref.".type = '".$this->_value_type."'";

			if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS=='true' && $this->_simple_permissions=='true'){
				$mkey = $this->_simple_permissions_key;
				$mtable = $this->_simple_table;
			}else{
				$mkey = $this->_master_key;
				$mtable = $this->_master_table;
			}

			$this->_perm_table = " left JOIN " . $mtable . " ".$this->_master_pref." ON (".$this->_master_pref.".pid = ".$this->_value_pref.".".$mkey." and ".$this->_master_pref.".pgroup = '".$this->_type."_".$this->_master_status."' ".$value.") ";

			if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
				$this->_perm_where = " and ".$this->_master_pref.".permission IS NULL";
			}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
				$this->_perm_where = " and ".$this->_master_pref.".permission = 1";
			}

		}
	}

	function _get(){
		global $db;

		$this->_setValueData();

		foreach ($this->value_data as $key => $val) {

			if($this->value_id){
				$data[$this->_type.'_'.$val['id']] = $this->_getValues($val['id']);
			}else{
				$data[$this->_type.'_'.$val['id']] = '';
			}

		}

		return $data;

	}

	function _getValues($id=''){
		global $db;

		if($this->_value_type)
		$value = "and ".$this->_master_pref.".type = '".$this->_value_type."'";

		$record = $db->CacheExecute(
			"SELECT permission FROM " . $this->_master_table . " ".$this->_master_pref." where ".$this->_master_pref.".pid = ? and ".$this->_master_pref.".pgroup = ? ".$value." ",
			array($this->value_id, $this->_type."_".$id)
		);
			if($record->RecordCount() > 0){
				return $record->fields['permission'];
			}else{
				return false;
			}

	}

	function _saveData($data, $id){
		global $db;

		if(!$this->value_data)
		$this->_setValueData();

		foreach ($this->value_data as $key => $val) {

			if($this->_value_type)
			$value = "and type = '".$this->_value_type."'";

			if($id)
			$db->Execute(
				"DELETE FROM ". $this->_master_table ." WHERE pid = ? and pgroup = ? ".$value."",
				array($id, $this->_type."_".$val['id'])
			);

			if($data[$this->_type."_".$val['id']]=='1'){
				$record = array('pid'=>$id, 'permission'=>'1', 'pgroup'=>$this->_type."_".$val['id']);

				if($this->_value_type){
					$val_record = array('type'=>$this->_value_type);
					$record = array_merge($record, $val_record);
				}

				$db->AutoExecute($this->_master_table, $record, 'INSERT');
			}
		}

	}

	function _deleteData($id){
		global $db;

			if($this->_value_type)
			$value = "and type = '".$this->_value_type."'";

 			$db->Execute(
				"DELETE FROM ". $this->_master_table ." WHERE pid = ? ".$value."",
				array($id)
			);
	}

	function _unsetFields($data){

		if(!$this->value_data)
		$this->_setValueData();

		foreach ($this->value_data as $key => $val) {
			unset($data[$this->_type."_".$val['id']]);
		}

		return $data;

	}

	function _excludeFields($data){

		if(!$this->value_data)
		$this->_setValueData();

		foreach ($this->value_data as $key => $val) {

			$tmp_array = array($this->_type."_".$val['id']);
			$data = array_merge($data, $tmp_array);

		}
		return $data;
	}
}