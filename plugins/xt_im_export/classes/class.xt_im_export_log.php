<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
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


class xt_im_export_log extends default_table {

	protected $_table = TABLE_EXPORTIMPORT_LOG;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {


		$params = array();

		$header['cron_id'] = array('type' => 'hidden');

		$params['header']         = $header;
		$params['display_searchPanel']  = false;
		$params['master_key']     = $this->_master_key;

        $params['display_statusTrueBtn']  = true;
        $params['display_statusFalseBtn']  = false;

        $params['display_newBtn']  = false;
        $params['display_deleteBtn']  = true;
        $params['display_editBtn']  = false;

        if (!$this->url_data['edit_id'] && $this->url_data['new'] != true) {
            $params['include'] = array('id','error_message');
        } else {

        }

		return $params;
	}


	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;


		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}		
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key,'ei_id='.(int)$this->url_data['ei_id'], $this->sql_limit,'','','');

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
            foreach ($data as $key=>$value) {
                $str=str_replace('&quot;','"',$data[$key]['data']);
                $arr = unserialize($str);
                $data[$key]['runtime']=$arr['runtime'];
            }

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

	function _set($data, $set_type='edit'){
		global $db,$language,$filter;


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

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = '".$id."'");

	}


}
?>