<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . 'plugins/xt_recaptcha/classes/class.xt_recaptcha.php';

$recaptcha = new xt_recaptcha();

if($recaptcha->isShowReCaptcha())
{
    $tpl_data = array(
        'public_key' => $recaptcha->getPublickey(),
        'size' => $recaptcha->getSize(),
        'theme' => $recaptcha->getTheme(),
        'language' => $recaptcha->getLang()
    );

    $tplFile = $recaptcha->isInvisible() ? 'g-recaptcha-inv.tpl.html' : 'g-recaptcha-v2.tpl.html';
    $template = new Template();
    $template->getTemplatePath($tplFile, 'xt_recaptcha', '', 'plugin');
    $html = $template->getTemplate('', $tplFile, $tpl_data);

    echo $html;
}