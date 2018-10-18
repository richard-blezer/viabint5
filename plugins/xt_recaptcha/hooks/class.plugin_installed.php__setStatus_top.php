<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if($_REQUEST["load_section"] == 'plugin_installed' && $_REQUEST["pg"] == 'overview' && !isset($_REQUEST["pluginHistoryId"])
    && $status == 0)
{
    global $store_handler;

    foreach($store_handler->getStores() as $store)
    {
        //$db->Execute("UPDATE ".TABLE_CONFIGURATION_MULTI.$store['id']." SET config_value='Standard' WHERE config_key='_STORE_CAPTCHA' AND config_value='ReCaptcha' ");
    }
}