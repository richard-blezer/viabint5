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
# @version $Id: row_actions_php_actions.php 4953 2012-02-03 15:18:56Z tu $
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

if ($_GET['type']=='cleancache' && isset($_GET['typeid'])) {
	$typeid = $_GET['typeid'];
	$params = 'cleancache=true&typeid='.$_GET['typeid'].'&seckey='._SYSTEM_SECURITY_KEY;
	$iframe_target = $xtLink->_adminlink(array('default_page'=>'cronjob.php', 'params'=>$params));
	echo '<iframe src="'.$iframe_target.'" frameborder="0" width="100%" height="500"></iframe>';
}
?>