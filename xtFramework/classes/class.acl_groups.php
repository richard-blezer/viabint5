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

class acl_groups extends acl{

	protected $_table = TABLE_ADMIN_ACL_AREA_GROUPS;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'group_id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getGroupList(){
		global $db;

		$query = "SELECT * FROM " . TABLE_ADMIN_ACL_AREA_GROUPS	 . " ";

		$record = $db->Execute($query);
		if ($record->RecordCount() > 0) {
			while(!$record->EOF){
				$data[] = $record->fields;
				$record->MoveNext();
			} $record->Close();

		}

		return $data;
	}

	function _getParams() {
		global $xtPlugin;
		
		$params = array();

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getParams_top')) ? eval($plugin_code) : false;
		
		
		if($this->url_data['edit_id'] || $this->url_data['new'] == true){

			if ($this->url_data['new'] == true && !$this->url_data['edit_id']) {

				$check_task = new adminTask();
				$check_task->setClass(__CLASS__);

				$check_new = $check_task->checkTask('new');

				if($check_new === 'new'){
					$obj = $this->_set(array(), 'new');
					$this->url_data['edit_id'] = $obj->new_id;
				}else{
					$this->url_data['edit_id'] = $check_new;
				}

			}
		}

		$rowActions[] = array('iconCls' => 'acl_group_permissions', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ACL_GROUP_PERMISSIONS);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id'].";";
		else
         $js = "var edit_id = record.id;";
        $js .= "addTab('adminHandler.php?load_section=acl_group_to_permission&pg=overview&group_id='+edit_id,'".TEXT_ACL_GROUP_PERMISSIONS."')";

		$rowActionsFunctions['acl_group_permissions'] = $js;		
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getParams_rowactions')) ? eval($plugin_code) : false;
			
		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;		
		
		$params['header']         = array();
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
			$ID = $this->url_data['edit_id'];
		}

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_top')) ? eval($plugin_code) : false;
			
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);

		if ($this->url_data['get_data']){
		$data = $table_data->getData();
		}elseif($ID){
		$data = $table_data->getData($ID);
		}else{
		$data = $table_data->getHeader();
		}
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_data')) ? eval($plugin_code) : false;
		
		
		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter,$xtPlugin;

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_set_top')) ? eval($plugin_code) : false;
				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_set_top')) ? eval($plugin_code) : false;
		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_set_bottom')) ? eval($plugin_code) : false;		
		
		return $obj;
	}

	function _unset($id = 0) {
		global $db,$xtPlugin;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

		$db->Execute("DELETE FROM ".TABLE_ADMIN_ACL_AREA_GROUPS." WHERE group_id=?", array($id));
		$db->Execute("DELETE FROM ".TABLE_ADMIN_ACL_AREA_PERMISSIONS." WHERE group_id=?", array($id));
		// get & delete users
		$db->Execute("DELETE FROM ".TABLE_ADMIN_ACL_AREA_USER." WHERE group_id = ?", array($id));
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_unset_bottom')) ? eval($plugin_code) : false;
	}
}