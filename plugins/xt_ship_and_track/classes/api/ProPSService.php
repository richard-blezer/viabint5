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



include_once('propsGetCollectionOrdersResponse.php');
include_once('propsOrderDelete.php');
include_once('propsOrderDeleteResponse.php');
include_once('propsCollectionRequest.php');
include_once('propsCollectionRequestResponse.php');
include_once('propsOrderPrintLabelPdf.php');
include_once('propsOrderPrintLabelPdfResponse.php');
include_once('propsCollectionCancel.php');
include_once('propsCollectionCancelResponse.php');
include_once('propsOrderSaveAndPrintLabelPdf.php');
include_once('propsOrderSaveAndPrintLabelPdfResponse.php');
include_once('init.php');
include_once('initResponse.php');
include_once('propsGetPropsOrder.php');
include_once('propsGetPropsOrderResponse.php');
include_once('propsOrderSave.php');
include_once('propsOrderSaveResponse.php');
include_once('propsOrdersPrintLabelsPdf.php');
include_once('propsOrdersPrintLabelsPdfResponse.php');
include_once('propsUserLogin.php');
include_once('propsUserLoginResponse.php');
include_once('propsOrderPrintLabelJpeg.php');
include_once('propsOrderPrintLabelJpegResponse.php');
include_once('propsListOfProductsATG.php');
include_once('propsListOfProductsATGResponse.php');
include_once('propsReadShipmentStatus.php');
include_once('propsReadShipmentStatusResponse.php');
include_once('propsGetPropsOrders.php');
include_once('propsGetPropsOrdersResponse.php');
include_once('propsProductlnformation.php');
include_once('propsProductlnformationResponse.php');
include_once('propsCheckAvailability.php');
include_once('propsCheckAvailabilityResponse.php');
include_once('propsOrderSaveAndPrintLabelJpeg.php');
include_once('propsOrderSaveAndPrintLabelJpegResponse.php');
include_once('destroy.php');
include_once('destroyResponse.php');
include_once('propsImportOrders.php');
include_once('propsImportOrdersResponse.php');
include_once('propsGetCollectionOrders.php');
include_once('ListOfPropsCollectionOrders.php');
include_once('PropsCollectionOrderLong.php');
include_once('PropsCollectionOrder.php');
include_once('ShippingLabelPdf.php');
include_once('PropsOrder.php');
include_once('PropsOrderLong.php');
include_once('RequestedOrderNumbers.php');
include_once('MultipleShippingLabelsPdf.php');
include_once('OrderResponse.php');
include_once('ShippingLabelJpeg.php');
include_once('ListOfProducts.php');
include_once('ProductWithPrice.php');
include_once('ProductInfo.php');
include_once('ParcelFormat.php');
include_once('DeliveryDestination.php');
include_once('ServiceCharge.php');
include_once('ShippingStatus.php');
include_once('PropsOrderSearchCriteria.php');
include_once('ListOfPropsOrders.php');
include_once('PropsOrderShort.php');
include_once('ListOfProductInfos.php');
include_once('RequestedOrders.php');
include_once('ImportResult.php');
include_once('HermesAddress.php');
include_once('ServiceException.php');
include_once('ExceptionItem.php');
include_once('HermesLogin.php');

include_once('HermesException.php');


/**
 *
 */
class ProPSService extends SoapClient
{

    const API_URL           = 'https://hermesapi.hlg.de/hermes-api-props-web/services/v15/ProPS';
    const API_URL_SANDBOX   = 'https://hermesapisbx.hlg.de/hermes-api-props-web/services/v15/ProPS';
    const NS_HEADER = "http://props.hermes_api.service.hlg.de";

    private $apiPartnerId = "apiPartnerEmpty";
    private $apiPartnerPwd = "apiPasswordEmpty";
    private $userToken = "";

    private static $_DEBUG = true;

    /**
     *
     * @var array $classmap The defined classes
     * @access private
     */
    private static $classmap = array(
        'propsGetCollectionOrdersResponse' => 'propsGetCollectionOrdersResponse',
        'propsOrderDelete' => 'propsOrderDelete',
        'propsOrderDeleteResponse' => 'propsOrderDeleteResponse',
        'propsCollectionRequest' => 'propsCollectionRequest',
        'propsCollectionRequestResponse' => 'propsCollectionRequestResponse',
        'propsOrderPrintLabelPdf' => 'propsOrderPrintLabelPdf',
        'propsOrderPrintLabelPdfResponse' => 'propsOrderPrintLabelPdfResponse',
        'propsCollectionCancel' => 'propsCollectionCancel',
        'propsCollectionCancelResponse' => 'propsCollectionCancelResponse',
        'propsOrderSaveAndPrintLabelPdf' => 'propsOrderSaveAndPrintLabelPdf',
        'propsOrderSaveAndPrintLabelPdfResponse' => 'propsOrderSaveAndPrintLabelPdfResponse',
        'init' => 'init',
        'initResponse' => 'initResponse',
        'propsGetPropsOrder' => 'propsGetPropsOrder',
        'propsGetPropsOrderResponse' => 'propsGetPropsOrderResponse',
        'propsOrderSave' => 'propsOrderSave',
        'propsOrderSaveResponse' => 'propsOrderSaveResponse',
        'propsOrdersPrintLabelsPdf' => 'propsOrdersPrintLabelsPdf',
        'propsOrdersPrintLabelsPdfResponse' => 'propsOrdersPrintLabelsPdfResponse',
        'propsUserLogin' => 'propsUserLogin',
        'propsUserLoginResponse' => 'propsUserLoginResponse',
        'propsOrderPrintLabelJpeg' => 'propsOrderPrintLabelJpeg',
        'propsOrderPrintLabelJpegResponse' => 'propsOrderPrintLabelJpegResponse',
        'propsListOfProductsATG' => 'propsListOfProductsATG',
        'propsListOfProductsATGResponse' => 'propsListOfProductsATGResponse',
        'propsReadShipmentStatus' => 'propsReadShipmentStatus',
        'propsReadShipmentStatusResponse' => 'propsReadShipmentStatusResponse',
        'propsGetPropsOrders' => 'propsGetPropsOrders',
        'propsGetPropsOrdersResponse' => 'propsGetPropsOrdersResponse',
        'propsProductlnformation' => 'propsProductlnformation',
        'propsProductlnformationResponse' => 'propsProductlnformationResponse',
        'propsCheckAvailability' => 'propsCheckAvailability',
        'propsCheckAvailabilityResponse' => 'propsCheckAvailabilityResponse',
        'propsOrderSaveAndPrintLabelJpeg' => 'propsOrderSaveAndPrintLabelJpeg',
        'propsOrderSaveAndPrintLabelJpegResponse' => 'propsOrderSaveAndPrintLabelJpegResponse',
        'destroy' => 'destroy',
        'destroyResponse' => 'destroyResponse',
        'propsImportOrders' => 'propsImportOrders',
        'propsImportOrdersResponse' => 'propsImportOrdersResponse',
        'propsGetCollectionOrders' => 'propsGetCollectionOrders',
        'ListOfPropsCollectionOrders' => 'ListOfPropsCollectionOrders',
        'PropsCollectionOrderLong' => 'PropsCollectionOrderLong',
        'PropsCollectionOrder' => 'PropsCollectionOrder',
        'ShippingLabelPdf' => 'ShippingLabelPdf',
        'PropsOrder' => 'PropsOrder',
        'PropsOrderLong' => 'PropsOrderLong',
        'RequestedOrderNumbers' => 'RequestedOrderNumbers',
        'MultipleShippingLabelsPdf' => 'MultipleShippingLabelsPdf',
        'OrderResponse' => 'OrderResponse',
        'ShippingLabelJpeg' => 'ShippingLabelJpeg',
        'ListOfProducts' => 'ListOfProducts',
        'ProductWithPrice' => 'ProductWithPrice',
        'ProductInfo' => 'ProductInfo',
        'ParcelFormat' => 'ParcelFormat',
        'DeliveryDestination' => 'DeliveryDestination',
        'ServiceCharge' => 'ServiceCharge',
        'ShippingStatus' => 'ShippingStatus',
        'PropsOrderSearchCriteria' => 'PropsOrderSearchCriteria',
        'ListOfPropsOrders' => 'ListOfPropsOrders',
        'PropsOrderShort' => 'PropsOrderShort',
        'ListOfProductInfos' => 'ListOfProductInfos',
        'RequestedOrders' => 'RequestedOrders',
        'ImportResult' => 'ImportResult',
        'Address' => 'HermesAddress',
        'ServiceException' => 'ServiceException',
        'ExceptionItem' => 'ExceptionItem',
        'HermesLogin' => 'HermesLogin');

    /**
     *
     * @param array $config A array of config values
     * @param string $wsdl The wsdl file to use
     * @access public
     */
    public function __construct($apiPartnerId, $apiPartnerPwd, $userToken, $sandbox = false)
    {
        $this->apiPartnerId = $apiPartnerId;
        $this->apiPartnerPwd = $apiPartnerPwd;
        $this->userToken = $userToken != false ? $userToken : "";

        $wsdlDir = dirname(__FILE__);
        $wsdl = $wsdlDir . '/ProPS.wsdl';

        $options = array();
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }

        // ermittlung response header
        $options['trace'] = 1;

        // fehler werfen
        $options['exceptions'] = 1;

        // non-WSDL mode
        /*
        $options['location'] = XT_HERMES_API_URL;
        $options['uri'] = NS_HEADER;
        $wsdl = null;
        */

        // endpoint
        $apiUrl = $sandbox ? self::API_URL_SANDBOX : self::API_URL;
        $this->__setLocation($apiUrl);

        // default soap headers api 1.4
        /*
        $shPartner = new SoapHeader(self::NS_HEADER, "PartnerId", $apiPartnerId);
        $shPwd = new SoapHeader(self::NS_HEADER, "PartnerPwd", $apiPartnerPwd);
        $shPartnerToken = new SoapHeader(self::NS_HEADER, "PartnerToken", '');
        $sh = new SoapHeader(self::NS_HEADER, "UserToken", '');
        $this->__setSoapHeaders(array($shPwd,$shPartner,$shPartnerToken,$sh));
        */
        $this->__setSoapHeaders(array());

        parent::__construct($wsdl, $options);
    }

    public function __soapCall ($function_name, $arguments, $options = NULL, $input_headers = NULL, &$output_headers = NULL)
    {
        try
        {
            $r = parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);
        }
        catch(Soapfault $e)
        {
            $request = self::__getLastRequest();
            $response = self::__getLastResponse();
            $he = new HermesException($e, $function_name);
            self::__logHermesException($he);
            throw $he;
        }
        catch(Exception $e)
        {
            $he = new HermesException($e, $function_name);
            self::__logHermesException($he);
            throw $he;
        }

        return $r;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = NULL)
    {
        /*
         * $request is a XML string representation of the SOAP request
         * that can e.g. be loaded into a DomDocument to make it modifiable.
         */
        $domRequest = new DOMDocument();
        $a = $domRequest->saveXML();
        $domRequest->loadXML($request);
        $a = $domRequest->saveXML();

        // modify XML using the DOM API, e.g. get the <s:Header>-tag
        // and add your custom headers
        $xp = new DOMXPath($domRequest);
        $xp->registerNamespace('SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');
        // fails if no <s:Header> is found - error checking needed
        $header = $xp->query('/SOAP-ENV:Envelope/SOAP-ENV:Header')->item(0);

        // now add your custom header

        $security = $domRequest->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:Security');
        $security->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:wsu', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd');


        $usernameToken = $domRequest->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:UsernameToken');
        $usernameToken->setAttribute('wsu:Id', "UsernameToken-102");
        $username = $domRequest->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:Username', $this->apiPartnerId);
        $password = $domRequest->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'wsse:Password', $this->apiPartnerPwd);
        $password->setAttribute("Type","http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");
        $usernameToken->appendChild($username);
        $usernameToken->appendChild($password);

        $security->appendChild($usernameToken);
        $header->appendChild($security);

        $userToken = $domRequest->createElementNS("http://hermes_api.service.hlg.de",'her:UserToken',$this->userToken);
        $header->appendChild($userToken);

        $request = $domRequest->saveXML();

        return parent::__doRequest($request, $location, $action, $version) ;
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

    /**
     *
     * @param propsGetCollectionOrders $parameters
     * @access public
     */
    public function propsGetCollectionOrders(propsGetCollectionOrders $parameters)
    {
        return $this->__soapCall('propsGetCollectionOrders', array($parameters));
    }

    /**
     *
     * @param propsOrderDelete $parameters
     * @access public
     */
    public function propsOrderDelete(propsOrderDelete $parameters)
    {
        return $this->__soapCall('propsOrderDelete', array($parameters));
    }

    /**
     *
     * @param propsCollectionRequest $parameters
     * @access public
     */
    public function propsCollectionRequest(propsCollectionRequest $parameters)
    {
        return $this->__soapCall('propsCollectionRequest', array($parameters));
    }

    /**
     *
     * @param propsOrderPrintLabelPdf $parameters
     * @access public
     */
    public function propsOrderPrintLabelPdf(propsOrderPrintLabelPdf $parameters)
    {
        return $this->__soapCall('propsOrderPrintLabelPdf', array($parameters));
    }

    /**
     *
     * @param propsCollectionCancel $parameters
     * @access public
     */
    public function propsCollectionCancel(propsCollectionCancel $parameters)
    {
        return $this->__soapCall('propsCollectionCancel', array($parameters));
    }

    /**
     *
     * @param propsOrderSaveAndPrintLabelPdf $parameters
     * @access public
     */
    public function propsOrderSaveAndPrintLabelPdf(propsOrderSaveAndPrintLabelPdf $parameters)
    {
        return $this->__soapCall('propsOrderSaveAndPrintLabelPdf', array($parameters));
    }

    /**
     *
     * @param init $parameters
     * @access public
     */
    public function init(init $parameters)
    {
        return $this->__soapCall('init', array($parameters));
    }

    /**
     *
     * @param propsGetPropsOrder $parameters
     * @access public
     */
    public function propsGetPropsOrder(propsGetPropsOrder $parameters)
    {
        return $this->__soapCall('propsGetPropsOrder', array($parameters));
    }

    /**
     *
     * @param propsOrderSave $parameters
     * @access public
     */
    public function propsOrderSave(propsOrderSave $parameters)
    {
        return $this->__soapCall('propsOrderSave', array($parameters));
    }

    /**
     *
     * @param propsOrdersPrintLabelsPdf $parameters
     * @access public
     */
    public function propsOrdersPrintLabelsPdf(propsOrdersPrintLabelsPdf $parameters)
    {
        return $this->__soapCall('propsOrdersPrintLabelsPdf', array($parameters));
    }

    /**
     *
     * @param propsUserLogin $parameters
     * @access public
     */
    public function propsUserLogin(propsUserLogin $parameters)
    {
        $r = $this->__soapCall('propsUserLogin', array($parameters));
        if ($r->propsUserLoginReturn)
        {
            $this->userToken = preg_replace('/\s+/', ' ', trim($r->propsUserLoginReturn));
        }
        return $r;
    }

    /**
     *
     * @param propsOrderPrintLabelJpeg $parameters
     * @access public
     */
    public function propsOrderPrintLabelJpeg(propsOrderPrintLabelJpeg $parameters)
    {
        return $this->__soapCall('propsOrderPrintLabelJpeg', array($parameters));
    }

    /**
     *
     * @param propsListOfProductsATG $parameters
     * @access public
     */
    public function propsListOfProductsATG(propsListOfProductsATG $parameters)
    {
        return $this->__soapCall('propsListOfProductsATG', array($parameters));
    }

    /**
     *
     * @param propsReadShipmentStatus $parameters
     * @access public
     */
    public function propsReadShipmentStatus(propsReadShipmentStatus $parameters)
    {
        return $this->__soapCall('propsReadShipmentStatus', array($parameters));
    }

    /**
     *
     * @param propsGetPropsOrders $parameters
     * @access public
     */
    public function propsGetPropsOrders(propsGetPropsOrders $parameters)
    {
        return $this->__soapCall('propsGetPropsOrders', array($parameters));
    }

    /**
     *
     * @param propsProductlnformation $parameters
     * @access public
     */
    public function propsProductlnformation(propsProductlnformation $parameters)
    {
        return $this->__soapCall('propsProductlnformation', array($parameters));
    }

    /**
     *
     * @param propsCheckAvailability $parameters
     * @access public
     */
    public function propsCheckAvailability(propsCheckAvailability $parameters)
    {
        return $this->__call('propsCheckAvailability', array($parameters));
    }

    /**
     *
     * @param propsOrderSaveAndPrintLabelJpeg $parameters
     * @access public
     */
    public function propsOrderSaveAndPrintLabelJpeg(propsOrderSaveAndPrintLabelJpeg $parameters)
    {
        return $this->__soapCall('propsOrderSaveAndPrintLabelJpeg', array($parameters));
    }

    /**
     *
     * @param destroy $parameters
     * @access public
     */
    public function destroy(destroy $parameters)
    {
        return $this->__soapCall('destroy', array($parameters));
    }

    /**
     *
     * @param propsImportOrders $parameters
     * @access public
     */
    public function propsImportOrders(propsImportOrders $parameters)
    {
        return $this->__soapCall('propsImportOrders', array($parameters));
    }

}
