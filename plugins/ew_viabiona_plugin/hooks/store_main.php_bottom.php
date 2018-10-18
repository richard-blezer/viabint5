<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Manage html error reporting
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\plugin as ew_viabiona_plugin;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    //enable error messages
    if (ew_viabiona_plugin::isDebugMode() && ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_DEBUG_MODE_REPORTING')) {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
        ini_set("display_errors", "1");
    }

    //clear cache
    ew_viabiona_plugin::clearCache();

    //current shop id
    ew_viabiona_plugin::setShopIdConstant();

}