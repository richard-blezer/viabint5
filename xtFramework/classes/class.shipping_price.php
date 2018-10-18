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

class shipping_price {

	protected $_table = TABLE_SHIPPING_COST;
	protected $_table_lang = NULL;
	protected $_table_seo = NULL;
	protected $_master_key = 'shipping_cost_id';

	function shipping_price(){

	}


	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['shipping_geo_zone'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=tax_shipping_zones','renderer'=>'zoneRenderer'
		);

		$header['shipping_country_code'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=countries'
		);

		$header['shipping_allowed'] = array('type' => 'status');
		$header['shipping_free'] = array('type' => 'status');

		$header['shipping_cost_id'] = array('type' => 'hidden');
		$header['shipping_id'] = array('type' => 'hidden');

		$params['display_checkCol'] = false;
		$params['display_adminActionStatus'] = false;

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
 		$params['languageTab']    = false;
 		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;
		$params['display_checkItemsCheckbox']  = true;

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
		$ID=(int)$ID;

		$where  =' shipping_id = "'.$this->url_data['shipping_id'].'"';

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}		
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
			if(is_array($data) && count($data)!=0){
				foreach ($data as $key => $val) {
					$data[$key]['shipping_price'] = $this->build_price($this->url_data['shipping_id'], $data[$key]['shipping_price']);
				}
			}
		}elseif($ID){
        	$data = $table_data->getData($ID);

        	if(is_array($data) && count($data)!=0){
				foreach ($data as $key => $val) {
					$data[$key]['shipping_price'] = $this->build_price($this->url_data['shipping_id'], $data[$key]['shipping_price']);
				}
        	}
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

	function _set($data, $set_type='edit'){
		global $db,$language,$filter;

		 if($set_type == 'new'){
		 	$data['shipping_id'] = $this->url_data['shipping_id'];
		 }

		if($set_type=='edit')
		$data['shipping_price'] = $this->build_price($data['shipping_id'], $data['shipping_price'], '', 'save');

		 $obj = new stdClass;
		 $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		 if ($obj->success) {
		     $obj->success = true;
		 } else {
		     $obj->failed = true;
		 }

		return $obj;
	}

	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id = (int)$id;
		if ($id>0) {
	    	$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
		}

	}

	function _setStatus($id, $status) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;

		$db->Execute(
			"update " . $this->_table . " set shipping_allowed = ? where ".$this->_master_key." = ?",
			array($status, $id)
		);
	}

	function build_price($id, $pprice, $tax_class='', $type='show'){
		global $price;

		if(!$tax_class)
		$tax_class = $price->getTaxClass('shipping_tax_class', TABLE_SHIPPING, 'shipping_id', $id);

		$pprice = $price->_BuildPrice($pprice, $tax_class, $type);
		return $pprice;
	}
}