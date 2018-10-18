<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

$cat = new sunrise();
global $template;

if(is_object($brotkrumen))
	$brotkrumen->_addItem($xtLink->_link(array('page'=>'categories')),TEXT_BOX_TITLE_CATEGORIES);

$tpl_data = array(
	'categories_array' => $cat->get_categories(TRUE),
	'max_level' => XT_8WORKS_SUNRISE_CATEGORY_LEVEL
);

$tpl = 'categories.html';
$template->getTemplatePath($tpl, 'xt_8works_sunrise', '', 'plugin');
if (!$template->isTemplateCache($tpl)) {
	$page_data = $template->getTemplate('xt_8works_categories_overview_smarty', $tpl, $tpl_data);
} else {
	$page_data = $template->getCachedTemplate($tpl);
}

?>