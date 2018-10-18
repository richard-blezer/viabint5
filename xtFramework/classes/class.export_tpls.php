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

class export_tpls {

	protected $_table = TABLE_EXPORT_TPLS;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'export_tpls_id';


	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['export_tpls_id'] = array('type' => 'hidden');
		$header['export_tpls_h'] = array('type' =>  'textarea','height'=>'150');
		$header['export_tpls_b'] = array('type' =>  'textarea','height'=>'150');
		$header['export_tpls_f'] = array('type' =>  'textarea','height'=>'150');
		$header['last_modified'] = array('type' => 'hidden');
		$header['date_added'] = array('type' => 'hidden');
		
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		
		$extF = new ExtFunctions();
		
		$js = "Ext.Msg.show({
			   title:'".TEXT_START."',
			   msg: '".TEXT_START_ASK."',
			   buttons: Ext.Msg.YESNO,
			   animEl: 'elId',
			   fn: function(btn){
						var edit_id = edit_id;
					  		if (btn == 'yes') {
					  			var conn = new Ext.data.Connection();
				                 conn.request({
				                 url: '../cronjob.php',
				                 method:'GET',
				                 params: {'export_tlps': 'true'},
				                 success: function(responseObject) {
				                           Ext.MessageBox.alert('Message', '".TEXT_EXPORT_SUCCESS."');
				                          }
				                 });
							}
				},
			   icon: Ext.MessageBox.QUESTION
			});";
		
		
		$UserButtons['options_add'] = array('text'=>'TEXT_EXPORT_TPLS_EXPORT', 'style'=>'options_add', 'icon'=>'add.png', 'acl'=>'edit', 'stm' => $js);

		$params['display_options_addBtn'] = true;
		$params['UserButtons']      = $UserButtons;
		

		return $params;
	}

	 function _get($ID = 0) {
        global $xtPlugin, $db, $language;

        if ($this->position != 'admin') return false;

		$obj = new stdClass;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}
		$ID=(int)$ID;

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}			
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', $this->sql_limit);    

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

	function _set($data,$set_type='edit') {
		global $db,$language,$filter;
		
		if($set_type=='new'){
			$data['date_added'] = date("Y-m-d H:i:s");   
		}
		
		$data['last_modified'] = date("Y-m-d H:i:s");
		
		$obj = new stdClass;
		$oM = new adminDB_DataSave($this->_table, $data);
		$obj = $oM->saveDataSet();

		return $obj;
	}
	
	function _setStatus($id, $status) {
		global $db,$xtPlugin;
	
		$id = (int)$id;
		if (!is_int($id)) return false;
	
		$db->Execute(
			"UPDATE " . $this->_table . " SET export_tpls_status = ? WHERE export_tpls_id = ?",
			array($status, $id)
		);
	
	}

	function _unset($id = 0) {
	    global $db;
	    $db->Execute("DELETE FROM " . $this->_table . " WHERE export_tpls_id = ?", array($id));
	    return true;
	}
	
	function _export($id = 0) {
		global $db;
		
		$xml_path = _SRV_WEBROOT. '/ps/';

		$list = array();
		$data = $this->_buildExportData();
		
		foreach($data as $key => $value){
			$a['id'] = $value['export_tpls_id'];
			$a['name'] = $value['export_tpls_name'];
			$a['file'] = $value['export_tpls_fname'].'_'.$value['export_tpls_id'].'.xml';
			$list['templates']['template'][] = $a;
			
			$tpl['template'] = $value;

			$xml = XML_serialize($tpl);
			$fp = fopen($xml_path. $value['export_tpls_fname'].'_'.$value['export_tpls_id'].'.xml', "w");
			fwrite($fp, $xml);
			fclose($fp);
		}
		
		$xml = XML_serialize($list);
		
		$fp = fopen($xml_path.'list.xml', "w");
		fwrite($fp, $xml);
		fclose($fp);
		
		return true;
	}
	
	function _buildExportData(){
		global $db;
		
		$data = array();
		
		$query = 'SELECT * from '. $this->_table . ' WHERE export_tpls_status = 1';
		$record = $db->Execute($query);
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$data[] = $record->fields;
				$record->MoveNext();
			}
		}
		return $data;
	}
}