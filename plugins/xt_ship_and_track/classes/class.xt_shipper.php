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

class xt_shipper {

    private $_master_key = COL_SHIPPER_ID_PK;

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $header = array();
        $header[COL_SHIPPER_ID_PK] = array('type' => 'hidden', 'readonly'=>true);
        $header[COL_SHIPPER_CODE] = array('type' => 'textfield');
        $header[COL_SHIPPER_NAME] = array('type' => 'textfield');
        $header[COL_SHIPPER_TRACKING_URL] = array('type' => 'textfield');
        $header[COL_SHIPPER_API_ENABLED] = array('type' => 'status');
        $header[COL_SHIPPER_ENABLED] = array('type' => 'status');

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['display_deleteBtn'] = false;
        $params['display_resetBtn'] = true;
        $params['display_editBtn'] = true;
        $params['display_newBtn'] = true;
        $params['display_searchPanel']  = false;

        return $params;
    }

    function _get($ID = 0)
    {
        if ($this->position != 'admin') return false;

        $where = '';

        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,50";
        }

        $table_data = new adminDB_DataRead(TABLE_SHIPPER, '', '', $this->_master_key, $where , '', '', '',  'ORDER BY '.COL_SHIPPER_NAME. ' ');
        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        }
        elseif($ID==='new'){
            $defaultOrder = array(
                //COL_SHIPPER_ID_PK,
                COL_SHIPPER_CODE,
                COL_SHIPPER_NAME,
                COL_SHIPPER_TRACKING_URL,
                //COL_SHIPPER_API_ENABLED,
                COL_SHIPPER_ENABLED
            );

            $orderedData = array();
            foreach ($defaultOrder as $key) {
                $orderedData[$key] = '';
            }
            $data = array($orderedData);
        }
        elseif($ID) {
            $data = $table_data->getData($ID);
            $defaultOrder = array(
                COL_SHIPPER_ID_PK,
                COL_SHIPPER_CODE,
                COL_SHIPPER_NAME,
                COL_SHIPPER_TRACKING_URL,
                //COL_SHIPPER_API_ENABLED,
                COL_SHIPPER_ENABLED
            );
            $orderedData = array();
            foreach ($defaultOrder as $key) {
                $orderedData[$key] = $data[0][$key];
            }
            $data = array($orderedData);

        } else {
            $data = $table_data->getHeader();
            $defaultOrder = array(
                COL_SHIPPER_ID_PK,
                COL_SHIPPER_CODE,
                COL_SHIPPER_NAME,
                COL_SHIPPER_TRACKING_URL,
                //COL_SHIPPER_API_ENABLED,
                COL_SHIPPER_ENABLED
            );
            $orderedData = array();
            foreach ($defaultOrder as $key) {
                $orderedData[$key] = $data[0][$key];
            }
            $data = array($orderedData);
        }

        $obj = new stdClass;
        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);
        $obj->totalCount = $count_data;
        $obj->data = $data;
        return $obj;
    }

    function _set($data, $set_type = 'edit')
    {
        $o = new adminDB_DataSave(TABLE_SHIPPER, $data, false, __CLASS__);
        try {
            $result = $o->saveDataSet();

            $shipperId = $result->new_id;
            $langs = array('de');
            $this->_installStatusCodes($shipperId, $data['shipper_code'], $langs);
        }
        catch(Exception $e){
            return false;
        }

        return $result;
    }

    function _unset($id = 0)
    {
        global $db;
        return false;
    }

    function _installStatusCodes($shipperId, $shipperCode, $langs) {
        global $db;

        $dir = _SRV_WEBROOT.'plugins/xt_ship_and_track/installer/status_codes/';

        foreach($langs as $lang)
        {
            if (file_exists($dir.$lang.'/status_codes_'.$shipperCode.'.csv'))
            {
                $csv =  _getFileContent($dir.$lang.'/status_codes_'.$shipperCode.'.csv');

                foreach(preg_split("/((\r?\n)|(\r\n?))/", $csv) as $line)
                {

                    $data = explode(';', $line);
                    $insertData = array(
                        COL_TRACKING_STATUS_CODE => $data[0],
                        COL_TRACKING_SHIPPER_ID => $shipperId
                    );
                    try {
                        $db->AutoExecute(TABLE_TRACKING_STATUS ,$insertData);
                    } catch (exception $e) {
                        return $e->msg;
                    }

                    $key = 'TEXT_'.strtoupper($shipperCode).'_'.strtoupper(str_replace('-','_',$data[0])).'_SHORT';

                    $insertData = array(
                        'translated' => 0,
                        'language_code' => $lang,
                        'language_key' => $key,
                        'language_value' => $data[1],
                        'class' => 'both',
                        'plugin_key' => 'xt_ship_and_track'
                    );
                    try {
                        $db->AutoExecute(TABLE_LANGUAGE_CONTENT ,$insertData);
                    } catch (exception $e) {
                        return $e->msg;
                    }

                    $key = 'TEXT_'.strtoupper($shipperCode).'_'.strtoupper(str_replace('-','_',$data[0])).'_LONG';

                    $insertData = array(
                        'translated' => 0,
                        'language_code' => $lang,
                        'language_key' => $key,
                        'language_value' => $data[2],
                        'class' => 'both',
                        'plugin_key' => 'xt_ship_and_track'
                    );
                    try {
                        $db->AutoExecute(TABLE_LANGUAGE_CONTENT ,$insertData);
                    } catch (exception $e) {
                        return $e->msg;
                    }
                }

            }

            $insertData = array(
                COL_TRACKING_STATUS_CODE => 0,
                COL_TRACKING_SHIPPER_ID => $shipperId
            );
            try {
                $db->AutoExecute(TABLE_TRACKING_STATUS ,$insertData);
            } catch (exception $e) {
                return $e->msg;
            }

            $key = 'TEXT_'.strtoupper($shipperCode).'_0_SHORT';
            switch($lang)
            {
                case 'de':
                    $value = 'manuell hinzugefügt';
                    break;
                case 'en':
                    $value = 'added manually';
                    break;
                default:
                    $value = 'added manually';
            }
            $insertData = array(
                'translated' => 0,
                'language_code' => $lang,
                'language_key' => $key,
                'language_value' => $value,
                'class' => 'both',
                'plugin_key' => 'xt_ship_and_track'
            );
            try {
                $db->AutoExecute(TABLE_LANGUAGE_CONTENT ,$insertData);
            } catch (exception $e) {
                return $e->msg;
            }

            $key = 'TEXT_'.strtoupper($shipperCode).'_0_LONG';

            $insertData = array(
                'translated' => 0,
                'language_code' => $lang,
                'language_key' => $key,
                'language_value' => $value,
                'class' => 'both',
                'plugin_key' => 'xt_ship_and_track'
            );
            try {
                $db->AutoExecute(TABLE_LANGUAGE_CONTENT ,$insertData);
            } catch (exception $e) {
                return $e->msg;
            }
        }
    }
}