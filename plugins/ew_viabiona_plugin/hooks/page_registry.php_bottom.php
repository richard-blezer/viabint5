<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Global page registry and bootstrapping
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

if (isset($xtPlugin->active_modules['ew_viabiona_plugin'])) {
    require_once __DIR__ . '/../pluginBootstrap.php';
}

// Cache watcher page
define('PAGE_EW_VIABIONA_AJAX_CACHE_WATCHER', _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'ew_viabiona_plugin/pages/ew_viabiona_ajax_cache_watcher.php');