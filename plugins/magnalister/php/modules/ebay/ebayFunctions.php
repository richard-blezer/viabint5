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
 * $Id: ebayFunctions.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

# eBay gibt GMT-Zeit zurueck,
# Format wie '2011-01-07T22:07:23.174Z',
# mache universellen Unix Timestamp daraus
function eBayTimeToTs($eBayTime) {
	return gmmktime(
		substr($eBayTime, 11,2), substr($eBayTime, 14,2), substr($eBayTime, 17,2),
		substr($eBayTime, 5,2),  substr($eBayTime, 8,2),  substr($eBayTime, 0,4)
	);
}

function updateEbayInventoryByEdit($mpID, $updateData) {
	$updateItem = genericInventoryUpdateByEdit($mpID, $updateData);	
	if (!is_array($updateItem)) {
		return false;
	}
	$pID = array_first(array_keys($updateData));

	$requestMode = ('SET' == $updateItem['NewQuantity']['Mode'])? 'NewQuantity':'AddQuantity';
	$QtyToSubmit = ('SUB' == $updateItem['NewQuantity']['Mode'])? -1 * $updateItem['NewQuantity']['Value']: $updateItem['NewQuantity']['Value'];
	$updateData = array(
		'SKU' => $updateItem['SKU'],
		"$requestMode" => (int)$QtyToSubmit,
		'fixed.stocksync' => getDBConfigValue('ebay.stocksync.tomarketplace', $mpID, ''),
		'chinese.stocksync' => getDBConfigValue('ebay.chinese.stocksync.tomarketplace', $mpID, ''),
	);
	$request = array(
		'ACTION' => 'UpdateQuantity',
		'SUBSYSTEM' => 'eBay',
		'MARKETPLACEID' => $mpID,
		'DATA' => $updateData
	);

	if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
		echo print_m($request, __FUNCTION__);
		return true;
	}
	// we dont wait for answer
	MagnaConnector::gi()->setTimeOutInSeconds(1);
	try {
		$result = MagnaConnector::gi()->submitRequest($request);
		#echo print_m($result, '$result');
	} catch (MagnaException $e) {
		if ($e->getCode() == MagnaException::TIMEOUT) {
			//$e->saveRequest();//if there is a really problem cron shold make it
			$e->setCriticalStatus(false);
		}
		#echo print_m($e->getErrorArray(), '$error');
	}
	MagnaConnector::gi()->resetTimeOut();
}

function updateEbayInventoryByOrder($mpID, $boughtItems, $subRelQuant = true) {
	global $_MagnaSession;
    if (!isset($_MagnaSession) || !is_array($_MagnaSession)) {
        $_MagnaSession = array('mpID' => $mpID);
    } else if (!isset($_MagnaSession['mpID'])) {
        $_MagnaSession['mpID'] = $mpID;
    }
	$ess = getDBConfigValue('ebay.stocksync.tomarketplace', $mpID, 'no');
	$ecss = getDBConfigValue('ebay.chinese.stocksync.tomarketplace', $mpID, 'no');
	$syncPrice = getDBConfigValue('ebay.inventorysync.price', $mpID, null) == 'auto';
	$syncPriceC = getDBConfigValue('ebay.chinese.inventorysync.price', $mpID, null) == 'auto';

	if (($ess == 'no') && ($ecss == 'no')) {
		return;
	}
	$data = genericInventoryUpdateByOrder($mpID, $boughtItems, $subRelQuant);
	foreach($data as $i => $updateItem) {
		$requestMode = ('SET' == $updateItem['NewQuantity']['Mode'])? 'NewQuantity':'AddQuantity';
		$QtyToSubmit = ('SUB' == $updateItem['NewQuantity']['Mode'])? -1 * $updateItem['NewQuantity']['Value']: $updateItem['NewQuantity']['Value'];
		$updateData = array(
			'SKU' => $updateItem['SKU'],
			"$requestMode" => (int)$QtyToSubmit,
			'fixed.stocksync' => $ess,
			'chinese.stocksync' => $ecss,
		);
		$pID = magnaSKU2pID($updateItem['SKU'], true);
        # schauen ob Preis eingefroren
        $priceFrozenQuery = 'SELECT Price FROM '.TABLE_MAGNA_EBAY_PROPERTIES
                    .' WHERE mpID = '.$mpID.' AND ';
        if ('artNr' == getDBConfigValue('general.keytype', '0'))
                    $priceFrozenQuery .= ' products_model = \''.$updateItem['SKU'].'\'';
        else
                    $priceFrozenQuery .= ' products_id = '.$pID;
        $priceFrozen = MagnaDB::gi()->fetchOne($priceFrozenQuery);
        if(0.0 == $priceFrozen) $priceFrozen = false;
		/*$variationMatrix = getVariations($pID, null, true, $syncPrice && !$priceFrozen);
        $totalQuantity = makeQuantity($pID);
        if (false != $variationMatrix) {
            # mode ist immer SUB (kommt so aus magnaInventoryUpdateByOrder)
            setVariationQuantity($variationMatrix, $pID, $updateItem['Attributes'], $boughtItems[$i]['NewQuantity']['Value'], 'SUB');
            # wenn Variantions da, hat Anzahl keine Bedeutung, entscheidend ist die variationMatrix
            # es darf dann aber nicht sein dass 0 uebergeben wird, denn das wuerde EndItem ausloesen
            unset($updateData["$requestMode"]);
            $updateData['NewQuantity'] = (int)$totalQuantity;
        }
		$updateData['Variations'] = $variationMatrix;*/
		try {
			$request = array(
				'ACTION' => 'UpdateQuantity',
				'SUBSYSTEM' => 'eBay',
				'MARKETPLACEID' => $mpID,
				'DATA' => $updateData,
			);
			if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
				echo print_m($request, __FUNCTION__);
			} else {
				$result = MagnaConnector::gi()->submitRequest($request);
				#echo print_m($result, '$result');
			}
		} catch (MagnaException $e) {
			/* don't show errors for now. should be saved in the errorlog instead. */
			$e->setCriticalStatus(false);
			/* Do NOT save the request incase of timeout. Since this is a synchronous
			 * call to ebay it may take up to 9 seconds before we receive a reply.
			 * The reply isn't important anyway (as it isn't processed anyway) so no need
			 * to repeat the request later on.
			 */
			/*
			if ($e->getCode() == MagnaException::TIMEOUT) {
				$e->saveRequest();
			}
			*/
			#echo print_m($e->getErrorArray(), '$error');
		}
	}
}

function geteBayShippingDetails() {
	global $_MagnaSession;

	$mpID = $_MagnaSession['mpID'];
	$site = getDBConfigValue('ebay.site', $mpID);
	
	initArrayIfNecessary($_MagnaSession, array($mpID, $site, 'eBayShippingDetails'));
	
	if (!empty($_MagnaSession[$mpID][$site]['eBayShippingDetails'])) {
		return $_MagnaSession[$mpID][$site]['eBayShippingDetails'];
	}
	try {
		$shippingDetails = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetShippingServiceDetails',
			'DATA' => array('Site' => $site),
		));
		$shippingDetails = $shippingDetails['DATA'];
	} catch (MagnaException $e) {
		return false;
	}
	unset($shippingDetails['Version']);
	unset($shippingDetails['Timestamp']);
	unset($shippingDetails['Site']);
	foreach ($shippingDetails['ShippingServices'] as &$service) {
		$service['Description'] = fixHTMLUTF8Entities($service['Description']);
	}
	foreach ($shippingDetails['ShippingLocations'] as &$location) {
		$location = fixHTMLUTF8Entities($location);
	}
	$_MagnaSession[$mpID][$site]['eBayShippingDetails'] = $shippingDetails;
	return $_MagnaSession[$mpID][$site]['eBayShippingDetails'];
}

function geteBayShippingServicesList() {
	$shippingDetails = geteBayShippingDetails();
	$servicesList = array();
	return $servicesList;
}

function geteBayLocalShippingServicesList() {
	$shippingDetails = geteBayShippingDetails();
	$servicesList = array();
	foreach($shippingDetails['ShippingServices'] as $service=>$serviceData) {
		if ('1' == $serviceData['InternationalService']) continue;
	#	$servicesList["$service"] = utf8_decode($serviceData['Description']);
		$servicesList["$service"] = $serviceData['Description'];
	}
	return $servicesList;
}

function geteBayInternationalShippingServicesList() {
	$shippingDetails = geteBayShippingDetails();
	$servicesList = array('' => ML_EBAY_LABEL_NO_INTL_SHIPPING);
	foreach($shippingDetails['ShippingServices'] as $service=>$serviceData) {
		if ('0' == $serviceData['InternationalService']) continue;
	#	$servicesList["$service"] = utf8_decode($serviceData['Description']);
		$servicesList["$service"] = $serviceData['Description'];
	}
	return $servicesList;
}

function geteBayShippingLocationsList() {
	$shippingDetails = geteBayShippingDetails();
	return $shippingDetails['ShippingLocations'];
}

function geteBayShippingDiscountProfiles($forceRefresh = false) {
	global $_MagnaSession;
	$mpID = $_MagnaSession['mpID'];
	initArrayIfNecessary($_MagnaSession, array($mpID, 'eBayShippingDiscountProfiles'));
	if ($forceRefresh) unset($_MagnaSession[$mpID]['eBayShippingDiscountProfiles']);

	$storedProfileData = getDBConfigValue('ebay.shippingprofiles', $mpID);
	if (!empty($storedProfileData)) {
		if (($storedProfileData['Timestamp'] < time() - 60) || $forceRefresh)
			unset($storedProfileData);
	}

	if (!empty($_MagnaSession[$mpID]['eBayShippingDiscountProfiles'])) {
		return $_MagnaSession[$mpID]['eBayShippingDiscountProfiles'];
	}
	if (empty($storedProfileData)) {
		try {
			$shippingDiscountProfiles = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetShippingDiscountProfiles'
			));
		} catch (MagnaException $e) {
			return false;
		}
		$shippingDiscountProfiles['DATA']['Timestamp'] = time();
		setDBConfigValue('ebay.shippingprofiles', $mpID, $shippingDiscountProfiles['DATA'], true);
		$storedProfileData = $shippingDiscountProfiles['DATA'];
	}
	$profiles = array(0 => '&nbsp; &mdash; &nbsp;');
	$myPrice = new SimplePrice(null,
					(getCurrencyFromMarketplace($_MagnaSession['mpID'])
					? getCurrencyFromMarketplace($_MagnaSession['mpID'])
					: DEFAULT_CURRENCY)
			   );
	# aufbereiten
	if (array_key_exists('Profiles', $storedProfileData)) {
		foreach ($storedProfileData['Profiles'] as $key => $profile) {
			if (empty($profile['ProfileName'])) $profile['ProfileName'] = $key;
			$profiles[$key] = $profile['ProfileName']. ' ('.$myPrice->setPrice($profile['EachAdditionalAmount'])->format().' '.ML_EBAY_LABEL_EACH_ONE_MORE.')';
		}
	}
	$_MagnaSession[$mpID]['eBayShippingDiscountProfiles'] = $profiles;
	return $_MagnaSession[$mpID]['eBayShippingDiscountProfiles'];
}

function geteBaySiteID() {
    global $_MagnaSession;
	$mpID = $_MagnaSession['mpID'];
    if (isset($_MagnaSession[$mpID]['SiteID']))
        return $_MagnaSession[$mpID]['SiteID'];
    try {
        $baseCall = MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'GeteBayOfficialTime'
        ));
    } catch (MagnaException $e) {
        return 77;
    }
    $_MagnaSession[$mpID]['SiteID'] = $baseCall['DATA']['SiteID'];
    return $_MagnaSession[$mpID]['SiteID'];
}

function geteBayPaymentOptions() {
	global $_MagnaSession;

	#echo print_m($_MagnaSession,'$_MagnaSession');

	$mpID = $_MagnaSession['mpID'];
	$site = getDBConfigValue('ebay.site', $mpID);

	#echo print_m($site,'$site');
	
	if(@isset($_MagnaSession[$mpID]['eBayPaymentOptions']['Site']) && 
		($_MagnaSession[$mpID]['eBayPaymentOptions']['Site'] == getDBConfigValue('ebay.site', $mpID, '999'))
	) { # 999 um keine falsche Gleichheit bei nicht gesetzten Werten zu bekommen
		return $_MagnaSession[$mpID][$site]['eBayPaymentOptions'];
	} else {
		try { $paymentOptions = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetPaymentOptions',
				'DATA' => array('Site' => $site),
			  ));
		} catch (MagnaException $e) {
			$paymentOptions = array(
				'DATA' => false
			);
		}
		if (!is_array($paymentOptions) || @empty($paymentOptions['DATA'])) {
			return false;
		}
		foreach ($paymentOptions['DATA']['PaymentOptions'] as &$option) {
			$option = fixHTMLUTF8Entities($option);
		}
	}
	$_MagnaSession[$mpID]['eBayPaymentOptions'] = $paymentOptions['DATA']['PaymentOptions'];
	return $paymentOptions['DATA']['PaymentOptions'];
}

function geteBayReturnPolicyDetails() {
	global $_MagnaSession;

	#echo print_m($_MagnaSession,'$_MagnaSession');

	$mpID = $_MagnaSession['mpID'];
	$site = getDBConfigValue('ebay.site', $mpID);

	#echo print_m($site,'$site');
	if(@isset($_MagnaSession[$mpID]['eBayReturnPolicyDetails']['Site']) &&
		($_MagnaSession[$mpID]['eBayReturnPolicyDetails']['Site'] == getDBConfigValue('ebay.site', $mpID, '999'))
	) { # 999 um keine falsche Gleichheit bei nicht gesetzten Werten zu bekommen
		return $_MagnaSession[$mpID][$site]['eBayReturnPolicyDetails'];
	} else {
		try { $returnPolicyDetails = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetReturnPolicyDetails',
			'DATA' => array('Site' => $site),
		));
		} catch (MagnaException $e) {
			$returnPolicyDetails = array(
				'DATA' => false
			);
		}
		if (!is_array($returnPolicyDetails) || @empty($returnPolicyDetails['DATA'])) {
			return false;
		}
		arrayEntitiesFixHTMLUTF8($returnPolicyDetails['DATA']['ReturnPolicyDetails']);
	}
	$_MagnaSession[$mpID]['eBayReturnPolicyDetails'] = $returnPolicyDetails['DATA']['ReturnPolicyDetails'];
	return $returnPolicyDetails['DATA']['ReturnPolicyDetails'];
}

# einzelnes Detail: ReturnsAccepted, ReturnsWithin, ShippingCostPaidBy oder RefundOption
# (letzteres noch nicht implementiert, gibts in Europa nicht)
function geteBaySingleReturnPolicyDetail($detailName) {
	global $_MagnaSession;
	$mpID = $_MagnaSession['mpID'];
	if ( (!isset ($_MagnaSession[$mpID]['eBayReturnPolicyDetails']))
		|| (!is_array($_MagnaSession[$mpID]['eBayReturnPolicyDetails']))) {
		$returnPolicyDetails = geteBayReturnPolicyDetails();
	} else {
		$returnPolicyDetails = $_MagnaSession[$mpID]['eBayReturnPolicyDetails'];
	}
	if (!isset($returnPolicyDetails[$detailName])) {
		return array('' => '-');
	}
	return $returnPolicyDetails[$detailName];
}


function getEBayAttributes($cID, $mode, $preselectedValues = array()) {
	global $_MagnaSession;
	# erst schauen obs ItemSpecifics gibt (sind neuer & das andere ist uU deprecated)
	$itemSpecs = getEBayItemSpecifics($cID, $mode, $preselectedValues);
	if (!empty($itemSpecs)) return $itemSpecs;
	try {
		$attrOptions = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetAttributes',
			'DATA' => array (
				'CategoryID' => $cID,
				'FormStructure' => true,
				'Site' => getDBConfigValue('ebay.site', $_MagnaSession['mpID']),
			)
		));
	} catch (MagnaException $e) {
		return '';
	}
	if (!array_key_exists('attributes', $attrOptions['DATA'])
		|| empty($attrOptions['DATA']['attributes'])
	) {
		#return getEBayItemSpecifics($cID, $mode, $preselectedValues);
		return '';
	}
	$attrOptions = $attrOptions['DATA'];
	$attrOptions['attributes']['key'] = array('Attributes', $mode);
	$attrOptions['attributes']['head'] = ML_EBAY_LABEL_ATTRIBUTES_FOR.' '.(($mode == 1) 
		? ML_LABEL_EBAY_PRIMARY_CATEGORY
		: ML_LABEL_EBAY_SECONDARY_CATEGORY
	);
	if (!is_array($preselectedValues)) {
		$preselectedValues = json_decode($preselectedValues, true);
	}
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/GenerateProductsDetailInput.php');
	if (!empty($preselectedValues)) {
		if (!isset($preselectedValues[0])) {
			if (isset($preselectedValues[1]))
				$preselectedValues = $preselectedValues[1];
			else if (isset($preselectedValues[2]))
				$preselectedValues = $preselectedValues[2];
		}
		$gPDI = new GenerateProductsDetailInput($attrOptions, $preselectedValues);
	} else
		$gPDI = new GenerateProductsDetailInput($attrOptions);
	return $gPDI->render();
}

function getEBayItemSpecifics($cID, $mode, $preselectedValues='') {
	global $_MagnaSession;
	try {
		$specsOptions = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetItemSpecifics',
			'DATA' => array (
				'CategoryID' => $cID,
				'FormStructure' => true,
				'Site' => getDBConfigValue('ebay.site', $_MagnaSession['mpID']),
			)
		));
	} catch (MagnaException $e) {
		return '';
	}
	if (!array_key_exists('specifics', $specsOptions['DATA'])
		|| empty($specsOptions['DATA']['specifics'])
	) {
		return '';
	}
	$specsOptions = $specsOptions['DATA'];
	$specsOptions['specifics']['key'] = array('ItemSpecifics', $mode);
	$specsOptions['specifics']['head'] = ML_EBAY_LABEL_ATTRIBUTES_FOR.' '.(($mode == 1) 
		? ML_LABEL_EBAY_PRIMARY_CATEGORY
		: ML_LABEL_EBAY_SECONDARY_CATEGORY
	);
	if (!is_array($preselectedValues)) {
		$preselectedValues = json_decode($preselectedValues, true);
	}
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/GenerateProductsDetailInput.php');
	if (!empty($preselectedValues)) {
		if (!isset($preselectedValues[0])) {
			if (isset($preselectedValues[1]))
				$preselectedValues = $preselectedValues[1];
			else if (isset($preselectedValues[2]))
				$preselectedValues = $preselectedValues[2];
		}
		$gPDI = new GenerateProductsDetailInput($specsOptions, $preselectedValues);
	} else
		$gPDI = new GenerateProductsDetailInput($specsOptions);
	return $gPDI->render();
}

function VariationsEnabled($cID) {
	try {
		$VariationsEnabledResult = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'VariationsEnabled',
			'DATA' => array ( 'CategoryID' => $cID, ),
			));
	} catch (MagnaException $e) {
		return false;
	}
	if (!array_key_exists('VariationsEnabled', $VariationsEnabledResult['DATA']))
		return false;
	if ('true' == (string)$VariationsEnabledResult['DATA']['VariationsEnabled'])
		return true;
	else return false;
}

function substitutePictures($tmplStr, $pID, $imagePath) {
	$undo = ml_extractBase64($tmplStr);
	# Tabelle nur bei xtCommerce- und Gambio- Shops vorhanden (nicht OsC)
	if (   defined('TABLE_MEDIA')      && MagnaDB::gi()->tableExists(TABLE_MEDIA)
	    && defined('TABLE_MEDIA_LINK') && MagnaDB::gi()->tableExists(TABLE_MEDIA_LINK)
	) {
		$pics = MagnaDB::gi()->fetchArray('SELECT
            id image_nr, file image_name
			FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml
            WHERE m.type=\'images\' AND ml.class=\'product\' AND m.id=ml.m_id AND ml.link_id='.$pID);
		$i = 2;
		# Ersetze #PICTURE2# usw. (#PICTURE1# ist das Hauptbild und wird vorher ersetzt)
		foreach($pics as $pic) {
			$tmplStr = str_replace('#PICTURE'.$i.'#', "<img src=\"".$imagePath.$pic['image_name']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
				 preg_replace( '/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(#PICTURE'.$i.'#)/', '\1\2\3'.$imagePath.$pic['image_name'], $tmplStr));
			$i++;
		}
		# Uebriggebliebene #PICTUREx# loeschen
		$tmplStr = preg_replace('/<[^<]*(src|SRC|href|HREF|rev|REV)\s*=\s*(\'|")#PICTURE\d+#(\'|")[^>]*\/*>/', '', $tmplStr);
		$tmplStr = preg_replace('/#PICTURE\d+#/','', $tmplStr);
		$str = ml_restoreBase64($tmplStr, $undo);
	} else {
		$tmplStr = preg_replace('/<[^<]*(src|SRC|href|HREF|rev|REV)\s*=\s*(\'|")#PICTURE\d+#(\'|")[^>]*\/*>/', '', $tmplStr);
		$tmplStr = preg_replace('/#PICTURE\d+#/','', $tmplStr);
		$str = ml_restoreBase64($tmplStr, $undo);
	}
	# ggf. leere image tags loeschen
	$str = preg_replace('/<img[^>]*src=(""|\'\')[^>]*>/i', '', $str);
	return $str;
}

# Hilfsfunktion: Preis bestimmen
# priceType: == ListingType oder BuyItNowPrice
function makePrice($pID, $priceType, $takePrepared = false, $variationPrice = 0.0) {
	global $_MagnaSession;
	if ($takePrepared) {
		$iBuyItNowPrice = magnalisterEbayGetPriceByType($pID, $priceType);
		if ($iBuyItNowPrice !== false) {
			return $iBuyItNowPrice;
		}
	}
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	switch ($priceType) {
		case 'Chinese': {
			$which = 'chinese';
			break;
		}
		case 'BuyItNowPrice': {
			$which = 'chinese.buyitnow';
			break;
		}
		default: { # 'FixedPriceItem' oder 'StoresFixedPrice'
			$which = 'fixed';
			break;
		}
	}
	$myPrice = new SimplePrice(null,getCurrencyFromMarketplace($_MagnaSession['mpID']));
	if ($variationPrice) {
		$myPrice->setPriceFromDB($pID, $_MagnaSession['mpID'], $which)->addLump($variationPrice)->finalizePrice($pID, $_MagnaSession['mpID'], $which);
	} else {
		$myPrice->setFinalPriceFromDB($pID, $_MagnaSession['mpID'], $which);
	}

	return $myPrice->getPrice();
}

# Hilfsfunktion: Variation-Preis zu einem Grundpreis berechnen
# (benoetigt wenn Grundpreis per Hand geaendert)
# Grundpreis ist brutto, Varianten-Aufschlaege netto, daher kann man nicht einfach addieren
function addVarPriceToPrice($pID, $mainPrice, $varPrice) {
	global $_MagnaSession;
	$myPrice = new SimplePrice($mainPrice, getCurrencyFromMarketplace($_MagnaSession['mpID']));
	$myPrice->removeTaxByPID($pID)->addLump($varPrice)->addTaxByPID($pID);
	return $myPrice->getPrice();
}

# Hilfsfunktion: Anzahl bestimmen
function makeQuantity($pID, $ListingType = 'StoresFixedPrice') {
	global $_MagnaSession;
	switch ($ListingType) {
		case 'Chinese': {
			$calc_method = 'lump';
			$qValue = 1;
			break;
		}
		default: { # 'FixedPriceItem' oder 'StoresFixedPrice'
            $calc_method = getDBConfigValue('ebay.fixed.quantity.type', $_MagnaSession['mpID']);
			$qValue = (int)getDBConfigValue('ebay.fixed.quantity.value', $_MagnaSession['mpID']);
			$maxQuantity = (int)getDBConfigValue('ebay.maxquantity', $_MagnaSession['mpID'], 0);
			if (0 == $maxQuantity) $maxQuantity = PHP_INT_MAX;
			break;
		}
	}
	if ('lump' == $calc_method) {
		return $qValue;
	}
	$shop_stock = 0;
	# Nehme Anzahl Varianten, soweit Varianten lt konfig aktiviert, und soweit solche existieren
    //if (('Chinese' != $ListingType) && getDBConfigValue(array($_MagnaSession['currentPlatform'].'.usevariations', 'val'), $_MagnaSession['mpID'], true) && variationsExist($pID)) {
    //    if ('stock' == $calc_method)
	//	    $shop_stock = min(getProductVariationsQuantity($pID), $maxQuantity);
    //    else if ('stocksub' == $calc_method)
	//	    $shop_stock = min(getProductVariationsQuantity($pID, $qValue), $maxQuantity);
    //        return $shop_stock;
    //}
    # Keine Varianten da, nehme Stammartikel
		$shop_stock = MagnaDB::gi()->fetchOne('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id ='.$pID);
	if ('stock' == $calc_method) {
		return min($shop_stock, $maxQuantity);
	} else if ('stocksub' == $calc_method) {
		return min(max(0, $shop_stock - $qValue), $maxQuantity);
	} else {
		return 0;
	}
}

# Hilfsfunktion: Varianten-Matrix fuer die Einstellung aufbauen
function getVariations($pID, $otherMainPrice = null) {
	global $_MagnaSession;
	$variations = array();
	$namelist   = array();
	$valuelist   = array();
	return false;
}

function geteBayCategoryPath($CategoryID, $StoreCategory = false, $justImported = false) {
    global $_MagnaSession;
	$appendedText = '&nbsp;<span class="cp_next">&gt;</span>&nbsp;';
    if ($StoreCategory) {
    	$SiteID = $_MagnaSession['mpID'];
    } else {
    	$SiteID = geteBaySiteID();
    }
	$StoreCategory = $StoreCategory ? '1' : '0';
	$catPath = '';
	do {
		# Ermittle Namen, CategoryID und ParentID,
		# dann das gleiche fuer die ParentCategory usw.
		# bis bei Top angelangt (CategoryID = ParentID)
		$yCP = MagnaDB::gi()->fetchRow('
			SELECT CategoryID, CategoryName , ParentID
			  FROM '.TABLE_MAGNA_EBAY_CATEGORIES.'
			 WHERE CategoryID=\''.$CategoryID.'\'
			 AND StoreCategory=\''.$StoreCategory.'\'
			 AND SiteID = \''.$SiteID.'\'
			 ORDER BY InsertTimestamp DESC LIMIT 1
		');
		if ($yCP === false) break;
		if (empty($catPath)) {
			$catPath = fixHTMLUTF8Entities($yCP['CategoryName']);
		} else {
			$catPath = fixHTMLUTF8Entities($yCP['CategoryName']) . $appendedText . $catPath;
		}
		$CategoryID = $yCP['ParentID'];
	} while ($yCP['CategoryID'] != $yCP['ParentID']);
	
	if (($yCP === false) && ($justImported == true)) {
		return '<span class="invalid">'.ML_LABEL_INVALID.'</span>';
	}
	if (($yCP === false) && ($justImported == false)) {
        if ($StoreCategory) {
            require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayCategoryMatching.php');
            $cm = new eBayCategoryMatching();
            $cm->importeBayStoreCategories();
        } else {
            importeBayCategoryPath($CategoryID);
        }
        return geteBayCategoryPath($CategoryID, $StoreCategory, true);
    }
	return $catPath;
}

# Die Funktion wird verwendet beim Aufruf der Kategorie-Zuordnung, nicht vorher.
# Beim Aufruf werden die Hauptkategorien gezogen,
# und beim Anklicken der einzelnen Kategorie die Kind-Kategorien, falls noch nicht vorhanden.
function importeBayCategoryPath($CategoryID) {
	global $_MagnaSession;
	try {
		$categories = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetCategoryWithAncestors',
			'DATA' => array (
				'CategoryID' => $CategoryID,
				'Site' => getDBConfigValue('ebay.site', $_MagnaSession['mpID'])
			),
		));
	} catch (MagnaException $e) {
		$categories = array(
			'DATA' => false
		);
	}
	if (!is_array($categories['DATA']) || empty($categories['DATA'])) {
		return false;
	}
	$now = time();
	foreach($categories['DATA'] as &$curRow) {
		$curRow['InsertTimestamp'] = $now;
		$curRow['StoreCategory'] = '0';
	}
	#$delete_query = 'DELETE FROM '.TABLE_MAGNA_EBAY_CATEGORIES
	#	.' WHERE StoreCategory=\'0\'
	#	AND SiteID = '.$categories['DATA'][0]['SiteID'].'
	#	AND ParentID = ';
	# ganz oben ist CategoryID == ParentID
	#if (0 == $ParentID)	$delete_query .= 'CategoryID';
	#else			$delete_query .= $ParentID.' AND ParentID <> CategoryID';
	#MagnaDB::gi()->query($delete_query);
	#echo print_m($categories['DATA'], __FUNCTION__);
	MagnaDB::gi()->batchinsert(TABLE_MAGNA_EBAY_CATEGORIES, $categories['DATA'], true);
	return true;
}

# Hilfsfunktion fuer SaveEBaySingleProductProperties und SaveEBayMultipleProductProperties
# bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
function prepareEBayPropertiesRow($pID, $itemDetails) {
	global $_MagnaSession;
	
	$row = array();
	$row['mpID'] = $_MagnaSession['mpID'];
	$row['products_id'] = $pID;
	$row['products_model'] = MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id ='.$pID);
	$row['Site']            = $itemDetails['Site'];
	$row['PrimaryCategory'] = $itemDetails['PrimaryCategory'];
	if (!empty($itemDetails['PrimaryCategory'])) {
		$row['PrimaryCategoryName'] = MagnaDB::gi()->fetchOne('SELECT CategoryName FROM '.TABLE_MAGNA_EBAY_CATEGORIES.' WHERE CategoryID ='.$itemDetails['PrimaryCategory'].' LIMIT 1');
	}
	if (!empty($itemDetails['SecondaryCategory'])) {
		$row['SecondaryCategory'] = $itemDetails['SecondaryCategory'];
		$row['SecondaryCategoryName'] = MagnaDB::gi()->fetchOne('SELECT CategoryName FROM '.TABLE_MAGNA_EBAY_CATEGORIES.' WHERE CategoryID ='.$itemDetails['SecondaryCategory'].' LIMIT 1');
	}
	if (!empty($itemDetails['StoreCategory'])) {
		$row['StoreCategory'] = $itemDetails['StoreCategory'];
	}
	if (!empty($itemDetails['StoreCategory2'])) {
		$row['StoreCategory2'] = $itemDetails['StoreCategory2'];
	}
	$row['ListingType']     = $itemDetails['ListingType'];
	$row['ListingDuration'] = $itemDetails['ListingDuration'];
	$row['PaymentMethods']  = json_encode($itemDetails['PaymentMethods']);
	if (!empty($itemDetails['Attributes'])) {
		$row['Attributes'] = json_encode($itemDetails['Attributes']);
	} elseif ($oldAttributes = MagnaDB::gi()->fetchOne('SELECT Attributes FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' WHERE products_id ='.$pID.' AND mpID = '.$_MagnaSession['mpID'])) {
		$row['Attributes'] = $oldAttributes;
	}
	if (!empty($itemDetails['ItemSpecifics'])) {
		 arrayEntitiesFixHTMLUTF8($itemDetails['ItemSpecifics']);
		$row['ItemSpecifics'] = json_encode($itemDetails['ItemSpecifics']);
	} elseif ($oldItemSpecifics = MagnaDB::gi()->fetchOne('SELECT ItemSpecifics FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' WHERE products_id ='.$pID.' AND mpID = '.$_MagnaSession['mpID'])) {
		$row['ItemSpecifics'] = $oldItemSpecifics;
	}

	$row['ConditionID'] = $itemDetails['ConditionID'];
	$ShippingDetails = array();
	$ShippingDetails['ShippingServiceOptions'] = array();
	foreach($itemDetails['ebay_default_shipping_local'] as $key => $localService) {
		$ShippingDetails['ShippingServiceOptions'][$key] = array(
			'ShippingService' => $localService['service'],
			'ShippingServiceCost' => priceToFloat($localService['cost']),
		);
		if (   array_key_exists('addcost', $localService)
			&& isset($localService['addcost'])
		) {
			$ShippingDetails['ShippingServiceOptions'][$key]['ShippingServiceAdditionalCost'] = priceToFloat($localService['addcost']);
		}
		if ('=GEWICHT' == strtoupper($localService['cost'])) {
			$ShippingDetails['ShippingServiceOptions'][$key]['ShippingServiceCost'] = '=GEWICHT';
			$ShippingDetails['ShippingServiceOptions'][$key]['ShippingServiceAdditionalCost'] = 0.0;
		}
		if (   !isset($next_service)
		    && (is_numeric($ShippingDetails['ShippingServiceOptions'][$key]['ShippingServiceCost']))
		    && (0.0 == $ShippingDetails['ShippingServiceOptions'][$key]['ShippingServiceCost'])
		    && (0.0 == $ShippingDetails['ShippingServiceOptions'][$key]['ShippingServiceAdditionalCost'])
		) {
		$ShippingDetails['ShippingServiceOptions'][$key]['FreeShipping'] = 1;
		}
		$next_service = true; # FreeShipping darf nur beim 1ten Service gesetzt sein
	}
	$row['DispatchTimeMax'] = array_key_exists('dispatchTime', $itemDetails)? $itemDetails['dispatchTime']:getDBConfigValue('ebay.DispatchTimeMax', $_MagnaSession['mpID'],30);
	if (isset($itemDetails['localProfile'])) {
		$ShippingDetails['LocalProfile'] = $itemDetails['localProfile'];
	}
	if ('on' == $itemDetails['localPromotionalDiscount']) {
		$ShippingDetails['LocalPromotionalDiscount'] = 'true';
	} else {
		$ShippingDetails['LocalPromotionalDiscount'] = 'false';
	}
	$ShippingDetails['InternationalShippingServiceOption'] = array();
	if (is_array($itemDetails['ebay_default_shipping_international'])) {
		foreach($itemDetails['ebay_default_shipping_international'] as $key => $intlService) {
			if (empty($intlService['service'])) break;
			$ShippingDetails['InternationalShippingServiceOption'][$key] = array(
				'ShippingService' => $intlService['service'],
				'ShippingServiceCost' => priceToFloat($intlService['cost']),
				'ShipToLocation' => $intlService['location']
			);
		}
		if (   array_key_exists('addcost', $intlService)
			&& isset($intlService['addcost'])
		) {
			$ShippingDetails['InternationalShippingServiceOption'][$key]['ShippingServiceAdditionalCost'] = priceToFloat($intlService['addcost']);
		}
	}
	if (0 == count($ShippingDetails['InternationalShippingServiceOption'])) {
	 	unset($ShippingDetails['InternationalShippingServiceOption']);
	}
	if (isset($itemDetails['internationalProfile'])) {
		$ShippingDetails['InternationalProfile'] = $itemDetails['internationalProfile'];
	}
	if ('on' == $itemDetails['internationalPromotionalDiscount']) {
		$ShippingDetails['InternationalPromotionalDiscount'] = 'true';
	} else {
		$ShippingDetails['InternationalPromotionalDiscount'] = 'false';
	}
	$row['ShippingDetails'] = json_encode($ShippingDetails);
	# Noch nicht verifiziert:
	$row['Verified'] = 'OPEN';
	return $row;
}

function eBayInsertPrepareData($data) {	
	$data['topPrimaryCategory']	  = $data['PrimaryCategory']      == NULL ? '': $data['PrimaryCategory'];
	$data['topSecondaryCategory'] = $data['topSecondaryCategory'] == NULL ? '': $data['SecondaryCategory'];
	$data['topStoreCategory1']    = $data['topStoreCategory1']    == NULL ? '': $data['StoreCategory'];
	$data['topStoreCategory2']    = $data['topStoreCategory2']    == NULL ? '': $data['StoreCategory2'];
	/* {Hook} "eBayInsertPrepareData": Enables you to modify the prepared product data before it will be saved.<br>
	   Variables that can be used:
	   <ul>
		<li><code>$data</code>: The data of a product.</li>
		<li><code>$data['mpID']</code>: The ID of the marketplace.</li>
	   </ul>
	 */
	if (($hp = magnaContribVerify('eBayInsertPrepareData', 1)) !== false) {
		require($hp);
	}
	MagnaDB::gi()->insert(TABLE_MAGNA_EBAY_PROPERTIES, $data, true);
}

function SaveEBaySingleProductProperties($pID, $itemDetails) {
	global $_MagnaSession;
	$row = prepareEBayPropertiesRow($pID, $itemDetails);
	$row['Title'] = trim(strip_tags(html_entity_decode($itemDetails['Title'])));
	if (('on' == $itemDetails['enableSubtitle']) && !empty($itemDetails['Subtitle'])) {
		$row['Subtitle'] = trim(strip_tags($itemDetails['Subtitle']));
	}
	if (!empty($itemDetails['PictureURL'])) {
		$row['PictureURL'] = trim($itemDetails['PictureURL']);
	}
	if (!empty($itemDetails['GalleryURL']) && ('on' == $itemDetails['enableGallery'])) {
		$row['GalleryURL'] = trim($itemDetails['GalleryURL']);
	}
	 if ('on' == $itemDetails['privateListing']) {
        $row['PrivateListing'] = '1';
    }
    if (('on' == $itemDetails['bestOfferEnabled']) && ('Chinese' != $itemDetails['ListingType'])){
        $row['BestOfferEnabled'] = '1';
    }
    if (!empty($itemDetails['startTime'])) {
        $row['StartTime'] = $itemDetails['startTime'];
    }
    if (!empty($itemDetails['hitcounter'])) {
        $row['HitCounter'] = $itemDetails['hitcounter'];
    }

	// only set price if a chinese auction otherwise set it to zero
	if (('true' == $itemDetails['isPriceFrozen']) && ('Chinese' == $itemDetails['ListingType'])) {
		if ($itemDetails['frozenPrice'] == (string)(float) $itemDetails['frozenPrice']) {
			$row['Price'] = $itemDetails['frozenPrice'];	
		} else {
			$row['Price'] = priceToFloat($itemDetails['frozenPrice']);
		}
        # Einfrieren, aber nicht ausgefuellt => berechneten Preis einfrieren
        if (0.00 == $row['Price']) $row['Price'] = priceToFloat($itemDetails['Price']);
	} else {
		$row['Price'] = (float)0;
	}
	if (   isset($itemDetails['isPriceFrozen'])
	   && isset($itemDetails['enableBuyItNowPrice'])
	   && !empty($itemDetails['BuyItNowPrice'])
	   && ('Chinese' == $itemDetails['ListingType'])
	  ) {
		if ($itemDetails['BuyItNowPrice'] == (string)(float) $itemDetails['BuyItNowPrice']) {
			$row['BuyItNowPrice'] = $itemDetails['BuyItNowPrice'];
		} else {
			$row['BuyItNowPrice'] = priceToFloat($itemDetails['BuyItNowPrice']);
		}
	}
	$row['Description'] = trim($itemDetails['Description']);
	#echo print_m($row, 'final');
	# doppelte Eintraege verhindern
	if ('artNr' == getDBConfigValue('general.keytype', '0')) {
		MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, array (
			'mpID' => $_MagnaSession['mpID'],
			'products_model' => $row['products_model']
		));
	} else {
		MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, array (
			'mpID' => $_MagnaSession['mpID'],
			'products_id' => $pID
		));
	}

	// reset frozen price if not chinese auction
	if ('Chinese' != $itemDetails['ListingType']) {
		$row['Price'] = (float)0;
	}

	$row['PreparedTs'] = date('Y-m-d H:i:s');
	eBayInsertPrepareData($row);
}

function eBaySubstituteTemplate($mpID, $pID, $template, $substitution) {
	/* {Hook} "eBaySubstituteTemplate": Enables you to extend the eBay Template substitution (e.g. use your own placeholders).<br>
	Variables that can be used:
	<ul><li><code>$mpID</code>: The ID of the marketplace.</li>
	<li><code>$pID</code>: The ID of the product (Table <code>products.products_id</code>).</li>
	<li><code>$template</code>: The eBay product template.</li>
	<li><code>$substitution</code>: Associative array. Keys are placeholders, Values are their content.</li>
	</ul>
	*/
	if (($hp = magnaContribVerify('eBaySubstituteTemplate', 1)) !== false) {
	require($hp);
	}
	 
	return substituteTemplate($template, $substitution);
}

function SaveEBayMultipleProductProperties($pIDs, $itemDetails) {
	global $_MagnaSession;
	# Analog zu SaveEBaySingleProductProperties, aber
	# Title, (Subtitle), PictureURL aus der Datenbank
	# und Descriptions zusammenbauen
	if (!is_array($pIDs)) {
		if(!empty($pIDs)) $pIDs = array($pIDs);
		else return false;
	}
	$more_data_select = 'SELECT p.products_id products_id, p.products_model products_model, pd.products_name Title, ';
	if (MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION)) {
		$more_data_select .= ' pd.products_short_description products_short_description, ';
	} else {
		$more_data_select .= ' \'\' products_short_description, ';
	}
	$language_code = MagnaDB::gi()->fetchOne('SELECT code FROM '.TABLE_LANGUAGES.' WHERE languages_id = '.getDBConfigValue('ebay.lang', $_MagnaSession['mpID'],999));
	if (false === $language_code) $language_code = $_SESSION['magna']['selected_language'];
	$more_data_select .=  ' pd.products_description description, 
				p.products_price Price,	p.products_image image 
				FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
				WHERE p.products_id = pd.products_id
				AND pd.language_code = \''.$language_code.'\'
				AND p.products_id IN ('.implode($pIDs, ', ').')';
	
	$more_data = MagnaDB::gi()->fetchArray($more_data_select);
	$prefilled_data_select = 'SELECT products_id, Title, Subtitle FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' WHERE products_id IN ('.implode($pIDs, ', ').')';
	$prefilled_data = MagnaDB::gi()->fetchArray($prefilled_data_select);
	if (is_array($prefilled_data)) {
		$prefilled_data_by_pID = array();
		foreach ($prefilled_data as $prefilled_data_row) {
			$pID = $prefilled_data_row['products_id'];
			$prefilled_data_by_pID["$pID"]['Title']    = $prefilled_data_row['Title'];
			$prefilled_data_by_pID["$pID"]['Subtitle'] = $prefilled_data_row['Subtitle'];
		}
	}
	$imagePath = getDBConfigValue('ebay.imagepath',$_MagnaSession['mpID']);
	$eBayTemplate = getDBConfigValue('ebay.template.content',$_MagnaSession['mpID']);
	$eBayTitleTemplate = getDBConfigValue('ebay.template.name',$_MagnaSession['mpID'], '#TITLE#');
	$preparedTs = date('Y-m-d H:i:s');
	foreach ($more_data as $dataRow) {
		$row = prepareEBayPropertiesRow($dataRow['products_id'], $itemDetails);
		$pID = $dataRow['products_id'];
		$row['Title'] = (isset($prefilled_data_by_pID[$pID]) && isset($prefilled_data_by_pID[$pID]['Title']))
			? $prefilled_data_by_pID[$pID]['Title']
			: eBaySubstituteTemplate($_MagnaSession['mpID'], $dataRow['products_id'], $eBayTitleTemplate, array (
			'#TITLE#' => strip_tags($dataRow['Title']),
			'#ARTNR#' => $dbSelection[0]['products_model']
		));
	    if('on' == $itemDetails['enableSubtitle'] && !empty($dataRow['products_short_description'])) {
		    $row['Subtitle'] = (isset($prefilled_data_by_pID[$pID]) && isset($prefilled_data_by_pID[$pID]['Subtitle']))
			? $prefilled_data_by_pID[$pID]['Subtitle']
			: substr(trim(strip_tags($dataRow['products_short_description'])),0,55);
	    }
        if ('on' == $itemDetails['privateListing']) {
            $row['PrivateListing'] = '1';
        }
        if (('on' == $itemDetails['bestOfferEnabled']) && ('Chinese' != $itemDetails['ListingType'])){
            $row['BestOfferEnabled'] = '1';
        }
        if (!empty($itemDetails['startTime'])) {
            $row['StartTime'] = $itemDetails['startTime'];
        }
        if (!empty($itemDetails['hitcounter'])) {
            $row['HitCounter'] = $itemDetails['hitcounter'];
        }
		# if (isset($itemDetails['subtitle_checked'])) $dataRow['Subtitle'] = strip_tags($dataRow['Subtitle']);
		#$row['Price'] = makePrice($dataRow['products_id'], $itemDetails['ListingType']);
		$row['Price'] = (float)0; # Preis nicht einfrieren
		if (('Chinese' == $itemDetails['ListingType']) && getDBConfigValue(array('ebay.chinese.buyitnow.price.active', 'val'), $_MagnaSession['mpID'])) {
			$row['BuyItNowPrice'] = makePrice($dataRow['products_id'], 'BuyItNowPrice');
		}
		$row['PictureURL'] = empty($dataRow['image'])? '': $imagePath . $dataRow['image'];
		if ('on' == $itemDetails['enableGallery']) {
			$galleryPath = getDBConfigValue('ebay.gallery.imagepath',$_MagnaSession['mpID']);
			$row['GalleryURL'] = empty($dataRow['image'])? '': $galleryPath . $dataRow['image'];
		}
		# Descriptions zusammenbauen
		$substitution = array(
			'#TITLE#' => fixHTMLUTF8Entities($dataRow['Title']),
			'#ARTNR#' => $dataRow['products_model'],
			'#PID#' => $dataRow['products_id'],
			'#SKU#' => magnaPID2SKU($dataRow['products_id']),
			'#SHORTDESCRIPTION#' => $dataRow['products_short_description'],
			'#DESCRIPTION#' => stripLocalWindowsLinks($dataRow['description']),
			'#PICTURE1#' => $row['PictureURL'],
			);
		$row['Description'] = substitutePictures(eBaySubstituteTemplate(
			$_MagnaSession['mpID'], $dataRow['products_id'], $eBayTemplate, $substitution
		), $dataRow['products_id'], $imagePath);
		
		# ggf vorher eingefrorene Preise nicht plattmachen &
		# doppelte Eintraege verhindern
		if ('artNr' == getDBConfigValue('general.keytype', '0')) {
            $row['Price'] = (float)MagnaDB::gi()->fetchOne('SELECT Price FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' WHERE mpID = '.$_MagnaSession['mpID'].' AND products_model = \''.$row['products_model'].'\'');
			MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, array('mpID'=>$_MagnaSession['mpID'], 'products_model'=>$row['products_model']));
        } else {
            $row['Price'] = (float)MagnaDB::gi()->fetchOne('SELECT Price FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' WHERE mpID = '.$_MagnaSession['mpID'].' AND products_id = '.$dataRow['products_id']);
			MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, array('mpID'=>$_MagnaSession['mpID'], 'products_id'=>$dataRow['products_id']));
        }
		$row['PreparedTs'] = $preparedTs;
		eBayInsertPrepareData($row);
	}
}

function magnalisterEbayGetPriceByType($iProductsId, $sPriceType = false) {
	global $_MagnaSession;
	if ('artNr' == getDBConfigValue('general.keytype', '0')) {
		$preparedPriceQuery = "
			SELECT ".('BuyItNowPrice' == $sPriceType ? 'ep.BuyItNowPrice' : 'ep.Price')."
			  FROM ".TABLE_MAGNA_EBAY_PROPERTIES ." ep, ".TABLE_PRODUCTS." p
			 WHERE     ep.products_model = p.products_model
			       AND p.products_id = ".$iProductsId."
			       AND ep.mpID = ".$_MagnaSession['mpID']."
			 LIMIT 1
		";
	} else {
		$preparedPriceQuery = "
			SELECT ".('BuyItNowPrice' == $sPriceType ? 'ep.BuyItNowPrice' : 'ep.Price')."
			  FROM ".TABLE_MAGNA_EBAY_PROPERTIES ." ep
			 WHERE     ep.products_id = ".$iProductsId."
			       AND ep.mpID = ".$_MagnaSession['mpID']."
			LIMIT 1
		";
	}

	return MagnaDB::gi()->fetchOne($preparedPriceQuery);
}
