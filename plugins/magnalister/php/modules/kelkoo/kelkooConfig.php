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
 * $Id: kelkooConfig.php 1110 2011-06-17 13:28:24Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

$_url['mode'] = 'conf';

$form = loadConfigForm($_lang,
	array(
		'comparisonshopping_generic.form' => array()
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title']
	)
);

try {
	$result = MagnaConnector::gi()->submitRequest(array(
		'SUBSYSTEM' => 'ComparisonShopping',
		'ACTION' => 'GetCSInfo',
	));
	if ($result['DATA']['HasUpload'] == 'no') {
		$pathHtml = '
			<h3>'.ML_COMPARISON_SHOPPING_LABEL_PATH_TO_CSV_TABLE.'</h3>
			<input type="text" class="fullwidth" value="'.(
				!empty($result['DATA']['CSVPath']) 
					? $result['DATA']['CSVPath'] 
					: ML_COMPARISON_SHOPPING_TEXT_NO_CSV_TABLE_YET
			).'" /><br/><br/>
		';
	} else if ($result['DATA']['HasUpload'] == 'ftp') {
		$form = array_merge(
			json_decode(
				str_replace(array('_#_platform_#_', '_#_platformName_#_'), array($_Marketplace, $_modules[$_Marketplace]['title']),
					file_get_contents(DIR_MAGNALISTER.'config/'.$_lang.'/comparisonshopping_ftp.form')
				), true
			),
			$form
		);
	}
} catch (MagnaException $e) { }

mlGetShopOptions($form['shop']['fields']['shop']);
mlGetCountries($form['shipping']['fields']['country']);
mlGetLanguages($form['lang']['fields']['lang']);
mlGetShippingMethods($form['shipping']['fields']['method']);
mlGetCustomersStatus($form['price']['fields']['whichprice'], false);
if (!empty($form['price']['fields']['whichprice'])) {
	$form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
	ksort($form['price']['fields']['whichprice']['values']);
	unset($form['price']['fields']['specialprices']);
} else {
	unset($form['price']['fields']['whichprice']);
}

$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_kelkoo');
$cG->setRenderTabIdent(true);
if (isset($pathHtml)) {
	$cG->setTopHTML($pathHtml);
}

if (array_key_exists('conf', $_POST) && array_key_exists($_Marketplace.'.host', $_POST['conf'])) {
	$nHost = trim($_POST['conf'][$_Marketplace.'.host']);
	$nUser = trim($_POST['conf'][$_Marketplace.'.user']);
	$nPass = trim($_POST['conf'][$_Marketplace.'.pass']);
	$nPath = trim($_POST['conf'][$_Marketplace.'.path']);

	if (empty($nPass) && !empty($nUser) && (getDBConfigValue($_Marketplace.'password', $_MagnaSession['mpID']) == '__saved__')) {
		$nPass = '__saved__';
	}
	if (!empty($nUser) && !empty($nPass)) {
		if ((strpos($nPass, '&#9679;') === false) && (strpos($nPass, '&#8226;') === false)) {
			/*               Windows                                  Mac                */
			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'SetCredentials',
					'HOST' => $nHost,
					'USER' => $nUser,
					'PASS' => $nPass,
					'PATH' => $nPath,
				));
				$boxes .= '
					<p class="successBox">'.ML_GENERIC_STATUS_LOGIN_SAVED.'</p>
				';
			} catch (MagnaException $e) {
				$boxes .= '
					<p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>
				';
			}
		} else {
	        $boxes .= '
	            <p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>
	        ';
		}
	}
}
$allCorrect = $cG->processPOST($keysToSubmit);

if (!empty($keysToSubmit)) {
	$request = array(
		'ACTION' => 'SetConfigValues',
		'DATA' => array(),
	);
	foreach ($keysToSubmit as $key) {
		$request['DATA'][$key] = getDBConfigValue($key);
	}
	try {
		MagnaConnector::gi()->submitRequest($request);
	} catch (MagnaException $me) { }
}

if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	echo $cG->processAjaxRequest();
} else {
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	echo $boxes;
	echo $cG->renderConfigForm();
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
