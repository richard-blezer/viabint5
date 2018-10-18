<?php

/**
 * Plugin bootstrap file
 *
 * @author Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

//absolute path config
define('EW_VIABIONA_PLUGIN_ROOT_DIR', __DIR__);
define('EW_VIABIONA_PLUGIN_LIB_DIR', EW_VIABIONA_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . 'lib');
define('EW_VIABIONA_PLUGIN_ASSETS_DIR', EW_VIABIONA_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . 'assets');
define('EW_VIABIONA_PLUGIN_CONFIG_DIR', EW_VIABIONA_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . 'config');
define('EW_VIABIONA_PLUGIN_CLASSES_DIR', EW_VIABIONA_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . 'classes');
define('EW_VIABIONA_PLUGIN_HOOKS_DIR', EW_VIABIONA_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . 'hooks');

//less.php compiler
if (!class_exists('lessc')) {
    require_once EW_VIABIONA_PLUGIN_LIB_DIR . DIRECTORY_SEPARATOR . 'oyejorge/less.php/lessc.inc.php';
}
if (!class_exists('\cakebake\lesscss\LessConverter')) {
    require_once EW_VIABIONA_PLUGIN_LIB_DIR . DIRECTORY_SEPARATOR . 'cakebake/php-lesscss-compiler/src/LessConverter.php';
}

//listing switcher
require_once EW_VIABIONA_PLUGIN_CLASSES_DIR . DIRECTORY_SEPARATOR . 'ListingSwitch.php';

//auto thumbs
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.image.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaData.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaImages.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaFiles.php';
require_once EW_VIABIONA_PLUGIN_CLASSES_DIR . DIRECTORY_SEPARATOR . 'createThumbnails.php';

//session storage
if (!class_exists('cakebake\sessionStorage')) {
    require_once EW_VIABIONA_PLUGIN_LIB_DIR . DIRECTORY_SEPARATOR . 'cakebake/php-session-storage/class.sessionStorage.php';
}

//plugin class
require_once EW_VIABIONA_PLUGIN_CLASSES_DIR . DIRECTORY_SEPARATOR . 'class.ew_viabiona_plugin.php';
require_once EW_VIABIONA_PLUGIN_CLASSES_DIR . DIRECTORY_SEPARATOR . 'Template.php';

//global plugin object
if (class_exists('ew_viabiona\plugin')) {
    global $ew_viabiona_plugin;

    $ew_viabiona_plugin = (is_object($ew_viabiona_plugin) && $ew_viabiona_plugin instanceof ew_viabiona\plugin) ? $ew_viabiona_plugin : new ew_viabiona\plugin();
}