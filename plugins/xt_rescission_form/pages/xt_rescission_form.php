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

if (ACTIVATE_XT_RESCISSION_FORM == 'true' && isset($xtPlugin->active_modules['xt_rescission_form'])) {
	$reinsert = array();
	$show_form = 'true';
	$countries = new countries('true','store');
	
	if (is_array($_POST) && isset($_POST['action'])) {
		$send_mail = true;
		$reinsert = $_POST;
		
		switch (_STORE_CAPTCHA){
			
			case 'Standard':
				include _SRV_WEBROOT.'/xtFramework/library/captcha/php-captcha.inc.php';
				if (PhpCaptcha::Validate($_POST['captcha'])) {
					$send_mail = true;
				} else {
					$send_mail = false;
					$info->_addInfo(ERROR_CAPTCHA_INVALID);
				}
				break;
					
			case 'ReCaptcha':
				($plugin_code = $xtPlugin->PluginCode('forms:contact_captcha_validator')) ? eval($plugin_code) : false;
				break;
			default: $send_mail = true;
		}
		
		
		$form_check = new check_fields();
		$form_check->_checkLenght($_POST['firstname'], _STORE_FIRST_NAME_MIN_LENGTH, ERROR_FIRST_NAME);
		$form_check->_checkLenght($_POST['lastname'], _STORE_LAST_NAME_MIN_LENGTH, ERROR_LAST_NAME);
		$form_check->_checkLenght($_POST['email_address'], _STORE_EMAIL_ADDRESS_MIN_LENGTH, ERROR_EMAIL_ADDRESS);
		$form_check->_checkEmailAddress($_POST['email_address'], ERROR_EMAIL_ADDRESS_SYNTAX);
		if (XT_RESCISSION_FORM_CLIENT_NO_REQUIRED == 'true') {
			$form_check->_checkLenght($_POST['client_number'], 1, ERROR_CLIENT_NUMBER);
		}
		if (XT_RESCISSION_FORM_INVOICE_NO_REQUIRED == 'true') {
			$form_check->_checkLenght($_POST['invoice_number'], 1, ERROR_INVOICE_NUMBER);
		}
		$form_check->_checkLenght($_POST['product_numbers'], _STORE_LAST_NAME_MIN_LENGTH, ERROR_PRODUCT_NUMBERS);
		$form_check->_checkDate($_POST['order_date'], 'dd.mm.yyyy', ERROR_ORDER_DATE_EMPTY);
		$form_check->_checkLenght($_POST['gender'], 1, ERROR_GENDER_EMPTY);
		$form_check->_checkLenght($_POST['street'], 1, ERROR_STREET_EMPTY);
		$form_check->_checkLenght($_POST['city'], 1, ERROR_CITY_EMPTY);
		$form_check->_checkLenght($_POST['country'], 1, ERROR_COUNTRY);
		$form_check->_checkDate($_POST['revocation_date'], 'dd.mm.yyyy', ERROR_REVOCATION_DATE_EMPTY);
		
		if ($form_check->error==true) $send_mail=false;
		
		if ($send_mail) {
			$countryData = $countries->_getCountryData($_POST['country']);
			
			$message = html_entity_decode ($_POST['customer_message']);
			$productNumbers = html_entity_decode ($_POST['product_numbers']);
		
			//mail to customers
			$mail = new xtMailer('rescission_form');
			$mail->_addReceiver($_POST['email_address'], $_POST['firstname'] . ' ' . $_POST['lastname']);
			$mail->_assign('client_number',$_POST['client_number']);
			$mail->_assign('invoice_number',$_POST['invoice_number']);
			$mail->_assign('mail',$_POST['email_address']);
			$mail->_assign('message',$message);
			$mail->_assign('product_numbers',$productNumbers);
			$mail->_assign('customers_firstname',$_POST['firstname']);
			$mail->_assign('customers_lastname',$_POST['lastname']);
			
			$mail->_assign('order_date',$_POST['order_date']);
			$mail->_assign('gender',$_POST['gender']);
			$mail->_assign('street',$_POST['street']);
			$mail->_assign('city',$_POST['city']);
			$mail->_assign('country',$countryData['countries_name']);
			$mail->_assign('revocation_date',$_POST['revocation_date']);
			$mail->_assign('recieved_date',$_POST['recieved_date']);
			$mail->_sendMail();
			
			$info->_addInfo(RESCISSION_FORM_EMAIL_SENT,'success');
			$show_form = 'false';
		}
		/*
		 xtFramework/classes/class.filter.php automatically replaces newline characters with <br>,
		and they appear back in the form when the validation fails, so we need to replace them
		back to newline chars.
		*/
		else
		{
			if(isset($reinsert['customer_message']))
				$reinsert['customer_message'] = str_replace('<br>', "\n", $reinsert['customer_message']);
			
			if(isset($reinsert['product_numbers']))
				$reinsert['product_numbers'] = str_replace('<br>', "\n", $reinsert['product_numbers']);
		}
	} else {
		$reinsert['product_numbers'] = TEXT_RESCISSION_FORM_REVOCATION;
	}
	
    if (is_object($brotkrumen))
        $brotkrumen->_addItem($xtLink->_link(array('page' => 'rescission_form')), TEXT_RESCISSION_FORM);

	$tpl_data = array(
		'show_form' => $show_form,
		'message' => $info->info_content,
		'captcha_link'=>$xtLink->_link(array('default_page'=>'captcha.php','conn'=>'SSL')),
		'country_data' => $countries->countries_list_sorted,
	);
	if (is_array($reinsert)) $tpl_data=array_merge($tpl_data,$reinsert);
    $template = new Template();
    ($plugin_code = $xtPlugin->PluginCode('plugin_xt_rescission_form.php:tpl_data')) ? eval($plugin_code) : false;
    $page_data = $template->getTemplate('xt_rescission_form_smarty', _SRV_WEBROOT . _SRV_WEB_PLUGINS . '/xt_rescission_form/templates/rescission_form.html', $tpl_data);
} else {
    $show_page = false;
}
?>