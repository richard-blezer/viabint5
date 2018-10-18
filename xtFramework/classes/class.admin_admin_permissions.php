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

class admin_permissions{

	var $page_limit = 25;

	function admin_permissions($cID){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:admin_permissions_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->customer_admin_groups = $this->_getCustomerAdminGroups($cID);
		$this->rights = $this->_buildAdminRights();

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:admin_permissions_bottom')) ? eval($plugin_code) : false;
	}

	function _getCustomerAdminGroups($cID){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getCustomerAdminGroups_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM " . TABLE_ADMIN_GROUPS_TO_CUSTOMER . " where customer_id=?", array($cID));
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getCustomerAdminGroups_data')) ? eval($plugin_code) : false;
					$data[] = $record->fields;
					$record->MoveNext();
				}$record->Close();
				($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getCustomerAdminGroups_bottom')) ? eval($plugin_code) : false;
				return $data;
			}else{
				return false;
			}
	}

	function _buildAdminRights(){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_buildAdminRights_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$groups = $this->customer_admin_groups;

		while (list ($key, $value) = each($groups)) {

			$record = $db->Execute("SELECT * FROM " . TABLE_ADMIN_PERMISSIONS_TO_ADMIN_GROUPS . " where admin_group_id=?", array($value['admin_group_id']));
				if($record->RecordCount() > 0){
					while(!$record->EOF){
						($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_buildAdminRights_data')) ? eval($plugin_code) : false;
						$data[$record->fields['admin_permissions_key']] = $record->fields;
						$record->MoveNext();
					}$record->Close();
					($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_buildAdminRights_bottom')) ? eval($plugin_code) : false;
				}
		}
		return $data;
	}

	function _checkRights($key, $type='p_view', $handler='info'){
		global $xtPlugin, $info;

		$perm_to_check = $this->rights[$key][$type];
		if($perm_to_check != 1){
			if($handler=='info'){
				return true;
			}else{
				$info->_addInfo(TEXT_ADMIN_PERMISSION_ERROR);
				$this->error = true;
			}
		}elseif($perm_to_check == 1){
				return false;
		}

	}

	function _getAdminGroupList(){
		global $xtPlugin, $db, $xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getAdminGroupList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$query = "SELECT * FROM " . TABLE_ADMIN_GROUPS;
		$pages = new split_page($query, $this->page_limit, $xtLink->_getParams(array ('next_page', 'info')));

		$navigation_count = $pages->split_data['count'];
		$navigation_pages = $pages->split_data['pages'];

		for ($i = 0; $i < count($pages->split_data['data']);$i++) {
			($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getAdminGroupList_data')) ? eval($plugin_code) : false;

			if($this->_checkRights('admin_groups', 'p_edit') != true)
			$pages->split_data['data'][$i]['link_edit'] = $xtLink->_adminlink(array('page'=>'admin_groups_edit', 'params'=>'id='.$pages->split_data['data'][$i]['admin_group_id']));

			if($this->_checkRights('admin_groups', 'p_delete') != true)
			$pages->split_data['data'][$i]['link_delete'] = $xtLink->_adminlink(array('page'=>'admin_groups_edit', 'params'=>'id='.$pages->split_data['data'][$i]['admin_group_id']));

			$data[] = $pages->split_data['data'][$i];
		}

		$data_array = array('data'=>$data, 'count'=>$navigation_count, 'pages'=>$navigation_pages);

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getAdminGroupList_bottom')) ? eval($plugin_code) : false;
		return $data_array;

	}

	function _getAdminGroupData($id){
		global $xtPlugin, $db, $xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getAdminGroupData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

			$query = "SELECT * FROM " . TABLE_ADMIN_GROUPS." where admin_group_id = ?";
			$record = $db->Execute($query, array($id));
				if($record->RecordCount() > 0){
					while(!$record->EOF){
						($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getAdminGroupData_data')) ? eval($plugin_code) : false;
						$data = $record->fields;
						$record->MoveNext();
					}$record->Close();
				}
		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getAdminGroupData_bottom')) ? eval($plugin_code) : false;
		return $data;

	}

	function _getPermissions($id=''){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getPermissions_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

			$record = $db->Execute("SELECT * FROM " . TABLE_ADMIN_PERMISSIONS);
				if($record->RecordCount() > 0){
					while(!$record->EOF){
						($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getPermissions_data')) ? eval($plugin_code) : false;

						$record->fields['p_view_name'] = 'p_view['.$record->fields['admin_permissions_key'].']';
						$record->fields['p_edit_name'] = 'p_edit['.$record->fields['admin_permissions_key'].']';
						$record->fields['p_delete_name'] = 'p_delete['.$record->fields['admin_permissions_key'].']';
						$record->fields['p_new_name'] = 'p_new['.$record->fields['admin_permissions_key'].']';

						if(!empty($id)){
							$check_data = $this->_checkParam($id, $record->fields['admin_permissions_key']);

							if($check_data['p_view'] == 1)
							$record->fields['p_view_param'] = 'checked';

							if($check_data['p_edit'] == 1)
							$record->fields['p_edit_param'] = 'checked';

							if($check_data['p_delete'] == 1)
							$record->fields['p_delete_param'] = 'checked';

							if($check_data['p_new'] == 1)
							$record->fields['p_new_param'] = 'checked';

							($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getPermissions_check')) ? eval($plugin_code) : false;
						}

						$data[] = $record->fields;
						$record->MoveNext();
					}$record->Close();
				}else{
					return false;
				}

			($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_getPermissions_bottom')) ? eval($plugin_code) : false;
			return $data;
	}

	function _checkParam($id, $key){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_checkParam_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

			$record = $db->Execute(
				"SELECT * FROM " . TABLE_ADMIN_PERMISSIONS_TO_ADMIN_GROUPS." where admin_group_id = ? and admin_permissions_key= ? ",
				array($id, $key)
			);
				if($record->RecordCount() > 0){
					while(!$record->EOF){
						($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_checkParam_data')) ? eval($plugin_code) : false;
						$data = $record->fields;
						$record->MoveNext();
					}$record->Close();
				}else{
					return false;
				}

			($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_checkParam_bottom')) ? eval($plugin_code) : false;
			return $data;
	}

	function _setAdminGroup($data){

		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_setAdminGroup_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($data['admin_group_id'])){
			$setType = 'update';
		}else{
			$setType = 'insert';
		}

		$group_array = array('admin_group_name' => $data['admin_group_name']);

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_setAdminGroup_group_array')) ? eval($plugin_code) : false;

		if(!empty($data['admin_group_id'])){
			$db->AutoExecute(TABLE_ADMIN_GROUPS, $group_array, 'UPDATE', "admin_group_id=".$data['admin_group_id']."");
			$id = $data['admin_group_id'];
		}else{
			$db->AutoExecute(TABLE_ADMIN_GROUPS, $group_array, 'INSERT');
			$id = $db->Insert_ID();
		}

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_setAdminGroup_top')) ? eval($plugin_code) : false;

		$this->_setRights($id, $data['p_view'], 'p_view');
		$this->_setRights($id, $data['p_edit'], 'p_edit');
		$this->_setRights($id, $data['p_delete'], 'p_delete');
		$this->_setRights($id, $data['p_new'], 'p_new');

	}

	function _setRights($id, $data, $type){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.admin_admin_permissions.php:_setRights_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;


		while (list ($key, $value) = each($data)) {

			$data_array = array();
			$data_array = array($type=>$value);

			$record = $db->Execute(
				"SELECT * FROM " . TABLE_ADMIN_PERMISSIONS_TO_ADMIN_GROUPS." where admin_group_id = ? and admin_permissions_key= ? ",
				array($id, $key)
			);

			echo 'key:'.$key.'<br>';
			echo 'val:'.$value.'<br>';
		}
	}
}