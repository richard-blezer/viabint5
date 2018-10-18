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

$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_plg_customer_bankaccount");
$emailTpls = $db->GetAll("SELECT `tpl_id` FROM ". TABLE_MAIL_TEMPLATES ." WHERE `tpl_type` LIKE 'sepa_mandat' ");
foreach($emailTpls as $tpl)
{
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES_CONTENT." WHERE `tpl_id` =? ",array($tpl['tpl_id']));
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES." WHERE `tpl_id` =? ",array($tpl['tpl_id']));
}
?>