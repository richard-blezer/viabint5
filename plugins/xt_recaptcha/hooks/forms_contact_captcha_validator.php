<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . 'plugins/xt_recaptcha/classes/class.xt_recaptcha.php';
$recaptcha = new xt_recaptcha();

if ($recaptcha->isShowReCaptcha())
{
    $captcha_response = $_POST['g-recaptcha-response'];
    $resp = $recaptcha->verifyResponse($captcha_response);
    if (!$resp['success'])
    {
        $send_mail = false;
        $info->_addInfo($resp['error']);
    } else {
        $send_mail = true;
    }
}
