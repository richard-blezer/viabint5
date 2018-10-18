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


class product_serials {

	protected $_table = TABLE_PRODUCTS_SERIAL;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'serial_id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function assignSerials($orders_id) {
		global $db;

		$orders_id = (int)$orders_id;

		$rs = $db->Execute(
			"SELECT p.products_serials,p.products_id,op.orders_products_id,op.products_quantity FROM ".TABLE_PRODUCTS." p, ".TABLE_ORDERS_PRODUCTS." op WHERE p.products_id=op.products_id and op.orders_id=?",
			array($orders_id)
		);
		while (!$rs->EOF) {
			if ($rs->fields['products_serials']=='1') {
				// check if already attached serial
				if (!$this->_hasSerial($orders_id,$rs->fields['orders_products_id'])) {
					$this->_setSerial($rs->fields['products_id'],$rs->fields['orders_products_id'],$orders_id,(int)$rs->fields['products_quantity']);
				}
			}
			$rs->MoveNext();
		}
	}

	/**
	 * check if enough serials are in stock, assign free serial to product
	 *
	 * @param int $products_id
	 * @param int $orders_products_id
	 * @param int $orders_id
	 */
	function _setSerial($products_id,$orders_products_id,$orders_id,$qty=1) {
		global $db,$logHandler;

		$rs = $db->Execute(
			"SELECT count(*) as count FROM ".$this->_table." WHERE orders_id='0' and orders_products_id='0' and status='1' and products_id=?",
			array((int)$products_id)
		);
		if ($rs->fields['count']<XT_SERIALS_WARNING_MIN) {
			// escalate log
			$log_data = array();
			$log_data['message'] = 'low serial stock';
			$log_data['stock_left'] = $rs->fields['count'];
			$logHandler->_addLog('warning','xt_serials',$products_id,$log_data);
		}

		// get free serials
		$i = 0;
		while ($i<$qty) {
		$rs = $db->Execute(
			"SELECT serial_id FROM ".$this->_table." WHERE orders_id='0' and orders_products_id='0' and status='1' and products_id=? LIMIT 0,1",
			array((int)$products_id)
		);
		$i++;
		if ($rs->RecordCount()==0) {
			$log_data = array();
			$log_data['message'] = 'EMPTY serial stock';
			$log_data['stock_left'] = 0;
			$logHandler->_addLog('error','xt_serials',$products_id,$log_data);
		} else {
			$update_array= array();
			$update_array['orders_id']=(int)$orders_id;
			$update_array['orders_products_id']=(int)$orders_products_id;
			$db->AutoExecute($this->_table,$update_array,'UPDATE',"serial_id='".(int)$rs->fields['serial_id']."'");
		}
		}
	}

	/**
	 * check if product has allready assigned serial number
	 *
	 * @param int $orders_id
	 * @param int $orders_products_id
	 * @return boolean
	 */
	function _hasSerial($orders_id,$orders_products_id) {
		global $db;
		$rs = $db->Execute(
			"SELECT * FROM ".$this->_table." WHERE orders_id=? and orders_products_id=?",
			array((int)$orders_id, (int)$orders_products_id)
		);
		if ($rs->RecordCount()>=1) return true;
		return false;

	}

	function getSerialsAdmin($orders_id) {
		global $db;

		$template='';
		$rs = $db->Execute(
			"SELECT op.*, ps.* FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_PRODUCTS_SERIAL." ps WHERE op.orders_id=ps.orders_id and op.orders_products_id=ps.orders_products_id and op.orders_id=?",
			array($orders_id)
		);
		if ($rs->RecordCount()>0) {

			$template .='<div class="x-panel-header x-unselectable">'.__define('TEXT_PRODUCTS_SERIALS').'</div>';
			$template .='<table cellspacing="0" width="100%">'.
            	'<tbody>'.
                '<tpl for="order_products" >';

			while (!$rs->EOF) {
				$template.='<tr>'.
                    '<td style="text-align:left;">'.__define('TEXT_PRODUCTS_NAME').': '.$rs->fields['products_name'].'</td>'.
					'<td style="text-align:left;">'.__define('TEXT_PRODUCTS_MODEL').': '.$rs->fields['products_model'].'</td>'.
					'<td style="text-align:left;">'.__define('TEXT_SERIAL_NUMBER').': '.$rs->fields['serial_number'].'</td>'.
                '</tr>';
				$rs->MoveNext();
			}
			$template.='</tpl>'.
                '</tbody></table><br /><br />';

		}
		return $template;
	}

	function getSerialsFrontend($orders_id) {
		global $db;
		$serials = array();
		$rs = $db->Execute(
			"SELECT op.*, ps.* FROM ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_PRODUCTS_SERIAL." ps WHERE op.orders_id=ps.orders_id and op.orders_products_id=ps.orders_products_id and op.orders_id=?",
			array($orders_id)
		);
		if ($rs->RecordCount()>0) {
			while (!$rs->EOF) {
				$serials[]=$rs->fields;	
				$rs->MoveNext();
			}
		}

		if (count($serials)==0) return;

		$tpl_data = array('serials'=>$serials);
		$tmp_data = '';
		$tpl = 'history_info.html';
		$template = new Template();
		$template->getTemplatePath($tpl, 'xt_serials', '', 'plugin');

		$tmp_data = $template->getTemplate('xt_serials_history_smarty', $tpl, $tpl_data);
		echo $tmp_data;
	}

	function _getParams() {
		$params = array();

		$header['serial_id'] = array('type' => 'hidden');
		$header['orders_id'] = array('type' => 'hidden');
		$header['orders_products_id'] = array('type' => 'hidden');
		$header['products_id'] = array('type' => 'hidden');

		$params['header']         = $header;
		$params['display_searchPanel']  = true;
		$params['master_key']     = $this->_master_key;

		return $params;
	}


	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}			
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key,'products_id='.(int)$this->url_data['products_id'], $this->sql_limit);

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

	function _set($data, $set_type='edit'){
		global $db,$language,$filter;

		if($set_type=='new'){
			$data['products_id'] = (int)$this->url_data['products_id'];
		}


		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();

		$obj->success = true;

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