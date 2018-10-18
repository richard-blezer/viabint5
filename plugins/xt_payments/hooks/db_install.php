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
$cols = $db->MetaColumns(TABLE_ORDERS);
if(!array_key_exists('XT_PAYMENTS_AUTHORIZATION_ID', $cols)){
	$db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD COLUMN `XT_PAYMENTS_AUTHORIZATION_ID` VARCHAR(255) NOT NULL DEFAULT ''");
}
if(!array_key_exists('XT_PAYMENTS_AUTHORIZATION_AMOUNT', $cols) && !array_key_exists('XT_PAYMENTS_AUTHORIZATION_EXPIRE', $cols)){
	$db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD COLUMN `XT_PAYMENTS_AUTHORIZATION_AMOUNT` DECIMAL(15,4) NULL AFTER `XT_PAYMENTS_AUTHORIZATION_ID`, ADD COLUMN `XT_PAYMENTS_AUTHORIZATION_EXPIRE` DATETIME NULL AFTER `XT_PAYMENTS_AUTHORIZATION_AMOUNT`");
}
if(!array_key_exists('XT_PAYMENTS_GW_RELATED_ID', $cols)){
	$db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD COLUMN `XT_PAYMENTS_GW_RELATED_ID` VARCHAR(255) NOT NULL DEFAULT '' AFTER `XT_PAYMENTS_AUTHORIZATION_EXPIRE`");
}
/* Add tmp_xt_payments table 19.09*/
$db->Execute("CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_payments_tmp (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,payment_code VARCHAR(50), payment_type VARCHAR(10), col_key VARCHAR(255), col_value VARCHAR(255))");
?>