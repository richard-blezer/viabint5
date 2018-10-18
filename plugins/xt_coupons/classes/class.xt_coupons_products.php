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


class xt_coupons_products extends product {

	protected $_table_coupons_products = TABLE_COUPONS_PRODUCTS;


	function _getParams() {
		global $language;

		$params = array();
		$header['coupon_id'] = array('type'=>'hidden');

		$params['display_checkCol']  = true;
		$params['display_editBtn']  = false;
		$params['display_newBtn']  = false;
		$params['display_GetSelectedBtn'] = true;		

		$params['display_searchPanel']  = true;		
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('products_id', 'products_name_'.$language->code, 'products_model', 'products_price', 'products_status');
		}

		return $params;
	}

	function _getIDs($id) {
		global $xtPlugin, $db, $language, $seo;
		$query = "select products_id from ".$this->_table_coupons_products." where coupon_id = ? ";

		$record = $db->Execute($query,array((int)$id));
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id'];
				$record->MoveNext();
			} $record->Close();
		}

		return $data;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		$sql_where_trenner = '';
        $obj = new stdClass;
		if ($this->position != 'admin') return false;
		if(!$this->url_data['query']){

			if ($this->url_data['get_data']){
	
				$search_result = $this->_getIDs($this->url_data['coupon_id']);

				if(is_array($search_result) && count($search_result != 0)){
					$sql_where = " products_id IN (".implode(',', $search_result).")";
					$sql_where_trenner = ' and ';

					$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, '', $this->perm_array);
					$data = $table_data->getData();
				}else{
					$data = array();
				}
			}else{
				$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);
				$data = $table_data->getHeader();
			}

		}else{
		
			$search_result = $this->_getIDs($this->url_data['coupon_id']);  
            if(is_array($search_result) && count($search_result)>0){
                $sql_where .= $sql_where." products_id NOT IN (".implode(',', $search_result).")";
                $sql_where_trenner = ' and ';
            }
	
			// 1.3.3.: by deactivating these lines, slaves can be chosen also
            /*		
			if(XT_MASTER_SLAVE_ACTIVE=='true'){
				$sql_where .= $sql_where_trenner. " products_master_model = '' ";
				$sql_where_trenner = ' and ';				
			}				
			*/
			
			if ($this->url_data['query']) {
				$search_result = $this->_getSearchIDs($this->url_data['query']);
				if(is_array($search_result) && count($search_result)>0){
					$sql_where .= $sql_where_trenner." products_id IN (".implode(',', $search_result).")";
					$sql_where_trenner = ' and ';
				}
			}
	
			if (!isset($this->sql_limit)) {
				$this->sql_limit = "0,25";
			}
            

			$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $this->perm_array);
	
			if ($this->url_data['get_data']){
				$data = $table_data->getData();
			}else{
				$data = $table_data->getHeader();
			}		
		
		}
		
		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		if(($count_data <0) or (is_null($count_data))) $count_data = 0;
		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($id, $set_type = 'edit') {
		global $db,$language,$filter;
		 $data = array();
		 $data['coupon_id'] = (int)$this->url_data['coupon_id'];
		 $data['products_id'] = (int)$id;

		 $obj = new stdClass;
		 $o = new adminDB_DataSave($this->_table_coupons_products, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		return $obj;
	}	
	
	function _unset($id = 0) {
	    global $db, $xtPlugin;

        $coupon_id = (int)$this->url_data['coupon_id']; 
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". $this->_table_coupons_products ." WHERE products_id = ? and coupon_id=?",array($id,$coupon_id));

	}
}
?>