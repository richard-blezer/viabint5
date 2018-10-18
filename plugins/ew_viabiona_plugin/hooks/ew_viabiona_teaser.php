<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Main Teaser
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   use in templates like {hook key=ew_viabiona_teaser}
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\plugin as ew_viabiona_plugin;
use ew_viabiona\Template as template;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    if (ew_viabiona_plugin::show_teaser()) {
        $tpl_object = new template();
        $tpl = 'ew_viabiona_teaser.html';
        $tpl_data = array();
        $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
        if (!ew_viabiona_plugin::isFileCacheAllowed() || !$tpl_object->isTemplateCache($tpl)) {
            global $ew_viabiona_plugin;

            $dataFormatted = null;
            if ($data = $ew_viabiona_plugin->getContentsByBlock('ew_viabiona_teaser', false)) {
                foreach ($data as $dKey => $dItem) {
                    $dItemLink = null;
                    if ((int)$dItem['ew_viabiona_hyperlink_status'] == 1) {
                        if (!empty($dItem['ew_viabiona_hyperlink']) && $test = trim(str_replace('http://', '', $dItem['ew_viabiona_hyperlink'])) != '') {
                            $dItemLink = trim($dItem['ew_viabiona_hyperlink']);
                        } else {
                            $dItemLink = $dItem['link'];
                        }
                    }
                    $dataFormatted[] = array(
                        'id'            => (int)$dItem['content_id'],
                        'title'         => (trim(strip_tags($dItem['content_body_short'])) != '') ? $dItem['content_title'] : null,
                        'heading'       => !empty($dItem['content_heading']) ? $dItem['content_heading'] : $dItem['content_title'],
                        'short_content' => (trim(strip_tags($dItem['content_body_short'])) != '') ? $dItem['content_body_short'] : null,
                        'long_content'  => (trim(strip_tags($dItem['content_body'])) != '') ? $dItem['content_body'] : null,
                        'image'         => !empty($dItem['content_image']) ? $dItem['content_image'] : null,
                        'link'          => $dItemLink,
                        'show_text'     => ((int)$dItem['ew_viabiona_show_text_status'] == 1) ? true : false,
                        'orgData'       => $dItem,
                    );
                }
            }

            $tpl_data = array(
                'data'         => $dataFormatted,
                'navItemWidth' => ((int)($elementCount = @count($dataFormatted)) > 1) ? ew_viabiona_plugin::floorPrecision(100 / $elementCount, 2) . '%' : null,
            );

            $output = $tpl_object->getTemplate('ew_viabiona_teaser', $tpl, $tpl_data);
        } else {
            $output = $tpl_object->getCachedTemplate($tpl);
        }

        echo $output;
        unset($tpl_object, $tpl_data, $output, $tpl, $dataFormatted, $data);
    }
}