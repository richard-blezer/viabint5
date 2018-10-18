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
 # @version $Rev: 558 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(DB_PREFIX!=''){
 $DB_PREFIX = DB_PREFIX . '_';
}else{
 define('DB_PREFIX','xt');
 $DB_PREFIX = DB_PREFIX . '_';
}
define('TABLE_SHOPGATE_CONFIG', $DB_PREFIX.'shopgate_config');
define('TABLE_SHOPGATE_ORDERS', $DB_PREFIX.'shopgate_orders');
