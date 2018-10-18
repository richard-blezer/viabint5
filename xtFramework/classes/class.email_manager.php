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

class email_manager
{

    protected $_table = TABLE_MAIL_TEMPLATES;
    protected $_table_lang = TABLE_MAIL_TEMPLATES_CONTENT;
    protected $_table_seo = null;
    protected $_master_key = 'tpl_id';

    function email_manager ()
    {
        $this->getPermission();
    }

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function getPermission ()
    {
        global $store_handler, $customers_status, $xtPlugin;

        $this->perm_array = array(
            'shop_perm' => array(
                'type' => 'shop',
                'key' => $this->_master_key,
                'value_type' => 'email',
                'pref' => 'e'
            ),
            'group_perm' => array('type' => 'group_permission',
                'key' => $this->_master_key,
                'value_type' => 'email',
                'pref' => 'e'
            )
        );

        ($plugin_code = $xtPlugin->PluginCode(__CLASS__ . ':getPermission')) ? eval($plugin_code) : false;

        $this->permission = new item_permission($this->perm_array);

        return $this->perm_array;
    }

    function _getParams ()
    {
        global $language, $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_getParams_top')) ? eval($plugin_code) : false;
        $params = array();

        $header['tpl_id'] = array('type' => 'hidden');

        foreach ($language->_getLanguageList() as $key => $val) {
            $header['mail_body_html_' . $val['code']] = array('type' => 'textarea', 'height' => '400', 'width' => '100%');
            $header['mail_body_txt_' . $val['code']] = array('type' => 'textarea', 'height' => '400', 'width' => '100%');
            $header['mail_subject_' . $val['code']] = array('width' => '450');
        }

        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_getParams_header')) ? eval($plugin_code) : false;

        $rowActions[] = array('iconCls' => 'email_media', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_EMAIL_MEDIA);
        if ($this->url_data['edit_id'])
            $js = "var edit_id = " . $this->url_data['edit_id'] . "; var edit_name = '';\n";
        else
            $js = "var edit_id = record.id; var edit_name=record.get('tpl_id');\n";
        $extF = new ExtFunctions();
        $js .= $extF->_RemoteWindow("TEXT_EMAIL_MEDIA", "TEXT_EMAIL_MEDIA", "adminHandler.php?load_section=email_to_media&pg=getTreePanel&tpl_id='+edit_id+'", '', array(), 800, 600) . ' new_window.show();';

        $rowActionsFunctions['email_media'] = $js;

        $params['rowActions'] = $rowActions;
        $params['rowActionsFunctions'] = $rowActionsFunctions;

        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;

        $params['display_copyBtn'] = true;
        $params['display_checkCol'] = true;


        if ($this->url_data['pg'] == 'overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true) {
            $params['include'] = array('tpl_id', 'tpl_type', 'mail_subject_' . $language->code);
        }

        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_getParams_params')) ? eval($plugin_code) : false;
        return $params;
    }

    function _get ($ID = 0)
    {
        global $xtPlugin, $db, $language;
		$obj = new stdClass;
        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        $where = '';
        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_get_top')) ? eval($plugin_code) : false;
        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,25";
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit, $this->perm_array);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID) {
            $data = $table_data->getData($ID);
            $data[0]['group_permission_info']=_getPermissionInfo();
            $data[0]['shop_permission_info']=_getPermissionInfo();
        } else {
            $data = $table_data->getHeader();
        }
        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_get_bottom')) ? eval($plugin_code) : false;

        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $filter;

        $obj = new stdClass;

        foreach ($data as $key => $val) {

            if ($val == 'on')
                $val = 1;
            $data[$key] = $val;
        }

        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        if ($set_type == 'new') { // edit existing
            $obj->new_id = $objC->new_id;
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
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

    function _unset ($id = 0)
    {
        global $db;
        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id = (int)$id;
        if (!is_int($id)) return false;

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_deleteData($id);

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?", array($id));
        if ($this->_table_lang !== null)
            $db->Execute("DELETE FROM " . $this->_table_lang . " WHERE " . $this->_master_key . " = ?", array($id));
    }

    function _copy ($ID)
    {
        global $xtPlugin, $db, $language, $filter, $seo, $customers_status;
        if ($this->position != 'admin') return false;

        $ID = (int)$ID;
        if (!is_int($ID)) return false;

        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_copy_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        $obj = new stdClass;

        // Email Data:
        $e_table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array, 'false');
        $e_data = $e_table_data->getData($ID);
        $e_data = $e_data[0];

        $old_email = $e_data[$this->_master_key];

        unset($e_data[$this->_master_key]);

        $oE = new adminDB_DataSave($this->_table, $e_data);
        $objE = $oE->saveDataSet();

        $obj->new_id = $objE->new_id;
        $e_data[$this->_master_key] = $objE->new_id;

        $oED = new adminDB_DataSave($this->_table_lang, $e_data, true);
        $objED = $oED->saveDataSet();

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($e_data, $e_data[$this->_master_key]);

        ($plugin_code = $xtPlugin->PluginCode('class.email_manager.php:_copy_bottom')) ? eval($plugin_code) : false;

        $obj = new stdClass;
        $obj->success = true;
        return $obj;
    }
}