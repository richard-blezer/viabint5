<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Cache watcher
 * Deletes files by specifying a maximum age
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

$display_output = false;

if (class_exists('ew_viabiona\plugin') &&
    ew_viabiona_plugin::status() &&
    ew_viabiona_plugin::check_conf('CONFIG_EW_VIABIONA_PLUGIN_AJAX_CACHE_WATCHER') &&
    (int)CACHE_LIFETIME > 0) {

    /**
     * now, -1 day, -2 weeks, etc... see: selfphp.de/funktionsreferenz/datums_und_zeit_funktionen/strtotime.php
     *
     * @var mixed
     */
    $age = '-' . (int)CACHE_LIFETIME . ' seconds';

    /**
     * delete path array
     * glob style
     *
     * @var string
     * @var array
     */
    $rootpath = _SRV_WEBROOT;
    $delpath = array(
        $rootpath . 'cache/',
    );
    foreach ($delpath as $k => $path) {
        if (file_exists($path)) {
            $files = glob("$path*.{html,tmp}", GLOB_BRACE);
            if (is_array($files) && count($files) !== 0) {
                foreach ($files as $item) {
                    $filename = pathinfo($item, PATHINFO_FILENAME) . '.' . pathinfo($item, PATHINFO_EXTENSION);
                    $filemtime = @filemtime($item);
                    if (($filename !== 'index.html' && strtotime($age) > $filemtime) || ($filename !== 'index.html' && $filemtime == false)) {
                        if (is_dir($item)) {
                            $erg = @rmdir($item);
                        } else {
                            $erg = @unlink($item);
                        }
                    }
                }
            }
        }
    }

} else die('Disabled.');