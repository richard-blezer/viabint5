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
 # @version $Id: xt_trusted_shops_schutz.php 6060 2013-03-14 13:10:33Z mario $
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


if (ACTIVATE_XT_TRUSTED_SHOPS_SCHUTZ_BOX == 'true' && isset($xtPlugin->active_modules['xt_trusted_shops_schutz'])){

	global $language;

	if ($language->code=='de') {
		$shop_id = XT_TRUSTED_SHOPS_SCHUTZ_KEY_DE;
	}
	if ($language->code=='en') {
		$shop_id = XT_TRUSTED_SHOPS_SCHUTZ_KEY_EN;
	}
	if ($language->code=='fr') {
		$shop_id = XT_TRUSTED_SHOPS_SCHUTZ_KEY_FR;
	}
  
  $tpl_data = array('shop_id'=> $shop_id,'language'=>$language->code,'shop_name'=>XT_TRUSTED_SHOPS_SCHUTZ_SHOPNAME);
	
	$trusted = new xt_trusted_shops_schutz();
	
	$show_box = true;
	if (!$trusted->_validID($shop_id)) $show_box = false;
	
} else {
	$show_box = false;
}

?>