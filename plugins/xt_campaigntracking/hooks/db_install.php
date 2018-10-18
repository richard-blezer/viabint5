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
$db->Execute("
	 CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_campaigntracking (
	  `id` INT UNSIGNED NOT NULL auto_increment,
	  `linked_class` VARCHAR(100) NOT NULL,
	  `linked_id` INT UNSIGNED NOT NULL,
	  `passed_by_reference_method` VARCHAR(100) NULL DEFAULT '',
	  `checkout_completed_method` VARCHAR(100) NULL DEFAULT '',
	  `custom_reference` VARCHAR(255) NULL DEFAULT '',
	  `hash` VARCHAR(100) NOT NULL,
	  PRIMARY KEY (id),
	  INDEX (linked_class, linked_id),
	  INDEX (hash)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	");
//check coupons table
//global $db;
$db_tables = $db->MetaTables();
if(in_array(DB_PREFIX."_coupons",$db_tables)){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_coupons ADD custom_reference varchar(255) NULL DEFAULT '';");
}
?>