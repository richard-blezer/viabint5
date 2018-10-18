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

class product_sp_price extends product {

	var $master_id = 'id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $xtPlugin;

		$params = array();

		$header['id'] = array('type' => 'hidden');
		
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;		

		($plugin_code = $xtPlugin->PluginCode('plugin_special_products:_get_Params_header')) ? eval($plugin_code) : false;
		
		$params['header']         = $header;
		$params['master_key']     = $this->master_id;
		$params['default_sort']   = 'status';

		/* grouping params */
		$params['GroupField']     = "status";
		$params['SortField']      = "status";
		$params['SortDir']        = "DESC";
		/* grouping params end */

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language, $customers_status;
		if ($this->position != 'admin') return false;
		 $obj = new stdClass;
		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}

		 $db_check = new database_check();
		 $db_check->GroupCheckTable(TABLE_PRODUCTS_PRICE_SPECIAL);
		
		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}		
		
		$sql_where  ="products_id='".$this->url_data['products_id']."'";
		
		$table_data = new adminDB_DataRead(TABLE_PRODUCTS_PRICE_SPECIAL, '', '', $this->master_id, $sql_where, $this->sql_limit);
		
		if ($this->url_data['get_data']){
            $data = $table_data->getData();
            foreach ($data as $data_Key => $data_Item) {
                $data[$data_Key]['specials_price'] = $this->build_price($data[$data_Key]['products_id'], $data[$data_Key]['specials_price']);
            }
		}elseif($ID){
        $data = $table_data->getData($ID);
        
        		if(is_array($data)&&count($data)!=0){
					foreach ($data as $key => $val) {
						$data[$key]['specials_price'] = $this->build_price($data[$key]['products_id'], $data[$key]['specials_price']);
					}
        		}
        }else{
		$data = $table_data->getHeader();
        }

	($plugin_code = $xtPlugin->PluginCode('plugin_special_products:_get_data')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
			return $plugin_return_value;


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

		if($this->url_data['products_id'] && !$data['products_id'])
			$data['products_id'] = $this->url_data['products_id'];

		if($set_type=='edit')
		$data['specials_price'] = $this->build_price($data['products_id'], $data['specials_price'], '', 'save');
					
			$oP = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_SPECIAL, $data, false, __CLASS__);

		$obj = $oP->saveDataSet();
		
		$this->_updateProductsSpecialflag($data['products_id']);

($plugin_code = $xtPlugin->PluginCode('plugin_special_products:_set_buttom')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
			return $plugin_return_value;
		
		return $obj;
	}
	
	function _updateProductsSpecialflag($pID) {
		global $db;
		
		$pID = (int)$pID;
		if (!is_int($pID)) return false;
		
		$rs = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS_PRICE_SPECIAL." WHERE products_id=?",array($pID));
		if ($rs->RecordCount()==0) {
			$db->Execute("UPDATE ".TABLE_PRODUCTS." SET flag_has_specials=0 WHERE products_id=?",array($pID));
		} else {
			$db->Execute("UPDATE ".TABLE_PRODUCTS." SET flag_has_specials=1 WHERE products_id=?",array($pID));
		}
		
	}
	
	function _setStatus($id, $status) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;

		$db->Execute("update " . TABLE_PRODUCTS_PRICE_SPECIAL . " set status = ? where ".$this->master_id." = ? ",array((int)$status,$id));
		
		($plugin_code = $xtPlugin->PluginCode('plugin_special_products:_setStatus_buttom')) ? eval($plugin_code) : false;

	}	

	function _unset($id) {
	    global $db, $xtPlugin;

	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

		$rs = $db->Execute("SELECT products_id FROM ".TABLE_PRODUCTS_PRICE_SPECIAL." WHERE ".$this->master_id." = ? ",array($id));
		

		$db->Execute("DELETE FROM ". TABLE_PRODUCTS_PRICE_SPECIAL ." WHERE ".$this->master_id." = ?",array($id));
		
		if ($rs->RecordCount()==1) $this->_updateProductsSpecialflag($rs->fields['products_id']);
		
			($plugin_code = $xtPlugin->PluginCode('plugin_special_products:_unset_buttom')) ? eval($plugin_code) : false;
	}
	
	function build_price($id, $pprice, $tax_class='', $type='show'){
		global $price;
		
		if(!$tax_class)
		$tax_class = $price->getTaxClass('products_tax_class_id', TABLE_PRODUCTS, 'products_id', $id);
		
		$pprice = $price->_BuildPrice($pprice, $tax_class, $type);
		return $pprice;
	}		
}
?>