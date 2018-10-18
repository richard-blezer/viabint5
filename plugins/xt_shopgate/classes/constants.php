<?php
/*
 #########################################################################
 #                       Shogate GmbH
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # http://www.shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Rev: 54 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

//defined('_VALID_CALL') or die('Direct Access is not allowed.');

// Plugin version
define('SHOPGATE_PLUGIN_VERSION', '2.3.0');

// Database constants
if (DB_PREFIX != '') {
	$DB_PREFIX = DB_PREFIX.'_';
} else {
	define('DB_PREFIX', 'xt');
	$DB_PREFIX = DB_PREFIX.'_';
}
define('TABLE_SHOPGATE_CONFIG', $DB_PREFIX.'shopgate_config');
define('TABLE_SHOPGATE_ORDERS', $DB_PREFIX.'shopgate_orders');