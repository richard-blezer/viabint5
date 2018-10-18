<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Sidebar Boxes
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   use in templates like {hook key=ew_viabiona_sidebar_boxes}
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

use ew_viabiona\plugin as ew_viabiona_plugin;
use ew_viabiona\Template as template;

if (class_exists('ew_viabiona\plugin') && ew_viabiona_plugin::status()) {

    if (ew_viabiona_plugin::get_current_pagename() != 'product' && ew_viabiona_plugin::get_current_pagename() != '404') {

        $tpl_object = new template();
        $tpl = 'ew_viabiona_sidebar_boxes.html';
        $tpl_data = array();
        $tpl_object->getTemplatePath($tpl, 'ew_viabiona_plugin', 'hooks', 'plugin');
        if (!ew_viabiona_plugin::isFileCacheAllowed() || !$tpl_object->isTemplateCache($tpl)) {
            global $ew_viabiona_plugin;

            $dataFormatted = null;
            if ($data = $ew_viabiona_plugin->getContentsByBlock('ew_viabiona_sidebar_boxes', false)) {
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
                        'title'         => $dItem['content_title'],
                        'heading'       => !empty($dItem['content_heading']) ? $dItem['content_heading'] : $dItem['content_title'],
                        'link'          => $dItemLink,
                        'short_content' => (trim(strip_tags($dItem['content_body_short'])) != '') ? $dItem['content_body_short'] : null,
                        'image'         => !empty($dItem['content_image']) ? $dItem['content_image'] : null,
                        'orgData'       => $dItem,
                    );
                }
            }

            $tpl_data = array(
                'data' => $dataFormatted,
                'is_index' => ew_viabiona_plugin::is_index(),
            );

            $output = $tpl_object->getTemplate('ew_viabiona_sidebar_boxes', $tpl, $tpl_data);
            unset($data, $dataFormatted);

        } else {
            $output = $tpl_object->getCachedTemplate($tpl);
        }

        echo $output;
        $smarty->assign('ew_viabiona_sidebar_boxes', $output);
        unset($tpl_object, $tpl_data, $output, $tpl);

    }

}