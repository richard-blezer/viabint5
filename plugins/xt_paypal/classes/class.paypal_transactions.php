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
 # @version $Id: class.paypal_transactions.php 5382 2012-07-23 14:03:53Z tu $
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


class paypal_transactions
{

    protected $_table = TABLE_CALLBACK_LOG;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'id';
    protected $log_callback_data = true;
    protected $ip = array();
    protected $logFile = 'callback.txt';

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

        $header['orders_id'] = array('disabled' => 'true');
        $header['transaction_id'] = array('disabled' => 'true');
        $header['created'] = array('disabled' => 'true');
        $header['class'] = array('type' => 'hidden', 'disabled' => 'true');
        $header['error_msg'] = array('type' => 'hidden', 'disabled' => 'true');
        $header['module'] = array('type' => 'hidden', 'disabled' => 'true');

        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;

        $params['SortField'] = $this->_master_key;
        $params['SortDir'] = "DESC";

        $params['display_newBtn'] = false;
        $params['display_deleteBtn'] = false;
        $params['display_editBtn'] = true;

        $rowActions[] = array('iconCls' => 'xt_paypal_refunds', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PAYPAL_REFUNDS);
        if ($this->url_data['edit_id'])
            $js = "var edit_id = " . $this->url_data['edit_id'] . ";";
        else
            $js = "var edit_id = record.id;";
        $js .= "addTab('adminHandler.php?plugin=xt_paypal&load_section=paypal_refunds&pg=overview&new=true&callback_log_id='+edit_id,'" . TEXT_PAYPAL_REFUNDS . " ' +edit_id, 'Refund_TransID_'+edit_id)";
        $rowActionsFunctions['xt_paypal_refunds'] = $js;

        if ($this->url_data['pg'] == 'overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true) {
            $params['include'] = array('orders_id', 'transaction_id', 'created');
        } else {
            $params['exclude'] = array('module', 'error_msg', 'class', 'error_data', 'callback_data');
        }

        $params['rowActions'] = $rowActions;
        $params['rowActionsFunctions'] = $rowActionsFunctions;

        return $params;
    }

    function _get ($ID = 0)
    {
        global $xtPlugin, $db, $language;

        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        $ID = (int)$ID;

        $sql_where = ' (module = "xt_paypal" OR module = "xt_paypal_installments")   AND orders_id !=0 AND transaction_id != "" AND class NOT LIKE "callback_data_paypal_refund"';
        require_once(_SRV_WEBROOT . "plugins/xt_paypal/classes/class.paypalpost.php");

        if (count($where_ar) > 0) {
            $where1 = " AND " . implode(" AND ", $where_ar);
            $sql_where = $sql_where . $where1;
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where);


        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID) {
            $data = $table_data->getData($ID);

            if (strlen($data[0]['callback_data']) > 0) {
                $callback_data = unserialize($data[0]['callback_data']);
                $callback = array();
                if (is_array($callback_data)) {
                    foreach ($callback_data as $key => $val) {
                        define('TEXT_DATA_' . strtoupper($key), $key);
                        $callback['data_' . $key] = $val;
                    }

                    unset($data[0]['callback_data']);
                    $data[0] = array_merge($data[0], $callback);
                }
            }
            if (strlen($data[0]['error_data']) > 0) {
                $callback_data = unserialize($data[0]['data_error']);
                $callback = array();
                if (is_array($callback_data)) {
                    foreach ($callback_data as $key => $val) {
                        define('TEXT_ERROR_' . strtoupper($key), $key);
                        $callback['error_' . $key] = $val;
                    }
                    unset($data[0]['data_error']);
                    $data[0] = array_merge($data[0], $callback);
                }
            }

        } else {
            $data = $table_data->getHeader();
        }

        $obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;
    }

    function _set ($ID = 0)
    {

        $obj = new stdClass;
        $obj->success = true;
        return $obj;

    }

}

?>