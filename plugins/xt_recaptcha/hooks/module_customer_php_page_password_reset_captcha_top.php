<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . 'plugins/xt_recaptcha/classes/class.xt_recaptcha.php';

$recaptcha = new xt_recaptcha();

if ($recaptcha->isShowReCaptcha()) {
    $captcha_plugin = true;
}
