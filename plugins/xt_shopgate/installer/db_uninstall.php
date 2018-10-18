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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text like 'xt_shopgate_%'");
$db->Execute("DROP TABLE IF EXISTS `".TABLE_SHOPGATE_CONFIG."`");

