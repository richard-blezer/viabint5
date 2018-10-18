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

class item_permission {

	protected $_data;
	public $_table;
	public $_where;

	function __construct($data = '') {
		$this->_setData($data);
		$this->_buildData();
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _setData ($data=''){
		$this->_data = $data;
	}

	function _buildData(){
		if(is_array($this->_data)){
			foreach ($this->_data as $key => $val) {

				$this->$key = new permission($val);
				$this->_table .= $this->$key->_perm_table;
				$this->_where .= $this->$key->_perm_where;
			}
		}
	}

	function _saveData($data, $id){

		if(is_array($this->_data)){
			foreach ($this->_data as $key => $val) {

				$this->$key = new permission($val);
				$this->$key->_saveData($data, $id);
			}
		}
	}

	function _deleteData($id){

		if(is_array($this->_data)){
			foreach ($this->_data as $key => $val) {

				$this->$key = new permission($val);
				$this->$key->_deleteData($id);
			}
		}

	}

	function _setSimplePermissionID($master_value=0, $set_value, $master_key='categories_id', $set_key='categories_id', $get_table=TABLE_CATEGORIES, $set_table=TABLE_CATEGORIES, $link_table=TABLE_PRODUCTS_TO_CATEGORIES){
		global $db;

		if($master_value==0){
			$record = $db->Execute("SELECT ".$master_key." FROM " . $link_table . " where ".$set_key."=?", array($set_value));
			if($record->RecordCount() > 0){
				$master_value = $record->fields[$master_key];
			}
		}

		if($master_value==0) return false;

		require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.recursive.php');
		$rec_data = new recursive($get_table, $master_key);
		$path = $rec_data->getPath($master_value);
		$path = array_reverse($path);

		$update_record = array('permission_id'=>(int) $path[0]);
		$db->AutoExecute($set_table, $update_record, 'UPDATE', "".$set_key."=".$db->Quote($set_value)."");

	}

	function _setSimplePermissionRecursiv($master_value, $set_value, $set_key='categories_id', $set_table=TABLE_CATEGORIES){
		global $db;

		$set_value[] = $master_value;

		if(is_array($set_value)){
			foreach ($set_value as $key => $val) {
				$update_record = array('permission_id'=>(int) $master_value);
				$db->AutoExecute($set_table, $update_record, 'UPDATE', "".$set_key."=".$db->Quote($val)."");
			}
		}
	}

	function _unsetFields($data){

		if(is_array($this->_data)){
			foreach ($this->_data as $key => $val) {

				$this->$key = new permission($val);

				foreach ($data as $dkey => $dval) {
					$new_data[] = $this->$key->_unsetFields($dval);
				}
			}

		return $new_data;
		}

	}

	function _excludeFields($data){

		$new_data = array();
		if(is_array($this->_data)){
			foreach ($this->_data as $key => $val) {

				$this->$key = new permission($val);
					$tmp_new_data = $this->$key->_excludeFields($data);
					$new_data = array_merge($new_data, $tmp_new_data);
			}

			return $new_data;
		}
	}
}