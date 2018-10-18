<?php 
/*
 #########################################################################
 #                       Shogate GmbH
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # http://www.shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Rev: 54 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

$veytonDir = realpath(dirname(__FILE__).'/../../../');

include_once $veytonDir.'/xtFramework/admin/main.php';
if (!$xtc_acl->isLoggedIn()) {
	die('login required');
}

$url = urldecode($_GET["url"]);
?>
<iframe
	id="_shopgateIFrame"
	style="border: none; width: 100%; height: 100%;"
	src="<?= $url ?>"></iframe>
