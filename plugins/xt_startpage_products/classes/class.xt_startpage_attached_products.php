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

class xt_startpage_attached_products extends product {
	
	function _getParams() {
		global $language;
	
		$params = array();
		$header['products_id'] = array('type'=>'hidden');
		$header['startpage_products_sort'] = array('type'=>'hidden');
	
		$params['display_GetSelectedBtn'] = false;
		$params['display_checkCol']  = true;
		$params['display_deleteBtn']  = true;
		$params['display_editBtn']  = false;
		$params['display_newBtn']  = false;
		$params['display_searchPanel']  = true;
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = 'startpage_products_sort';
		$params['SortField']      = 'startpage_products_sort';
		$params['SortDir']        = "ASC";
		$params['sort_url'] = 'adminHandler.php?load_section=xt_startpage_attached_products&plugin=xt_startpage_products&shop_id=' . $this->url_data['shop_id'] . '&pg=sort&';
		$params['include'] = array ('products_id', 'products_name_'.$language->code, 'products_model', 'products_price', 'products_status', 'startpage_products_sort');
		
		$extF = new ExtFunctions();
		$rowActionsFunctions['sort_up'] = $extF->_MultiButton_stm(TEXT_SORT_UP, 'sort_up');
		$rowActionsFunctions['sort_down'] = $extF->_MultiButton_stm(TEXT_SORT_DOWN, 'sort_down');
		$rowActions[] = array('iconCls' => 'sort_up', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_SORT_UP);
		$rowActions[] = array('iconCls' => 'sort_down', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_SORT_DOWN);
		 
		$params['rowActions'] = $rowActions;
		$params['rowActionsFunctions'] = $rowActionsFunctions;
	
		return $params;
	}
	
	public function setPosition ($position) {
		$this->position = $position;
	}
	
	function sort () {
		
		$currentElement = $this->url_data['m_ids'];
		
		if ($this->url_data['pos'] == 'up') {
			$this->moveUp($currentElement);
		}
		 
		if ($this->url_data['pos'] == 'down') {
			$this->moveDown($currentElement);
		}
		 
		$obj = new stdClass();
		$obj->success = true;
		return '';
	}

	protected function getCurrentSort($id, $shop_id) {
		global $db;
		$table = DB_PREFIX . '_startpage_products';
		$rs = $db->Execute("SELECT startpage_products_sort FROM {$table} WHERE products_id=? AND shop_id=? ",array((int)$id,(int)$shop_id));
		
		if ($rs->RecordCount() == 0) {
			return 0;
		}
		
		return $rs->fields['startpage_products_sort'];
	}
	protected function moveUp($id) {
		global $db;
		$table = DB_PREFIX . '_startpage_products';
		$currentSort = $this->getCurrentSort($id, $this->url_data['shop_id']);
		if ($currentSort == 0) {
			return;
		}
		$db->Execute("UPDATE {$table} SET startpage_products_sort=startpage_products_sort+1 WHERE shop_id=? AND startpage_products_sort<? ORDER BY startpage_products_sort DESC LIMIT 1",
		                                      array((int)$this->url_data['shop_id'],$currentSort));
		$db->Execute("UPDATE {$table} SET startpage_products_sort=startpage_products_sort-1 WHERE shop_id=? AND products_id=?",
		                                      array((int)$this->url_data['shop_id'],(int)$id));
	}
	
	
	protected function moveDown($id) {
		global $db;
		$table = DB_PREFIX . '_startpage_products';
		$currentSort = $this->getCurrentSort($id, $this->url_data['shop_id']);
		if ($currentSort == 0) {
			return;
		}
		$db->Execute("UPDATE {$table} SET startpage_products_sort=startpage_products_sort-1 
		              WHERE shop_id=? AND startpage_products_sort>? 
		              ORDER BY startpage_products_sort ASC LIMIT 1",
                      array((int)$this->url_data['shop_id'],$currentSort));
		$db->Execute("UPDATE {$table} SET startpage_products_sort=startpage_products_sort+1 
		              WHERE shop_id=? AND products_id=?",
                      array((int)$this->url_data['shop_id'],(int)$id));
	}
	
	function _get($ID=0){
		global $xtPlugin, $language, $db;
	
		if ($this->position != 'admin') return false;
	
		$show_productList = 1;
	
		$obj = new stdClass;
		$sql_where = '';
		
		if ($this->url_data['get_data']) {
			$rs = $db->Execute("SELECT products_id FROM " . DB_PREFIX . "_startpage_products WHERE shop_id=?",array((int)$this->url_data['shop_id']));
			
			if ($rs->RecordCount() == 0) {
				$obj->totalCount = 0;
				$obj->data = array();
				
				return $obj;
			}
			$attached_ids = array();
			
			while (!$rs->EOF) {
				$attached_ids[] = $rs->fields['products_id'];
				$rs->MoveNext();
			}
			$rs->Close();
			
			$sql_where .= " {$this->_table}.products_id IN (".implode(',', $attached_ids).")";
		}
		
		// set limit if not set
		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}
	
		if(_SYSTEM_SIMPLE_GROUP_PERMISSIONS=='false')
			$permissions = $this->perm_array;
		else
			$permissions = '';
		$store_field= '';
		if ($this->store_field_exists)
		{
			$store_field= $this->_store_field;
		}
	
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $permissions,'',' ORDER BY startpage_products_sort ASC',$store_field);
		$join_string = 'LEFT JOIN ' . DB_PREFIX . '_startpage_products ON (' . $this->_table . '.products_id=' . DB_PREFIX . '_startpage_products.products_id AND ' . DB_PREFIX . '_startpage_products.shop_id=' . $this->url_data['shop_id'] . ')';
		$table_data->setJoinCondtion($join_string);
		if ($this->url_data['get_data']){
	
			if($show_productList==1){
				$data = $table_data->getData();
				if(is_array($data)){
					foreach ($data as $key => $val) {
						$data[$key]['products_price'] = $this->build_price($data[$key]['products_id'], $data[$key]['products_price'], $data[$key]['products_tax_class_id']);
						$data[$key]['products_type']=$data[$key]['products_master_model'].'_'.$data[$key]['products_master_flag'];
					}
				}
				$data_count = $table_data->_total_count;
	
			}else{
				$data = '';
			}
	
		}elseif($ID){
			$data = $table_data->getData($ID);
	
			$data[0]['products_cmc'] = $this->url_data['changeMasterCat'];
	
			$data[0]['group_permission_info']=_getPermissionInfo();
			$data[0]['shop_permission_info']=_getPermissionInfo();
			if(is_array($data)){
				foreach ($data as $key => $val) {
					$data[$key]['products_price'] = $this->build_price($data[$key]['products_id'], $data[$key]['products_price'], $data[$key]['products_tax_class_id']);
	
					foreach ($language->_getLanguageList() as $k => $v) {
						$data[$key]['url_text_'.$v['code']] = urldecode($data[$key]['url_text_'.$v['code']]);
					}
				}
			}
		}else{
			$data = $table_data->getHeader();
		}
		
		if($data_count!=0 || !$data_count)
			$count_data = $data_count;
		else
			$count_data = count($data);
	
		$obj->totalCount = $count_data;
		$obj->data = $data;
	
		return $obj;
	}
	
	function _unset($pID) {
		global $db;
		$ID = (int)$pID;
		
		$db->Execute("DELETE FROM " . DB_PREFIX . "_startpage_products 
		              WHERE shop_id=? AND products_id=?",array((int)$this->url_data['shop_id'],(int)$pID));
		$obj = new stdClass;
		$obj->success = true;
		return $obj;
	}
}