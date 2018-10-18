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

class address extends customer{

	public $_table = TABLE_CUSTOMERS_ADDRESSES;
	public $_table_lang = null;
	public $_table_seo = null;
	public $_master_key = 'address_book_id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['customers_dob'] = array('type' => 'date');
		$header['customers_id'] = array('type' => 'hidden');
		$header['address_book_id'] = array('type' => 'hidden');

		$header['customers_country_code'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=countries'
		);

		$header['address_class'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=address_types'
		);

		$header['customers_gender'] = array(
			'renderer' => 'genderRenderer','type' => 'dropdown',
			'url'  => 'DropdownData.php?get=gender'
		);
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
 		$params['languageTab']    = false;
		$params['edit_masterkey'] = false;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array (
				'address_book_id',
				'customers_gender',
				'customers_company',
				'customers_firstname',
				'customers_lastname',
				'customers_postcode',
				'customers_city',
				'customers_postcode',
				'customers_street_address'
			);
		}else{
			$params['exclude'] = array('date_added', 'last_modified','external_id');
		}

		return $params;
	}

	function _get($ID = 0, $searched = '') {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, 'customers_id='.(int)$this->url_data['adID']);
		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}elseif($ID){
			$data = $table_data->getData($ID);
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

		if($set_type=='new'){
			$data['customers_id'] = (int)$this->url_data['adID'];
		}

		 $obj = new stdClass;
		 $o = new adminDB_DataSave(TABLE_CUSTOMERS_ADDRESSES, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		return $obj;
	}

	function _unset($id = 0) {
	    global $db;

	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
	}
}