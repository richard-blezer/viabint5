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
 * $Id: generictests.php 576 2014-12-29 14:28:32Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

header('Content-Type: text/plain; charset=utf-8');
#echo "\xEF\xBB\xBF";
//*
function ml_debug_out($m) {
	echo $m;
	flush();
}
//*/
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/ordertest.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/inventoryEdit.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/syncAmazonOrder.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/checkinTest.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/genEANForSelection.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/orderimport.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/callback.php');

function verifyUniqueSKUs() {
	if (getDBConfigValue('general.keytype', '0', 'pID') != 'artNr') {
		//return true;
	}

	# Verify products
	$countProductsIDs = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_id) FROM '.TABLE_PRODUCTS
	);
	$countProductsModels = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_model) FROM '.TABLE_PRODUCTS.' 
		 WHERE products_model <> \'\' AND products_model IS NOT NULL
	');
	#echo '$countProductsIDs['.$countProductsIDs.'] != $countProductsModels['.$countProductsModels.']'."\n";
	if ($countProductsIDs != $countProductsModels) {
		return false;
	}
	
	return true;
}

//$s = new Shipping();
//var_dump($s);
//var_dump(verifyUniqueSKUs());

/*
$allTables = MagnaDB::gi()->getAvailableTables();
foreach ($allTables as $table) {
	$showCreate = MagnaDB::gi()->fetchRow('SHOW CREATE TABLE '.$table);
	echo $showCreate['Create Table'];
	if (preg_match("/CHARSET=([^\s]*)/i", $showCreate['Create Table'], $match)) {
		echo "\n".'Charset: '.print_m($match[1])."\n";
	} else {
		echo "OMGWTFBBQ\n";
	}
	echo "\n\n";
}
*/

function testMLProduct() {
	$cfg = array (
		'lang' => isset($_GET['lang']) ? $_GET['lang'] : 'de',
		'pid' => (isset($_GET['pid']) ? $_GET['pid'] : 4),
		'onlyoffer' => isset($_GET['onlyoffer']),
		'optionsTmp' => array (
			'sameVariationsToAttributes' => isset($_GET['optionsTmp']['sameVariationsToAttributes']) && ($_GET['optionsTmp']['sameVariationsToAttributes'] == 'true'),
			'allowSingleVariations' => !isset($_GET['optionsTmp']['allowSingleVariations']) || isset($_GET['optionsTmp']['allowSingleVariations']) && ($_GET['optionsTmp']['allowSingleVariations'] == 'true')
		),
		'useold' => isset($_GET['useold']) && ($_GET['useold'] == 'true'),
		'mpid' => isset($_GET['mpid']) && ctype_digit($_GET['mpid']) ? $_GET['mpid'] : '0',
		'info' => array (
			'marketplace' => '',
			'configs' => array(
				'price' => array(),
				'quantity' => array(),
			),
		)
	);
	if (preg_match('/^([a-zA-Z0-9]+,)+[a-zA-Z0-9]+$/', $cfg['lang'])) {
		$cfg['lang'] = explode(',', $cfg['lang']);
	}
	
	if (
		($cfg['mpid'] > 0)
		&& ($cfg['info']['marketplace'] = magnaGetMarketplaceByID($cfg['mpid']))
		&& !empty($cfg['info']['marketplace'])
		&& ($helper = ucfirst($cfg['info']['marketplace']).'Helper')
		&& ($helperFile = DIR_MAGNALISTER_MODULES.$cfg['info']['marketplace'].'/'.$helper.'.php')
		&& file_exists($helperFile)
	) {
		require_once($helperFile);
		if (method_exists($helper, 'loadPriceSettings')) {
			$cfg['info']['configs']['price'] = $helper::loadPriceSettings($cfg['mpid']);
		}
		if (method_exists($helper, 'loadQuantitySettings')) {
			$cfg['info']['configs']['quantity'] = $helper::loadQuantitySettings($cfg['mpid']);
		}
	}
	
	echo print_m($cfg, '$cfg');
	
	if ($cfg['useold']) {
		$product = MLProduct::gi()->getProductByIdOld($cfg['pid'], $cfg['lang']);
	} else {
		MLProduct::gi()->setLanguage($cfg['lang']);
		MLProduct::gi()->setDbMatching('ManufacturerPartNumber', array (
			'Table' => TABLE_PRODUCTS,
			'Column' => 'products_model',
			'Alias' => 'products_id',
		));
		if (!empty($cfg['info']['configs']['price'])) {
			MLProduct::gi()->setPriceConfig($cfg['info']['configs']['price']);
		}
		if (!empty($cfg['info']['configs']['quantity'])) {
			MLProduct::gi()->setQuantityConfig($cfg['info']['configs']['quantity']);
		}

		if ($cfg['onlyoffer']) {
			$product = MLProduct::gi()->getProductOfferById($cfg['pid'], $cfg['optionsTmp']);
		} else {
			$product = MLProduct::gi()->getProductById($cfg['pid'], $cfg['optionsTmp']);
		}
	}
	
	arrayEntitiesToUTF8($product);
	echo print_m($product, '$product');
}

testMLProduct();


function testIdentifySkus() {
	global $_MagnaSession;
	
	$_MagnaSession['mpID'] = 3505;
	$_MagnaSession['currentPlatform'] = magnaGetMarketplaceByID($_MagnaSession['mpID']);
	
	MagnaConnector::gi()->setSubsystem($_MagnaSession['currentPlatform']);
	MagnaConnector::gi()->setAddRequestsProps(array('MARKETPLACEID' => $_MagnaSession['mpID']));
	
	$data = MagnaConnector::gi()->submitRequest(array(
		"ACTION" => "GetInventoryOnlySKUs",
		"SUBSYSTEM" => "eBay"
	));
	
	$lookup = array();
	foreach ($data['DATA'] as $sku) {
		$lookup[$sku] = magnaSKU2pID($sku);
	}
	
	echo print_m($lookup);
	
	$_SESSION['magna_deletedFilter'] = array();
	
}
#testIdentifySkus();


echo '
----------------------------------------------------
 Entire page served in '.microtime2human(microtime(true) -  $_executionTime).'.
----------------------------------------------------
 Updater Time: '.microtime2human($_updaterTime).'.
 API-Request Time: '.microtime2human(MagnaConnector::gi()->getRequestTime()).'.
 Processing Time: '.microtime2human(microtime(true) -  $_executionTime - $_updaterTime - MagnaConnector::gi()->getRequestTime() - MagnaDB::gi()->getRealQueryTime()).'.
----------------------------------------------------
 '.((memory_usage() !== false) ? 'Max. Memory used: '.memory_usage().'.' : '').'
----------------------------------------------------
 DB-Stats:
 	Queries used: '.MagnaDB::gi()->getQueryCount().'
 	Real query time: '.microtime2human(MagnaDB::gi()->getRealQueryTime()).'
----------------------------------------------------
';
if (class_exists('MagnaConnector') && true) {
	$tpR = MagnaConnector::gi()->getTimePerRequest();
	if (!empty($tpR)) {
		foreach ($tpR as $item) {
			echo print_m($item['request'], microtime2human($item['time']).' ['.$item['status'].']', true)."\n";
		}
		echo '----------------------------------------------------'."\n";
	}
	
}
if (class_exists('MagnaDB') && false) {
	$tpR = MagnaDB::gi()->getTimePerQuery();
	if (!empty($tpR)) {
		foreach ($tpR as $item) {
			echo print_m(ltrim(rtrim($item['query'], "\n"), "\n"), microtime2human($item['time']), true)."\n";
		}
		echo '----------------------------------------------------'."\n";
	}
}
#include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
