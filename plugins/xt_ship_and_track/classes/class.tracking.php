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

/**
 * Logik, DB-Zugriff, Tools
 */
class tracking
{
    protected function setTracking($orders_id, $shipperId, $trackinCodes, $sendMail, $statusId = 0)
    {
        global $db;

        $result = new stdClass();
        $result->success = false;

        if (!$statusId)
        {
            $statusId = $db->GetOne('SELECT `'.COL_TRACKING_STATUS_ID_PK.'` FROM '.TABLE_TRACKING_STATUS.' WHERE `'.COL_TRACKING_STATUS_CODE."`=0 AND `".COL_TRACKING_SHIPPER_ID. "`=?", array($shipperId) );
        }

        $insertData = array();
        foreach($trackinCodes as $code)
        {
            $insertData[] = array(
                COL_TRACKING_CODE => $code,
                COL_TRACKING_ORDER_ID => $orders_id,
                COL_TRACKING_STATUS_ID => $statusId,
                COL_TRACKING_SHIPPER_ID => $shipperId
            );
            $trackingId = $db->GetOne('SELECT `'.COL_TRACKING_ID_PK.'` FROM '.TABLE_TRACKING.' WHERE `'.COL_TRACKING_CODE."`=?", array($code));
            if ($trackingId)
            {
                $insertData[count($insertData)-1][COL_TRACKING_ID_PK] = $trackingId;
            }
            try {
                $db->AutoExecute(TABLE_TRACKING ,$insertData[count($insertData)-1]);
            } catch (exception $e) {
                $result->msg = $e->msg;
                return $result;
            }
        }
        if ($sendMail)
        {
            $trackingCodes = array();
            foreach($insertData as $v)
            {
                $trackingCodes[] = $v[COL_TRACKING_CODE];
            }
            $this->sendTrackingMail($orders_id,$trackingCodes);
        }
        $result->success = true;
        return $result;
    }


    public static function getTrackingForOrder($orders_id, $tracking_codes=array())
    {
        global $db;
        $trackings = array();

        $whereTracking = '';
        if ( ($size = count($tracking_codes))>0)
        {
            $whereTracking = ' AND `'.COL_TRACKING_CODE.'` IN (';
            for($i=0; $i<$size; $i++)
            {
                $whereTracking .= "'".$tracking_codes[$i]."'";
                if ($i<$size-1)
                {
                    $whereTracking .= ",";
                }
                else{
                    $whereTracking .= ")";
                }
            }
        }

        $rs = $db->Execute('SELECT * FROM '.VIEW_TRACKING.' t WHERE t.'.COL_TRACKING_ORDER_ID.'=? ' . $whereTracking, array($orders_id));
        if ($rs->RecordCount()>0)
        {
            while (!$rs->EOF) {
                $rs->fields[COL_SHIPPER_TRACKING_URL] = str_replace('[TRACKING_CODE]',$rs->fields[COL_TRACKING_CODE], $rs->fields[COL_SHIPPER_TRACKING_URL]);
                $trackings[] = $rs->fields;
                $rs->MoveNext();
            }
        }
        $rs->Close();
        return $trackings;
    }

    public static function getShippers($onlyActive = true)
    {
        global $db;
        $shippers = array();

        $whereActive = '';
        if ($onlyActive)
        {
            $whereActive = ' WHERE s.'.COL_SHIPPER_ENABLED.'=1';
        }
        $rs = $db->Execute('SELECT * FROM '.TABLE_SHIPPER.' s '.$whereActive.' ORDER BY '.COL_SHIPPER_NAME);
        if ($rs->RecordCount()>0)
        {
            while (!$rs->EOF) {
                $shippers[] = $rs->fields;
                $rs->MoveNext();
            }
        }
        return $shippers;
    }

    function sendTrackingMail($orderId, $tracking_codes=array())
    {
        global $language;

        $order = new order($orderId, -1);

        $customer = new customer($order->customer);
        $cGroup = $order->order_customer['customers_status'];
        $lang = empty($customer->customer_info['customers_default_language']) ?
            $language->code : $customer->customer_info['customers_default_language'];

        $shopId = $order->order_data['shop_id'];

        $tracking_infos = $this->getTrackingForOrder($orderId, $tracking_codes);

        $mailer = new xtMailer('tracking_links', $lang, $cGroup, -1, $shopId);
        $mailer->_addReceiver($order->order_customer['customers_email_address'],$order->order_customer['customers_email_address']);

        $mailer->_assign('customer',  $customer);
        $mailer->_assign('lang',  $lang);
        $mailer->_assign('tracking_infos',  $tracking_infos);

        $result = new stdClass();
        $result->success = false;
        if ($mailer->_sendMail())
        {
            $result->success = true;
        }
        else{
            $result->errorMsg = $mailer->ErrorInfo ? $mailer->ErrorInfo : TEXT_FAILURE;
            $result->msg = $mailer->ErrorInfo ? $mailer->ErrorInfo : TEXT_FAILURE;
            error_log('Failed to send tracking codes for ' . $order->order_customer['customers_email_address']);
        }

        return $result;
    }
}