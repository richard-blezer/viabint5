<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once __DIR__ . '/../pluginBootstrap.php';
use ew_viabiona\plugin as ew_viabiona_plugin;

global $ew_viabiona_plugin, $db;

/**
 * DB DeInstallation
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   ew_viabiona_plugin::mysqlDropColumn(TABLE_CATEGORIES, 'teaser_sort');
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

//obsolete content blocks
$content_block = $ew_viabiona_plugin->getConfig('content_block');
if (!empty($content_block)) {
    foreach ((array)$content_block['block'] as $block) {
        $db->Execute("DELETE FROM `" . TABLE_CONTENT_BLOCK . "` WHERE `block_tag` = '" . ew_viabiona_plugin::CONTENT_BLOCK_PREFIX . (string)$block['tag'] . "'");
    }
}

//obsolete content options
ew_viabiona_plugin::mysqlDropColumn(TABLE_CONTENT, 'ew_viabiona_show_text_status');
ew_viabiona_plugin::mysqlDropColumn(TABLE_CONTENT, 'ew_viabiona_hyperlink');
ew_viabiona_plugin::mysqlDropColumn(TABLE_CONTENT, 'ew_viabiona_hyperlink_status');

//obsolete image types
if (($imageTypes = $ew_viabiona_plugin->getTemplateImageTypes()) !== null) {
    foreach ($imageTypes as $type) {
        ew_viabiona_plugin::removeImageType($type['dir']);
    }
}