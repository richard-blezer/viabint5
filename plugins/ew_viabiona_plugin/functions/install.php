<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once __DIR__ . '/../pluginBootstrap.php';
use ew_viabiona\plugin as ew_viabiona_plugin;
global $ew_viabiona_plugin, $db;

/**
* DB Installation
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
* @example ew_viabiona_plugin::mysqlAddColumn(TABLE_CATEGORIES, 'teaser_sort', "INT(4) DEFAULT '0' AFTER `products_sorting2`");
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

//new content blocks
$content_block = $ew_viabiona_plugin->getConfig('content_block');
if (!empty($content_block)) {
    foreach ((array)$content_block['block'] as $block) {
        if (ew_viabiona_plugin::addContentBlock($block)) {
            ew_viabiona_plugin::formatInstallerLogMessage("New content block `" . ew_viabiona_plugin::CONTENT_BLOCK_PREFIX . "{$block['tag']}` added", $this->debug_output);
        }
    }
}

//content options
if (ew_viabiona_plugin::mysqlAddColumn(
    ($table = TABLE_CONTENT), 
    ($column = 'ew_viabiona_show_text_status'), 
    "INT(1) DEFAULT '0' AFTER `content_sort`"
)) {
    ew_viabiona_plugin::formatInstallerLogMessage("Column `$table`.`$column` added", $this->debug_output);
} else {
    ew_viabiona_plugin::formatInstallerLogMessage("ERROR: Column `$table`.`$column` could not be added", $this->debug_output, 'error');
}

if (ew_viabiona_plugin::mysqlAddColumn(
    ($table = TABLE_CONTENT), 
    ($column = 'ew_viabiona_hyperlink'), 
    "VARCHAR(255) DEFAULT 'http://' AFTER `content_sort`"
)) {
    ew_viabiona_plugin::formatInstallerLogMessage("Column `$table`.`$column` added", $this->debug_output);
} else {
    ew_viabiona_plugin::formatInstallerLogMessage("ERROR: Column `$table`.`$column` could not be added", $this->debug_output, 'error');
}

if (ew_viabiona_plugin::mysqlAddColumn(
    ($table = TABLE_CONTENT), 
    ($column = 'ew_viabiona_hyperlink_status'), 
    "INT(1) DEFAULT '0' AFTER `content_sort`"
)) {
    ew_viabiona_plugin::formatInstallerLogMessage("Column `$table`.`$column` added", $this->debug_output);
} else {
    ew_viabiona_plugin::formatInstallerLogMessage("ERROR: Column `$table`.`$column` could not be added", $this->debug_output, 'error');
}

//image types
if (($imageTypes = $ew_viabiona_plugin->getTemplateImageTypes()) !== null) {
    foreach ($imageTypes as $type) {
        if ($ew_viabiona_plugin->addImageType($type['dir'], $type['class'], $type['width'], $type['height'], $type['watermark'], $type['processing'])) {
            ew_viabiona_plugin::formatInstallerLogMessage("New image size `{$type['dir']}` added", $this->debug_output);
        } else {
            ew_viabiona_plugin::formatInstallerLogMessage("ERROR: Image size `{$type['dir']}` could not be added", $this->debug_output, 'error');
        }
    }
}