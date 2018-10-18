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
 # @version $Id: db_uninstall.php 6578 2013-11-15 17:15:39Z silviyap $
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

$emailTpls = $db->GetAll("SELECT `tpl_id` FROM ". TABLE_MAIL_TEMPLATES ." WHERE `tpl_type` LIKE 'rescission_form' ");
foreach($emailTpls as $tpl)
{
	$db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES_CONTENT." WHERE `tpl_id` = ".$tpl['tpl_id']);
	$db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES." WHERE `tpl_id` = ".$tpl['tpl_id']);
}

$seo_plugin_file = _SRV_WEBROOT.'/xtFramework/classes/class.seo_plugins.php';
if (file_exists($seo_plugin_file))
{
	require_once $seo_plugin_file;

	$seo_plugin = new seo_plugins();
	$seo_plugin->unsetPluginSEO('xt_rescission_form');
}
?>