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

class MediaImageSearch extends MediaImages {

	protected $_table = TABLE_MEDIA;
	protected $_table_lang = TABLE_MEDIA_DESCRIPTION;
	protected $_table_seo = null;
	protected $_master_key = 'id';

    function __construct() {

    	$this->getPermission();

		$this->path 		= _SRV_WEB_IMAGES;
        $this->urlPath      = _SYSTEM_BASE_HTTP._SRV_WEB_UPLOAD;
		$this->type = 'images';
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
		$header['file'] = array('type' => 'image');

		$params['default_sort']   = 'sort_order';
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['PageSize']		  = 10;

		$extF = new ExtFunctions();
		
		if($this->url_data['pg']=='overview' && !$this->url_data['link_id'] && !($this->url_data['new'] || $this->url_data['edit'])){
			$params['include'] = array('id','sort_order','file', 'media_name_'.$language->code, 'status');
		} else {
			$params['exclude'] = array('owner', 'download_status', 'date_added', 'last_modified', 'type','class', 'type','max_dl_count','max_dl_days');
		}

		$params['display_searchPanel']  = true;
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = false;
		$params['display_GetSelectedBtn'] = true;
		$params['display_statusFalseBtn']  = false;
		$params['display_editBtn']  = false;
		$params['display_newBtn']  = false;		

		return $params;
	}

	function _getSearchIds($search_data) {
		global $xtPlugin, $db, $language;
		
		$searchQry = '';

		$searchArray = array($this->_table.'.file', $this->_table_lang.'.media_name', $this->_table_lang.'.media_description');
		$searchQry = ' ';
		foreach ($searchArray as $key) {
			$searchQry .= "  ".$key." like '%".$search_data."%'  or ";			
		}
		$searchQry = substr($searchQry, 0, -4);
		$searchQry .=" ";

		$ids = array();
        $record = $db->Execute("SELECT distinct ".$this->_table.".".$this->_master_key." 
        						FROM ".$this->_table." 
        						LEFT JOIN ".$this->_table_lang." 
        						ON ".$this->_table.".".$this->_master_key." = ".$this->_table_lang.".".$this->_master_key." 
        						WHERE  ".$searchQry);
    	if ($record->RecordCount() > 0) {
    		while(!$record->EOF){
    			$ids[] = $record->fields[$this->_master_key];
    			$record->MoveNext();
    		} $record->Close();
    	}			
    	return $ids;
	}

	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		
		if ($this->position != 'admin') return false;

		$ID=(int)$ID;

		$qry .= " type = '".$this->type."' ";
		
		if($this->url_data['currentType'])
		$qry .= "and class = '".$this->url_data['currentType']."' ";
		
		$searchQry = '';
		$ids = array();
		if ($this->url_data['query']) {
			$ids = $this->_getSearchIDs($this->url_data['query']);
		} 
				
		if (count($ids) > 0) {
			$ids = array_unique($ids);
			$searchQry= " and ".$this->_table.".id IN (".implode(',', $ids).") ";
		}		
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $qry.$searchQry, '', $this->perm_array);

			if ($this->url_data['get_data']){
		    	$data = $table_data->getData();
		    	
		    	if(is_array($data)){
			    	foreach ($data as $count => $d) {
				    	if(!$data[$count]['media_name_'.$language->code])
				    	$data[$count]['media_name_'.$language->code] = $data[$count]['file'];
			    	}
		    	}	    	
		    
		    	$data = $data;
			}elseif($ID){
				$data = $table_data->getData($ID);
			}else{
				$data = $table_data->getHeader();
			}

		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		$obj = new stdClass();
		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($ID, $set_type = 'edit'){
		global $db;

		$obj = new stdClass;
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);
		$data = $table_data->getData($ID);
		
		$this->setMediaToCurrentType($data[0]['file']);
		 
		$obj->success = true;
		return $obj;
	}
}