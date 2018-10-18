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

define('TABLE_PRODUCTS_ATTRIBUTES', $DB_PREFIX . 'plg_products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION', $DB_PREFIX . 'plg_products_attributes_description');
define('TABLE_PRODUCTS_TO_ATTRIBUTES', $DB_PREFIX . 'plg_products_to_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES', $DB_PREFIX.'plg_products_attributes_templates');
define('TABLE_TMP_PRODUCTS', $DB_PREFIX . 'tmp_products');
define('TABLE_TMP_PRODUCTS_TO_ATTRIBUTES', $DB_PREFIX . 'tmp_plg_products_to_attributes');

if ($_GET['currentType']=='xt_master_slave')
	include _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.xt_master_slave.php';


?>