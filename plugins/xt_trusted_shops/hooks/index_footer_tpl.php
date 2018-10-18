<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops_ui_settings.php';

// badge layer
global $language, $store_handler;
$uiSettings = trusted_shops_ui_settings::getSettings($language->content_language, SHOP_DOMAIN_HTTP);

if ($uiSettings[COL_TS_CERTS_SHOW_BADGE])
{
    // trusted shops badge layer
    $tpl = 'trusted_shops_badge.tpl.html';
    $template = new Template();
    $template->getTemplatePath($tpl, 'xt_trusted_shops', '', 'plugin');
    $tpl_html = $template->getTemplate('', $tpl, array('ts_ui'=>$uiSettings));
    echo $tpl_html;
}