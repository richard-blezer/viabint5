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
 # @version $Id: class.xt_trusted_shops.php 6060 2013-03-14 13:10:33Z mario $
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


class xt_trusted_shops {
	
	function _validID($id) {
		if (strlen($id)!=33) return false;
		if (substr($id,0,1)!='X') return false;
		return true;	
	}
	
	function _display() {
		global $xtPlugin, $xtLink, $db,$success_order;

		global $language;

		if ($language->code=='de') {
			$shop_id = XT_TRUSTED_SHOPS_KEY_DE;
			$tpl = 'ts_money_back_de.html';
		}
		if ($language->code=='en') {
			$shop_id = XT_TRUSTED_SHOPS_KEY_EN;
			$tpl = 'ts_money_back_en.html';
		}
		if ($language->code=='fr') {
			$shop_id = XT_TRUSTED_SHOPS_KEY_FR;
			$tpl = 'ts_money_back_fr.html';
		}
		
		if (!$this->_validID($shop_id)) return false;

		if (!is_array($success_order->order_data) || $shop_id=='') return false;

		$data = $success_order->order_data;

		$form_fields = array();
		$form_fields['email'] = $data['customers_email_address'];
    $payment = $data['payment_code'];
    $payment_type=$this->_getPaymentMethod($payment);
    $form_fields['paymentType']=$payment_type;
		$form_fields['amount'] = $success_order->order_total['total']['plain'];
		$form_fields['curr'] = $data['currency_code'];
		$form_fields['KDNR'] = $data['customers_id'];
		$form_fields['ORDERNR'] = $data['orders_id'];

		$tpl_data = array('_form_fields'=>$form_fields,'shop_id'=>$shop_id);

		$tmp_data = '';
		$template = new Template();
		$template->getTemplatePath($tpl, 'xt_trusted_shops', '', 'plugin');

		$tmp_data = $template->getTemplate('xt_trusted_shops_smarty', $tpl, $tpl_data);
		return $tmp_data;

	}
    
    private function _getPaymentMethod($payment_code) {
        
        switch ($payment_code) {
            
            case 'xt_invoice':
            case 'xt_billpay':
                return 'INVOICE';
                break;
            case 'xt_saferpay':
            case 'pay_ogone':
            case 'pay_paymentpartner':
            case 'xt_qenta':
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
            case 'xt_prepayment': 
                return 'PREPAYMENT';
                break;
            case 'xt_banktransfer':
                return 'DIRECT_DEBIT';
            default:
                return 'OTHER';
                break;
              
        }
        
    }

}
?>