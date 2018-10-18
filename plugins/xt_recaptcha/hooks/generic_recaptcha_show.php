<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// template hook

require_once _SRV_WEBROOT . 'plugins/xt_recaptcha/classes/class.xt_recaptcha.php';

if (isset($_SESSION['registered_customer']))
{
} else {
    global $xtLink;
    $tpl_data_captcha = array('captcha_link' => $xtLink->_link(array('default_page' => 'captcha.php', 'conn' => 'SSL')));

    $tpl_captcha = 'captcha.html';
    $template = new Template();
    $template->getTemplatePath($tpl_captcha, 'xt_recaptcha', '', 'plugin');

    $captcha_html = $template->getTemplate('xt_recaptcha', $tpl_captcha, $tpl_data_captcha);
    echo $captcha_html;
}







