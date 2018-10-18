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

function smarty_function_link($params, & $smarty) {
	global $xtLink, $xtPlugin, $seo;

	($plugin_code = $xtPlugin->PluginCode('smarty_function_link:_link_top')) ? eval($plugin_code) : false;

	if($params['params_exclude'])
	$exclude = $params['params_exclude'];
	
	if($params['params_value'])
	$params['params'] = $params['params'].'='.$params['params_value'].'&'.$xtLink->_getParams($exclude);

	unset($params['params_value']);
	
	($plugin_code = $xtPlugin->PluginCode('smarty_function_link:_link_bottom')) ? eval($plugin_code) : false;
	$tmp_link = $xtLink->_link($params);

	echo $tmp_link;
}
?>