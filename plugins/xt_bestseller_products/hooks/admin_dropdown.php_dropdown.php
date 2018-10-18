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


// ADMIN: config for search mode

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($request['get'] == 'plg_xt_bestseller_products_show_type') {
	if (!isset($result)) $result = array();
	$result[] = array('id' => 'master', 'name' => XT_BESTSELLER_PRODUCTS_MASTER, 'desc' => XT_BESTSELLER_PRODUCTS_MASTER);
	$result[] = array('id' => 'slave', 'name' => XT_BESTSELLER_PRODUCTS_SLAVE, 'desc' => XT_BESTSELLER_PRODUCTS_SLAVE);
	$result[] = array('id' => 'nothing', 'name' => XT_BESTSELLER_PRODUCTS_NOTHING, 'desc' => XT_BESTSELLER_PRODUCTS_NOTHING);
}

?>