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

class MediaFileTypes {

	protected $_table = TABLE_MEDIA_FILE_TYPES;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'mft_id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['mft_id'] = array('type'=>'hidden');
		$header['file_ext'] = array('type'=>'');
		
		$header['file_type'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=file_types'
		);

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
 		$params['languageTab']    = false;

		return $params;
	}
	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data'])
        $data = $table_data->getData();
        elseif($ID)
        $data = $table_data->getData($ID);
        else
		$data = $table_data->getHeader();

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

		 $obj = new stdClass;
		 $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		return $obj;
	}


	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
	}
	
	function getFileExt($file_type){
		global $db;
		$FileTypes='';
        $UploadTypes='';
        $UploadTypesArray=array();
        
	    $record = $db->Execute(
			"SELECT file_ext FROM ".$this->_table." where file_type=? ",
			array($file_type)
		);
    	if ($record->RecordCount() > 0) {
    		while(!$record->EOF){
    			
    			$FileTypes .= $record->fields['file_ext'].'|';
    			$UploadTypes .= '*.'.$record->fields['file_ext'].';';  
    			$UploadTypesArray[] = '.'.$record->fields['file_ext'];
    			
   				$record->MoveNext();		
    		} $record->Close();
    	}		
    	
    	$FileTypes = substr($FileTypes, 0, strlen($FileTypes)-1);
		$UploadTypes = substr($UploadTypes, 0, strlen($UploadTypes)-1);
    	
		$data_array = array('FileTypes'=>$FileTypes, 'UploadTypes'=>$UploadTypes, 'UploadTypesArray'=>$UploadTypesArray);
		
		return $data_array;
	}
}