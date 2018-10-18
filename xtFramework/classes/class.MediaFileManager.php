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

class MediaFileManager extends MediaData {

	protected $_table = TABLE_MEDIA;
	protected $_table_lang = TABLE_MEDIA_DESCRIPTION;
	protected $_table_seo = null;
	protected $_master_key = 'id';


    function __construct() {

    	$this->getPermission();

		$this->path 		= _SRV_WEB_MEDIA_FILES;
        $this->urlPath      = _SYSTEM_BASE_HTTP._SRV_WEB_UPLOAD;
		$this->type = 'files';
    }

	function getPermission(){
		global $store_handler, $customers_status, $xtPlugin;

		$this->perm_array = array(
			'group_perm' => array(
				'type'=>'group_permission',
				'key'=>$this->_master_key,
				'value_type'=>'media_file',
				'pref'=>'mi'
			)
		);

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

		$this->permission = new item_permission($this->perm_array);

		return $this->perm_array;
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language;

		$params = array();

		foreach ($language->_getLanguageList() as $key => $val) {
			$header['media_description_'.$val['code']] = array('type' => 'htmleditor');
		}

		$header['download_status'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=download_status'
		);

		$header['status'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=status_truefalse'
		);

		$header['id'] = array('type'=>'hidden');

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;


		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array('id', 'download_status','file', 'status', 'type','max_dl_count','max_dl_days');
		} else {
			$params['exclude'] = array('owner', 'date_added', 'last_modified', 'type','class');
		}
		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}
		$ID=(int)$ID;

		$qry = " type = 'files'";
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $qry, '', $this->perm_array);

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

	function _set($data, $set_type = 'edit'){
		global $db, $language, $filter, $seo,$db;

		$obj = new stdClass;

		$data['type'] = 'files';

		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$objC = $oC->saveDataSet();

		if ($set_type=='new') {	// edit existing
			$obj->new_id = $objC->new_id;
			$data['date_added'] = $db->DBTimeStamp(time());
			$data = array_merge($data, array($this->_master_key=>$objC->new_id));
		}

		$oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
		$objCD = $oCD->saveDataSet();


		$set_perm = new item_permission($this->perm_array);
		$set_perm->_saveData($data, $data[$this->_master_key]);

		if ($objC->success && $objCD->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		return $obj;
	}

	function _unset($id = 0) {
		global $db;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

		$set_perm = new item_permission($this->perm_array);
		$set_perm->_deleteData($id);

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
		$db->Execute("DELETE FROM ". $this->_table_lang ." WHERE ".$this->_master_key." = ?", array($id));
	}
}