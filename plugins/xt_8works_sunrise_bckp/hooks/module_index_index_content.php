<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

$default = new sunrise();
global $template;

// PLUGIN CHECK //
$plugin_check = array();
if ($default->check_plugin_status('xt_startpage_products')) {
	$startpage = 1;
	$plugin_check['xt_startpage_products']['check'] = 1;
	$plugin_check['xt_startpage_products']['first'] = 1;
}
if ($default->check_plugin_status('xt_new_products') && ACTIVATE_XT_NEW_PRODUCTS_BOX == 'true') {
	$new = 1;
	$plugin_check['xt_new_products']['check'] = 1;
	
	if (!$startpage)
		$plugin_check['xt_new_products']['first'] = 1;
}
if ($default->check_plugin_status('xt_bestseller_products') && ACTIVATE_XT_BESTSELLER_PRODUCTS_BOX == 'true') {
	$bestseller = 1;
	$plugin_check['xt_bestseller_products']['check'] = 1;
	
	if (!$startpage && !$new)
		$plugin_check['xt_bestseller_products']['first'] = 1;
}
if ($default->check_plugin_status('xt_special_products') && ACTIVATE_XT_SPECIAL_PRODUCTS_BOX == 'true') {
	$special = 1;
	$plugin_check['xt_special_products']['check'] = 1;
	
	if (!$startpage && !$new && !$bestseller)
		$plugin_check['xt_special_products']['first'] = 1;
}
if ($default->check_plugin_status('xt_upcoming_products') && ACTIVATE_XT_UPCOMING_PRODUCTS_BOX == 'true') {
	$plugin_check['xt_upcoming_products']['check'] = 1;
	
	if (!$startpage && !$new && !$bestseller && !$special)
		$plugin_check['xt_upcoming_products']['first'] = 1;
}
$plugin_check['all'] = count($plugin_check);
$tpl_data['plugin'] = $plugin_check;


/**
 * TEASER
 */
if (!isset($_GET['page']) || $_GET['page'] == 'index') {
	$teaser_tpl = '/cInc/teaser.html';
	if (!$template->isTemplateCache($teaser_tpl)) {
		$teaser = $template->getTemplate('8w_sunrise_teaser', $teaser_tpl, array('teaser_data' => $default->get_teaser()));
	} else {
		$teaser = $template->getCachedTemplate($teaser_tpl);
	}
	$tpl_data['teaser_data'] = $teaser;
}

?>