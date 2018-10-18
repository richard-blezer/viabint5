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

function _checkPW($pw, $pw_coded)
{
	global $xtPlugin, $db;

	($plugin_code = $xtPlugin->PluginCode('check_pw.php:_checkPW_top')) ? eval($plugin_code) : false;
	if (isset($plugin_return_value))
	{
		return $plugin_return_value;
	}

	if (isset($_SESSION['orderEditAdminUser'], $_SESSION['orderEditAdminUser']['user_id']))
	{
		$sql = "SELECT `user_password` FROM `".TABLE_ADMIN_ACL_AREA_USER."` WHERE `user_id`=".$_SESSION['orderEditAdminUser']['user_id'];
		$pw_coded = $db->GetOne($sql);
	}

	return ($pw_coded === md5($pw));
}