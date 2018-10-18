<?php
/*
 #########################################################################
#                       xt:Commerce VEYTON 4.0 Shopsoftware
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
# This file may not be redistributed in whole or significant part.
# Content of this file is Protected By International Copyright Laws.
#
# ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
#
# http://www.xt-commerce.com
#
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# @version $Id: class.products_search.php 4611 2011-03-30 16:39:15Z mzanier $
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

$createSql = 'CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . '_elefunds_transactions` (' .
				'`orders_id` int(11) NOT NULL,' . 
				'`donation` blob,' . 
				'`donation_status` tinyint(1) NOT NULL DEFAULT 0,' . 
			'PRIMARY KEY (`orders_id`)' . 
			') ENGINE=MyISAM DEFAULT CHARSET=utf8';

$db->Execute($createSql);

$db->Execute("INSERT INTO `".TABLE_ADMIN_NAVIGATION."` (`text`, `icon`, `url_i`, `url_d`, `sortorder`, `parent`, `type`, `navtype`, `cls`, `handler`, `iconCls`) 
                   VALUES ('elefunds_account', 'images/icons/building_key.png', '', 'row_actions.php?type=elefunds&seckey="._SYSTEM_SECURITY_KEY."', 320000, 'config', 'I', 'W', NULL, NULL, '')");
                   