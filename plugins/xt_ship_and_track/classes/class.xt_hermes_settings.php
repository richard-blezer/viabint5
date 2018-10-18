<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/constants.php';
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.xt_tracking.php';
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/api/Hermes.php';

class xt_hermes_settings {

    private $_master_key = COL_HERMES_SETTINGS_PK;

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $header = array();
        $header[COL_HERMES_SETTINGS_HINT_LABEL] = array('type' => 'textarea', 'readonly' => true, 'width' => 310);
        $header[COL_HERMES_USER] = array('type' => 'textfield');
        $header[COL_HERMES_PWD] = array('type' => 'textfield');
        $header[COL_HERMES_SANDBOX] = array('type' => 'status');


        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['display_deleteBtn'] = false;
        $params['display_resetBtn'] = false;
        $params['display_editBtn'] = true;
        $params['display_newBtn'] = false;
        $params['display_searchPanel']  = false;

        $params['display_checkCol']  = false;

        return $params;
    }

    function _get($ID = 0)
    {
        if ($this->position != 'admin') return false;


        if($ID) {
            $data = array();
            $data[0] = array(
                COL_HERMES_SETTINGS_HINT_LABEL =>  constant('TEXT_HERMES_SETTINGS_HINT_TEXT'),
                COL_HERMES_SANDBOX => XT_HERMES_SANDBOX,
                COL_HERMES_USER => XT_HERMES_USER,
                COL_HERMES_PWD => XT_HERMES_PWD
            );
            $count_data = 1;

        } else {
            $data = array();
            $data[] = array(
                COL_HERMES_SETTINGS_HINT => '',
                COL_HERMES_SANDBOX => '',
                COL_HERMES_USER => '',
                COL_HERMES_PWD => ''
            );
            $count_data = 0;
        }

        $obj = new stdClass;
        $obj->totalCount = $count_data;
        $obj->data = $data;
        return $obj;
    }


    function _set($data, $set_type = 'edit')
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        global $db;

        $exist = $db->GetOne('SELECT count(*) FROM '.TABLE_HERMES_SETTINGS);

       if (!$exist)
       {
           $sql = "INSERT INTO ". TABLE_HERMES_SETTINGS .
               " (".COL_HERMES_USER.", ".COL_HERMES_PWD.", ".COL_HERMES_SANDBOX.") ".
               " VALUES('".$data[COL_HERMES_USER]."', '".$data[COL_HERMES_PWD]."', '".$data[COL_HERMES_SANDBOX]."')";
       }
       else{
           $sql = "UPDATE ". TABLE_HERMES_SETTINGS .
               " SET "
               .COL_HERMES_SANDBOX. "= '".$data[COL_HERMES_SANDBOX]."', "
               .COL_HERMES_USER."= '".$data[COL_HERMES_USER]."', "
               .COL_HERMES_PWD." = '".$data[COL_HERMES_PWD]."' WHERE 1";
       }

        $db->Execute($sql);

        $r->success = true;
        $r->msg = __define('TEXT_SUCCESS');
        $r->errorMsg = __define('TEXT_SUCCESS');

        return $r;
    }

    function _unset($id = 0)
    {
        return false;
    }
}