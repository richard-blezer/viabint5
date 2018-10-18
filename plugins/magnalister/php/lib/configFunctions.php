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
 * $Id: configFunctions.php 531 2014-11-04 19:21:50Z derpapst $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function mlGetLanguages(&$form) {
	$langs = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_LANGUAGES);
	$form['values'] = array();
	foreach ($langs as $lang) {
		$form['values'][$lang['languages_id']] = $lang['name'].' ('.$lang['code'].')';
		if (strtolower($lang['code']) == 'de') { /* Deutsch als standard */
			$form['default'] = $lang['languages_id'];
		}
	}
}

function mlGetDefaultLanguage() {
	$defaultLanguage = MagnaDB::gi()->fetchOne('SELECT UPPER(config_value) FROM '.TABLE_CONFIGURATION
										.' WHERE config_key = \'_STORE_COUNTRY\'');
	if (false != $defaultLanguage) return $defaultLanguage;
	$i = 1;
	while (false == $defaultLanguage) {
		if (!MagnaDB::gi()->tableExists(TABLE_CONFIGURATION_MULTI.$i)) return 'DE'; /* Deutsch als standard */
		$defaultLanguage = MagnaDB::gi()->fetchOne('SELECT UPPER(config_value)
								FROM '.TABLE_CONFIGURATION_MULTI.$i
								.' WHERE config_key = \'_STORE_COUNTRY\'');
		++$i;
	}
	return $defaultLanguage;
}

function mlGetCountries(&$form) {
	$countries = MagnaDB::gi()->fetchArray('
		SELECT sd.countries_name, sd.countries_iso_code_2
		  FROM '.TABLE_COUNTRIES_DESCRIPTION.' sd, '.TABLE_COUNTRIES.' sdd
		 WHERE sd.countries_iso_code_2 = sdd.countries_iso_code_2 
		       AND sdd.status = \'1\''
	);

	foreach ($countries as $country ) {
		$form['values'][$country['countries_iso_code_2']] = $country['countries_name'].' ('.$country['countries_iso_code_2'].')';
		if (strtolower($country['countries_iso_code_2']) == 'de') { // Deutschland als Standard 
			$form['default'] = $country['countries_iso_code_2'];
		}
	}
}

function mlGetCountriesWithIso2Keys(&$form) {
	$defaultLanguage = mlGetDefaultLanguage();
	$countries = MagnaDB::gi()->fetchArray('
	    SELECT UPPER(countries_iso_code_2) as iso2, countries_name
	      FROM '.TABLE_COUNTRIES_DESCRIPTION.'
	     WHERE UPPER(language_code) = \''.$defaultLanguage.'\'
	  ORDER BY countries_name
	');
	$form['values'] = array();
	foreach ($countries as $country) {
		$form['values'][$country['iso2']] = $country['countries_name'];
		if ($country['iso2'] == 'DE') { /* Deutschland als standard */
			$form['default'] = $country['iso2'];
		}
	}
}

function mlGetShippingMethods(&$form) {
	if (!class_exists('MagnaShipping')) {
		require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/Shipping.php');
	}
	//simon tried to load different classes because ebay is buggy / dont work
	//var_dump($form['morefields']['quantity']['default']);
	$shippingClass = new MagnaShipping();
	
	$shippingMethods = $shippingClass->getShippingMethods();
	$form['values'] = array(
		'__ml_lump' => ML_COMPARISON_SHOPPING_LABEL_LUMP,
		'__ml_weight' => ML_LABEL_SHIPPINGCOSTS_EQ_ARTICLEWEIGHT,
	);
	if (!empty($shippingMethods)) {
		foreach ($shippingMethods as $method) {
			if ($method['code'] == 'gambioultra') continue;
			$form['values'][$method['code']] = fixHTMLUTF8Entities($method['title']);
		}
	}
	unset($shippingClass);
}

function mlGetPaymentMethods(&$form) {
	$form['values'] = array(
		'__ml_lump' => ML_COMPARISON_SHOPPING_LABEL_LUMP
	);
}

function mlGetOrderStatus(&$form) {
	$order_stats = MagnaDB::gi()->fetchArray(eecho('
		SELECT sd.status_id, status_name
		  FROM `'.TABLE_SYSTEM_STATUS_DESCRIPTION.'` ssd, `'.TABLE_SYSTEM_STATUS.'` sd
		 WHERE ssd.language_code = "'.$_SESSION['magna']['selected_language'].'"
		       AND sd.status_id = ssd.status_id
		       AND sd.status_class = "order_status"
	', false));
	
	$form['values'] = array();
	
	foreach ($order_stats as $status) {
		$form['values'][$status['status_id']] =  fixHTMLUTF8Entities($status['status_name']); 
	}
}

function mlGetCustomersStatus(&$form, $inclAdmin = true) {
	//Abfragen der möglichen Status IDs + Namen für Kunden (in entsprechender Sprache)
	$customers_status_array = MagnaDB::gi()->fetchArray(
		'SELECT sd.customers_status_id, sdd.customers_status_name '. 
		'FROM '.TABLE_CUSTOMERS_STATUS.' sd, '.TABLE_CUSTOMERS_STATUS_DESCRIPTION.' sdd '.
		'WHERE sd.customers_status_id = sdd.customers_status_id '.
		'AND sdd.language_code = \''.$_SESSION['magna']['selected_language'].'\''
	
	);
	
	foreach ($customers_status_array as $item) {
		$form['values'][$item['customers_status_id']] = $item['customers_status_name'];
	}
}

function mlGetPaymentModules(&$form) {
	/*
	$payments = explode(';', MODULE_PAYMENT_INSTALLED);
	$lang = (isset($_SESSION['language']) && !empty($_SESSION['language'])) ? $_SESSION['language'] : 'english';
	
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) ^ E_NOTICE);
	foreach ($payments as $p) {
		if (empty($p)) continue;
		$m = DIR_FS_LANGUAGES.$lang.'/modules/payment/'.$p;
		$payment = substr($p, 0, strrpos($p, '.'));
		$c = 'MODULE_PAYMENT_'.strtoupper($payment).'_TEXT_TITLE';
		if (!defined($c) && file_exists($m) && is_file($m)) {
			try {
				require_once($m);
			} catch (Exception $e) {}
		}
		if (!defined($c)) continue;
		$c = trim(strip_tags(constant($c)));
		$form['values'][$payment] = $c;
	}
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
	*/
}

function mlGetShippingModules(&$form) {
	/*
	$shippings = explode(';', MODULE_SHIPPING_INSTALLED);
	$lang = (isset($_SESSION['language']) && !empty($_SESSION['language'])) ? $_SESSION['language'] : 'english';
	
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) ^ E_NOTICE);
	foreach ($shippings as $s) {
		if (empty($s)) continue;
		$m = DIR_FS_LANGUAGES.$lang.'/modules/shipping/'.$s;
		$shipping = substr($s, 0, strrpos($s, '.'));
		$c = 'MODULE_SHIPPING_'.strtoupper($shipping).'_TEXT_TITLE';
		if (!defined($c) && file_exists($m) && is_file($m)) {
			try {
				require_once($m);
			} catch (Exception $e) {}
		}
		if (!defined($c)) continue;
		$c = trim(strip_tags(constant($c)));
		$form['values'][$shipping] = $c;
	}
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
	*/
}

function mlGetShopOptions(&$form) {
	$shops = MagnaDB::gi()->fetchArray('
		SELECT shop_id, shop_title
		  FROM '.TABLE_MANDANT_CONFIG.'
	');
	foreach ($shops as $item) {
		$form['values'][$item['shop_id']] = $item['shop_title'];
	}
}

function mlGetManufacturers(&$form){
	$manufacturers = MagnaDB::gi()->fetchArray('
	    SELECT manufacturers_id, manufacturers_name 
	      FROM '.TABLE_MANUFACTURERS.'
	     WHERE manufacturers_id<>0
	  ORDER BY manufacturers_name ASC
	');
	
	$form['values'] = array();
	
	if (!empty($manufacturers)) {
		foreach ($manufacturers as $manufacturer) {
			$form['values'][$manufacturer['manufacturers_id']] = fixHTMLUTF8Entities($manufacturer['manufacturers_name']);
		}
	}
}

function mlGetShippingStatus(&$form) {
	$data = MagnaDB::gi()->fetchArray(eecho('
		SELECT sd.status_id AS id, status_name AS name
		  FROM `'.TABLE_SYSTEM_STATUS_DESCRIPTION.'` ssd, `'.TABLE_SYSTEM_STATUS.'` sd
		 WHERE ssd.language_code = "'.$_SESSION['magna']['selected_language'].'"
		       AND sd.status_id = ssd.status_id
		       AND sd.status_class = "shipping_status"
	', false));
	
	$form['values'] = array();
	
	foreach ($data as $elem) {
		$form['values'][$elem['id']] = fixHTMLUTF8Entities($elem['name']); 
	}
}

