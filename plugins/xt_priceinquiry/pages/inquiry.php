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



if (!isset($current_product_id) or $current_product_id=='') {
	$tmp_link  = $xtLink->_link(array('page'=>'404'));
	$xtLink->_redirect($tmp_link);
}


if (is_array($_POST) && isset($_POST['action'])) {

	$reinsert = array('customer_message'=>$_POST['customer_message']);

	// check if logged in (captcha check)
	if(!isset($_SESSION['registered_customer'])) {
		include _SRV_WEBROOT.'/xtFramework/library/captcha/php-captcha.inc.php';
		if (PhpCaptcha::Validate($_POST['captcha'])) {
			$send_mail = true;
		} else {
			$send_mail = false;
			$info->_addInfo(ERROR_CAPTCHA_INVALID);
		}
	} else {
		$send_mail = true;
	}

	$form_check = new check_fields();

	$form_check->_checkLenght($_POST['email_address'], _STORE_EMAIL_ADDRESS_MIN_LENGTH, ERROR_EMAIL_ADDRESS);
    $form_check->_checkEmailAddress($_POST['email_address'], ERROR_EMAIL_ADDRESS_SYNTAX);
	$form_check->_checkLenght($_POST['firstname'], _STORE_FIRST_NAME_MIN_LENGTH, ERROR_FIRST_NAME);
	$form_check->_checkLenght($_POST['lastname'], _STORE_LAST_NAME_MIN_LENGTH, ERROR_LAST_NAME);
	$form_check->_checkLenght($_POST['competitor_price'], '2', TEXT_XT_PRICEINQUIRY_PRICE_ERROR);
	$form_check->_checkLenght($_POST['competitor_url'], '5', TEXT_XT_PRICEINQUIRY_URL_ERROR);

	if ($form_check->error==true) $send_mail=false;

	if ($send_mail) {
		$coontent = "";
		foreach ($_POST as $key => $val) {
			if ($key!='action' && $key!='x' && $key !='y') {
				$content .= $key . "\n".$val."\n";
			}
		}

		$body_html = nl2br($content);
		$inquiryMail = new xtMailer('none');
		$inquiryMail->_addReceiver(_STORE_CONTACT_EMAIL,'');
		$inquiryMail->_setSubject(TEXT_XT_PRICEINQUIRY);
		$inquiryMail->_setContent($body_html, $content);
		$inquiryMail->_setFrom(_STORE_CONTACT_EMAIL,_STORE_NAME);
		$inquiryMail->_addReplyAddress($_POST['email_address'], $_POST['firstname'].' '.$_POST['lastname']);
		$inquiryMail->_sendMail();

		$info->_addInfo(SUCCESS_EMAIL_SEND,'success');
	}

}

$p_info = new product($current_product_id,'default');

$brotkrumen->_addItem($xtLink->_link(array('page'=>'inquiry', 'params'=>'info='.$current_product_id)),TEXT_XT_PRICEINQUIRY);

// customer logged in ?
if(isset($_SESSION['registered_customer'])) {

	$add_data = array('logged_in'=>'true','firstname'=>$_SESSION['customer']->customer_default_address['customers_firstname'],'lastname'=>$_SESSION['customer']->customer_default_address['customers_lastname'],'company'=>$_SESSION['customer']->customer_default_address['customers_company'],'email_address'=>$_SESSION['customer']->customer_info['customers_email_address']);


} else {
	$add_data = array('logged_in'=>'false');
}


$tpl_data = array('message'=>$info->info_content,'product_data'=>$p_info->data,'data'=>$shop_content_data, 'subdata'=>$subdata,'captcha_link'=>$xtLink->_link(array('default_page'=>'captcha.php','conn'=>'SSL')));
$tpl_data = array_merge($tpl_data,$add_data);
if (is_array($reinsert)) $tpl_data=array_merge($tpl_data,$reinsert);

$tpl = 'write_inquiry.html';

$template = new Template();
$template->getTemplatePath($tpl, 'xt_priceinquiry', '', 'plugin');

$page_data = $template->getTemplate('xt_write_inqury_smarty', $tpl, $tpl_data);

?>