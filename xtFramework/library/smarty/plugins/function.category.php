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

function smarty_function_category($params, & $smarty) {
	global $category, $xtPlugin, $template;

	$startlevel = 0;
	if ($params['startlevel']) {
		$startlevel = $params['startlevel'];
	}
	

	if ($params['skip_no_cat_selected']) {
	 	if (!$category->current_category_id) {
			return;
		}
		$params['cID'] = $category->level[$startlevel];
	}
	
	

	$cID = 0;
	if ($params['cID'])
	$cID = (int) $params['cID'];

	$nested = false;
	if ($params['levels'] =='nested') {
	    $nested = true;
	}


    $category_array['data'] = $category->getCategoryBox($cID, $nested);
	if (is_array($category->current_category_data)){
		$category_array['current_category'] = $category->current_category_data;
	} 
	
	if ($params['cID']) {
		$levelCatData = new category($params['cID']);
		if (is_array($levelCatData->current_category_data)){
			$category_array['level_category'] = $levelCatData->current_category_data;
		} 
	}
	


	
    ($plugin_code = $xtPlugin->PluginCode('smarty_function_category:category_array')) ? eval($plugin_code) : false;

	$assignTo = '_category_box_'.$cID; 
	if($params['assignTo'])
	$assignTo = $params['assignTo'];
	
    $smarty->assign($assignTo,$category_array);

	($plugin_code = $xtPlugin->PluginCode('smarty_function_category:category')) ? eval($plugin_code) : false;
	$smarty->assign('_category_'.$cID,$category);


	return;
}
?>