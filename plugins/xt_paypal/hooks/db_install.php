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
 # @version $Id: db_install.php 4816 2011-09-15 13:39:14Z dev_tunxa $
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

$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 24, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 25, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 26, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 27, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 28, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 29, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 30, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO " . TABLE_PAYMENT_COST . " (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(" . $payment_id . ", 31, '', 0, 10000.00, 0, 1);");

$db->Execute("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "_plg_paypal_refunds (
          `refunds_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,          
          `refunds_type` varchar(50),
          `total` decimal(15,4) default 0,
          `transaction_id` varchar(20) default NULL,
          `orders_id` int(11) NULL default NULL,
          `callback_log_id` int(11) NULL default NULL,
          `status` tinyint(1) NULL default NULL,
          `refunded` tinyint(1) NULL default 0,
          `success` tinyint(1) NULL default 0,
          `refund_memo` varchar(255),
          `date_added` datetime default NULL,
          `error_data` longtext default NULL,
          `error_msg` varchar(255) default NULL,
          `callback_data` longtext default NULL,
          PRIMARY KEY  (`refunds_id`)
         );");

//for 4015, 4016
// get actual Version
$rs = $db->Execute("SELECT * FROM ".TABLE_CONFIGURATION." WHERE config_key='_SYSTEM_VERSION'");
$shop_version = $rs->fields['config_value'];
$shop_version  = str_replace('.','',$shop_version);

//without refund function for older than 4015
if($shop_version > 4015){
    $db->Execute("UPDATE " . TABLE_ADMIN_NAVIGATION . " SET TYPE='G' WHERE text='payment' AND type='I'");
    $db->Execute("INSERT INTO " . TABLE_ADMIN_NAVIGATION . " (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'paypal_transactions', 'images/icons/money_euro.png', '&plugin=xt_paypal', 'adminHandler.php', '4000', 'payment', 'I', 'W');");
    $db->Execute("INSERT INTO " . TABLE_ADMIN_NAVIGATION . " (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'paypal_refunds', 'images/icons/money_euro.png', '&plugin=xt_paypal', 'adminHandler.php', '4000', 'payment', 'I', 'W');");
}

if($shop_version < 4100){
    $cols = $db->MetaColumns(TABLE_ORDERS);
    if(!array_key_exists('AUTHORIZATION_ID', $cols)){
        $db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD COLUMN `authorization_id` VARCHAR(255) NOT NULL DEFAULT ''");
        $db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD COLUMN `authorization_amount` DECIMAL(15,4) NULL AFTER `authorization_id`, ADD COLUMN `authorization_expire` DATETIME NULL AFTER `authorization_amount`");
    }
}
?>