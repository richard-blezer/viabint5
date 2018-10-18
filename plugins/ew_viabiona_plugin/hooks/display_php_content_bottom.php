<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Output in display php bottom some debug info
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
use ew_viabiona\Template as template;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    if (ew_viabiona_plugin::isDebugMode()) {
        global $logHandler;

        $tpl_object = new template();
        $tpl = 'ew_viabiona_debugbar.html';
        $tpl_data = array();
        $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
        $logHandler->parseTime(false);

        $tpl_data = array(
            'request_ip'         => ew_viabiona_plugin::getUserIP(),
            'response_ip'        => ew_viabiona_plugin::getServerIP(),
            'parse_time'         => $logHandler->timer_total,
            'sys_cache'          => ew_viabiona_plugin::isFileCacheAllowed(),
            'session_cache'      => ew_viabiona_plugin::isSessionCacheActive(),
            'clear_cache'        => ew_viabiona_plugin::shouldClearCache(),
            'sys_cache_lifetime' => defined('CACHE_LIFETIME') ? round((CACHE_LIFETIME / 60)) . 'm' : 0,
            'template'           => ew_viabiona_plugin::templateName(),
            'pluginRootUrl'      => ew_viabiona_plugin::getPluginRootURL(),
        );

        echo $tpl_object->getTemplate('ew_viabiona_debugbar', $tpl, $tpl_data); //print output
        unset($tpl_object, $tpl_data, $tpl);
    }

}