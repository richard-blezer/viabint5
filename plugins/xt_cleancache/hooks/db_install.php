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
# @version $Id: db_install.php 4953 2012-02-03 15:18:56Z tu $
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

$db->Execute("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_clean_cache` (
		`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`type` VARCHAR(10) NOT NULL ,
		`last_run` datetime NULL,
		`date_added` datetime NOT NULL,
		`last_modified` datetime NOT NULL
);");

$db->Execute("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_clean_cache_logs` (
		`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`type` VARCHAR(10) NOT NULL,
		`change_trigger` VARCHAR(5) NOT NULL,
		`last_run` datetime NOT NULL ,
		`date_added` datetime NOT NULL,
		`last_modified` datetime NOT NULL
);");

// Navigation anlegen
$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_cleancache', 'images/icons/page_save.png', '', '', '4000', 'systemroot', 'G', 'W');");
$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_cleancache_types', 'images/icons/page.png', '&plugin=xt_cleancache&load_section=xt_cleancache_types', 'adminHandler.php', '4010', 'xt_cleancache', 'I', 'W');");
$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_cleancache_logs', 'images/icons/folder.png', '&plugin=xt_cleancache&load_section=xt_cleancache_logs', 'adminHandler.php', '4020', 'xt_cleancache', 'I', 'W');");

$db->Execute("INSERT INTO `".DB_PREFIX."_clean_cache` (`id`, `type`, `date_added`, `last_modified`) VALUES
(1, 'All', '2012-04-05 00:00:00', '2012-04-05 00:00:00'),
(2, 'Feed', '2012-04-05 00:00:00', '2012-04-05 00:00:00'),
(3, 'HTML', '2012-04-05 00:00:00', '2012-04-05 00:00:00'),
(4, 'CSS', '2012-04-05 00:00:00', '2012-04-05 00:00:00'),
(5, 'Javascript', '2012-04-05 00:00:00', '2012-04-05 00:00:00'),
(6, 'Templates Cache', '2012-04-05 00:00:00', '2012-04-05 00:00:00');");
?>