<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$is_recaptcha_uninstall = $db->GetOne("SELECT 1 FROM ".TABLE_PLUGIN_PRODUCTS." WHERE plugin_id=? and `code`='xt_recaptcha'", array($plugin_id));
if($is_recaptcha_uninstall)
{
    global $store_handler;

    foreach($store_handler->getStores() as $store)
    {
        $db->Execute("UPDATE ".TABLE_CONFIGURATION_MULTI.$store['id']." SET config_value='Standard' WHERE config_key='_STORE_CAPTCHA' AND config_value='ReCaptcha' ");
    }
}