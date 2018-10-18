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

$xtMinify->add_resource(_SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/stylesheet.css',50);

//TODO auto add script at this hook
($plugin_code = $xtPlugin->PluginCode('styles.php:top')) ? eval($plugin_code) : false;
// <link rel="stylesheet" type="text/css" href="<?php echo _SYSTEM_BASE_URL._SRV_WEB._SRV_WEB_TEMPLATES._STORE_TEMPLATE.'/css/stylesheet.css'; " />
($plugin_code = $xtPlugin->PluginCode('styles.php:bottom')) ? eval($plugin_code) : false;
?>