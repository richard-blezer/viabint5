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


class plg_config
{

    protected $_table = null;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'id';
    protected $twitter = null;

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $params = array();
        $header = array();

        $this->plugin_config = $this->getPluginConfig($this->url_data['edit_id']);
        if (is_array($this->plugin_config)) {
            foreach ($this->plugin_config as $item) {
                $header[$item['config_key'] . '_CFG'] = array('type' => $item['type'], 'url' => 'DropdownData.php?get=' . $item['url']);
                if (defined($item['config_key'] . '_TITLE')) {
                    define('TEXT_' . trim($item['config_key']) . '_CFG', constant($item['config_key'] . '_TITLE'));
                }
            }
        }
        $header[$this->_master_key] = array('type' => 'hidden');

        $params['display_deleteBtn'] = false;
        $params['display_editBtn'] = true;

        $params['display_searchPanel'] = false;

        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;

        $rowActions = array();
        $rowActionsFunctions = array();

        if (count($rowActions) > 0) $params['rowActions'] = $rowActions;
        if (count($rowActionsFunctions) > 0) $params['rowActionsFunctions'] = $rowActionsFunctions;

        return $params;
    }

    function _get($ID = 0)
    {
        global $xtPlugin, $db, $language;

        $erg = array();
        if ($this->position != 'admin') return false;
        if ($ID === 'new') {
            $ID = 999;
        }
        if ($this->url_data['get_data']) {
            // alle holen
            if (is_array($this->plugin_config)) {
                foreach ($this->plugin_config as $item) {
                    $erg[$item['config_key'] . '_CFG'] = $item['config_value'];
                }
            }
        } elseif ($ID) {
            // einen bestimmten holen
            if (is_array($this->plugin_config)) {
                foreach ($this->plugin_config as $item) {
                    $erg[$item['config_key'] . '_CFG'] = $item['config_value'];
                }
            }
//            }
        } else {
            // nur die Header holen
            if (is_array($this->plugin_config)) {
                foreach ($this->plugin_config as $item) {
                    $erg[$item['config_key'] . '_CFG'] = $item['config_value'];
                }
            }
        }
        $arr_data = array($erg);
        $obj = new stdClass();
        $obj->totalCount = count($arr_data);
        $obj->data = $arr_data;

        return $obj;
    }

    function _set($data, $set_type = 'edit')
    {
        global $db, $language, $filter;
        unset($data['id']);

        if ($this->position != 'admin') return false;
        foreach ($data as $key => $value) {
            // _cfg wieder wegschneiden
            $key = substr($key, 0, strlen($key) - 4);
            $sql = 'update ' . TABLE_PLUGIN_CONFIGURATION . ' set config_value = ? where plugin_id = ? and config_key=? limit 1;';
            $db->Execute($sql, array($value, $this->plugin_id, $key));
        }
        return true;
    }


    function _unset($id = 0)
    {
        global $db;
        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        if (!is_object($this->twitter->twitter)) return false;
        return false;
    }

    function getPluginConfig($plugin_code)
    {
        global $db;
        $erg = false;
        $sql = 'select plugin_id from ' . TABLE_PLUGIN_PRODUCTS . ' where code =' . $db->Quote($plugin_code) . ';';
        $arr = $db->getRow($sql);
        if ($arr['plugin_id'] > 0) {
            $this->plugin_id = $arr['plugin_id'];
            $sql = 'select * from ' . TABLE_PLUGIN_CONFIGURATION . ' where plugin_id =' . (int)$this->plugin_id . ';';
            $erg = $db->getAll($sql);
        }
        return $erg;
    }
}