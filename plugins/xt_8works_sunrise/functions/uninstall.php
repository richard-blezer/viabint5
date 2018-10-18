<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

//new content_blocks
$file = _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_8works_sunrise/functions/content_blocks.php';
if (file_exists($file)) require_once $file;

foreach ($content_blocks as $block) {
	$db->Execute("DELETE FROM ".DB_PREFIX."_content_block WHERE block_tag = '".$block."'");
}

//remove categories startpage check from database
$db->Execute("ALTER TABLE `".DB_PREFIX."_categories` DROP `teaser_status`");
$db->Execute("ALTER TABLE `".DB_PREFIX."_categories` DROP `teaser_sort`");

//remove cache button
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_8works_delete_cache'");

/**
 * REMOVING PLUGIN IMAGE TYPES FROM DATABASE
 */
 
//remove
function ja_remove_image_types($folder) {
	global $db;
	
	$exe = "DELETE FROM ".DB_PREFIX."_image_type WHERE folder = '".$folder."'";
	$db->Execute($exe);
}

?>