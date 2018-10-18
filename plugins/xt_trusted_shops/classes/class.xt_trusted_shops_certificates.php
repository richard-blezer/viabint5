<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops_certificates.php';

class xt_trusted_shops_certificates extends trusted_shops_certificates {

    private $_table_lang = '';
    private $_table_seo = '';
    private $_perm_array = array();
    private $_defaultSort;

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $header = array();
        $header[COL_TS_CERTS_ID] = array('type' => 'hidden', 'readonly'=>true);
        $header[COL_TS_CERTS_KEY] = array('type' => 'textfield');

        $header[COL_CHECKOUT_OPTIONS_CALC_BASE] = array(
            'type' => 'dropdown',
            'url'  => 'DropdownData.php?get=checkout-options-calc-base');

        $header[COL_TS_CERTS_SHOW_BADGE]     = array('type' => 'dropdown','url' => 'DropdownData.php?get=ts-show-badge');
        $header[COL_TS_CERTS_SHOW_BADGE_POS] = array('type' => 'textfield');
        $header[COL_TS_CERTS_SHOW_SEAL]      = array('type' => 'dropdown','url' => 'DropdownData.php?get=ts-show-seal', 'width'=>300);
        $header[COL_TS_CERTS_SHOW_VIDEO]     = array('type' => 'dropdown','url' => 'DropdownData.php?get=ts-show-video', 'width'=>300);
        $header[COL_TS_CERTS_SHOW_RATING]    = array('type' => 'status');
        $header[COL_TS_CERTS_SHOW_RICH_SNIPPETS]    = array('type' => 'status');

        $header[COL_TS_CERTS_STATE]          = array('type' => 'textfield', 'readonly'=>true);
        $header[COL_TS_CERTS_TYPE]           = array('type' => 'textfield', 'readonly'=>true);
        $header[VIEW_COL_TS_URL]             = array('type' => 'textfield', 'readonly'=>true);
        $header[VIEW_COL_TS_CERTS_LANG]      = array('type' => 'textfield', 'readonly'=>true);
        $header[COL_TS_CERTS_RATING_ENABLED] = array('type' => 'status', 'readonly'=>true);

        $header[COL_TS_CERTS_RATE_LATER_AFTER]  = array('type' => 'textfield');

        $this->_defaultSort = $header;

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['display_deleteBtn'] = true;
        $params['display_resetBtn'] = true;
        $params['display_editBtn'] = true;
        $params['display_newBtn'] = true;

        $params['include'] = array (
            COL_TS_CERTS_ID,
            COL_TS_CERTS_KEY,
        );
        if ($this->url_data['new']!='true')
        {
            $incl = array(
                COL_TS_CERTS_TYPE,
                COL_TS_CERTS_STATE,
                COL_TS_CERTS_RATING_ENABLED,
                VIEW_COL_TS_URL,
                VIEW_COL_TS_CERTS_LANG
            );
            $params['include'] = array_merge($params['include'], $incl);
        }
        if ($this->url_data['new']=='true' || $this->url_data['edit_id']) {
            $incl = array(
                COL_TS_CERTS_SHOW_BADGE,
                COL_TS_CERTS_SHOW_BADGE_POS,
                COL_TS_CERTS_SHOW_SEAL,
                COL_TS_CERTS_SHOW_VIDEO,
                COL_TS_CERTS_SHOW_RATING,
                COL_TS_CERTS_SHOW_RICH_SNIPPETS,
                COL_TS_CERTS_RATE_LATER_AFTER
            );
            $params['include'] = array_merge($params['include'], $incl);
        }
        if ($this->url_data['new']=='true' && empty($_REQUEST['gridHandle']))
        {
            // _NewButton_stm() in ExtFunctions erzeugt keinen gridHandle param
            // dieser fehlt dann in _ExtSubmitButtonHandler()
            $_REQUEST['gridHandle'] = 'xt_trusted_shops_certificatesgridForm';
        }

        /////////////////////////////////////////////////// row actions
        $rowActionsFunctions = array();
        // open certificate
        $js_reload_all = "
            var certKey = null;
            if (typeof record != 'undefined')
            {
                certKey = record.data.".COL_TS_CERTS_KEY.";
            }
            else if (typeof xt_trusted_shops_certificatesgridEditForm != 'undefined')
            {
                certKey = xt_trusted_shops_certificatesgridEditForm.getForm().getValues()['".COL_TS_CERTS_KEY."'];
            }
            if (certKey!=null)
            {
                window.open('".TS_URL_CERTIFICATE_SHOW."'+certKey,'_blank');
            }
              ";
        $rowActionsFunctions['ts_certificate_show'] = $js_reload_all;
        $rowActions[] = array('iconCls' => 'ts_certificate_show', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_TS_CERTIFICATE_SHOW);

        if ($this->url_data['new']!='true' && !$this->url_data['edit_id'])
        {
            // refresh certificate
            $js_reload_single = "
                var oldMsg = 'Loading...';
                var certKey = null;
                var waitEl = null;
                var store2reload = null;

                if (typeof record != 'undefined')
                {
                    console.log('record ', record, record.data.".COL_TS_CERTS_KEY.");
                    certKey = record.data.".COL_TS_CERTS_KEY.";
                    waitEl = Ext.getCmp('xt_trusted_shops_certificatesgridForm');
                    store2reload = xt_trusted_shops_certificatesds
                }

                if (certKey!=null)
                {
                    if (waitEl != null)
                    {
                        oldMsg = waitEl.loadMask.msg;
                        waitEl.loadMask.msg = 'Contacting Trusted Shops';
                        waitEl.loadMask.show();
                    }

                    var conn = new Ext.data.Connection();
                    conn.request({
                        url: 'adminHandler.php?plugin=xt_trusted_shops&load_section=xt_trusted_shops_certificates&pg=refreshCertStatus&".COL_TS_CERTS_KEY."='+ certKey,
                        method: 'GET',
                        params: {'".COL_TS_CERTS_KEY."':certKey},
                        waitMsg: 'Refreshing...',
                        error: function(responseObject) {
                            if (waitEl!=null)
                            {
                                waitEl.loadMask.msg = oldMsg;
                                waitEl.loadMask.hide();
                            }
                            Ext.Msg.alert('" . __define('TEXT_ALERT') . "', '" . __define('TEXT_NO_SUCCESS') . "');
                        },
                        success: function(responseObject) {
                            if (waitEl!=null)
                            {
                                waitEl.loadMask.msg = oldMsg;
                                waitEl.loadMask.hide();
                            }
                            store2reload.reload();
                        }
                    });
                }
                  ";
            $rowActionsFunctions['ts_certificate_refresh'] = $js_reload_single;
            $rowActions[] = array('iconCls' => 'ts_certificate_refresh', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_TS_REFRESH);
        }

        if (count($rowActionsFunctions) > 0 /* &&  $this->url_data['new']!='true' && !$this->url_data['edit_id']*/) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

        /////////////////////////////////////////////////// user buttons
        $js_ts_refresh = "
            var total = xt_trusted_shops_certificatesds.getTotalCount();
            if (total<1) return;

            var grid = Ext.getCmp('xt_trusted_shops_certificatesgridForm');
            var oldMsg = grid.loadMask.msg;
            grid.loadMask.msg = 'Contacting Trusted Shops';
            grid.loadMask.show();

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php?plugin=xt_trusted_shops&load_section=xt_trusted_shops_certificates&pg=refreshCertStatus',
                method: 'GET',
                params: {},
                waitMsg: 'Refreshing...',
                error: function(responseObject) {
                    grid.loadMask.msg = oldMsg;
                    grid.loadMask.hide();
                    Ext.Msg.alert('" . __define('TEXT_ALERT') . "', '" . __define('TEXT_NO_SUCCESS') . "');
                },
                success: function(responseObject) {
                    grid.loadMask.msg = oldMsg;
                    grid.loadMask.hide();
                    xt_trusted_shops_certificatesds.reload();
                }
            });
        ";

        $UserButtons = array();
        $UserButtons['ts_refresh'] = array('text'=>'TEXT_TS_REFRESH', 'style'=>'ts_refresh', 'icon'=>'key_go.png', 'acl'=>'', 'stm' => $js_ts_refresh);
        $params['display_ts_refreshBtn'] = true;


        $params['UserButtons']      = $UserButtons;

        return $params;
    }

    function _get($ID = 0)
    {
        if ($this->position != 'admin') return false;

        $sql_where = "";
        if ($this->url_data['get_data'])
        {
            $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $this->_perm_array);
            $data = $table_data->getData();
            foreach($data as $k => $cert)
            {
                $data[$k][VIEW_COL_TS_CERTS_LANG] = $data[$k][COL_TS_CERTS_LANG];
                $data[$k][VIEW_COL_TS_URL] = $data[$k][COL_TS_CERTS_URL];
                if ($data[$k][COL_TS_CERTS_STATUS] != TS_SUCCESS)
                {
                    $data[$k][COL_TS_CERTS_STATE] = "<span class='ts-error'>".$data[$k][COL_TS_CERTS_STATUS].": ".TEXT_TS_INVALID_TS_ID."</span>";
                }
            }
        }
        else if($ID && $ID!='new' && $this->url_data['new']!='true')
        {
            $sql_where = COL_TS_CERTS_ID."=$ID";
            $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $this->_perm_array);
            $data = $table_data->getData();
            foreach($data as $k => $cert)
            {
                $data[$k][VIEW_COL_TS_CERTS_LANG] = $data[$k][COL_TS_CERTS_LANG];
                $data[$k][VIEW_COL_TS_URL] = $data[$k][COL_TS_CERTS_URL];
                if ($data[$k][COL_TS_CERTS_STATUS] != TS_SUCCESS)
                {
                    $data[$k][COL_TS_CERTS_STATE] = $data[$k][COL_TS_CERTS_STATUS].": ".TEXT_TS_INVALID_TS_ID;
                }
            }
            $sorted = array(array());
            foreach(array_keys($this->_defaultSort) as $k)
            {
                $sorted[0][$k] = $data[0][$k];
            }
            $data = $sorted;
        }
        else
        {
            $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);
            $data = $table_data->getHeader();

            $sorted = array(array());
            foreach(array_keys($this->_defaultSort) as $k)
            {
                $sorted[0][$k] = $data[0][$k];
            }
            $data = $sorted;
        }
        if($ID=='new')
        {
            $data[0][COL_TS_CERTS_SHOW_BADGE] = TS_BADGE_SIZE_DEFAULT;
            $data[0][COL_TS_CERTS_SHOW_BADGE_POS] = 250;
            $data[0][COL_TS_CERTS_RATE_LATER_AFTER] = TS_DEF_RATE_LATER_AFTER;
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


    function _set($data, $set_type = 'edit')
    {
        if (empty($data[COL_TS_CERTS_KEY]))
        {
            return false;
        }
        if (strlen($data[COL_TS_CERTS_KEY])!=TS_VLD_CERT_KEY_LENGTH)
        {
            //return false;
        }
        $dbs = new adminDB_DataSave($this->_table, $data);
        $dbs->saveDataSet();

        $this->refreshCertStatus(array(COL_TS_CERTS_KEY => $data[COL_TS_CERTS_KEY]));

        return true;
    }

    function _unset($id = 0)
    {
        global $db;
        $sql = "DELETE FROM ".$this->_table." WHERE `".COL_TS_CERTS_ID."`=$id";
        $db->Execute($sql);
    }

} 