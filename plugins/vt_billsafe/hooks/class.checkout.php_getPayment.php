<?php
/*
 ##############################################################################
 #	Plugin for xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 # @version $Id: class.paymentCheck.php 1613 2010-09-16 12:00:00Z aa $
 # @copyright:   found in /lic/copyright.txt
 #
 ##############################################################################
 */

	defined('_VALID_CALL') or die('Direct Access is not allowed.');

	global $page, $currency;

// check if billsafe is enabled
if (isset($payment_data['vt_billsafe'])) {

	if($page->page_action == 'payment') {
		require_once _SRV_WEBROOT.'plugins/vt_billsafe/classes/class.SoapApi.php';
		$SoapApi = new SoapApi();

		$response = $SoapApi->prevalidateOrder($_SESSION, $currency);

        $_SESSION['show_billsafe_INV']='1';
        $_SESSION['show_billsafe_RAT']='1';

        // clear installments
        $_SESSION['billsafe_installmentAmount']='';
        $_SESSION['billsafe_installmentCount']='';
        $_SESSION['billsafe_processingFee']='';
        $_SESSION['billsafe_annualPercentageRate']='';

        if ($response->ack == 'ERROR') {
            global $logHandler;
            $log_data = array();
            $log_data['message'] = $response->errorList;
            $logHandler->_addLog('error','vt_billsafe','',$log_data);
        } else {

            // invoice available ?
            if ($response->invoice->isAvailable != true || $_SESSION['BILLSAFEDecline'] === true || VT_BILLSAFE_ACTIVATE_INVOICE=='false') {
                $_SESSION['show_billsafe_INV']='0';
            }

            // rate pay available ?
            if ($response->hirePurchase->isAvailable != true || $_SESSION['BILLSAFEDecline'] === true || VT_BILLSAFE_ACTIVATE_INSTALLMENTS=='false') {
                // dont show rate pay
                $_SESSION['show_billsafe_RAT']='0';
            } else {
                $_SESSION['billsafe_installmentAmount']=$response->hirePurchase->installmentAmount;
                $_SESSION['billsafe_installmentCount']=$response->hirePurchase->installmentCount;
                $_SESSION['billsafe_processingFee']=$response->hirePurchase->annualPercentageRate;
                $_SESSION['billsafe_annualPercentageRate']=$response->hirePurchase->annualPercentageRate;
            }
        }
	}
}
?>