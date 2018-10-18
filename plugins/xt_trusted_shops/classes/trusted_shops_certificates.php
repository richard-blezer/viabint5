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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/constants.php';

class trusted_shops_certificates
{
    public $position;

    protected $_table = TABLE_TS_CERTIFICATES;
    protected $_master_key = COL_TS_CERTS_ID;

    public function refreshCertStatus($data)
    {
        if ($this->position != 'admin') die('admin required');

        $sql_where = "";
        global $filter, $db;
        if ($data[TS_PARAM_CERT_KEY])
        {
            $sql_where = COL_TS_CERTS_KEY."='". $filter->_quote($data[TS_PARAM_CERT_KEY])."'";
        }
        else if ($data[TS_PARAM_CERT_ID])
        {
            $id = intval($data[TS_PARAM_CERT_ID]);
            if (!$id) die ('no id');
            $sql_where = COL_TS_CERTS_ID."='". $filter->_quote($data[TS_PARAM_CERT_ID])."'";
        }

        $dbr = new adminDB_DataRead($this->_table, '', '', $this->_master_key, $sql_where);
        $certs = $dbr->getData();
        foreach($certs as $k => $cert)
        {
            $resp = $this->readStatus($cert[COL_TS_CERTS_KEY]);
            $r = json_decode($resp);
            if ($r->status == TS_SUCCESS)
            {
                $cert = array(
                    COL_TS_CERTS_ID =>                  $cert[COL_TS_CERTS_ID],
                    COL_TS_CERTS_KEY =>                 $cert[COL_TS_CERTS_KEY],
                    COL_TS_CERTS_STATUS =>               $r->status,
                    COL_TS_CERTS_RATING_ENABLED =>       $r->result->ratingEnabled,
                    COL_TS_CERTS_PROTECTION_ITEMS =>     json_encode($r->result->protectionItems),
                    COL_TS_CERTS_STATE =>                $r->result->certificateState,
                    COL_TS_CERTS_TYPE =>                 $r->result->certificateType,
                    COL_TS_CERTS_WSLOGIN =>              $r->result->wsLoginOK,
                    COL_TS_CERTS_CURRENCIES =>           json_encode($r->result->supportedCurrencies),
                    COL_TS_CERTS_URL =>                  $r->result->url,
                    COL_TS_CERTS_LANG =>                $r->result->shopLanguage
                );
            }
            else {
                $cert = array(
                    COL_TS_CERTS_ID =>                  $cert[COL_TS_CERTS_ID],
                    COL_TS_CERTS_KEY =>                 $cert[COL_TS_CERTS_KEY],
                    COL_TS_CERTS_STATUS =>               $r->status,
                    COL_TS_CERTS_RATING_ENABLED =>       false,
                    COL_TS_CERTS_PROTECTION_ITEMS =>     '{}',
                    COL_TS_CERTS_STATE =>                '',
                    COL_TS_CERTS_TYPE =>                 '',
                    COL_TS_CERTS_WSLOGIN =>              '',
                    COL_TS_CERTS_CURRENCIES =>           '{}',
                    COL_TS_CERTS_URL =>                  '',
                    COL_TS_CERTS_LANG =>                 ''
                );
            }

            $sql = "UPDATE ".TABLE_TS_CERTIFICATES." SET";
            $i=0;
            foreach($cert as $k => $v)
            {
                $sql.= " `$k`='$v'";
                if ($i < sizeof($cert)-1)
                {
                    $sql.= ",";
                }
                $i++;
            }
            $sql.= " WHERE ".COL_TS_CERTS_ID."='".$cert[COL_TS_CERTS_ID]."'";

            $db->Execute($sql);
            //$dbs = new adminDB_DataSave($this->_table, $cert);
            //$dbs->saveDataSet();
        }
    }

    private function readStatus($certKey)
    {
        $url = TS_URL_SRVC_SHOP_STATUS . $certKey . "/ts_classic/ts_classic";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $r = curl_exec($ch);
        curl_close($ch);

        return $r;
    }

    public static function mapPaymentMethod($payment_code)
    {
        switch ($payment_code)
        {
            case 'xt_payments':
                return 'OTHER';
                break;
            case 'xt_amazon_checkout':
            case 'shs_amazon_payments':
                return 'AMAZON_PAYMENTS';
                break;
            case 'xt_invoice':
            case 'xt_billpay':
            case 'vt_billsafe':
            case 'xt_klarna':
                return 'INVOICE';
                break;
            case 'PiRatepayPayment':
            case 'expokredit':
            case 'vt_santander':
                return 'FINANCING';
                break;
            case 'xt_saferpay':
            case 'pay_ogone':
            case 'pay_paymentpartner':
            case 'xt_qenta':
            case 'pay_ipayment':
                return 'CREDIT_CARD';
                break;
            case 'xt_moneybookers':
                return 'MONEYBOOKERS';
                break;
            case 'xt_paypal':
                return 'PAYPAL';
                break;
            case 'xt_sofortueberweisung':
                return 'DIRECT_E_BANKING';
                break;
            case 'xt_clickandbuy':
                return 'CLICKANDBUY';
                break;
            case 'xt_cashondelivery':
                return 'CASH_ON_DELIVERY';
                break;
            case 'xt_cashpayment':
                return 'CASH_ON_PICKUP';
                break;
            case 'xt_prepayment':
                return 'PREPAYMENT';
                break;
            case 'xt_banktransfer':
                return 'DIRECT_DEBIT';
                break;
            default:
                return 'OTHER';
                break;
        }
    }

} 