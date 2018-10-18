<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . 'plugins/xt_recaptcha/classes/class.xt_recaptcha.php';
$recaptcha = new xt_recaptcha();
$publickey = $recaptcha->getPublickey();
$privatekey = $recaptcha->getPrivatekey();

if ($recaptcha->isShowReCaptcha()) {


	$captcha_response = $_POST['g-recaptcha-response'];
	if (strlen($captcha_response)<32) {
		$send_mail = false;
		$info->_addInfo(ERROR_CAPTCHA_INVALID);
	} else {
		$resp = $recaptcha->verifyResponse($captcha_response);
	
		if (!$resp) {
			$send_mail = false;
			$info->_addInfo(ERROR_CAPTCHA_INVALID);
		} else {
			 $info->_addInfo(SUCCESS_CAPTCHA_VALID, 'success');
        $remember_customer = new customer;
        $remember_customer->_customer($record->fields['customers_id']);
        $remember_customer->_sendPasswordOptIn();
		}
	}
	
}
