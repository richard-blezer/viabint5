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

include_once realpath(dirname(__FILE__).'/../').'/classes/constants.php';
include_once realpath(dirname(__FILE__).'/../').'/shopgate_library/vendors/2d_is.php';

if(!defined("XT_SHOPGATE_ID")){
	$id = $db->GetOne("SELECT plugin_id FROM `".TABLE_PLUGIN_PRODUCTS."` WHERE code = 'xt_shopgate'");
	if(!empty($id)) define('XT_SHOPGATE_ID', $id);
}

$db->Execute("
CREATE TABLE IF NOT EXISTS `".TABLE_SHOPGATE_ORDERS."`
(
	shopgate_orders_id INT NOT NULL auto_increment,
	orders_id INT NOT NULL,
	shopgate_order_number BIGINT NOT NULL,
	is_paid tinyint(1) UNSIGNED DEFAULT NULL,
  	is_shipping_blocked tinyint(1) UNSIGNED DEFAULT NULL,
  	modified datetime DEFAULT NULL,
  	created datetime DEFAULT NULL,
	PRIMARY KEY  (shopgate_orders_id)
) ENGINE = MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
");

// Get fieldnames of table TABLE_SHOPGATE_ORDERS
$fields = $db->Execute("SHOW FIELDS FROM `".TABLE_SHOPGATE_ORDERS."`");
$fieldNames = array();
foreach($fields as $field){
	$fieldNames[] = $field['Field'];
}

// check column names and create them, if they doesnt exist
if(!in_array('is_paid', $fieldNames)){
	$db->Execute("ALTER TABLE `".TABLE_SHOPGATE_ORDERS."` ADD is_paid INT UNSIGNED NULL AFTER `shopgate_order_number`");
}
if(!in_array('is_shipping_blocked', $fieldNames)){
	$db->Execute("ALTER TABLE `".TABLE_SHOPGATE_ORDERS."` ADD is_shipping_blocked INT UNSIGNED NULL AFTER `is_paid`");
}
if(!in_array('payment_infos', $fieldNames)){
	$db->Execute("ALTER TABLE `".TABLE_SHOPGATE_ORDERS."` ADD payment_infos TEXT NULL AFTER `is_shipping_blocked`");
}
if(!in_array('modified', $fieldNames)){
	$db->Execute("ALTER TABLE `".TABLE_SHOPGATE_ORDERS."` ADD modified DATETIME AFTER `payment_infos`");
}
if(!in_array('created', $fieldNames)){
	$db->Execute("ALTER TABLE `".TABLE_SHOPGATE_ORDERS."` ADD created DATETIME AFTER `modified`");
}
if(!in_array('is_sent_to_shopgate', $fieldNames)) {
	$db->Execute("ALTER TABLE `".TABLE_SHOPGATE_ORDERS."` ADD is_sent_to_shopgate INT UNSIGNED NULL AFTER `payment_infos`");
}


// check shopgate status "shipping blocked"
$qry = "
SELECT
 ssd.status_id,
 ssd.status_name
 FROM ".TABLE_SYSTEM_STATUS." ss
 JOIN ".TABLE_SYSTEM_STATUS_DESCRIPTION." ssd
 ON ((ss.status_id = ssd.status_id) AND (ssd.language_code = 'de'))
WHERE ssd.status_name = 'Versand blockiert (Shopgate)'";

$systemStatusShippingBlocked = -1;

$result = $db->GetRow($qry);
if(empty($result)){
	// Add new order status
	$systemStatus = array(
			'status_class' => 'order_status',
			'status_values' => 'a:1:{s:4:"data";a:5:{s:15:"enable_download";i:0;s:7:"visible";i:0;s:13:"visible_admin";s:1:"1";s:19:"calculate_statistic";i:0;s:12:"reduce_stock";s:1:"0";}}',
	);
	$db->AutoExecute(TABLE_SYSTEM_STATUS, $systemStatus, "INSERT");
	$systemStatus['status_id'] = $db->Insert_ID();
	$systemStatusShippingBlocked = $systemStatus['status_id'];

	// save german translation
	$systemStatusDescription = array(
			'status_id' => $systemStatus['status_id'],
			'language_code' => 'de',
			'status_name' => 'Versand blockiert (Shopgate)'
	);
	$db->AutoExecute(TABLE_SYSTEM_STATUS_DESCRIPTION, $systemStatusDescription, "INSERT");

	// save english translation
	$systemStatusDescription = array(
			'status_id' => $systemStatus['status_id'],
			'language_code' => 'en',
			'status_name' => 'Shipping blocked (Shopgate)'
	);
	$db->AutoExecute(TABLE_SYSTEM_STATUS_DESCRIPTION, $systemStatusDescription, "INSERT");
} else {
	$systemStatusShippingBlocked = $result['status_id'];
}

// Set standard value for shipping blocked
if($systemStatusShippingBlocked != -1) {
	$shippingBlockedConfig = array(
		'config_value' => $systemStatusShippingBlocked,
	);
	$db->AutoExecute(TABLE_PLUGIN_CONFIGURATION, $shippingBlockedConfig, "UPDATE", 'config_key=\'XT_SHOPGATE_ORDER_STATUS_SHIPPING_BLOCKED\'');
}

$db->Execute("DROP TABLE IF EXISTS `" . TABLE_SHOPGATE_CONFIG . "`");
$db->Execute("
CREATE TABLE `" . TABLE_SHOPGATE_CONFIG . "` (
 `config_id` int(11) NOT NULL AUTO_INCREMENT,
 `shop_id` int(11) NOT NULL,
 `key` varchar(30) NOT NULL,
 `value` varchar(255) NOT NULL,
 PRIMARY KEY (`config_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
");

$db->Execute("
INSERT INTO `" . TABLE_ADMIN_NAVIGATION . "`
( `text`, `sortorder`, `parent`, `type`, `navtype`, `url_d`, `icon` )
VALUES
( 'xt_shopgate_shopgate', 10, 'config', 'G', 'W', NULL, '../plugins/xt_shopgate/images/shopgate_small.png' ),
( 'xt_shopgate_info', 10, 'xt_shopgate_shopgate', 'I', 'W', '../plugins/xt_shopgate/pages/iframe_wrapper.php?url=".urlencode("http://www.shopgate.com/de/sell")."', '../plugins/xt_shopgate/images/shopgate_small.png' ),
( 'xt_shopgate_install_manual', 20, 'xt_shopgate_shopgate', 'I', 'W', '../plugins/xt_shopgate/pages/iframe_wrapper.php?url=".urlencode("http://wiki.shopgate.com/Veyton/de")."', '../plugins/xt_shopgate/images/shopgate_small.png' ),
( 'xt_shopgate_register', 30, 'xt_shopgate_shopgate', 'I', 'W', '../plugins/xt_shopgate/pages/iframe_wrapper.php?url=".urlencode("https://www.shopgate.com/welcome/shop_register")."', '../plugins/xt_shopgate/images/shopgate_small.png' ),
( 'xt_shopgate_config', 40, 'xt_shopgate_shopgate', 'I', 'W', 'adminHandler.php?load_section=plugin_installed&pg=overview&parentNode=node_plugin_installed&edit_id=".XT_SHOPGATE_ID."&gridHandle=plugin_installedgridForm', '../plugins/xt_shopgate/images/shopgate_small.png' ),
( 'xt_shopgate_merchant_area', 60, 'xt_shopgate_shopgate', 'I', 'W', '../plugins/xt_shopgate/pages/iframe_wrapper.php?url=".urlencode("https://www.shopgate.com/users/login/0/2")."', '../plugins/xt_shopgate/images/shopgate_small.png' )
");

$stores = $db->Execute("SELECT shop_id FROM `" . TABLE_MANDANT_CONFIG . "`");
$configQry = "";
while(!$stores->EOF) {
	if(!empty($configQry)) $configQry .= ",\n";
	
	$storeId = $stores->fields["shop_id"];
	
	$configQry .= "( $storeId, 'background_color', '#333' ),\n";
	$configQry .= "( $storeId, 'foreground_color', '#3d3d3d' ),\n";
	$configQry .= "( $storeId, 'qr_generator', '1' ),\n";
	$configQry .= "( $storeId, 'qr_destination', '".sg_2d_is::SHOP_ITEM."' ),\n";
	$configQry .= "( $storeId, 'has_own_app', '0' ),\n";
	$configQry .= "( $storeId, 'itunes_link', '' )";
	
	$stores->MoveNext();
}
$configQry = "INSERT INTO `" . TABLE_SHOPGATE_CONFIG . "` ( `shop_id`, `key`, `value` ) VALUES " . $configQry;
$db->Execute($configQry);

$db->Execute("ALTER TABLE `".TABLE_PLUGIN_CONFIGURATION."` ORDER BY  `id`");