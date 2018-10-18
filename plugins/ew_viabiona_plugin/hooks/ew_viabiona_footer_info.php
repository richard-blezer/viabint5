<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Footer Content Block `ew_viabiona_footer_info`
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   use in templates like {hook key=ew_viabiona_footer_info}
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\plugin as ew_viabiona_plugin;
use ew_viabiona\Template as template;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    $tpl_object = new template();
    $tpl = 'ew_viabiona_footer_info.html';
    $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
    $output = null;

    if (!ew_viabiona_plugin::isFileCacheAllowed() || !$tpl_object->isTemplateCache($tpl)) {
        global $ew_viabiona_plugin;

        if ($data = $ew_viabiona_plugin->getContentsByBlock('ew_viabiona_footer_info', false)) {
            $output = $tpl_object->getTemplate('ew_viabiona_footer_info', $tpl, array('data' => $data));
            unset($data);
        }

    } else {
        $output = $tpl_object->getCachedTemplate($tpl);
    }

    echo $output;
    unset($tpl_object, $output, $tpl);

}