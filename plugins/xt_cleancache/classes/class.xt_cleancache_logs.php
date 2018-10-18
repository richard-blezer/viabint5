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
 # @version $Id: xt_cleancache_logs.php 4953 2012-02-03 15:18:56Z tu $
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

class xt_cleancache_logs{

	protected $_table = TABLE_CLEANCACHE_LOGS;
	protected $_table_lang = '';
	protected $_table_seo = null;
	protected $_master_key = 'id';


	public function xt_cleancache_logs(){
		global $xtPlugin;
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.'cleancache_logs')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;		
		
	}

	public function setPosition ($position) {
		$this->position = $position;
	}

	public function _getParams() {
		global $language;


		$params = array();
		$header['id'] = array('disabled' => 'true');
		$header['type'] = array('disabled' => 'true');
		$header['last_run'] = array('disabled' => 'true');
		$header['change_trigger'] = array('disabled' => 'true');
		$header['date_added'] = array('disabled' => 'true');
		$header['last_modified'] = array('disabled' => 'true');
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;

		$params['display_newBtn'] = false;
        $params['display_deleteBtn'] = false;
		$params['display_checkCol']  = false;
		$params['display_statusTrueBtn']  = false;
		$params['display_statusFalseBtn']  = false;
		
		$params['display_searchPanel']  = false;

		return $params;
	}

	public function _get($ID = 0) {
		global $xtPlugin, $db, $language,$filter;

		if ($this->position != 'admin') return false;

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

        $obj = new stdClass;
		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;

	}

	public function _set($data, $set_type='edit'){
		global $db,$language,$filter;

		 $obj = new stdClass;

		 $oC = new adminDB_DataSave(TABLE_CLEANCACHE_LOGS, $data);
		 $objC = $oC->saveDataSet();

		 if ($objC->success) {
		     $obj->success = true;
		 } else {
		     $obj->failed = true;
		 }

		return $obj;
	}

	public function _unset($id = 0) {
	    return false;
        global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;

	    $db->Execute("DELETE FROM ". TABLE_CLEANCACHE_LOGS ." WHERE ".$this->_master_key." = ".$id);
	}
}
?>