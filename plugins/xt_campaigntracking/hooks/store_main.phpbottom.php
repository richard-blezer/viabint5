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

if (XT_CAMPAIGNTRACKING_STATUS=='true') 
{
	if (!isset($_SESSION['campaigntracking_hash']))
	{
		if (isset($_GET[xt_campaigntracking::$key]))
		{
			$hash = $_GET[xt_campaigntracking::$key];
			// check if campaign exists
			$campaignTracking = new xt_campaigntracking($hash);
			if ($campaignTracking)
			{
				$campaignTracking->passedByReference();
				setcookie('campaigntracking_hash', $hash, time() + 86400);
				$_SESSION['campaigntracking_hash'] = $hash;
			}			
		}
		if ($_COOKIE['campaigntracking_hash']) 
		{
			$hash = $_COOKIE['campaigntracking_hash'];
			$campaignTracking = new xt_campaigntracking($hash);
			if ($campaignTracking)
			{
				$_SESSION['campaigntracking_hash'] = $hash;
			}
		}
	}
}
?>