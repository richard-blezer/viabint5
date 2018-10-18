<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

if (isset($_GET['8w_breakpoint']) && $_GET['8w_breakpoint'] == 'ajax' && !isset($_GET['page'])) {
	$ajax = new sunrise();
	global $template;

	/**
	 * CATEGORY NAVIGATION
	 * @check	 "/index.php?8w_breakpoint=ajax&8w_show=categories"
	 */
	if (isset($_GET['8w_show']) && $_GET['8w_show'] == 'categories') {
		$nav_tpl = '/cInc/specialnav_v2.html';
		if (!$template->isTemplateCache($nav_tpl)) {
			$deepest_level = (int)XT_8WORKS_SUNRISE_CATEGORY_LEVEL;
			$categories = $template->getTemplate('8w_sunrise_specialnav_v2', $nav_tpl, array('output' => $ajax->get_all_category_level_nav(), 'deepest_level' => $deepest_level));
		} else {
			$categories = $template->getCachedTemplate($nav_tpl);
		}
	}
	
	//html output
	if (isset($categories))
		echo $categories;
	
	//vor ausgabe des doctypes abwuergen, weil nicht benoetigt
	die();
}

?>