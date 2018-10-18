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

global $store_handler;

$db->Execute("
		INSERT INTO
		".DB_PREFIX."_acl_nav (`text`, `icon`, `url_i`, `url_d`, `sortorder`, `parent`, `type`, `navtype`, `cls`, `handler`, `iconCls`)
		VALUES ('xt_startpage_products', 'images/icons/database_gear.png', '&plugin=xt_startpage_products', 'adminHandler.php', '5002', 'shop', 'I', 'W', NULL, NULL, NULL)");

$create = "CREATE TABLE IF NOT EXISTS  `" . DB_PREFIX . "_startpage_products` (
	 `startpage_products_id` int(11) NOT NULL AUTO_INCREMENT,
	 `shop_id` int(11) NOT NULL,
	 `products_id` int(11) NOT NULL,
	 `startpage_products_sort` int(11) NOT NULL,
	 PRIMARY KEY (`startpage_products_id`),
	 UNIQUE KEY `shop_id_products_id_unique` (`shop_id`,`products_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$db->Execute($create);