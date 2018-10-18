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
# @version $Id: db_uninstall.php 4953 2012-02-03 15:18:56Z tu $
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

$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_cleancache'");
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_cleancache_types'");
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_cleancache_logs'");

$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_clean_cache");
$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_clean_cache_logs");

?>