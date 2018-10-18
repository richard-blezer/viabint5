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
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.xt_ship_and_track.php';

class xt_hermes_collect {

    private $_master_key = 'id';

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $header = array();
        $header['id'] = array( 'readonly'=>true);
        $header['collect_date'] = array('type'=>'date', 'dateFormat'=>"m/d/Y");
        $header['value_ids'] = array('type' => 'hidden', 'readonly'=>true);

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['display_deleteBtn'] = true;
        $params['display_resetBtn'] = true;
        $params['display_editBtn'] = false;
        $params['display_newBtn'] = false;
        $params['display_searchPanel']  = false;

        $this->params['dateFormat'] = "m/d/Y";

        $params['include'] = array('collect_date','collect_request_no', 'id');
        if ($this->url_data['edit_id']=='new')
        {
            $params['include'][] = 'value_ids';
            $params['display_editBtn'] = false;
            $params['display_resetBtn'] = false;
        }

        $js = "
            var cmpId = xt_hermes_collectbd.id;
            var form = Ext.getCmp(cmpId).getForm();
            var value_ids = form.findField('value_ids').getValue();
            var collect_date = form.findField('collect_date').getValue();

            if (collect_date=='') return;

            var lm = new Ext.LoadMask(Ext.getBody(),{msg:'".__define('TEXT_REQUESTING_COLLECT')."'});
            lm.show();

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             '_set',
                    load_section:   'xt_hermes_collect',
                    plugin:         'xt_ship_and_track',
                    value_ids:      value_ids,
                    collect_date:   String(collect_date),
                    dummy:          'dummy'
                },
                success: function(responseObject)
                {
                    var r = Ext.decode(responseObject.responseText);
                    //console.log('success');
                    //console.log(r);
                    lm.hide();
                    //contentTabs.getActiveTab().getUpdater().refresh();
                    if (!r.success)
                    {
                        Ext.MessageBox.alert('".__define('TEXT_ALERT')."', r.errorMsg);
                    }
                    else
                    {
                        Ext.MessageBox.alert('".__define('TEXT_ALERT')."',r.msg);
                    }
                },
                failure: function(responseObject)
                {
                    lm.hide();
                    var r = Ext.decode(responseObject.responseText);
                    //console.log('fail');
                    //console.log(r);
                    var title = responseObject.statusText ? '".__define('TEXT_ALERT')."'+responseObject.status : '".__define('TEXT_ALERT')."';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    //console.log(responseObject)
                }
            });
        \n";

        if ($this->url_data['edit_id']=='new')
        {
            $rowActionsFunctions['REQUEST_COLLECT'] = $js;
            $rowActions[] = array('iconCls' => 'HERMES_COLLECT', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_REQUEST_COLLECT);
        }

        if (count($rowActionsFunctions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

        if (count($rowActions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

        return $params;
    }

    function _get($ID = 0)
    {
        if ($this->position != 'admin') return false;

        $where = '';
        $table_data = new adminDB_DataRead(TABLE_HERMES_COLLECT, '', '', $this->_master_key, $where , '', '', '',  'ORDER BY '.COL_HERMES_COLLECT_DATE. ' ');
        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
            foreach($data as $k=>$v)
            {
                //$date = new DateTime($data[$k]['collect_date']);
                //$data[$k]['collect_date'] =  date($data[$k]['collect_date']);
                $a = date('Y-m-d', strtotime($data[$k][COL_HERMES_COLLECT_DATE]));
                $data[$k]['collect'] = $a;
            }
        }
        elseif($ID==='new')
        {
            //error_log('open window => '. print_r($this->url_data,true));
            $data = array();
            $data[] = array(
              'collect_date' => new DateTime(),
              'value_ids' => $this->url_data['value_ids']
            );
        }
        elseif($ID) {
            $data = $table_data->getData($ID);
            $defaultOrder = array(
                'id',
                COL_HERMES_COLLECT_NO,
                COL_HERMES_COLLECT_DATE
            );
            $orderedData = array();
            foreach ($defaultOrder as $key) {
                $orderedData[$key] = $data[0][$key];
            }
            $data = array($orderedData);

        } else {
            $data = $table_data->getHeader();
            unset($data[0]['collect_date']);
            $data[0][] = 'collect';
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
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        //error_log(print_r($this->url_data,true));

        //return json_encode($r);

        $hermesIds = explode(',', $this->url_data['value_ids']);
        if (sizeof($hermesIds)==0)
        {
            $r->msg = $r->errorMsg = 'No id\'s found';
            return json_encode($r);
        }
        $date = new DateTime($this->url_data['collect_date']);
        if (is_null($date))
        {
            $r->msg = $r->errorMsg = 'No date found';
            return json_encode($r);
        }

        $xtHermes = new xt_ship_and_track();
        $xtHermes->setPosition('admin');
        $xs = $s = $m = $l = $xl = $xl_bulk = 0;
        foreach($hermesIds as $hermesId)
        {
            if(!$hermesId) continue;
            $h = $xtHermes->_get($hermesId)->data[0];
            switch ($h[COL_HERMES_PARCEL_CLASS])
            {
                case 'XS':
                    $xs++;
                    break;
                case 'S':
                    $s++;
                    break;
                case 'M':
                    $m++;
                    break;
                case 'L':
                    $l++;
                    break;
                case 'XL':
                    if (is_set($h['hermes_bulk_good']) && $h['hermes_bulk_good'])
                    {
                        $xl_bulk++;
                    }
                    else{
                        $xl++;
                    }
                    break;
                default:
                    $m++; // fallback für durchschnittspreis kunden
            }
        }

        $xsdDate = $date->format('Y-m-d').'T'.$date->format('H:i:s');
        $collectData = array(
            'date' => $xsdDate,
            'xs' => $xs,
            's'  => $s,
            'm'  => $m,
            'l'  => $l,
            'xl' => $xl,
            'xl_bulk' => $xl_bulk
        );


        $hResult = $xtHermes->requestCollect($collectData);
        if (!$hResult->requestNo)
        {
            return json_encode($hResult);
        }

        $saveData = array(
            COL_HERMES_COLLECT_DATE => $date->format('Y-m-d'),
            COL_HERMES_COLLECT_NO => $hResult->requestNo
        );
        $ds = new adminDB_DataSave(TABLE_HERMES_COLLECT, $saveData);
        $ds->saveDataSet();

        foreach($hermesIds as $hermesId)
        {
            if(!$hermesId) continue;
            $h = $xtHermes->_get($hermesId, false)->data[0];
            $h['collect_date'] = $date->format('Y-m-d');
            $ds = new adminDB_DataSave(TABLE_HERMES_ORDER, $h);
            $ds->saveDataSet();
        }

        $r->success = true;
        $r->msg = __define('TEXT_SUCCESS');
        $r->errorMsg = __define('TEXT_SUCCESS');

        return json_encode($r);
    }

    function _unset($id = 0)
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        $collect = $this->_get($id)->data[0];

        $date = new DateTime($collect['collect_date']);
        $date = $date->format('Y-m-d').'T'.$date->format('H:i:s');

        $xtHermes = new xt_ship_and_track();
        $hResult = $xtHermes->deleteCollect($date);
        if ((!$hResult->canceled || empty($hResult->canceled)) && $hResult->code != '312324') // nicht vorhanden, also lokal löschen
        {
            return $hResult;
        }

        global $db;

        $db->Execute("DELETE FROM ".TABLE_HERMES_COLLECT." where `id` = ?", array($id));
        $db->Execute("UPDATE ".TABLE_HERMES_ORDER." SET `collect_date`=NULL where `collect_date` = ?", array($collect['collect_date']));


        $r->success = true;
        $r->msg = __define('TEXT_SUCCESS');
        $r->errorMsg = __define('TEXT_SUCCESS');

        return $r;
    }
}