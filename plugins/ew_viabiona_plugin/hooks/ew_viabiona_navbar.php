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
    $tpl = 'ew_viabiona_navbar.html';
    $tpl_data = array();
    $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
    if (!ew_viabiona_plugin::isFileCacheAllowed() || !$tpl_object->isTemplateCache($tpl)) {

        global $ew_viabiona_plugin;

        $categories = $ew_viabiona_plugin->get_categories_array(true, (($d = ew_viabiona_plugin::getCategoryDepth()) < 2) ? $d : 2, true, 0, 0);
        if (($specialCat = (int)$ew_viabiona_plugin->getViabionaConfig('angeboteKategorie')) != 0) {
            foreach ($categories as $k => $i) {
                if ($i['categories_id'] == $specialCat) {
                    $products_list = new products_list($specialCat);
                    if (($specialProds = count($products_list->getProductListing())) <= 0) {
                        unset($categories[$k]);
                        break;
                    }
                    $categories[$k]['categories_name'] = "{$categories[$k]['categories_name']} ({$specialProds})";
                }
            }
        }

        $tpl_data = array(
            '_categories' => $categories,
        );

        $output = $tpl_object->getTemplate('ew_viabiona_navbar', $tpl, $tpl_data);

    } else {

        $output = $tpl_object->getCachedTemplate($tpl);

    }

    echo $output;
    unset($tpl_object, $tpl_data, $output, $tpl);

}