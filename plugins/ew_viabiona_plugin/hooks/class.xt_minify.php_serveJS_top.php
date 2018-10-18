<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Manipulate xt:Minify JS
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

    if (ew_viabiona_plugin::refreshMinify($this->resources['js'], $filename)) {
        $this->js_cache_time = 0;
    }

}