<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: autosyncInventory.php 1283 2011-09-30 22:52:17Z derpapsst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

define('ML_LOG_INVENTORY_CHANGE', true);

require_once(DIR_MAGNALISTER_CALLBACK.'callbackFunctions.php');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');


if (!defined('MAGNA_ECHO_UPDATE') && isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
	define('MAGNA_ECHO_UPDATE', true);
	header('Content-Type: text/plain; charset=utf-8');
}

function magnaAutosyncInventories() {
	global $_MagnaShopSession, $magnaConfig;

    /* {Hook} "PreSyncInventory": Runs before synchronization from shop to the marketplaces starts.
        Useful e.g. if you have an external data source for stock keeping.
        You can fill the correct product's quantities into the shop's tables before synchronizing to the marketplaces.
    */
    if (($hp = magnaContribVerify('PreSyncInventory', 1)) !== false) {
        require($hp);
    }

	$verbose = isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true');

	$modules = magnaGetInvolvedMarketplaces();

	MagnaConnector::gi()->setTimeOutInSeconds(600);
	foreach ($modules as $marketplace) {
		$mpIDs = magnaGetInvolvedMPIDs($marketplace);
		if (empty($mpIDs)) {
			//if (function_exists('ml_debug_out')) ml_debug_out('Skip[2] ('.$marketplace.' not booked)'."\n");
			continue;
		}
		foreach ($mpIDs as $mpID) {
			@set_time_limit(60 * 10); // 10 minutes per module
			
			$funcName = false;
			$className = false;
			
			$funcFile = DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php';
			$classFile = DIR_MAGNALISTER_MODULES.strtolower($marketplace).'/crons/'.ucfirst($marketplace).'SyncInventory.php';

			if (file_exists($classFile)) {
				require_once($classFile);
				$className = ucfirst($marketplace).'SyncInventory';
				if (!class_exists($className)) {
					if ($verbose) echo $className.' not found.'."\n";
					continue;
				}
			} else if (file_exists($funcFile)) {
				require_once($funcFile);
				$funcName = 'autoupdate'.ucfirst($marketplace).'Inventory';
				
				if (!function_exists($funcName)) {
					if ($verbose) echo $funcName.' not found.'."\n";
					continue;
				}
			} else {
				if ($verbose) echo 'No sync functions available for '.$marketplace.'('.$mpID.').'."\n";
				continue;
			}

			if (!array_key_exists('db', $magnaConfig) || 
			    !array_key_exists($mpID, $magnaConfig['db'])
			) {
				loadDBConfig($mpID);
			}
			#echo print_m("MP: $marketplace  MPID: $mpID");

			if ($className !== false) {
				if (function_exists('ml_debug_out')) ml_debug_out("\nnew $className($mpID, $marketplace)\n");
				$ic = new $className($mpID, $marketplace);
				$ic->process();
			} else {
				if (function_exists('ml_debug_out')) ml_debug_out("\n$funcName($mpID);\n");
				$funcName($mpID);
			}
		}
		#echo print_m($mpIDs, $marketplace);
	}
	MagnaConnector::gi()->resetTimeOut();
	if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
		echo "\n\nComplete.";
		die();
	}
}
