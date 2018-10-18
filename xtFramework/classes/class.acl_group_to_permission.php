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

class acl_group_to_permission {

	protected $_table = TABLE_ADMIN_ACL_AREA_PERMISSIONS;
	protected $_table_area = TABLE_ADMIN_ACL_AREA;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'perm_id';
	protected $_master_area_key = 'area_id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['group_id'] = array('type' => 'hidden');
		$header['area_id'] = array('type' => 'hidden');
		$header['acl_read'] = array('type' => 'status', 'renderer'  => 'RightsRenderer', 'width'=>40);
		$header['acl_edit'] = array('type' => 'status', 'renderer'  => 'RightsRenderer', 'width'=>40);
		$header['acl_new'] = array('type' => 'status', 'renderer'  => 'RightsRenderer', 'width'=>40);
		$header['acl_delete'] = array('type' => 'status', 'renderer'  => 'RightsRenderer', 'width'=>40);

		$params['gridType'] = 'EditGrid';
		$params['display_editBtn']  = false;

		$params['display_checkCol']  = true;
		$params['display_newBtn']  = false;
		$params['display_saveBtn'] = true;	

		$params['SortField']      = "area_name";
		$params['SortDir']        = "ASC";	

		$menuGroups[] = array('group'=>'acl_rights', 'group_name'=>TEXT_SET_RIGHTS, 'ToolbarPos'=>'Toolbar', 'Pos'=>'grid'); // Toolbarpos = TopToolbar or Toolbar, Pos = grid / edit / both
		$params['menuGroups']			   = $menuGroups;
		
        $extF = new ExtFunctions();
        
        $arjs = $extF->_MultiButton_stm('TEXT_SET_ALL_RIGHTS', 'doAllRights');
        $params['display_aclGroupAllRightsMn']  = true;
		$menuActions['acl_rights']['aclGroupAllRights'] = array('status'=>'true', 'text'=>'TEXT_SET_ALL_RIGHTS', 'style'=>'allRights', 'icon'=>'key_go.png', 'acl'=>'new', 'stm'=>$arjs, 'func'=>'doAllRights', 'flag'=>'multiFlag_setAllRights', 'flag_value'=>'true');

        $uarjs = $extF->_MultiButton_stm('TEXT_UNSET_ALL_RIGHTS', 'doAllRightsDel');
        $params['display_aclGroupAllRightsDelMn']  = true;
		$menuActions['acl_rights']['aclGroupAllRightsDel'] = array('status'=>'true', 'text'=>'TEXT_UNSET_ALL_RIGHTS', 'style'=>'allRightsDel', 'icon'=>'key_delete.png', 'acl'=>'delete', 'stm'=>$uarjs, 'func'=>'doAllRightsDel', 'flag'=>'multiFlag_unsetAllRights', 'flag_value'=>'true');
		
		$params['menuActions']             = $menuActions;		
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_area_key;
		$params['default_sort']   = $this->_master_area_key;
		$params['languageTab']    = false;
		$params['PageSize']		  = 99999;
		
		return $params;
	}

	function _getPermData($ID){
		global $db;

		$qry = "SELECT * FROM " . $this->_table . " where area_id =? and group_id=?";

		$record = $db->Execute($qry, array((int)$ID, $this->url_data['group_id']));
		if($record->RecordCount() > 0){
			return $record->fields;
		}else{
			$table_data = new adminDB_DataRead($this->_table, null, null, $this->_master_key);
			return $table_data->getTableFields($this->_table);
		}
	}
	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table_area, null, null, $this->_master_area_key);
		if ($this->url_data['get_data']){
        	$data = $table_data->getData();
        	if(is_array($data)){
        		foreach ($data as $key => $val){
	        		$tmp_data = '';
	        		$tmp_data = $this->_getPermData($data[$key]['area_id']);        			
        			unset($tmp_data['area_id']);
        			unset($tmp_data['group_id']);
        			if(is_array($tmp_data))
        			$data[$key] = array_merge($data[$key], $tmp_data);
        			$data[$key]['group_id'] = $this->url_data['group_id'];
        		}
        	}
		}else{
			$acl_table_data = new adminDB_DataRead($this->_table, null, null, $this->_master_key);
			$acl_data = $acl_table_data->getHeader();	
			$data = $table_data->getHeader();
			$data[0] = array_merge($data[0], $acl_data[0]);
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
		global $db,$language,$filter;

		if($data['perm_id']=='')
		unset($data['perm_id']);
		
		$obj = new stdClass;
		
		foreach($data as $key => $val){
			
			if($data[$key]=='true')
			$data[$key] = 1;
			
		}
		
		if($set_type=='new')
		$data['group_id'] = $this->url_data['group_id'];

		 $oP = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		 $obj = $oP->saveDataSet();

		return $obj;
	}

	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;

		$id=(int)$id;
		if (!is_int($id)) return false;

	    $db->Execute(
			"DELETE FROM ". $this->_table ." WHERE area_id =? and group_id=? ",
			array($id, $this->url_data['group_id'])
		);
	}
	
	function _getName($id) {
		global $db, $language;

		$qry = "SELECT area_name FROM " . TABLE_ADMIN_ACL_AREA . " where area_id = ?";

		$record = $db->Execute($qry, array((int)$id));
		if($record->RecordCount() > 0){
			return $record->fields['area_name'];
		}
	}	
	
	function _setAllRights($id=''){
		global $db;
		
		$obj = new stdClass;
		
		if($this->url_data['group_id']){
		
			$this->_unsetAllRights($this->url_data['group_id']);
			
			$table_data = new adminDB_DataRead($this->_table_area, null, null, $this->_master_area_key);
			$data = $table_data->getData();		
			if(is_array($data)){
	       		foreach ($data as $key => $val){
			
	       			$data['area_id'] = $val['area_id'];
					$data['group_id'] = $this->url_data['group_id'];
					$data['acl_read'] = 1;
					$data['acl_edit'] = 1;
					$data['acl_new'] = 1;
					$data['acl_delete'] = 1;
	
			 		$oP = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
			 		$obj = $oP->saveDataSet();
	       		}
			}
		}
		return $obj;
	}
	
	function _unsetAllRights($id=''){
		global $db;
		
	    if ($id == 0)
	    $id = $this->url_data['group_id'];
	    
		if ($this->position != 'admin') return false;

		$id=(int)$id;
		if (!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE group_id=? ", array($id));
	
	}
}