<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Bootstrap navbar items
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   use in templates like {hook key=ew_viabiona_navbar}
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
    $tpl = 'ew_viabiona_topcategories.html';
    $tpl_data = array();
    $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
    if (!ew_viabiona_plugin::isFileCacheAllowed() || !$tpl_object->isTemplateCache($tpl)) {
        global $ew_viabiona_plugin;

        $tpl_data = array(
            '_categories_tree' => $categoriesTree = $ew_viabiona_plugin->get_categories_array(true, ew_viabiona_plugin::getCategoryDepth()),
            '_categories'      => ew_viabiona_plugin::buildTopCategoriesData($categoriesTree),
        );

        $output = $tpl_object->getTemplate('ew_viabiona_topcategories', $tpl, $tpl_data);
    } else {
        $output = $tpl_object->getCachedTemplate($tpl);
    }

    echo $output; //print output
    unset($tpl_object, $tpl_data, $output, $tpl);

}