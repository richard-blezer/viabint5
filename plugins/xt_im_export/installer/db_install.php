<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_im_export', 'images/icons/arrow_refresh.png', '&plugin=xt_im_export', 'adminHandler.php', '4000', 'contentroot', 'I', 'W');");

$db->Execute("
		CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_exportimport (
		  `id` int(11) NOT NULL auto_increment,
		  `ei_type` varchar(32) NOT NULL default 'import',
		  `ei_type_spec` varchar(32) NOT NULL default 'products',
		  `ei_type_match` varchar(32) NOT NULL default 'products_id',
		  `ei_type_match_2` varchar(32) NULL,
		  `ei_title` varchar(64) NOT NULL,
		  `ei_filename` varchar(64) NOT NULL,
		  `ei_delimiter` varchar(32) NOT NULL default ';',
		  `ei_cat_tree_delimiter` varchar(32) NOT NULL default '/',
		  `ei_enclosure` char(1) NOT NULL default '\"',
		  `ei_limit` int(11) NOT NULL default '100',
		  `ei_language` int(1) NOT NULL default '0',
		  `ei_price_type` varchar(32) NOT NULL default 'false',
		  `ei_id` varchar(32) NOT NULL,
		  `ei_store_id` int(11) NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 ;");

$db->Execute("
		CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_exportimport_log (
		  `id` int(11) NOT NULL auto_increment,
		  `ei_id` varchar(32) NOT NULL default 0,
		  `error_message` varchar(255) NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 ;");