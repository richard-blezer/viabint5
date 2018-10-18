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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/IShippmentTracker.php';
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/api/HermesException.php';

/* check for SoapClient */
if (!class_exists('SoapClient')) {
	$message = "php Soap library not installed ";
	echo $message;
	$log_data = array();
	$log_data['message'] = $message;
	global $logHandler;
	$logHandler->_addLog('error','xt_ship_and_track','',$log_data);
	die('missing Soap Library');
 }
 
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/api/ProPSService.php';

class Hermes implements IShippmentTracker
{
    const API_VERSION = '1.5';

    private static $_DEBUG = true;

    private $_apiPartner = false;
    private $_apiPwd = false;
    private $_userToken = false;

    private $_user = false;
    private $_pwd = false;

    private $_svc = false;

    private $_userProducts = false;

    public function __construct($apiPartner, $apiPwd, $userToken = false, $sandbox = false)
    {
        $svc = new ProPSService($apiPartner, $apiPwd, $userToken,  $sandbox);
        if ($userToken==false)
        {
        $param = new propsCheckAvailability();
        $r = $svc->propsCheckAvailability($param);
        self::__log("Hermes API ".$r->propsCheckAvailabilityReturn);
        }

        $this->_apiPartner = $apiPartner;
        $this->_apiPwd = $apiPwd;
        $this->_userToken = $userToken;

        $this->_svc = $svc;
    }

    public function getUserToken()
    {
        return $this->_userToken;
    }

    public function login($user, $pwd)
    {
        if ( !$this->_svc || !is_object($this->_svc)) return false;

        if ($this->_userToken) return true;

        $param = new propsUserLogin(new HermesLogin($user, $pwd));
        $r = $this->_svc->propsUserLogin($param);

        $this->_userToken = $r->propsUserLoginReturn;
        $this->_user = $user;
        $this->_pwd = $pwd;

        return true;
    }

    public function getUserProducts()
    {
        if ( !$this->_svc || !is_object($this->_svc)) return false;

        $param = new propsListOfProductsATG();
        $r = $this->_svc->propsListOfProductsATG($param);

        $this->_userProducts = $r->propsListOfProductsATGReturn->products->ProductWithPrice;

        return $this->_userProducts;
    }

    public function readShipmentStatus($orderNo, $shippingId = false)
    {
        if (!$shippingId)
        {
            $order = $this->getOrder($orderNo, $shippingId ? $shippingId : '');
        }
        // else TODO
        return $order->propsGetPropsOrderReturn->status;
    }

    public function printLabelPdf($orderNo, $printPosition)
    {
        $param = new propsOrderPrintLabelPdf($orderNo, $printPosition);
        $r = $this->_svc->propsOrderPrintLabelPdf($param);

        return $r->propsOrderPrintLabelPdfReturn->pdfData;
    }

    public function printLabelsPdf($orderNos)
    {
        $param = new propsOrdersPrintLabelsPdf(new RequestedOrderNumbers($orderNos));
        $r = $this->_svc->propsOrdersPrintLabelsPdf($param);

        return $r->propsOrdersPrintLabelsPdfReturn->pdfData;
    }

    public function printLabelJpeg($orderNo, $printPosition)
    {
        $param = new propsOrderPrintLabelJpeg($orderNo, $printPosition);
        $r = $this->_svc->propsOrderPrintLabelJpeg($param);

        return $r->propsOrderPrintLabelJpegReturn->jpegData;
    }

    public function getOrder($orderNo, $shippingId = '')
    {
        if ( !$this->_svc || !is_object($this->_svc)) return false;

        $param = new propsGetPropsOrder($orderNo, $shippingId);
        $r = $this->_svc->propsGetPropsOrder($param);

        return $r;
    }

    public function getOrders(PropsOrderSearchCriteria $sc = null)
    {
        if ( !$this->_svc || !is_object($this->_svc)) return false;

        if ($sc == null)
        {
            $sc = new PropsOrderSearchCriteria(
                null, //identNo
                null, //orderNo
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
        }
        $param = new propsGetPropsOrders($sc);
        $r = $this->_svc->propsGetPropsOrders($param);

        if (is_array($r->propsGetPropsOrdersReturn->orders->PropsOrderShort))
            return $r->propsGetPropsOrdersReturn->orders->PropsOrderShort;
        else
            return array($r->propsGetPropsOrdersReturn->orders->PropsOrderShort);
    }

    public  function createOrder(HermesAddress $receiver,
                                 $refNo = '', $parcelClass = '',
                                 $includeCashOnDelivery = false, $amountCashOnDeliveryEurocent = 0,
                                 $withBulkGoods = false,
                                 $orderNo = '')
    {
        if ( !$this->_svc || !is_object($this->_svc)) return false;

        $order = new PropsOrder($orderNo, $receiver, $refNo, $parcelClass, $amountCashOnDeliveryEurocent, $includeCashOnDelivery, $withBulkGoods);
        $orderSave = new PropsOrderSave($order);
        $r = $this->_svc->propsOrderSave($orderSave);

        return $r->propsOrderSaveReturn;
    }

    public function deleteOrder($orderNo)
    {
        $param = new propsOrderDelete($orderNo);
        $r = $this->_svc->propsOrderDelete($param);

        return $r->propsOrderDeleteReturn;
    }

    public function requestCollection($date, $xs, $s, $m, $l, $xl, $xl_bulk)
    {
        $param = new PropsCollectionRequest( new PropsCollectionOrder($date, $l, $m, $s, $xl, $xl_bulk, $xs));
        $r = $this->_svc->propsCollectionRequest($param);

        return $r->propsCollectionRequestReturn;
    }

    public function cancelCollection($date)
    {
        $param = new propsCollectionCancel($date);
        $r = $this->_svc->propsCollectionCancel($param);

        return $r->propsCollectionCancelReturn;
    }


    // DEBUG
    public static function setDebug(bool $d)
    {
        self::$_DEBUG = $d;
    }

    private static function __log($msg)
    {
        if (self::$_DEBUG)
        {
            error_log($msg);
        }
    }

    private static function __logHermesException(HermesException $he)
    {
        if (self::$_DEBUG)
        {
            error_log($he->getHermesMessage());
        }
    }
}