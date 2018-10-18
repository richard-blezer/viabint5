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

class tax_class{

	protected $_table = TABLE_TAX_CLASS;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'tax_class_id';

	function _getTaxClassList(){
		global $db;

		$record = $db->Execute("SELECT * FROM ".TABLE_TAX_CLASS);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$record->fields['id'] = $record->fields['tax_class_id'];
				$record->fields['text'] = $record->fields['tax_class_title'];

				$data[] = $record->fields;

				$record->MoveNext();
			}$record->Close();
			return $data;
		}else{
			return false;
		}
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header[$this->_master_key] = array('type' => 'hidden');

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
 		$params['languageTab']    = false;

		$params['exclude']        = array ('last_modified', 'date_added');

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

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data'])
        	$data = $table_data->getData();
        elseif($ID)
        	$data = $table_data->getData($ID);
        else
			$data = $table_data->getHeader();

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

		return $obj;
	}

	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ".$id);
	    $db->Execute("DELETE FROM ". TABLE_TAX_RATES ." WHERE ".$this->_master_key." = ".$id);
	}
}