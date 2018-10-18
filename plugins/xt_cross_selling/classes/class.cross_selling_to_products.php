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


class cross_selling_to_products extends product {

	protected $_table_xsell = TABLE_PRODUCTS_CROSS_SELL;

	function _getParams() {
		global $language;

		$params = array();
		$header['products_id'] = array('type'=>'hidden');

		$params['display_GetSelectedBtn'] = true;
		$params['display_checkCol']  = true;
		//$params['display_deleteBtn']  = false;
		$params['display_editBtn']  = false;
		$params['display_newBtn']  = false;
		$params['display_searchPanel']  = true;

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('products_id', 'products_name_'.$language->code, 'products_model', 'products_price', 'products_status');
		}

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		$sql_where .= " products_id!=".(int)$this->url_data['products_id'];

		if ($this->url_data['query']) {
			$search_result = $this->_getSearchIDs($this->url_data['query']);
			$sql_where .= " and products_id IN (".implode(',', $search_result).")";
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, '', $this->perm_array);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}else{
			$data = $table_data->getHeader();
		}

		$obj = new stdClass;
		$obj->totalCount = count($data);
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
}
?>