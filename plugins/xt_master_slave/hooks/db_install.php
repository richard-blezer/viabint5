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
$rc = $db->Execute("SELECT * FROM ".TABLE_ADMIN_NAVIGATION." WHERE text= 'xt_master_slave' ");
if ($rc->RecordCount()==0){
	$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_master_slave', 'images/icons/building_link.png', '&plugin=xt_master_slave&gridHandle=xt_master_slavegridForm', 'adminHandler.php', '5000', 'shop', 'I', 'W');");
}

$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_plg_products_attributes (
  attributes_id int(11) NOT NULL auto_increment,
  attributes_parent int(11) NULL DEFAULT '0',
  attributes_model varchar(255) default NULL,
  attributes_image varchar(255) default NULL,
  sort_order int(11) default '0',
  status tinyint(1) default '1',
  attributes_templates_id int(11) NOT NULL,
  PRIMARY KEY  (attributes_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_plg_products_attributes_description (
  attributes_id int(11) NOT NULL,
  language_code char(2) NOT NULL,
  attributes_name varchar(255) default NULL,
  attributes_desc text,
  PRIMARY KEY  (attributes_id,language_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_plg_products_to_attributes (
  products_id int(11) NOT NULL,
  attributes_id int(11) NOT NULL,
  attributes_parent_id int(11) NOT NULL,
  PRIMARY KEY  (products_id,attributes_id),
  KEY attributes_id (attributes_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");


$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_master_model' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD products_master_model VARCHAR( 255 ) NULL AFTER products_model");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_master_flag' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute(" ALTER TABLE ".TABLE_PRODUCTS." ADD products_master_flag TINYINT( 1 ) NULL AFTER products_master_model");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_image_from_master' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD products_image_from_master TINYINT( 1 ) NULL AFTER products_master_flag");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_option_template' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD products_option_template VARCHAR( 255 ) NULL AFTER products_image_from_master");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_option_list_template' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD products_option_list_template VARCHAR( 255 ) NULL AFTER products_option_template");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_option_master_price' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD products_option_master_price VARCHAR( 3 ) NULL AFTER products_option_list_template");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_master_slave_order' AND TABLE_NAME='".TABLE_PRODUCTS."'");
if (!$colExists ){
	$db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD products_master_slave_order INT NOT NULL AFTER products_option_master_price");
}


$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_plg_products_attributes_templates (
attributes_templates_id int(11) NOT NULL auto_increment,
  attributes_templates_name varchar(255) default NULL,
  PRIMARY KEY  (attributes_templates_id)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

$rc = $db->GetOne("SELECT attributes_templates_id FROM ".DB_PREFIX."_plg_products_attributes_templates WHERE attributes_templates_name='select'");

if ((int)$rc==0){
	$db->Execute("
	INSERT INTO ".DB_PREFIX."_plg_products_attributes_templates (`attributes_templates_id`, `attributes_templates_name`) VALUES
	(1, 'select'),
	(2, 'images');
	");
}

$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_tmp_products (
  `products_id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(255) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `products_owner` int(11) NOT NULL DEFAULT '1',
  `products_ean` varchar(128) DEFAULT NULL,
  `products_quantity` decimal(15,2) DEFAULT NULL,
  `products_average_quantity` int(11) DEFAULT '0',
  `products_shippingtime` int(4) DEFAULT NULL,
  `products_model` varchar(255) DEFAULT NULL,
  `products_master_model` varchar(255) DEFAULT NULL,
  `products_master_flag` tinyint(1) DEFAULT NULL,
  `products_option_template` varchar(255) DEFAULT NULL,
  `products_option_list_template` varchar(255) DEFAULT NULL,
  `products_option_master_price` varchar(3) DEFAULT NULL,
  `price_flag_graduated_all` int(1) DEFAULT '0',
  `price_flag_graduated_1` int(1) DEFAULT '0',
  `price_flag_graduated_2` int(1) DEFAULT '0',
  `price_flag_graduated_3` int(1) DEFAULT '0',
  `products_sort` int(4) DEFAULT '0',
  `products_image` varchar(64) DEFAULT NULL,
  `products_price` decimal(15,4) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_available` datetime DEFAULT NULL,
  `products_weight` decimal(15,4) DEFAULT NULL,
  `products_status` tinyint(1) DEFAULT NULL,
  `products_tax_class_id` int(11) DEFAULT NULL,
  `product_template` varchar(64) DEFAULT NULL,
  `product_list_template` varchar(64) DEFAULT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `products_ordered` int(11) DEFAULT '0',
  `products_transactions` int(11) DEFAULT '0',
  `products_fsk18` int(1) DEFAULT '0',
  `products_vpe` int(11) DEFAULT NULL,
  `products_vpe_status` int(1) DEFAULT '0',
  `products_vpe_value` decimal(15,4) DEFAULT '0.0000',
  `products_startpage` int(1) DEFAULT '0',
  `products_startpage_sort` int(4) DEFAULT '0',
  `products_average_rating` decimal(14,4) DEFAULT '0.0000',
  `products_rating_count` int(11) DEFAULT '0',
  `products_digital` int(1) DEFAULT '0',
  `flag_has_specials` int(1) NOT NULL DEFAULT '0',
  `products_serials` int(1) DEFAULT '0',
  `total_downloads` int(11) DEFAULT '0',
  `google_product_cat` varchar(255) NOT NULL,
  `ekomi_allow` int(1) NOT NULL DEFAULT '1',
  `products_name` varchar(255) NOT NULL,
  `main_products_id` int(11) NOT NULL,
  `attributes` varchar(255) NOT NULL,
  `saved` int(1) NOT NULL,
  `name_changed` TINYINT NOT NULL,
  PRIMARY KEY (`products_id`),
  KEY `idx_products_date_added` (`date_added`),
  KEY `products_status` (`products_status`,`products_startpage`),
  KEY `products_ordered` (`products_ordered`),
  KEY `manufacturers_id` (`manufacturers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$db->Execute("
CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_tmp_plg_products_to_attributes (
  `products_id` int(11) NOT NULL,
  `attributes_id` int(11) NOT NULL,
  `attributes_parent_id` int(11) NOT NULL,
  `main` tinyint(4) NOT NULL,
  PRIMARY KEY (`products_id`,`attributes_id`),
  KEY `attributes_id` (`attributes_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");
 
?>