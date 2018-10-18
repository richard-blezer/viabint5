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

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS."xt_ship_and_track/classes/hermes_ExtAdminHandler.php";

if (defined('HERMES_DEV')) die (' - dont even try - ');
define('HERMES_DEV', 0);

if (defined('XT_HERMES_API_PARTNER_ID')) die (' - dont even try - ');


if (HERMES_DEV) // digitalfabrik api daten
{
    define('XT_HERMES_API_PARTNER_ID',  'EXT000315');
    define('XT_HERMES_API_PARTNER_PWD', '70d0172475a9486a134b17f911332563');
}
else // XTC api daten
{
    define('XT_HERMES_API_PARTNER_ID',  'EXT000207');
    define('XT_HERMES_API_PARTNER_PWD', '42864422bb280c5d134b17f911332563');
}

global $db;
$sql = 'SELECT '.COL_HERMES_USER.' FROM '.TABLE_HERMES_SETTINGS. ' WHERE 1';
$a = $db->GetOne($sql);
define('XT_HERMES_USER', $a ? $a:'');

$sql = 'SELECT '.COL_HERMES_PWD.' FROM '.TABLE_HERMES_SETTINGS. ' WHERE 1';
$a = $db->GetOne($sql);
define('XT_HERMES_PWD',  $a ? $a:'');

$sql = 'SELECT '.COL_HERMES_SANDBOX.' FROM '.TABLE_HERMES_SETTINGS. ' WHERE 1';
$a = $db->GetOne($sql);
define('XT_HERMES_SANDBOX',  $a==1 ? 1:0);

class xt_ship_and_track {

    private $_master_key = 'id';

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $header = array();
        $header[COL_HERMES_ID_PK] = array( 'readonly'=>true);
        $header[COL_HERMES_XT_ORDER_ID] = array('readonly'=>true);
        $header[COL_HERMES_ORDER_NO] = array('readonly'=>true);
        $header[COL_HERMES_SHIPPING_ID] = array('type' => 'textfield', 'readonly'=>true);
        $header[COL_HERMES_STATUS] = array('type' => 'textfield', 'readonly'=>true);
        $header[COL_HERMES_COLLECT_DATE] = array('type' => 'textfield', 'readonly'=>true);
        $header[COL_HERMES_AMOUNT_CASH_ON_DELIVERY] = array('type' => 'textfield');
        $header[COL_HERMES_BULK_GOOD ] = array('type' => 'status');
        $header[COL_HERMES_PARCEL_CLASS] = array('type'=>'dropdown', 'width' => 100, 'url'  => 'DropdownData.php?get=hermes_parcel_class&plugin_code=xt_ship_and_track','text'=>TEXT_PARCEL_CLASS);

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['display_deleteBtn'] = false;
        $params['display_resetBtn'] = true;
        $params['display_editBtn'] = true;
        $params['display_newBtn'] = false;
        $params['display_searchPanel']  = true;

        $params['display_checkCol']  = true;

        //$params['PageSize'] = 2;

if (!$this->url_data['edit_id'])
{
        $rowActionsFunctions = array();
        // open item

        $js = "Ext.MessageBox.show({
               title:    'Druckposition wählen',
               msg:      '<input checked=\"1\" value=\"1\" id=\"printPosition1\" name=\"printPosition\" type=\"radio\" /> oben links<br /><input value=\"2\" id=\"printPosition2\" name=\"printPosition\" type=\"radio\" /> oben rechts<br /><input value=\"3\" id=\"printPosition3\" name=\"printPosition\" type=\"radio\" /> unten links<br /><input value=\"4\" id=\"printPosition4\" name=\"printPosition\" type=\"radio\" /> unten rechts<br />',
               buttons:  Ext.MessageBox.OKCANCEL,
               fn: function(btn) {
                  if( btn == 'ok') {
                      var pos = $(\"input:radio[name ='printPosition']:checked\").val();
                      //alert(pos);
                      window.open('adminHandler.php?plugin=xt_ship_and_track&load_section=xt_ship_and_track&pg=printLabel&type=pdf&order_no='+record.data.hermes_order_no+'&pos='+pos,'_blank');
                  }
               }
            });";
        $rowActionsFunctions['PRINT_LABEL'] = $js;
        $rowActions[] = array('iconCls' => 'PRINT_LABEL', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRINT_LABEL);

        $js = "
            var lm = new Ext.LoadMask(Ext.getBody(),{msg:'".__define('TEXT_HERMES_DELETING_ORDER')."'});
            lm.show();

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'deleteOrder',
                    load_section:   'xt_ship_and_track',
                    plugin:         'xt_ship_and_track',
                    tracking_code:      record.data.".COL_HERMES_ORDER_NO.",
                    cascade: 1
                },
                waitMsg: 'Laden',
                success: function(responseObject)
                {
                    var r = Ext.decode(responseObject.responseText);
                    lm.hide();
                    contentTabs.getActiveTab().getUpdater().refresh();
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

        $rowActionsFunctions['DELETE_HERMES_ORDER'] = $js;
        $rowActions[] = array('iconCls' => 'DELETE_HERMES_ORDER', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_DELETE);

        if (count($rowActionsFunctions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

        if (count($rowActions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }
}

        $ext = new hermes_ExtAdminHandler($this->_AdminHandler);
        $ext->setMasterKey($this->_master_key);
        $js = $ext->_multiactionPopup('my33', 'adminHandler.php?plugin=xt_ship_and_track&load_section=xt_hermes_collect&edit_id=new', TEXT_REQUEST_COLLECT);
        $UserButtons['my33'] = array('status'=>false, 'text'=>'TEXT_REQUEST_COLLECT', 'style'=>'HERMES_COLLECT', 'acl'=>'edit', 'icon'=>'lorry.png', 'stm'=>$js);
        $params['display_my33Btn'] = true;

        $js_refresh = "
            var lm = new Ext.LoadMask(Ext.getBody(),{msg:'".__define('TEXT_HERMES_REFRESHING')."'});
            lm.show();

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'updateStatus',
                    load_section:   'xt_ship_and_track',
                    plugin:         'xt_ship_and_track',
                    tracking_code: 'refresh_all'
                },
                success: function(responseObject)
                {
                    var r = Ext.decode(responseObject.responseText);
                    lm.hide();
                    contentTabs.getActiveTab().getUpdater().refresh();
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

        $UserButtons['refresh_status'] = array('text'=>'TEXT_HERMES_REFRESH', 'style'=>'HERMES_REFRESH', 'icon'=>'arrow_refresh.png', 'acl'=>'edit', 'stm' => $js_refresh);
        $params['display_refresh_statusBtn'] = true;

        $js_print = $ext->_multiactionWindow('printSelectedLabels', 'adminHandler.php?plugin=xt_ship_and_track&load_section=xt_ship_and_track&pg=printLabelsPdfSelection', TEXT_HERMES_PRINT_SELECTION);
        $UserButtons['printSelectedLabels'] = array('status'=>false, 'text'=>'TEXT_HERMES_PRINT_SELECTION', 'style'=>'HERMES_PRINT', 'acl'=>'edit', 'icon'=>'printer.png', 'stm'=>$js_print);
        $params['display_printSelectedLabelsBtn'] = true;

        $params['UserButtons']      = $UserButtons;

        return $params;
    }

    function _get($ID = 0, $format = true)
    {
        if ($this->position != 'admin') return false;

        $where = '';
        if($this->url_data['query'])
        {
            $where = "(" .COL_HERMES_ORDER_NO." LIKE '%".$this->url_data['query']."%' OR ". COL_HERMES_SHIPPING_ID." LIKE '%".$this->url_data['query']."%' )";
        }

        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,50";
        }

        $table_data = new adminDB_DataRead(TABLE_HERMES_ORDER, '', '', $this->_master_key, $where , '', '', '',  'ORDER BY '.COL_HERMES_ID_PK. ' DESC ');
        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
            foreach($data as $k=>$d)
            {
                if ($format)
                {
                    $data[$k][COL_HERMES_STATUS] = __define('TEXT_HERMES_'.$d[COL_HERMES_STATUS].'_SHORT');
                    $data[$k][COL_HERMES_COLLECT_DATE] = $data[$k][COL_HERMES_COLLECT_DATE] ? date('Y-m-d', strtotime($data[$k][COL_HERMES_COLLECT_DATE])) : '';
                }
            }
        }

        elseif($ID) {
            $data = $table_data->getData($ID);
            $defaultOrder = array(
                COL_HERMES_ID_PK,
                COL_HERMES_XT_ORDER_ID,
                COL_HERMES_ORDER_NO,
                COL_HERMES_SHIPPING_ID,
                COL_HERMES_SHIPPING_ID,
                COL_HERMES_COLLECT_DATE,
                COL_HERMES_STATUS,
                COL_HERMES_AMOUNT_CASH_ON_DELIVERY,
                COL_HERMES_PARCEL_CLASS,
                COL_HERMES_BULK_GOOD
            );

            $orderedData = array();
            foreach ($defaultOrder as $key) {
                $orderedData[$key] = $data[0][$key];
            }
            if ($format) {
                $orderedData[COL_HERMES_STATUS] = __define('TEXT_HERMES_'.$orderedData[COL_HERMES_STATUS].'_SHORT');
                $orderedData[COL_HERMES_COLLECT_DATE] = $orderedData[COL_HERMES_COLLECT_DATE] ? date('Y-m-d', strtotime($orderedData[COL_HERMES_COLLECT_DATE])) : '';
            }
            $data = array($orderedData);

        } else {
            $data = $table_data->getHeader();
            $defaultOrder = array(
                COL_HERMES_ID_PK,
                COL_HERMES_XT_ORDER_ID,
                COL_HERMES_ORDER_NO,
                COL_HERMES_SHIPPING_ID,
                COL_HERMES_SHIPPING_ID,
                COL_HERMES_PARCEL_CLASS,
                COL_HERMES_STATUS,
                COL_HERMES_BULK_GOOD,
                COL_HERMES_AMOUNT_CASH_ON_DELIVERY,
                COL_HERMES_COLLECT_DATE
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
        global $db;
        $sql = "SELECT `".COL_HERMES_XT_ORDER_ID."` FROM `".TABLE_HERMES_ORDER."` WHERE `".COL_HERMES_ID_PK."`=?";
        $xtOrderId = $db->GetOne($sql, array($_REQUEST['edit_id']));
        $data['orders_id'] = $xtOrderId;
        $sql = "SELECT `".COL_HERMES_ORDER_NO."` FROM `".TABLE_HERMES_ORDER."` WHERE `".COL_HERMES_ID_PK."`=?";
        $orderNo = $db->GetOne($sql, array($_REQUEST['edit_id']));
        $data['orderNo'] = $orderNo;
        $data['edit_id'] = $_REQUEST['edit_id'];
        $r = $this->saveOrder($data);

        return json_decode($r);
    }

    function _unset($id = 0)
    {
        return false;
    }

    function deleteOrder($data)
    {
        $cascadeTracking = $data['cascade'];

        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        global $db;
        $hermesError = false;
        try {
            $hermes = self::getHermesService();
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return json_encode($r);
            }
            $hResult = $hermes->deleteOrder($data[COL_TRACKING_CODE]);
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            $r->code = $he->getCode();
            $hermesError = $he->getCode();
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            $hermesError = true;
        }

        if ($hermesError!='312311') // 312311 - Der Auftrag kann nicht gelöscht werden.
        {
            $db->Execute("DELETE FROM ".TABLE_HERMES_ORDER." where `".COL_HERMES_ORDER_NO."` = ?", array($data[COL_TRACKING_CODE]));

            if ($cascadeTracking && $hermesError!='312311') // 312311 - Der Auftrag kann nicht gelöscht werden.
            {
                $xtTracking = new xt_tracking();
                $data['cascade'] = false;
                $xtTracking->deleteTracking($data);
            }
        }

        $r->success = true;
        $r->msg = $hermesError=='312311' ? $r->msg : __define('TEXT_SUCCESS');
        return json_encode($r);
    }

    public static function getParcelClassForUser()
    {
        if ($_SESSION['hermes_parcel_class_user'])
        {
            $data = $_SESSION['hermes_parcel_class_user'];
        }
        else
        {
            $data = array();
            $hermes = self::getHermesService();
            if ($hermes)
            {
                try
                {
                    $classes = $hermes->getUserProducts();
                    foreach ($classes as $pwp) {
                        $name = self::_buildParcelClassDesc($pwp);

                        $data[] =  array('id' => $pwp->productInfo->parcelFormat->parcelClass,
                            'name' => $name);
                    }
                    $_SESSION['hermes_parcel_class_user'] = $data;
                }
                catch(HermesException $he)
                {
                    $data = array();
                }
            }
            else {
                $data = array();
                $data[] = array('id' => '', 'name' => TEXT_HERMES_CHECK_SETTINGS);
            }
        }

        return $data;
    }

    public function updateStatus($data)
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        if($data['tracking_code'] == 'refresh_order')
        {
            return $this->updateStatusForOrder($data['orders_id']);
        }
        else if ($data['tracking_code'] == 'refresh_all')
        {
            return $this->updateStatusForAll();
        }

        try {
            $hermes =  $this->getHermesService();// new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD);
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return json_encode($r);
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                $status = $hermes->readShipmentStatus($data['tracking_code']);
                $xt_tracking = new xt_tracking();
                $xt_tracking->setStatus($data['tracking_code'], $status);

                global $db;
                $sql = "UPDATE ".TABLE_HERMES_ORDER." SET `".COL_HERMES_STATUS."`=? WHERE `".COL_HERMES_ORDER_NO."`=?";
                $r = $db->Execute($sql, array($status, $data['tracking_code']));

                $r->msg = __define('TEXT_HERMES_'.$status.'_LONG');
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return json_encode($r);
            }
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            return json_encode($r);
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            return json_encode($r);
        }

        $r->success = true;
        return json_encode($r);

    }

    public function updateStatusForAll()
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        try {
            $hermes = $this->getHermesService();// new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD);
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return json_encode($r);
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                global $db;
                $shipperId = $db->GetOne(" SELECT `".COL_SHIPPER_ID_PK."` FROM ". TABLE_SHIPPER ." WHERE `".COL_SHIPPER_CODE."`='hermes'");
                $rs = $db->Execute(
                    'SELECT `'.COL_TRACKING_STATUS_CODE.'` FROM '.TABLE_TRACKING_STATUS.' t WHERE t.'.COL_TRACKING_SHIPPER_ID.'=? AND `'.COL_TRACKING_STATUS_CODE.'`!=10120',
                    array($shipperId)
                );
                $codes = array();
                if ($rs->RecordCount()>0)
                {
                    while (!$rs->EOF) {
                        $codes[] = $rs->fields[COL_TRACKING_STATUS_CODE];
                        $rs->MoveNext();
                    }
                }
                $rs->Close();

                $endRow = 499;
                $startRow = 0;
                $maxRows = 500;

                $c = 0;
                do
                {
                    $limit = " {$startRow},{$endRow} ";
                    // einzelne abfrage an hermes wird eingeschränkt auf 500 datensätzen und sätze
                    // - nicht älter 90 tage seit einstellung
                    // - nicht im status 10120 (erhalt rv)
                    $sql = "SELECT * FROM `".TABLE_HERMES_ORDER."` WHERE ".
                        " `".COL_HERMES_STATUS."` != 10120 AND ".
                        " `".COL_HERMES_TS_CREATED."` >= DATE_SUB(CURRENT_TIMESTAMP , INTERVAL 90 DAY) ".
                        " ORDER BY ".COL_HERMES_TS_CREATED .
                        " LIMIT ".$limit;

                    $rs = $db->Execute($sql);
                    $c = $rs->RecordCount();
                    if ($c==0) break;

                    $rs->MoveFirst();
                    $from = new DateTime($rs->fields[COL_HERMES_TS_CREATED]);
                    $from = $from->format('Y-m-d').'T'.$from->format('H:i:s');

                    $rs->MoveLast();
                    $to = new DateTime($rs->fields[COL_HERMES_TS_CREATED]);
                    $to = $to->format('Y-m-d').'T'.$to->format('H:i:s');

                    $sc = new PropsOrderSearchCriteria(
                        null, //identNo
                        null, //orderNo
                        null, //lastname
                        null, //city
                        $from, //from xsdDate
                        $to, //to
                        null, //postcode
                        null, //coutryCode
                        null, //clientRefNumber
                        null, //ebay
                        $codes  //status array // TODO muss eigentlich null sein
                    );

                    $orders = $hermes->getOrders($sc);
                    $xt_tracking = new xt_tracking();
                    foreach($orders as $order)
                    {
                        $xt_tracking->setStatus($order->orderNo, $order->status);

                        global $db;
                        $sql = "UPDATE ".TABLE_HERMES_ORDER." SET ".
                            "`".COL_HERMES_STATUS."`=?,".
                            "`".COL_HERMES_SHIPPING_ID."`=?".
                            " WHERE `".COL_HERMES_ORDER_NO."`=?";
                        $r = $db->Execute($sql, array($order->status, $order->shippingId, $order->orderNo));
                    }

                    $startRow += $maxRows;
                    $endRow += $maxRows;
                }
                while($c>0);

                $r->msg = __define('TEXT_SUCCESS');
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return json_encode($r);
            }
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            return json_encode($r);
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            return json_encode($r);
        }

        $r->success = true;
        return json_encode($r);

    }

    public function updateStatusForOrder($xt_orders_id)
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        try {
            $hermes = $this->getHermesService();// new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD);
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return json_encode($r);
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                $refNo = 'xtc'.$xt_orders_id;

                $sc = new PropsOrderSearchCriteria(
                    null, //identNo
                    null, //orderNo
                    null, //lastname
                    null, //city
                    null, //date(DATE_RFC822,0), //from
                    null, //date(DATE_RFC822), //to
                    null, //postcode
                    null, //coutryCode
                    $refNo, //clientRefNumber
                    null, //ebay
                    null  //status array
                );

                $orders = $hermes->getOrders($sc);
                $xt_tracking = new xt_tracking();
                foreach($orders as $order)
                {
                    $xt_tracking->setStatus($order->orderNo, $order->status);

                    global $db;
                    $sql = "UPDATE ".TABLE_HERMES_ORDER." SET `".COL_HERMES_STATUS."`=? WHERE `".COL_HERMES_ORDER_NO."`=?";
                    $r = $db->Execute($sql, array($order->status, $order->orderNo));
                }

                $r->msg = __define('TEXT_SUCCESS');
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return json_encode($r);
            }
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            return json_encode($r);
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            return json_encode($r);
        }

        $r->success = true;
        return json_encode($r);

    }

    public function saveOrder($data, $cascadeTracking = true)
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;

        $xtOrder = new order($data['orders_id'], -1);

        try {
            $hermes = $this->getHermesService();// new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD);
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return json_encode($r);
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                global $db;
                $iso3countryCode = $db->GetOne(
                    "SELECT `countries_iso_code_3` FROM ". TABLE_COUNTRIES . " WHERE `countries_iso_code_2`=?",
                    array($xtOrder->order_data['delivery_country_code'])
                );

                $refNo = 'xtc'.$data['orders_id'];
                $lastName = !empty($xtOrder->order_data['delivery_company']) ?
                    $xtOrder->order_data['delivery_lastname'] . ' / '. $xtOrder->order_data['delivery_company'] :
                    $xtOrder->order_data['delivery_lastname'];

                $receiver = new HermesAddress('', // adresszusatz zb hinterhaus
                    $xtOrder->order_data['customers_email_address'],
                    '', // hausnummer
                    $iso3countryCode,
                    $lastName,
                    $xtOrder->order_data['delivery_city'], $xtOrder->order_data['delivery_suburb'], $xtOrder->order_data['delivery_postcode'],
                    $xtOrder->order_data['delivery_street_address'],
                    $xtOrder->order_data['delivery_phone'],
                    $xtOrder->order_data['delivery_firstname'],
                    '' // phone prefix
                );
                $cod = (is_numeric($data['hermes_amount_cash_on_delivery']) && $data['hermes_amount_cash_on_delivery'] > 0) ? true : false;
                $codAmount = $data['hermes_amount_cash_on_delivery'] ;
                $codAmountCent = $data['hermes_amount_cash_on_delivery'] * 100;

                $bulk = $data['hermes_bulk_good'] == 'on' || $data['hermes_bulk_good'] == 1 ? true : false;
                $orderNo = !empty($data['orderNo']) ? $data['orderNo'] : '';
                $orderNo = $hermes->createOrder($receiver, $refNo, $data['parcel_class'],$cod,$codAmountCent, $bulk, $orderNo);

                $sc = new PropsOrderSearchCriteria(
                    null, //identNo
                    $orderNo, //orderNo
                    null, //lastname
                    null, //city
                    null, //date(DATE_RFC822,0), //from
                    null, //date(DATE_RFC822), //to
                    null, //postcode
                    null, //coutryCode
                    null, //clientRefNumber
                    null, //ebay
                    null  //status array
                );

                $orders = $hermes->getOrders($sc);
                $order = $orders[0];
                $r->msg = __define('TEXT_HERMES_'.$order->status.'_LONG');

                $saveData = array(
                    COL_HERMES_ORDER_NO => $orderNo,
                    COL_HERMES_SHIPPING_ID => '',
                    COL_HERMES_STATUS => $order->status,
                    COL_HERMES_AMOUNT_CASH_ON_DELIVERY => $codAmount,
                    COL_HERMES_PARCEL_CLASS => $data['parcel_class'],
                    COL_HERMES_XT_ORDER_ID => $xtOrder->oID,
                    COL_HERMES_BULK_GOOD => $bulk ? 1 :0,
                    COL_HERMES_COLLECT_DATE => null,
                    COL_HERMES_TS_CREATED => $order->creationDate
                );
                if (!empty($data['edit_id']))
                {
                    $saveData[COL_HERMES_ID_PK] = $data['edit_id'];
                }
                $o = new adminDB_DataSave(TABLE_HERMES_ORDER, $saveData, false, __CLASS__);
                try {
                    $o->saveDataSet();
                }
                catch(Exception $e){
                    $r->errorMsg = $e->getMessage();
                    $r->msg = $e->getMessage();
                    return json_encode($r);
                }

                if ($cascadeTracking)
                {
                    global $db;
                    $shipperId = $db->GetOne(" SELECT `".COL_SHIPPER_ID_PK."` FROM ". TABLE_SHIPPER ." WHERE `".COL_SHIPPER_CODE."`='hermes'");
                    $tracking = new xt_tracking();
                    $data = array(
                        'orders_id' => $xtOrder->oID,
                        'shipper' => $shipperId,
                        'tracking_codes' => $orderNo,
                        'send_email' => false
                    );
                    $tracking->addTracking($data, false);
                    $tracking->setStatus($orderNo, $order->status);
                }

            }
            else
            {
                $r->errorMsg = TEXT_NOT_LOGGED_IN;
                $r->msg = TEXT_NOT_LOGGED_IN;
                return json_encode($r);
            }
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            return json_encode($r);
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            return json_encode($r);
        }


        $r->success = true;
        return json_encode($r);

    }

    public function printLabel($data)
    {
        $r = TEXT_FAILURE;

        try {
            $hermes = $this->getHermesService();// new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD);
            if (!$hermes)
            {
                return TEXT_HERMES_CHECK_SETTINGS;
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                if ($data['type'] == 'pdf')
                {
                    $pdf = $hermes->printLabelPdf($data['order_no'], $data['pos']);
                }
                else if ($data['type'] == 'jpeg' || $data['type'] == 'jpg')
                {
                    $pdf = $hermes->printLabelJpeg($data['order_no'], $data['pos']);
                }
                else {
                    echo 'Wrong type';
                    exit;
                }
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return $r;
            }
        }
        catch (HermesException $he)
        {
            $r = $he->getHermesMessage();
            return $r;
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            return $r;
        }

        header('Content-type: application/'.$data['type']);
        header('Content-Disposition:  filename='.$data['type']."'");
        echo $pdf;

        exit;

    }

    public function printLabelsPdf($data)
    {
        $r = TEXT_FAILURE;

        try {
            $hermes = $this->getHermesService();// new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD);
            if (!$hermes)
            {
                return TEXT_HERMES_CHECK_SETTINGS;
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                global $db;
                $sql = "SELECT `".COL_TRACKING_CODE."` FROM `".VIEW_TRACKING."` WHERE `".COL_SHIPPER_CODE."`='hermes' AND `".COL_TRACKING_ORDER_ID."`=?";
                $dbResult = $db->Query($sql, array($data['orders_id']));
                if ($dbResult->RecordCount() > 0)
                {
                    $codes = array();
                    while(!$dbResult->EOF)
                    {
                        $code = $dbResult->fields[COL_TRACKING_CODE];
                        $codes[] = $code;
                        $dbResult->MoveNext();
                    }
                    $dbResult->Close();

                    $pdf = $hermes->printLabelsPdf($codes);
                }
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return $r;
            }
        }
        catch (HermesException $he)
        {
            $r = $he->getHermesMessage();
            return $r;
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            return $r;
        }

        header('Content-type: application/pdf');
        header('Content-Disposition:  filename='.$data['orders_id']."'");
        echo $pdf;

        exit;

    }

    public function printLabelsPdfSelection($data)
    {
        $r = TEXT_FAILURE;

        $hermesIds = explode(',', $this->url_data['value_ids']);
        if (sizeof($hermesIds)==0)
        {
            $r->msg = $r->errorMsg = 'No id\'s found';
            return json_encode($r);
        }
        $orderNoIds = array();
        foreach($hermesIds as $hermesId)
        {
            if(!$hermesId) continue;
            $h = $this->_get($hermesId)->data[0];
            if ($h)
            {
                $orderNoIds[] = $h[COL_HERMES_ORDER_NO];
            }
        }

        if(sizeof($orderNoIds))
        {
            try {
                $hermes = $this->getHermesService();
                if (!$hermes)
                {
                    return TEXT_HERMES_CHECK_SETTINGS;
                }
                $pdf = $hermes->printLabelsPdf($orderNoIds);
            }
            catch (HermesException $he)
            {
                $r = $he->getMessage();
                echo $r;
                die();
            }
            catch (Exception $e)
            {
                $r = $e->getMessage();
                echo $r;
                die();
            }

            header('Content-type: application/pdf');
            header('Content-Disposition:  filename='.$data['orders_id']."'");
            echo $pdf;

            exit;
        }
        echo $r;
        die();
    }

    public function requestCollect($data)
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;
        $r->requestNo = false;

        try {
            $hermes = $this->getHermesService();
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return $r;
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                $requestNo = $hermes->requestCollection($data['date'],$data['xs'],$data['s'],$data['m'],$data['l'],$data['xl'],$data['xl_bulk']);
                $r->requestNo = $requestNo;
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return $r;
            }
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            return $r;
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            $r;
        }

        $r->msg = __define('TEXT_SUCCESS');
        $r->errorMsg = __define('TEXT_SUCCESS');
        $r->success = true;
        return $r;
    }


    public function deleteCollect($date)
    {
        $r = new stdClass();
        $r->success = false;
        $r->msg = false;
        $r->errorMsg = false;
        $r->canceled = false;
        $r->code = false;

        try {
            $hermes = $this->getHermesService();
            if (!$hermes)
            {
                $r->errorMsg = TEXT_HERMES_CHECK_SETTINGS;
                $r->msg = TEXT_HERMES_CHECK_SETTINGS;
                return $r;
            }
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if ($loggedIn)
            {
                $canceled = $hermes->cancelCollection($date);
                $r->canceled = $canceled;
            }
            else
            {
                $r->errorMsg = 'TEXT_HERMES_NOT_LOGGED_IN';
                return $r;
            }
        }
        catch (HermesException $he)
        {
            $r->errorMsg = $he->getHermesMessage();
            $r->msg = $he->getHermesMessage();
            $r->code = $he->getCode();
            return $r;
        }
        catch (Exception $e)
        {
            $r->errorMsg = $e->getMessage();
            $r->msg = $e->getMessage();
            $r->code = $e->getCode();
            $r;
        }

        $r->success = true;
        return $r;

    }



    private static function _buildParcelClassDesc(ProductWithPrice $pwp)
    {
        $desc = $pwp->productInfo->parcelFormat->parcelClass ? $pwp->productInfo->parcelFormat->parcelClass.' - ' : '';

        if (is_array($pwp->productInfo->deliveryDestinations->DeliveryDestination))
        {
            foreach($pwp->productInfo->deliveryDestinations->DeliveryDestination as $dd)
            {
                $desc .= $dd->countryCode.'-';
                $desc .= number_format($dd->weigthMaxKg,1,',','.').'kg ';
            }
        }
        else {
            $desc .= $pwp->productInfo->deliveryDestinations->DeliveryDestination->countryCode.'-';
            $desc .= number_format($pwp->productInfo->deliveryDestinations->DeliveryDestination->weigthMaxKg,1,',','.').'kg ';
        }
        return $desc;
    }

    public static function getHermesService()
    {

        $userToken = false;
        if ($_SESSION['hermes_user_token'])
        {
            $userToken = $_SESSION['hermes_user_token'];
        }

        try {
            $hermes = new Hermes(XT_HERMES_API_PARTNER_ID, XT_HERMES_API_PARTNER_PWD, $userToken, XT_HERMES_SANDBOX);
            $loggedIn = $hermes->login(XT_HERMES_USER, XT_HERMES_PWD);
            if (!$loggedIn)
            {
                return false;
            }
            else
            {
                $_SESSION['hermes_user_token'] = $hermes->getUserToken();
            }
        }
        catch (HermesException $he)
        {
            return false;
        }
        catch (Exception $e)
        {
            return false;
        }

        return $hermes;
    }


    public static function orderEdit_displayAddParcel($orders_id)
    {
        $addParcelPanel = self::getAddParcelPanel($orders_id);

        $js= PhpExt_Ext::onReady(
            $addParcelPanel->getJavascript(false, "addTrackingPanel")
        );

        return $js;
    }

    static function getAddParcelPanel($orders_id)
    {
        $Panel = new PhpExt_Form_FormPanel('addParcelForm');
        $Panel->setId('addParcelForm'.$orders_id)
            ->setTitle(__define('TEXT_ADD_HERMES_PARCEL'))
            ->setAutoWidth(true)
            ->setBodyStyle('padding: 10px;')
            ->setUrl("adminHandler.php?plugin=xt_ship_and_track&load_section=xt_ship_and_track&pg=saveOrder&orders_id=".$orders_id) ;

        $eF = new ExtFunctions();
        $combo = $eF->_comboBox('parcel_class', __define('TEXT_PARCEL_CLASS'), 'DropdownData.php?get=hermes_parcel_class&plugin_code=xt_ship_and_track');
        $Panel->addItem($combo);

        $Panel->addItem(PhpExt_Form_TextField::createTextField('hermes_amount_cash_on_delivery', __define('TEXT_HERMES_AMOUNT_CASH_ON_DELIVERY')));
        $Panel->addItem(PhpExt_Form_Checkbox::createCheckbox('hermes_bulk_good', __define('TEXT_BULK_GOODS')));

        $submitBtn = PhpExt_Button::createTextButton(__define("BUTTON_SAVE"),
            new PhpExt_Handler(PhpExt_Javascript::stm("Ext.getCmp('addParcelForm".$orders_id."').getForm().submit({
												   waitMsg:'".__define('TEXT_HERMES_CREATING_ORDER')."',
												   success: function(form, action) {
												        var r = action.result;
												        //console.log(r);
                                                        if (!r.success)
                                                        {
                                                            Ext.Msg.alert('".__define('TEXT_ALERT')."', r.errorMsg);
                                                        }
                                                        else
                                                        {
                                                            Ext.MessageBox.alert('".__define('TEXT_ALERT')."',r.msg);
                                                        }
                                                       contentTabs.getActiveTab().getUpdater().refresh()
                                                   },
                                                    failure: function(form, action)
                                                    {
                                                        var r = action.result;
                                                        //console.log(r);
                                                        Ext.Msg.alert('".__define('TEXT_ALERT')."', r.errorMsg);
                                                    }
												   })"))
        );

        $submitBtn->setType(PhpExt_Button::BUTTON_TYPE_SUBMIT);
        $Panel->addButton($submitBtn);

        return $Panel;
    }

    /**
     *
     * @return array die verfügbaren store names für DropdownData
     */
    public static function getStores()
    {
        $data = array();

        global $store_handler;
        $_data = $store_handler->getStores();

        foreach ($_data as $sdata) {
            $data[] =  array('id' => $sdata['id'],
                'name' => $sdata['text']);
        }

        return $data;
    }

    /**
     *
     * @return array die verfügbaren hermes status codes für DropdownData
     */
    public static function getStatusCodes()
    {
        global $db;
        $shipperId = $db->GetOne(" SELECT `".COL_SHIPPER_ID_PK."` FROM ". TABLE_SHIPPER ." WHERE `".COL_SHIPPER_CODE."`='hermes'");

        $sql = "SELECT * FROM ".TABLE_TRACKING_STATUS." WHERE ".COL_TRACKING_SHIPPER_ID."=?";
        $rs = $db->Execute($sql, array($shipperId));

        $data = array();
        if ($rs->RecordCount()>0)
        {
            while (!$rs->EOF) {
                $data[] =  array('id' => $rs->fields[COL_TRACKING_STATUS_CODE],
                    'name' => __define('TEXT_HERMES_'.str_replace('-','_',$rs->fields[COL_TRACKING_STATUS_CODE]).'_SHORT'));
                $rs->MoveNext();
            }
        }
        $rs->Close();

        return $data;
    }
}