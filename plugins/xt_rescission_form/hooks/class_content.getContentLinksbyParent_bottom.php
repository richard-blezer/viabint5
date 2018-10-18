<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class_checkout_php__selectShipping_tpl_data.php 4816 2011-09-15 13:39:14Z dev_tunxa $
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

if (ACTIVATE_XT_RESCISSION_FORM == 'true' && XT_RESCISSION_FORM_SHOW_IN_CONTENT_BLOCK != '' && XT_RESCISSION_FORM_SHOW_IN_CONTENT_BLOCK == $block) {
	$contentId = 'rescission_form';
	$conn = 'NOSSL';
	if (XT_RESCISSION_FORM_USE_SSL == 'true') {
		$conn = 'SSL';
	}
	$link_array = array('page'=>'xt_rescission_form', 'conn'=>$conn);
	$url = $xtLink->_link($link_array);
	
	// Set link sort
	$length = count($data);
	$firstPart = array_slice($data, 0, XT_RESCISSION_FORM_LINK_SORT);
	$secondPart = array_slice($data, XT_RESCISSION_FORM_LINK_SORT, $length);
	
	$firstPart[] = array(
		'content_id' => $contentId,
		'language_code' => $language->code,
		'title' => XT_RESCISSION_FORM_LINK_TITILE,
		'link' => $url,
	);
	
	$data = array_merge($firstPart, $secondPart);
}

?>