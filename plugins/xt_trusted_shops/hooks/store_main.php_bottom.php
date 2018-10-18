<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops_ui_settings.php';

global $language, $store_handler;
if (!isset($_GET['sendordermail']) || $_GET['sendordermail']!=1) {
    trusted_shops_ui_settings::getSettings($language->content_language, SHOP_DOMAIN_HTTP);
}