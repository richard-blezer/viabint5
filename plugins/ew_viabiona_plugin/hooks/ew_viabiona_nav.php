<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Bootstrap nav / Sidebar nav
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

    global $ew_viabiona_plugin, $category;

    $position = (isset($params['position']) && !empty($params['position'])) ? trim($params['position']) : 'default';
    $tpl_object = new template();
    $tpl = "ew_viabiona_nav_{$position}.html";
    $tpl_data = array();
    $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
    $output = '';
    $mainCats = $ew_viabiona_plugin->get_categories_array();

    if (!empty($mainCats)) {
        foreach ($mainCats as $mainCat) {
            if (($_categories = $ew_viabiona_plugin->get_categories_array(false, (ew_viabiona_plugin::getCategoryDepth() - 1), true, 1, $mainCat['categories_id']))) {
                $tpl_data = array(
                    '_categories'     => $_categories,
                    'categories_list' => ew_viabiona_plugin::buildHtmlList($_categories),
                    'parent'          => $mainCat,
                );
                $output .= $tpl_object->getTemplate("ew_viabiona_nav_{$mainCat['categories_id']}", $tpl, $tpl_data) . PHP_EOL;
            }
        }
    }

    echo $output;
    unset($tpl_object, $tpl_data, $output, $tpl);

}
