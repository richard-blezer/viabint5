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
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_sperrgut', 'images/icons/lorry.png', '&plugin=xt_sperrgut', 'adminHandler.php', '4000', 'config', 'I', 'W');");


if (!$this->_FieldExists('xt_sperrgut_class',TABLE_PRODUCTS))
    $db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD `xt_sperrgut_class` int(11) NOT NULL DEFAULT '0';");

$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_sperrgut;");
$db->Execute("CREATE TABLE ".DB_PREFIX."_sperrgut (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`price` DECIMAL( 15, 4 ) NOT NULL,`description` VARCHAR(64) NOT NULL DEFAULT '');");
?>