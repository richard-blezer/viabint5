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

class payment_price extends payment {

	protected $_table = TABLE_PAYMENT_COST;
	protected $_table_lang = NULL;
	protected $_table_seo = NULL;
	protected $_master_key = 'payment_cost_id';

	var $master_id = 'payment_cost_id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['payment_geo_zone'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=tax_zones','renderer'=>'zoneRenderer'
		);

		$header['payment_country_code'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=countries'
		);

		$header['payment_allowed'] = array('type' => 'status');
        $header['payment_cost_discount'] = array('type' => 'dropdown','url'=>'DropdownData.php?get=payment_cost_types');
        $header['payment_cost_percent'] = array('type' => 'hidden');
        
		$header['payment_cost_id'] = array('type' => 'hidden');
		$header['payment_id'] = array('type' => 'hidden');
		
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol'] = false;
		$params['display_adminActionStatus'] = false;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['exclude'] = array('payment_id');
		}

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['languageTab']    = false;
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}
		$ID=(int)$ID;

		$where  =' payment_id = "'.$this->url_data['payment_id'].'"';

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}		
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
			
			if(is_array($data) && count($data)!=0){
				foreach ($data as $key => $val) {
					$data[$key]['payment_price'] = $this->build_price($this->url_data['payment_id'], $data[$key]['payment_price']);
				}
			}
		}elseif($ID){
        	$data = $table_data->getData($ID);
        	if(is_array($data) && count($data)!=0){
				foreach ($data as $key => $val) {
					$data[$key]['payment_price'] = $this->build_price($this->url_data['payment_id'], $data[$key]['payment_price']);
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


	function _set($data, $set_type = 'edit'){
		global $db,$language,$filter;

		 $obj = new stdClass;

		foreach ($data as $key => $val) {

			if($val == 'on')
			   $val = 1;

			$data[$key] = $val;
		}

		if($this->url_data['payment_id'] && !$data['payment_id'])
			$data['payment_id'] = $this->url_data['payment_id'];

		if($set_type=='edit')
		$data['payment_price'] = $this->build_price($data['payment_id'], $data['payment_price'], '', 'save');


		 $oP = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		 $obj = $oP->saveDataSet();

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
	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
	}

	function _setStatus($id, $status) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;

		$db->Execute(
			"update " . $this->_table . " set payment_allowed = ? where ".$this->_master_key." = ?",
			array($status, $id)
		);

	}

	function build_price($id, $pprice, $tax_class='', $type='show'){
		global $price;

		if(!$tax_class)
		$tax_class = $price->getTaxClass('payment_tax_class', TABLE_PAYMENT, 'payment_id', $id);

		$pprice = $price->_BuildPrice($pprice, $tax_class, $type);
		return $pprice;
	}
}