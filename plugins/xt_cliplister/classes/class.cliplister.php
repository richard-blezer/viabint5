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

class cliplister extends products_list {


	function getcliplister () {
		global $xtPlugin, $xtLink, $db;

		$query_cliplister = "SELECT products_ean FROM ".TABLE_PRODUCTS." WHERE products_id='".(int)($GLOBALS[template]->content_smarty->_tpl_vars[products_id])."' LIMIT 0,1";
		$rs_cliplister = $db->Execute($query_cliplister);

		$EAN_Cliplister =		$rs_cliplister->fields['products_ean'];
		$query = $this->sql_products->getSQL_query();

		define('EAN_Cliplister',$EAN_Cliplister);

		return $module_content;
	}
}
?>