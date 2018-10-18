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


class cross_selling_products extends product {

	protected $_table_xsell = TABLE_PRODUCTS_CROSS_SELL;

	function _getParams() {
		global $language;

		$params = array();
		$header['products_id'] = array('type'=>'hidden');

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

		$query = "select products_id_cross_sell from ".$this->_table_xsell." where products_id = ? ";

		$record = $db->Execute($query,array((int)$id));
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id_cross_sell'];
				$record->MoveNext();
			} $record->Close();
		}

		return $data;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
        $obj = new stdClass;
		if ($this->position != 'admin') return false;
		
		if(!$this->url_data['query']){

			if ($this->url_data['get_data']){
	
				$search_result = $this->_getIDs($this->url_data['products_id']);
	
				if(is_array($search_result) && count($search_result != 0)){
					$sql_where = " products_id IN (".implode(',', $search_result).")";

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
		
			$sql_where .= " products_id!=".(int)$this->url_data['products_id'];
	
					
			if(XT_MASTER_SLAVE_ACTIVE=='true'){
				
				$sql_where .= " and (products_master_model = '' or products_master_model IS NULL)";
				
			}				
			
			if ($this->url_data['query']) {
				$search_result = $this->_getSearchIDs($this->url_data['query']);
				if(is_array($search_result) && count($search_result)>0){
					$sql_where .= " and products_id IN (".implode(',', $search_result).")";
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

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($id, $set_type = 'edit') {
		global $db,$language,$filter;

		 $data = array();
		 $data['products_id'] = (int)$this->url_data['products_id'];
		 $data['products_id_cross_sell'] = (int)$id;

		 $obj = new stdClass;
		 $o = new adminDB_DataSave($this->_table_xsell, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		return $obj;
	}	
	
	function _unset($id = 0) {
	    global $db, $xtPlugin;
		$pID=(int)$this->url_data['products_id'];
		$id=(int)$id;
		
	    if (!$id || !$pID || $this->position != 'admin') return false;
	    $db->Execute("DELETE FROM ". $this->_table_xsell ." WHERE products_id_cross_sell = ? and products_id = ?",array($id,$pID) );
	}
}
?>