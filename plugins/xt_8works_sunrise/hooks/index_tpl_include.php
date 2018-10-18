<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

$index = new sunrise();
$content = new content();
global $template;

//new content_blocks names
$file = _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_8works_sunrise/functions/content_blocks.php';
if (file_exists($file)) require_once $file;

//assign smarty vars by name
foreach ($content_blocks as $new_block) {
	$id = $index->get_content_id($new_block);
	if ($id) {
		$smarty->assign('content_'.$new_block, $content->getContentBox($id,true));
	}
}
$smarty->assign('tpl_cod',$index->get_design());
if ($index->check_ie_version())
	$smarty->assign('check_ie_version',$index->check_ie_version());
if ($index->is_old_ie())
	$smarty->assign('is_old_ie',1);
if ($index->is_old_ie6())
	$smarty->assign('is_old_ie6',1);

/**
 * CATEGORY NAVIGATION
 */
$nav_tpl = '/cInc/specialnav_v1.html';
if (!$template->isTemplateCache($nav_tpl)) {
	$deepest_level = (int)XT_8WORKS_SUNRISE_CATEGORY_LEVEL;
	$categories = $template->getTemplate('8w_sunrise_specialnav_v1', $nav_tpl, array('output' => $index->get_category_nav(), 'deepest_level' => $deepest_level));
} else {
	$categories = $template->getCachedTemplate($nav_tpl);
}
$smarty->assign('special_nav', $categories);

/**
 * CLEAR CACHE
 */
$index->reloadCacheOnce();
if (XT_8WORKS_SUNRISE_CACHEMODE == 'true')
	$index->clearCache();

?>