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

defined('_VALID_CALL') OR die('Direct Access is not allowed.');


class xt_protected_shops {


    var $api_url = 'https://www.protectedshops.de/api/';

    protected $_table = TABLE_PROTECTED_SHOPS;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'id';
    var $type = 'admin';
    protected $update_periode = 6;


    function setPosition ($position) {
        $this->position = $position;
    }

    public function loadDocumentTypes() {
        global $db,$info;

        $sql = "SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE config_key='XT_PROTECTED_SHOPS_SHOP_ID'";
        $rs=$db->Execute($sql);
        while (!$rs->EOF) {
            if (strlen($rs->fields['config_value'])>5) {
                $this->GetDocumentInfo($rs->fields['config_value'],$rs->fields['shop_id']);
                if ($this->type=='admin') $info->_addInfo("Dokumentenliste aktualisiert, Shop ID".$rs->fields['config_value'], 'success');
            }

            $rs->MoveNext();
        }
    }

    private function GetDocumentInfo($shopId,$shop_id) {
        global $db;

        $request = array();
        $request['Request']='GetDocumentInfo';
        $request['ShopId']=$shopId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);

        if( ! $result = curl_exec($ch))
        {
            $this->protLog('curl error: '.curl_error($ch),$shop_id,'error');
            return;
        }

        curl_close($ch);

        $arr = XML_unserialize($result);
        if (is_array($arr)) {
            if (isset($arr['Response']['DocumentDate'])) {
                foreach ($arr['Response']['DocumentDate'] as $id => $key) {
                    $insert_array=array();
                    $insert_array['document']=$id;
                    $insert_array['last_change']=$key;
                    $insert_array['content_id']=0;
                    $insert_array['store_id']=$shop_id;
                    $rs = $db->Execute("SELECT * FROM ".$this->_table." WHERE store_id=? and document =? LIMIT 1",array($shop_id,$id));
                    if ($rs->RecordCount()==1) {
                        $db->AutoExecute($this->_table,$insert_array,'UPDATE', "store_id=".$shop_id." and document ='".$id."'");
                    } else {
                        $db->AutoExecute($this->_table,$insert_array);
                    }
                }
            }

        }

        return true;

    }

    private function GetShopID($shop_id) {
        global $db;

        $sql = "SELECT config_value FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE config_key='XT_PROTECTED_SHOPS_SHOP_ID' AND shop_id=? ";
        $result =$db->GetOne($sql,array($shop_id));
        return $result;

    }

    public function getDocuments($force=false) {
        global $db,$info;

        // check last try
        if ($force!=true) {
            // get update day periode
            $secs = $this->update_periode*60*60;
            $next = _SYSTEM_PROTECTED_LAST_IMPORT+$secs;
            if (($next)>time()) {
                return;
            } else {
                $sql = "UPDATE ".TABLE_CONFIGURATION." SET config_value='".time()."' WHERE config_key='_SYSTEM_PROTECTED_LAST_IMPORT'";
                $db->Execute($sql);

            }
        }

        // check if acivated for this shop
        $sql = "SELECT * FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE config_key='XT_PROTECTED_ACTIVE'";
        $rs=$db->Execute($sql);

        while (!$rs->EOF) {
            if ($rs->fields['config_value']=='true') {

                // load documents
                $ds = $db->Execute("SELECT * FROM ".$this->_table." WHERE store_id=? AND content_id>0",array((int)$rs->fields['shop_id']));
                while (!$ds->EOF) {
                    $this->GetDocument($this->GetShopID($rs->fields['shop_id']),$ds->fields['document'],$rs->fields['shop_id'],$ds->fields['content_id']);
                    if ($this->type=='admin') $info->_addInfo("Dokument aktualisiert: ".$ds->fields['document'].", Shop ID: ".$rs->fields['shop_id'], 'success');
                    $ds->MoveNext();
                }
            }
            $rs->MoveNext();
        }
    }

    private function GetDocument($shopId,$document,$shop_id,$content_id) {
        global $db;

        $request = array();
        $request['Request']='GetDocument';
        $request['ShopId']=$shopId;
        $request['Document']=$document;
        $request['Format']='HtmlLite';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->api_url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);

        if( ! $result = curl_exec($ch))
        {
            $this->protLog('curl error: '.curl_error($ch),$content_id,'error');
            $db->Execute("UPDATE ".$this->_table." SET service_status=0 WHERE document=? and store_id=? ",array($document,$shop_id));
            return;
        }


        curl_close($ch);
        $arr = XML_unserialize($result);

        if (is_array($arr)) {
            if (isset($arr['Response']['Document'])) {

                // checksum check
                if (md5($arr['Response']['Document'])==$arr['Response']['MD5']) {

                $insert_array=array();
                $insert_array['content_body']=$arr['Response']['Document'];

                $db->AutoExecute(TABLE_CONTENT_ELEMENTS,$insert_array,'UPDATE', "content_id=".$content_id." AND language_code='de'");

                // update info in table
                $db->Execute("UPDATE ".$this->_table." SET last_update=NOW(),service_status=1 WHERE document=? and store_id=? ",array($document,$shop_id));

                } else {
                    // checksum mismatch
                    $db->Execute("UPDATE ".$this->_table." SET service_status=0 WHERE document=? and store_id=? ",array($document,$shop_id));
                    $this->protLog('checksum mismatch',$content_id,'error');
                }

                return;
            } else {
                $db->Execute("UPDATE ".$this->_table." SET service_status=0 WHERE document=? and store_id=? ",array($document,$shop_id));
                $this->protLog('data error',$content_id,'error');
            }
        } else {
            $db->Execute("UPDATE ".$this->_table." SET service_status=0 WHERE document=? and store_id=? ",array($document,$shop_id));
            $this->protLog('data error',$content_id,'error');
        }

    }

    function _getParams() {


        $params = array();

        $header[$this->_master_key] = array('type' => 'hidden');

        $header['document'] = array('disabled' => 'true');
        $header['store_id'] = array('disabled' => 'true');

        $header['content_id'] = array('type' => 'dropdown',
            'url' => 'DropdownData.php?get=content_list');


        $params['header']         = $header;
        $params['display_searchPanel']  = false;

        $params['GroupField']     = "store_id";
        $params['SortField']      = "id";
        $params['SortDir']        = "ASC";
        $params['PageSize'] = 25;

        $params['display_checkCol']  = false;
        $params['master_key']     = $this->_master_key;

        $params['display_options_addBtn'] = false;

        $params['display_statusTrueBtn']  = false;
        $params['display_statusFalseBtn']  = false;

        $params['display_newBtn']  = false;
        $params['display_deleteBtn']  = false;

        $extF = new ExtFunctions();
        $js = "addTab('row_actions.php?type=PS_getDocumentInfo','" . TEXT_XT_PROTECTED_SHOPS_IMPORT_DOCUMENTS . "');";
        $UserButtons['importDocuments'] = array('text' => 'TEXT_XT_PROTECTED_SHOPS_IMPORT_DOCUMENTS', 'style' => 'importDocuments', 'icon' => 'control_repeat_blue.png', 'acl' => 'edit', 'stm' => $js);

        $extF = new ExtFunctions();
        $js = "addTab('row_actions.php?type=PS_getDocuments','" . TEXT_XT_PROTECTED_SHOPS_UPDATE_DOCUMENTS . "');";
        $UserButtons['updateDocuments'] = array('text' => 'TEXT_XT_PROTECTED_SHOPS_UPDATE_DOCUMENTS', 'style' => 'updateDocuments', 'icon' => 'control_repeat_blue.png', 'acl' => 'edit', 'stm' => $js);

        $params['display_importDocumentsBtn'] = true;
        $params['display_updateDocumentsBtn'] = true;

        $params['display_options_addBtn'] = true;
        $params['UserButtons'] = $UserButtons;

        if (!$this->url_data['edit_id'] && $this->url_data['new'] != true) {
        } else {
            $params['exclude'] = array('last_change','last_update','service_status');

        }


        return $params;
    }

    function _get($ID = 0) {
        global $xtPlugin, $db, $language;
        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }


        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

        if ($this->url_data['get_data']){
            $data = $table_data->getData();
        }elseif($ID){

            $data = $table_data->getData($ID);

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

        return $obj;
    }

    private function protLog($message,$id=0,$type='info') {
        global $logHandler;

        $log_data = array();
        $log_data['message'] = $message;
        $logHandler->_addLog($type,'xt_protected_shops',$id,$log_data);
    }


}