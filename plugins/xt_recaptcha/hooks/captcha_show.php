<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . 'plugins/xt_recaptcha/classes/class.xt_recaptcha.php';
$recaptcha = new xt_recaptcha();
$publickey = $recaptcha->getPublickey();

if ($recaptcha->isShowReCaptcha()) {
    if (isset($_SESSION['registered_customer'])) {
        $add_data['logged_in'] = true;
    } else {
        $add_data['logged_in'] = false;
    }

    $add_data['recaptcha'] = true;
}
