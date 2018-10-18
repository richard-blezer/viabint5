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
	$db->Execute("INSERT INTO `".DB_PREFIX."_content_block` (`block_id`, `block_tag`, `block_status`, `block_protected`) VALUES (NULL, '".$block."', '1', '0')");
}

//add categories startpage check to database
$db->Execute("ALTER TABLE `".DB_PREFIX."_categories` ADD `teaser_sort` int(4) default '0' AFTER `products_sorting2`");
$db->Execute("ALTER TABLE `".DB_PREFIX."_categories` ADD `teaser_status` int(1) default '0' AFTER `products_sorting2`");

//add cache button
$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid`, `text`, `icon`, `url_i`, `url_d`, `sortorder`, `parent`, `type`, `navtype`, `cls`, `handler`, `iconCls`) VALUES (NULL, 'xt_8works_delete_cache','images/icons/lightning.png','../index.php?admin_delete_cache=1',NULL,NULL,'0','I','N',NULL,'clickHandler2',NULL);"); 

/**
 * INSERT LIST WITH NEW IMAGE TYPES
 */

function ja_add_image_types($folder, $class, $width, $height) {
	global $db;
	
	$exe = "INSERT INTO `".DB_PREFIX."_image_type` (`id`, `folder`, `width`, `height`, `watermark`, `process`, `class`) VALUES (NULL, '".$folder."', '".$width."', '".$height."', 'false', 'true', '".$class."')";
	$db->Execute($exe);
}

/**
 * HIDDEN CORE-PLUGIN-HOOKS ONLY FOR OUR TEMPLATE
 */

//plugin update function with "auto-escape"
function ja_update_core_plugin($plugin_code,$hook,$template) {
global $db;

$source = ja_select_code($plugin_code,$hook);
$date = date("Y-m-d H-i-s");

if (!preg_match('/---'.$template.'---/', $source)) {
$exe = "update ".DB_PREFIX."_plugin_code set code = '/*---".$template."---*/
//INFO: ".$plugin_code." hookpoint modified by 8works template-plugin at ".$date."
//INFO: changes should be implemented in the 8works plugin (backend->plugin->hookpoints)

if (_STORE_TEMPLATE != \'".$template."\') {
".addslashes($source)."
}
//".$plugin_code." code end
' where plugin_code='".$plugin_code."' and hook='".$hook."'";

$db->Execute($exe);
}
}

//select code from plugin for update function
function ja_select_code($plugin_code,$hook) {
	global $db;
	
	$sql = $db->Execute("SELECT code FROM ".DB_PREFIX."_plugin_code WHERE plugin_code = '".$plugin_code."' AND hook = '".$hook."'");
	$sql = $sql->fields['code'];
	
	return $sql;
}

?>