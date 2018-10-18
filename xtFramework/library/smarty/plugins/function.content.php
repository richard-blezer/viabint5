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

function smarty_function_content($params, & $smarty) {
	global $_content, $xtPlugin;

	$block = (int) $params['block_id'];
	$cont = (int) $params['cont_id'];

	$nested = false;
	if ($params['levels'] =='nested') {
	    $nested = true;
	}

	if (!isset($block) && !isset($cont)) return;

	if($block!=''){

		if (isset($params['levels'])) {
			$content_array = $_content->getContentBox($block, $nested);
		} else {
			$content_array = $_content->get_Content_Links($block);
		}

		($plugin_code = $xtPlugin->PluginCode('smarty_function_content:content_array')) ? eval($plugin_code) : false;

		$smarty->assign('_content_'.$block,$content_array);
	}

	if($cont!=''){
	if ($params['is_id']=='false') {
		$content = $_content->getHookContent($cont);
	} else {
		$content = $_content->getHookContent($cont, 'true');
	}

	($plugin_code = $xtPlugin->PluginCode('smarty_function_content:content')) ? eval($plugin_code) : false;
	$smarty->assign('_content_'.$cont,$content);
	}

	return;
}
?>