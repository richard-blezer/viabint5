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
 # @version $Id: class.paypal_refunds.php 5382 2012-07-23 14:03:53Z tu $
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


class paypal_refunds
{

    protected $_table = TABLE_PAYPAL_REFUNDS;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'refunds_id';
    protected $log_callback_data = true;
    protected $ip = array();
    protected $logFile = 'callback.txt';

    var $API_UserName,
        $API_Password,
        $API_Signature,
        $API_Endpoint;

    function __construct ()
    {
        if (XT_PAYPAL_MODE == 'sandbox') {
            $this->API_UserName = XT_PAYPAL_API_SANDBOX_USER;
            $this->API_Password = XT_PAYPAL_API_SANDBOX_PWD;
            $this->API_Signature = XT_PAYPAL_API_SANDBOX_SIGNATURE;
            $this->API_Endpoint = 'https://api-3t.sandbox.paypal.com/nvp';

        } elseif (XT_PAYPAL_MODE == 'live') {
            $this->API_UserName = XT_PAYPAL_API_USER;
            $this->API_Password = XT_PAYPAL_API_PWD;
            $this->API_Signature = XT_PAYPAL_API_SIGNATURE;
            $this->API_Endpoint = 'https://api-3t.paypal.com/nvp';

        }

    }

    /**
     * write log to file
     *
     * @param string $data
     * @param string $file
     */
    function _writeLogFile ($data)
    {

        $line = 'CALLBACK|' . date("d.m.Y H:i", time()) . '|';

        foreach ($data as $key => $val)
            $line .= $key . ':' . $val . '|';

        error_log($line . "\n", 3, $this->logFile);
    }


    /**
     * Add entry to callback log
     *
     * available fields:
     * module
     * orders_id
     * transaction_id
     * callback_data -> serialized array
     *
     * @param array $log_data
     */
    function _addLogEntry ($log_data)
    {
        global $db;
        if (is_array($log_data['callback_data'])) $log_data['callback_data'] = serialize($log_data['callback_data']);
        //$log_data['created'] =  $db->BindTimeStamp(time());
        if ($log_data['transaction_id'] == null) $log_data['transaction_id'] = '';
        $db->AutoExecute(TABLE_CALLBACK_LOG, $log_data, 'INSERT');
        $last_id = $db->Insert_ID();
        return $last_id;
    }

    /**
     * Update order Status, and send status mail
     *
     * @param int $new_order_status
     */
    function _updateOrderStatus ($new_order_status, $send_mail = 'true', $callback_id = '')
    {
        $order = new order($this->orders_id, $this->customers_id);
        if ($callback_id == null) $callback_id = '';
        $order->_updateOrderStatus($new_order_status, '', $send_mail, 'true', 'IPN', $callback_id);
    }


    function setPosition ($position)
    {
        $this->position = $position;
    }

    function _getParams ()
    {
        $params = array();

        $header = array();
        $header['orders_id'] = array('disabled' => 'true');
        $header['transaction_id'] = array('readonly' => 'true');
        $header['callback_log_id'] = array('disabled' => 'true');
        $header['error_data'] = array('disabled' => 'true');
        $header['error_msg'] = array('disabled' => 'true');
        $header['callback_data'] = array('disabled' => 'true');
        $header['refunded'] = array('disabled' => 'true');
        $header['success'] = array('readonly' => true, 'type' => 'status');
        $header['refunds_type'] = array(
            'type' => 'dropdown',
            'url' => 'DropdownData.php?get=refunds_type&plugin_code=xt_paypal');

        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;


        $params['display_newBtn'] = false;
        $params['display_deleteBtn'] = false;
        $params['display_editBtn'] = true;

        if ($this->url_data['pg'] == 'overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true) {
            $params['include'] = array('orders_id', 'transaction_id', 'refunded', 'status', 'total', 'success', 'refunds_type');
        } else {
            $params['exclude'] = array('date_added', 'last_modified', 'callback_log_id', 'callback_data', 'error_data', 'error_msg');
        }

        return $params;
    }


    function _get ($ID = 0)
    {
        global $xtPlugin, $db, $language;

        if ($this->position != 'admin') return false;

        if ($_GET['new'] == true) {
            $new_refunds = array();
            if (isset($_GET['callback_log_id'])) {
                $new_refunds = $this->_getData($_GET['callback_log_id']);
                $obj = $this->_set($new_refunds, 'new');
                $ID = $obj->new_id;
            }
/*
            $ID = $this->_getRefunds($_GET['callback_log_id']);
            if ($ID == 0) {
                $obj = $this->_set($new_refunds, 'new');
                $ID = $obj->new_id;
            }
            */
        }

        $ID = (int)$ID;


        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);


        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID) {
            $data = $table_data->getData($ID);

        } else {
            $data = $table_data->getHeader();
        }

        $obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;
    }

    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $filter, $seo, $currency;

        $obj = new stdClass;
        $obj->failed = true; // always false to force messgae box
        $obj->error_message = __define(TEXT_SUCCESS);

        if ($set_type == 'new') {
            $data['date_added'] = $db->BindTimeStamp(time());
            $data['last_modified'] = $db->BindTimeStamp(time());
        }

        //check entered data (refund_type vs total)
        $send_refund = false;
        $isRefunded = $this->_isRefunded($data['refunds_id']);

        if ($isRefunded == false) {
            if ($set_type == 'edit' && $data['status'] == 1) {
                $o_id = $this->getOrders_id($_GET['edit_id']);
                $o_total = $this->getTotal($o_id);

                switch ($data['refunds_type']) {
                    case 'Full':
                        if ($o_total != $data['total'] || $data['total'] == 0 || $o_total < $data['total']) {
                            //$obj->failed = false;
                            $obj->error_message = __define('PP_INVALID_REFUND_AMOUNT');
                            return $obj;
                        }
                        break;

                    case 'Partial' :
                        if ($data['total'] >= $o_total || $data['total'] <= 0) {
                            //$obj->failed = false;
                            $obj->error_message = __define('PP_INVALID_REFUND_AMOUNT');
                            return $obj;
                        }
                        break;
                }

                if ($data['success'] == 0) {
                    $send_refund = true;
                }
            }
        } else {
            $send_refund = false;
            if ($data['status'] == 0) {
                //$obj->failed = false;
                $obj->error_message = __define('PP_ALREADY_REFUNDED');
                return $obj;
            }
        }

        $success = $db->GetOne("SELECT success FROM " . TABLE_PAYPAL_REFUNDS . ' WHERE refunds_id=' . (int)$data['refunds_id']);
        if($success) $data['success'] = $success;

        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);

        $objC = $oC->saveDataSet();

        if ($set_type == 'new') { // edit existing
            $obj->new_id = $objC->new_id;
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
        }

        if ($objC->success) {
            //send refund to paypal
            if ($send_refund == true) {
                $callback_log_id = $this->getCallback_log_id($data['refunds_id']);
                $data_r = $this->_getData($callback_log_id);
                $data_r['refund_memo'] = $data['refund_memo'];
                $data_r['refunds_type'] = $this->getRefundtype($data['refunds_id']);
                $data_r['total'] = round($this->getRefundTotal($data['refunds_id']), $currency->decimals);
                $nvpStr_ = $this->buildRequest($data_r, $data['refunds_id']);
                $res = $this->PPHttpPost('RefundTransaction', $nvpStr_, $data['refunds_id']);

                if($res['ACK'] == 'Success')
                {
                $db->Execute("UPDATE " . TABLE_PAYPAL_REFUNDS . ' SET refunded = 1, success = 1 WHERE refunds_id=' . (int)$data['refunds_id']);

                $log_data['callback_data'] = serialize($res);
                $log_data['error_data'] = "erfolgreich";
                $this->_addCallbackLog($log_data, $data['refunds_id']);
                $o_refunds = new order($data_r['orders_id'], -1);
                $send_email = false;
                $status = XT_PAYPAL_ORDER_STATUS_REFUNDED;
                    $comments = 'Refund_id:' . $data['refunds_id'] . ',  '.TEXT_AMOUNT.':'.urldecode($res['GROSSREFUNDAMT']).' '.urldecode($res['CURRENCYCODE']). ',  Memo:' . $data_r['refund_memo'];
                $o_refunds->_updateOrderStatus($status, $comments, $send_email);

                    //$obj->failed = true;
                    //$obj->success = true;
                }
                else {
                    //$obj->failed = true;
                    //$obj->success = false;
                    $obj->error_message = urldecode($res['L_LONGMESSAGE0']);
                    $obj->message = $res['L_LONGMESSAGE0'];
                    $obj->message = $res['L_LONGMESSAGE0'];
                }
            }
            //$obj->success = true;

        } else {
            //$obj->failed = true;
            $obj->error_message = __define(TEXT_FAILURE);
        }

        return $obj;
    }

    function getCurrency ($orders_id)
    {
        global $currency;

        $refund_order = new order($orders_id, -1);
        return $refund_order->order_data[''];

    }

    public function getRefundtype ($refunds_id)
    {
        global $db;
        $record = $db->Execute("SELECT * FROM " . TABLE_PAYPAL_REFUNDS . " WHERE refunds_id = '" . (int)$refunds_id . "'");
        if ($record->RecordCount() > 0) {
            return $record->fields['refunds_type'];
        }
        return 'Full';
    }

    public function getRefundTotal ($refunds_id)
    {
        global $db;
        $record = $db->Execute("SELECT * FROM " . TABLE_PAYPAL_REFUNDS . " WHERE refunds_id = '" . (int)$refunds_id . "'");
        if ($record->RecordCount() > 0) {
            return $record->fields['total'];
        }
        return 0;
    }

    public function buildRequest ($data, $refunds_id)
    {
        global $currency;
        $log_data = array();
        $log_data['refunds_id'] = $refunds_id;

        // Set request-specific fields.
        $transactionID = urlencode($data['transaction_id']);
        $refundType = urlencode($data['refunds_type']); // or 'Partial'
        $amount = $data['total']; // required if Partial.
        $memo = $data['refund_memo']; // required if Partial.
        $currencyID = urlencode($this->getCurrencyCode($data['orders_id'])); // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')

        // Add request-specific fields to the request string.
        $nvpStr = "&TRANSACTIONID=$transactionID&REFUNDTYPE=$refundType&CURRENCYCODE=$currencyID";

        if (strcasecmp($refundType, 'Partial') == 0) {
            if (!isset($amount)) {
                $log_data['callback_data'] = $amount . '(' . $refundType . ')';
                $log_data['error_data'] = "Partial Refund Amount is not specified.";
                $this->_addCallbackLog($log_data);
                exit('Partial Refund Amount is not specified.');
            } else {
                $nvpStr = $nvpStr . "&AMT=$amount";
            }

            if (!isset($memo)) {
                $log_data['callback_data'] = $memo . '(' . $refundType . ')';
                $log_data['error_data'] = "Partial Refund Memo is not specified.";
                $this->_addCallbackLog($log_data);
                exit('Partial Refund Memo is not specified.');
            }
        }

        if (!empty($memo)) {
            $nvpStr .= "&NOTE=$memo";
        }
        return $nvpStr;

    }

    /**
     * Send HTTP POST Request
     *
     * @param    string    The API method name
     * @param    string    The POST Message fields in &name=value pair format
     * @return    array    Parsed HTTP Response body
     */
    function PPHttpPost ($methodName_, $nvpStr_, $refunds_id)
    {

        // Set up your API credentials, PayPal end point, and API version.

        $version = urlencode('51.0');
        $log_data = array();
        $log_data['refunds_id'] = $refunds_id;
        // Set the curl parameters.

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_Endpoint);

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $config = include _SRV_WEBROOT."plugins/xt_paypal/conf/curl_config.php";
	    curl_setopt($ch, CURLOPT_SSLVERSION, $config["SSL_VERSION"] );
    	curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, $config["CIPHER_LIST"]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$this->API_Password&USER=$this->API_UserName&SIGNATURE=$this->API_Signature$nvpStr_";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $httpResponse = curl_exec($ch);

        if (!$httpResponse) {

            $log_data['callback_data'] = curl_error($ch) . '(' . curl_errno($ch) . ')';
            $log_data['error_data'] = "$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')';
            $this->_addCallbackLog($log_data);
            exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            if (sizeof($tmpAr) > 1) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {

            $log_data['callback_data'] = $nvpreq;
            $log_data['error_data'] = "Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.";
            $this->_addCallbackLog($log_data);
            exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
        }

        return $httpParsedResponseAr;
    }

    function _addCallbackLog ($log_data, $refunds_id = 0)
    {
        global $db;
        if (is_array($log_data['callback_data'])) $log_data['callback_data'] = serialize($log_data['callback_data']);
        if (is_array($log_data['error_data'])) $log_data['error_data'] = serialize($log_data['error_data']);
        if ($log_data['error_data'] == '') $log_data['error_data'] = '';
        //$log_data['created'] =  $db->DBTimeStamp(time());
        if ($refunds_id == 0) {
            $log_data['refunds_id'] = (int)$_GET['edit_id'];
        } else {
            $log_data['refunds_id'] = $refunds_id;
        }
        $where = ' refunds_id = ' . $db->Quote($log_data['refunds_id']);
        $db->AutoExecute($this->_table, $log_data, 'UPDATE', $where);
    }

    public function getTotal ($oID)
    {
        $o_refunds = new order($oID, -1);
        $o_data = $o_refunds->_getOrderTotalData($oID);

        return $o_refunds->order_total['total']['plain'];
    }

    public function getCurrencyCode ($oID)
    {
        $o_refunds = new order($oID, -1);
        $o_data = $o_refunds->_getOrderData($oID);
        return $o_data['currency_code'];
    }

    public function getOrders_id ($refunds_id)
    {
        global $db;
        $record = $db->Execute("SELECT orders_id FROM " . TABLE_PAYPAL_REFUNDS . " WHERE refunds_id = '" . (int)$refunds_id . "'");
        if ($record->RecordCount() > 0) {
            return $record->fields['orders_id'];
        }
        return 0;
    }

    public function getCallback_log_id ($id)
    {
        global $db;
        $record = $db->Execute("SELECT * FROM " . TABLE_PAYPAL_REFUNDS . " WHERE refunds_id = '" . (int)$id . "'");
        if ($record->RecordCount() > 0) {
            return $record->fields['callback_log_id'];
        }
        return 0;
    }

    function _getData ($callback_log_id)
    {
        global $xtPlugin, $db, $language;
        $new_refunds = array();
        $record = $db->Execute("SELECT orders_id,transaction_id FROM " . TABLE_CALLBACK_LOG . " WHERE id = '" . (int)$callback_log_id . "'");
        if ($record->RecordCount() > 0) {
            $new_refunds['callback_log_id'] = (int)$callback_log_id;
            $new_refunds['transaction_id'] = $record->fields['transaction_id'];
            $new_refunds['orders_id'] = $record->fields['orders_id'];
            $new_refunds['status'] = 1;
            $new_refunds['refunded'] = 0;
            $new_refunds['refund_memo'] = 'retoure';
            $new_refunds['refunds_type'] = 'Full';
            $new_refunds['total'] = $this->getTotal($new_refunds['orders_id']);
        }

        return $new_refunds;
    }

    public function _getRefunds ($callback_log_id)
    {
        global $db;
        $record = $db->Execute("SELECT refunds_id FROM " . TABLE_PAYPAL_REFUNDS . " WHERE callback_log_id = '" . (int)$callback_log_id . "' LIMIT 1");
        if ($record->RecordCount() > 0) {
            return $record->fields['refunds_id'];
        }

        return 0;
    }

    public function _isRefunded ($refunds_id)
    {
        global $db;
        $record = $db->Execute("SELECT refunds_id FROM " . TABLE_PAYPAL_REFUNDS . " WHERE refunded = 1 AND success = 1 AND refunds_id = '" . (int)$refunds_id . "' LIMIT 1");
        if ($record->RecordCount() > 0) {
            return true;
        }

        return false;
    }
}

?>