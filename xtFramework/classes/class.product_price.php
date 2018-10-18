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

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.database_check.php';


class product_price extends product {

	public $_table = TABLE_PRODUCTS;
	public $_master_key = 'products_id';
	public $master_id = 'id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $xtPlugin;
		$params = array();

		$header['cstatus_id'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=customers_status'
		);

		$header['id'] = array('type'=>'hidden');
		$header['products_id'] = array('type'=>'hidden');
		$header['old_set_type'] = array('type'=>'hidden');
		$header['old_data_set'] = array('type'=>'hidden');
		$header['c_sort_id'] = array('type'=>'hidden');
		
		$params['display_checkCol']  = true;
		
		$params['header']         = $header;
		$params['master_key']     = 'id';
		$params['default_sort']   = 'discount_quantity';

		/* grouping params */
		$params['GroupField']     = "c_sort_id";
		$params['SortField']      = "discount_quantity";
		$params['SortDir']        = "DESC";

		($plugin_code = $xtPlugin->PluginCode('class.product_price.php:_getParams_header')) ? eval($plugin_code) : false;
		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language, $customers_status;
		if ($this->position != 'admin') return false;

		if ($this->url_data['new']=="true"){
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
			$old_set_type = true;
		}
		
		$cdata[] = array('id'=>'all', 'text'=>TEXT_ALL);
		$customer_data = $customers_status->_getStatusList('admin');
		$customer_data = array_merge($cdata, $customer_data);
		
		//edit
		if(isset($this->url_data['edit_id'])){
			
			$ID= $this->url_data['edit_id'];
			
			if(preg_match('/_/', $ID)){
			
				$tmp_id = explode('_', $ID);
				$ID = $tmp_id[1];
				$ctID = $tmp_id[0];
				
				$query = "SELECT * FROM ".TABLE_PRODUCTS_PRICE_GROUP.$ctID." WHERE products_id=? AND id=? ORDER BY discount_quantity ASC";
				$record = $db->Execute($query, array($this->url_data['products_id'], $ID));
				
				if($record->RecordCount() != 0){
					while (!$record->EOF) {
				
						$record->fields['cstatus_id'] = $ctID;
						$record->fields['c_sort_id'] = '('.$ctID.') '.$record->fields['text'];
				
						if($old_set_type){
							$record->fields['old_set_type'] = $old_set_type;
							$record->fields['old_data_set'] = $ID;
						}
				
						$record->fields['id'] = $ctID.'_'.$record->fields['id'];
				
						$record->fields['price'] = $this->build_price($record->fields['products_id'], $record->fields['price']);
				
						$data[] = $record->fields;
						$record->MoveNext();
					}$record->Close();
				}
			}
			
		//overview	
		} else {
			foreach ($customer_data as $key => $val){
				
				$qry = "SELECT * FROM ".TABLE_PRODUCTS_PRICE_GROUP.$val['id']." WHERE products_id=? ORDER BY discount_quantity ASC";
				$record = $db->Execute($qry, array($this->url_data['products_id']));
	
				if($record->RecordCount() != 0){
					while (!$record->EOF) {
	
						$record->fields['cstatus_id'] = $val['id'];
						$record->fields['c_sort_id'] = '('.$val['id'].') '.$val['text'];
						
						if($old_set_type){
							$record->fields['old_set_type'] = $old_set_type;
							$record->fields['old_data_set'] = $ID;
						}
	
						$record->fields['id'] = $val['id'].'_'.$record->fields['id'];
	
						$record->fields['price'] = $this->build_price($record->fields['products_id'], $record->fields['price']);
	
						$data[] = $record->fields;
						$record->MoveNext();
					}$record->Close();
				}
			}	
		}
		
		$count = count($data);
		
		($plugin_code = $xtPlugin->PluginCode('class.product_price.php:_get_bottom')) ? eval($plugin_code) : false;
		if($count > 0){
			$obj = new stdClass;
			$obj->data = $data;
			$obj->totalCount = $count;
			return $obj;
		}else{
			if($this->url_data['get_data']!=true){
				$data[0] = getEmptyDataset(TABLE_PRODUCTS_PRICE_GROUP.'all');
				$data[0]['cstatus_id'] =  'all';
				$data[0]['c_sort_id'] =  'all';
			}

			$obj = new stdClass;
			$obj->data = $data;
			$obj->totalCount = 0;
			return $obj;
		}
	}

	function _set($data, $set_type = 'edit'){
		global $db,$language,$filter, $xtPlugin;

		$obj = new stdClass;

		if(preg_match('/_/', $data['id'])){
			$tmp_id = explode('_', $data['id']);
			$data['id'] = $tmp_id[1];
			$data['group'] = $tmp_id[0];
		}
		if($this->url_data['products_id'] && !$data['products_id'])
		$data['products_id'] = $this->url_data['products_id'];

		if($set_type=='edit')
		$data['price'] = $this->build_price($data['products_id'], $data['price'], '', 'save');

		if($set_type=='new'){
			$oP = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_GROUP.'all', $data, false, __CLASS__);
		}else{
			if(empty($data['cstatus_id'])) $data['cstatus_id'] = 'all';
			if($data['cstatus_id'] != $data['group']){
				$this->_unset($data['id'], $data['group']);
				unset($data['id']);
			}
			$oP = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_GROUP.$data['cstatus_id'], $data, false, __CLASS__);
		}

		$obj = $oP->saveDataSet();

		$this->_updateProductsGroupPriceflag($data['products_id']);

		($plugin_code = $xtPlugin->PluginCode('class.product_price.php:_set_bottom')) ? eval($plugin_code) : false;
		

		return $obj;
	}

	function _updateProductsGroupPriceflag($pID) {
		global $db,$customers_status;

		$pID = (int)$pID;
		if (!is_int($pID)) return false;

		$cdata = array();
		$cdata[] = array('id'=>'all', 'text'=>TEXT_ALL);
		$customer_data = $customers_status->_getStatusList('admin');
		$customer_data = array_merge($cdata, $customer_data);

		$check_table = new database_check();
		$check_table->PriceCheckTable(TABLE_PRODUCTS);

		foreach ($customer_data as $key => $val){

			$record = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS_PRICE_GROUP.$val['id']." WHERE products_id=?", array($pID));

			if ($record->RecordCount()>=1) {
				$db->Execute("UPDATE ".TABLE_PRODUCTS." SET price_flag_graduated_".$val['id']."=1 WHERE products_id=?", array($pID));
			} else {
				$db->Execute("UPDATE ".TABLE_PRODUCTS." SET price_flag_graduated_".$val['id']."=0 WHERE products_id=?", array($pID));
			}
		}
	}

	function _unset($id, $table='') {
		global $db, $xtPlugin;

		if ($this->position != 'admin') return false;

		if(preg_match('/_/', $id)){
			$tmp_id = explode('_', $id);
			$id = $tmp_id[1];
			$table = $tmp_id[0];
		}
		
		$id = (int)$id;
		if (!is_int($id)) return false;
			
		$db->Execute("DELETE FROM ". TABLE_PRODUCTS_PRICE_GROUP.$table." WHERE ".$this->master_id." = ?", array($id));

		($plugin_code = $xtPlugin->PluginCode('class.product_price.php:_unset_buttom')) ? eval($plugin_code) : false;
	}

	function build_price($id, $pprice, $tax_class='', $type='show'){
		global $price;

		if(!$tax_class)
		$tax_class = $price->getTaxClass('products_tax_class_id', TABLE_PRODUCTS, 'products_id', $id);

		$pprice = $price->_BuildPrice($pprice, $tax_class, $type);
		return $pprice;
	}
}