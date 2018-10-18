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
 # @version $Id: class.xt_coupons_token.php 6060 2013-03-14 13:10:33Z mario $
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


class xt_feedbackplus_campaigns {

    protected $_table = TABLE_FEEDBACKPLUS_CAMPAIGNS;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'feedbackplus_campaign_id';

    function __construct() {
        $this->getPermission();
    }

    function setPosition ($position) {
        $this->position = $position;
    }

    function _getParams() {
        $params = array();

        $header['feedbackplus_campaign_id'] = array('type' => 'hidden');
        $header['feedbackplus_campaign_creation_date'] = array('type' => 'hidden');

        $header['feedbackplus_mail_class_reminder'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=feedback_mail_classes&plugin_code=xt_feedbackplus');
        $header['feedbackplus_mail_class_success'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=feedback_mail_classes&plugin_code=xt_feedbackplus');

        $header['feedbackplus_coupons_id'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=coupon&plugin_code=xt_coupons');
        $header['feedbackplus_campaign_testing'] = array('type' => 'status');


        $rowActions[] = array('iconCls' => 'feedbackplus_campaigns_categories', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_FEEDBACKPLUS_CAMPAIGNS_CATEGORIES);
        $js = '';
        if ($this->url_data['feedbackplus_campaign_id']) {
            $feedbackplus_campaign_id = $this->url_data['feedbackplus_campaign_id'];
            $js .= "var feedbackplus_campaign_id = " . $feedbackplus_campaign_id . ";";
        } elseif ($this->url_data['edit_id']) {
            $feedbackplus_campaign_id = $this->url_data['edit_id'];
            $js .= "var feedbackplus_campaign_id = " . $feedbackplus_campaign_id . ";";
        } else {
            $js = "var feedbackplus_campaign_id = record.data['feedbackplus_campaign_id'];";
        }
        $extF = new ExtFunctions();
        $js .= $extF->_RemoteWindow("TEXT_FEEDBACKPLUS_CAMPAIGNS_CATEGORIES", "TEXT_FEEDBACKPLUS_CAMPAIGNS_CATEGORIES", "adminHandler.php?plugin=xt_feedbackplus&load_section=xt_feedbackplus_campaigns_categories&pg=getTreePanel&feedbackplus_campaign_id='+feedbackplus_campaign_id+'", '', array(), 800, 600) . ' new_window.show();';
        $rowActionsFunctions['feedbackplus_campaigns_categories'] = $js;

        $params['rowActions']             = $rowActions;
        $params['rowActionsFunctions']    = $rowActionsFunctions;


        $params['header']         = $header;
        $params['display_searchPanel']  = false;

        $params['display_checkCol']  = false;
        $params['master_key']     = $this->_master_key;

        $params['display_options_addBtn'] = true;

        $params['display_statusTrueBtn']  = true;
        $params['display_statusFalseBtn']  = true;

        return $params;
    }

    function getPermission(){
        global $store_handler, $customers_status, $xtPlugin;

        $this->perm_array = array(
            'shop_perm' => array(
                'type'=>'shop',
                'table'=>TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS,
                'key'=>$this->_master_key,
                'simple_permissions' => 'true',
                'simple_permissions_key' => 'permission_id',
                'pref'=>'c'
            ),

            'group_perm' => array('type'=>'group_permission',
                'table'=>TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS,
                'key'=>$this->_master_key,
                'simple_permissions' => 'true',
                'simple_permissions_key' => 'permission_id',
                'pref'=>'c'
            )
        );

        ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':getPermission')) ? eval($plugin_code) : false;

        $this->permission = new item_permission($this->perm_array);

        return $this->perm_array;

    }

    function _get($ID = 0) {
        global $xtPlugin, $db, $language;

        $obj = new stdClass;

        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        $permissions = $this->perm_array;

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $permissions);

        if ($this->url_data['get_data']){
            $data = $table_data->getData();
        }elseif($ID){
            $data = $table_data->getData($ID);
            $data[0]['group_permission_info']=_getPermissionInfo();
            $data[0]['shop_permission_info']=_getPermissionInfo();
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

        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        if ($set_type=='new') {	// edit existing
            $obj->new_id = $obj->new_id;
            $data = array_merge($data, array($this->_master_key=>$obj->new_id));
        }

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($data, $data[$this->_master_key]);

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

    function _setStatus($id, $status) {
        global $db,$xtPlugin;
        $id = (int)$id;
        if (!is_int($id)) return false;

        $db->Execute("update " . $this->_table . " set coupon_token_status = ".$status." where ".$this->_master_key." = ?", array($id));
    }
}