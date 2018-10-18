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

$db->Execute("DELETE FROM ".DB_PREFIX."_acl_nav WHERE text = 'xt_master_slave'");

$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_plg_products_attributes");
$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_plg_products_attributes_description");
$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_plg_products_to_attributes");

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_master_model' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_master_model`");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_master_flag' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_master_flag`");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_option_template' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_option_template`");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_option_list_template' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_option_list_template`");
}
$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_option_master_price' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_option_master_price`");
}
$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_image_from_master' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_image_from_master`");
}

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='products_master_slave_order' AND TABLE_NAME='".DB_PREFIX."_products'");
if ($colExists ){
    $db->Execute("ALTER TABLE ".DB_PREFIX."_products DROP `products_master_slave_order` ");
}



$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_plg_products_attributes_templates");
$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_tmp_products");
$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_tmp_plg_products_to_attributes");

?>