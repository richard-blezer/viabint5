<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

defined('_VALID_CALL') or die('Direct Access is not allowed.');

function smarty_function_socialbookmark($params, & $smarty) {
	global $xtPlugin;

	$title = $params['title'];
	$url = $params['url'];
	
	if (!isset($params['url'])) $url = _SRV_WEB.$_SERVER['REQUEST_URI'];
	
	if (isset($xtPlugin->active_modules['xt_socialbookmarks'])) {
	require _SRV_WEBROOT.'plugins/xt_socialbookmarks/classes/class.xt_socialbookmarks.php';
	$bookmark = new socialbookmarks();
	
	$smarty->assign('_socialbookmarks',$bookmark->_getSocialBookmarks($url,$title));
	}
	
}
?>