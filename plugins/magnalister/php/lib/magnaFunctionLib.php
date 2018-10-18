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
 * $Id: magnaFunctionLib.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


/******************************************************************************\
 *                        Magnalister Specific Functions                      *
\******************************************************************************/

function magnaContribVerify($hookname, $version) {
	$path = DIR_MAGNALISTER_CONTRIBS.$hookname.'_'.$version.'.php';
	return file_exists($path) ? $path : false;
}

function getCurrentModulePage() {
	global $_modules, $_MagnaSession;
	$modulePages = $_modules[$_MagnaSession['currentPlatform']]['pages'];

	if (isset($_modules[$_MagnaSession['currentPlatform']]['settings']['defaultpage'])) {
		$page = $_modules[$_MagnaSession['currentPlatform']]['settings']['defaultpage'];
	} else {
		$page = array_first(array_keys($modulePages));
	}
	if (array_key_exists('mode', $_GET) && array_key_exists($_GET['mode'], $modulePages)) {
		$page = $_GET['mode'];
	}
	return $page;
}

function requirementsMet($product, $requirements, &$failed) {
	if (!is_array($product) || empty($product) || !is_array($requirements) || empty($requirements)) {
		$failed = true;
		return false;
	}
	$failed = array();
	foreach ($requirements as $req => $needed) {
		if (!$needed) continue;
		if (!array_key_exists($req, $product) || (empty($product[$req]) && ($product[$req] !== '0'))) {
			$failed[] = $req;
		}
	}
	return empty($failed);
}

function generateProductCategoryThumb($fName, $w, $h, $noImageIcon = false) {
	$imagePath = '';
	if (!eempty(trim($fName)) && !$noImageIcon) {
		if (file_exists(SHOP_FS_PRODUCT_IMAGES.$fName)) {
			$imagePath = SHOP_FS_PRODUCT_IMAGES;
			$cachePath = DIR_MAGNALISTER_IMAGECACHE.'product_';
		} else if (file_exists(SHOP_FS_CATEGORY_IMAGES.$fName)) {
			$imagePath = SHOP_FS_CATEGORY_IMAGES;
			$cachePath = DIR_MAGNALISTER_IMAGECACHE.'category_';
		}
	} else if ($noImageIcon) {
		$imagePath = DIR_MAGNALISTER_RESOURCE;
		$cachePath = DIR_MAGNALISTER_IMAGECACHE.'resource_';
	}

	if ($imagePath == '') {
		return generateProductCategoryThumb('noimage.png', $w, $h, true);
	}
	$newfName = basename($fName);
	if (($dotpos = strrpos($newfName, '.')) !== false) {
		$newfName = str_split($newfName, $dotpos);
		$newfName = $newfName[0];
	}
	$newfName .= '_'.$w.'x'.$h.'.jpg';
	//var_dump($cachePath); 
	/* original code
	if (!is_dir(dirname(DIR_FS_ADMIN.$cachePath))) {
		if (MAGNA_SAFE_MODE) return '&nbsp';
		mkdir(dirname(DIR_FS_ADMIN.$cachePath), 0775, true);
	}
	$destFile = DIR_FS_ADMIN.$cachePath.$newfName;
	 */
	//simon  mod cachePath seems to be complete DIR_FS_ADMIN removed
	if (!is_dir(dirname($cachePath))) {
		if (MAGNA_SAFE_MODE) return '&nbsp';
		mkdir(dirname($cachePath), 0775, true);
	}
	$destFile = $cachePath.$newfName;
 
	/* Replace absolute server path to relative*/
	
	$image_src = DIR_MAGNALISTER_IMAGECACHE_WEB; 
	$image_src .= ($noImageIcon == true ? 'resource_' : 'product_');
	//simon mod end 
	if (file_exists($destFile)) {
		list($width, $height) = getimagesize($destFile);
		if (($w == $width) || ($h == $height)) {
			return '<img width="'.$width.'" height="'.$height.'" src="'.$image_src.$newfName.'" alt="'.$fName.'"/>';
		}
	}

	$success = resizeImage($imagePath.$fName, $w, $h, $destFile);
	if (!$success && !$noImageIcon) {
		return generateProductCategoryThumb('noimage.png', $w, $h, true);
	} else if (!$success && $noImageIcon) {
		return 'X';
	}
	list($width, $height) = getimagesize($destFile);



	return '<img width="'.$width.'" height="'.$height.'" src="'.$image_src.$newfName.'" alt="'.$fName.'"/>';
}

function html_image($image, $alt = "", $width = "", $height = "") {
	return '<img src="'.$image.'"'.(!empty($alt) ? (' alt="'.$alt.'" title="'.$alt.'"') : '').(!empty($width) ? (' width="'.$width.'"') : '').(!empty($height) ? (' height="'.$height.'"') : '').'>';
}

function parseShippingStatusName($status, $fallback) {
	/* Extract largest number */
	$largestNumber = 0;
	if (preg_match_all('/([0-9]+)/', $status, $matches)) {
		$numbers = $matches[0];
		foreach ($numbers as $number) {
			if ($number > $largestNumber) {
				$largestNumber = $number;
			}
		}
	}
	if (preg_match('/(Day|Days|Tag|Tage)/i', $status)) {
		return $largestNumber;
	}
	if (preg_match('/(Week|Weeks|Woche|Wochen)/i', $status)) {
		return $largestNumber * 7;
	}
	if (preg_match('/(Month|Months|Monat|Monate)/i', $status)) {
		return $largestNumber * 30;
	}
	
	return $fallback;
}

function shopAdminDiePage($content) {
	global $_MagnaSession;
	$_MagnaSession['currentPlatform'] = '';
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	echo $content;
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
	#include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();	
}

function sanitizeProductDescription($str, $allowable_tags = '', $allowable_attributes = '') {
	$str = !magnalisterIsUTF8($str) ? utf8_encode($str) : $str;
	
	$str = stripEvilBlockTags($str);

	/* Convert Gambio-Tabs to H1-Headlines */
	$str = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $str);

	if (stripos($allowable_tags, '<br') === false) {
		/* Convert (x)html breaks with or without atrributes to newlines. */
		$str = preg_replace('/\<br(\s*)?([[:alpha:]]*=".*")?(\s*)?\/?\>/i', "\n", $str);
	} else {
		$str = str_replace('<br/>', '<br />', $str);
	}
	$str = preg_replace("/<([^([:alpha:]|\/)])/", '&lt;\\1', $str); 
	$str = strip_tags_attributes($str, $allowable_tags, $allowable_attributes);
	
	if ($allowable_tags == '') {
		$str = str_replace(array("\n", "\t", "\v", "|"), " ", $str);
		$str = str_replace(array("&quot;", "&qout;"), " \"", $str);

		$str = str_replace(array("&nbsp;"), " ", $str);

		/* Converts all html entities to "real" characters */
		$str = html_entity_decode($str);
		$str = str_replace(array(";", "'"), ", ", $str);
	}

	/* Strip excess whitespace */
	$str = preg_replace('/\s\s+/', ' ', trim($str));
	return $str;
}

function magnaGetAddressFormatID($country_id) {
	$ret = (int) MagnaDB::gi()->fetchOne(
		"SELECT address_format_id FROM " . TABLE_COUNTRIES . " WHERE countries_iso_code_2 = '" . $country_id . "'"
	);
	if ($ret < 1) {
		return '1';
	}
	return $ret;
}

function magnaGetCountryFromISOCode($code) {
	$countryRes = MagnaDB::gi()->fetchRow('
		SELECT countries_iso_code_2,
		       countries_name
		FROM '.TABLE_COUNTRIES_DESCRIPTION.'
		 WHERE countries_iso_code_2=\''.$code.'\' LIMIT 1
	');
	if ($countryRes === false) {
		$countryRes = array (
			'countries_id' => 0,
			'countries_name' => $code,
		);
	}
	return $countryRes;
}

function substituteTemplate($tmplStr, $substitution) {
	$find = array();
	$replace = array();

	# Sonderfall: PICTURE1, ersetze durch URL wenn img oder a href Tag angegeben, sonst durch vollst. img Tag.
	if (    isset($substitution['#PICTURE1#'])
	    && !empty($substitution['#PICTURE1#'])
	) {
		$tmplStr = str_replace(
			'#PICTURE1#',
			"<img src=\"".$substitution['#PICTURE1#']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
			preg_replace(
				'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(#PICTURE1#)/',
				'\1\2\3'.$substitution['#PICTURE1#'],
				$tmplStr
			)
		);
	}
	foreach ($substitution as $f => $r) {
		$find[] = $f;
		$replace[] = $r;
	}
	$str = str_replace($find, $replace, $tmplStr);

	# Bild 1 leer? entfernen
	$str = preg_replace('/<img[^>]*src=(""|\'\')[^>]*>/i', '', $str);

	# relative Pfade bei (nicht von uns eingesetzten) Bildern und Links ersetzen
	if (   ('none_none' != getDBConfigValue('general.editor',0,'tinyMCE')) 
	    && (preg_match('/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(?!http|HTTP|mailto|javascript|data:image|#|\+|\'\+|"\+)/', $str))
	) {
		$str = preg_replace(
			'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(?!http|HTTP|mailto|javascript|data:image|#|\+|\'\+|"\+)/',
			'\1\2\3'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'\4', 
			preg_replace(
				'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(\/)/',
				'\1\2\3'.HTTP_CATALOG_SERVER.'\4',
				$str
			)
		);
	}
	return $str;
}

/*function substituteTemplate($tmplStr, $substitution) {
	$find = array();
	$replace = array();
	# Sonderfall: PICTURE1, ersetze durch URL wenn img oder a href Tag angegeben, sonst durch vollst. img Tag.
	if (isset($substitution['#PICTURE1#'])) {
		$tmplStr = str_replace('#PICTURE1#', "<img src=\"".$substitution['#PICTURE1#']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
                                 preg_replace( '/(src|SRC|href|HREF)(\s*=\s*)(\'|")(#PICTURE1#)/', '\1\2\3'.$substitution['#PICTURE1#'], $tmplStr));
	}
	foreach ($substitution as $f => $r) {
		$find[] = $f;
		$replace[] = $r;
	}
	$str = str_replace($find, $replace, $tmplStr);
	# relative Pfade bei (nicht von uns eingesetzten) Bildern und Links ersetzen
	if (preg_match('/(src|SRC|href|HREF)(\s*=\s*)(\'|")(?!http|HTTP|#PIC)/', $str)) {
		$str = preg_replace('/(src|SRC|href|HREF)(\s*=\s*)(\'|")(?!http|HTTP|#PIC)/','\1\2\3'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'\4', preg_replace('/(src|SRC|href|HREF)(\s*=\s*)(\'|")(\/)/','\1\2\3'.HTTP_CATALOG_SERVER.'\4', $str));
	}
	return $str;
}*/

function magnaGetMarketplaceByID($mpID) {
	global $magnaConfig;
	$mpID = (string)$mpID;
	if (!ctype_digit((string)$mpID)) {
		if (MAGNA_DEBUG) {
			echo print_m(prepareErrorBacktrace(2));
		}
		trigger_error(__FUNCTION__.': Parameter $mpID ('.trim(var_dump_pre($mpID)).') must be numeric.');
	}
	if (!array_key_exists($mpID, $magnaConfig['maranon']['Marketplaces'])) {
		return false;
	}
	return $magnaConfig['maranon']['Marketplaces'][$mpID];
}

function magnaGetIDsByMarketplace($mp) {
	global $magnaConfig;
	
	if (!array_key_exists('maranon', $magnaConfig) || 
	    !array_key_exists('Marketplaces', $magnaConfig['maranon']) || 
	    empty($magnaConfig['maranon']['Marketplaces'])
	) {
		return false;
	}
	$ids = array();
	foreach ($magnaConfig['maranon']['Marketplaces'] as $mpID => $marketplace) {
		if ($marketplace == $mp) {
			$ids[] = $mpID;
		}
	}
	return $ids;
}

function sendSaleConfirmationMail($mpID, $recipientAdress, $substitution) {
	global $magnaConfig;
	$marketplace = magnaGetMarketplaceByID($mpID);
	if ($marketplace === false) {
		return false;
	}

	if (is_array($substitution['#ORDERSUMMARY#']) && !empty($substitution['#ORDERSUMMARY#'])) {
		$mailOrderSummary = '
			<table class="ordersummary">
				<thead>
					<tr>
						<td class="qty">St&uuml;ck</td>
						<td class="name">Artikel</td>
						<td class="price">Einzelpreis</td>
						<td class="fprice">Gesamtpreis</td>
					</tr>
				</thead>
				<tbody>';
		$isOdd = false;
		foreach ($substitution['#ORDERSUMMARY#'] as $item) {
			$item['price'] = str_replace('&curren;', '&euro;', fixHTMLUTF8Entities($item['price']));
			$item['finalprice'] = str_replace('&curren;', '&euro;', fixHTMLUTF8Entities($item['finalprice']));
			$mailOrderSummary .= '
					<tr class="'.(($isOdd = !$isOdd) ? 'odd' : 'even').'">
						<td class="qty">'.$item['quantity'].'</td>
						<td class="name">'.fixHTMLUTF8Entities($item['name']).'</td>
						<td class="price">'.$item['price'].'</td>
						<td class="fprice">'.$item['finalprice'].'</td>
					</tr>';
		}
		$mailOrderSummary .= '
				</tbody>
			</table>';
		$substitution['#ORDERSUMMARY#'] = $mailOrderSummary;
	}
	
	$content = stringToUTF8(substituteTemplate(getDBConfigValue($marketplace.'.mail.content', $mpID), $substitution));
	unset($substitution['#ORDERSUMMARY#']);
	$subject = stringToUTF8(substituteTemplate(getDBConfigValue($marketplace.'.mail.subject', $mpID), $substitution));
/*
	echo print_m(array(
		'SUBJECT' => $subject,
		'CONTENT' => $content,
	), 'mail');
	return true;
//*/
	$orignator = getDBConfigValue($marketplace.'.mail.originator.adress', $mpID);
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'SendSaleConfirmationMail',
			'SUBSYSTEM' => 'Core',
			'RECIPIENTADRESS' => $recipientAdress,
			'ORIGINATORNAME' => fixHTMLUTF8Entities(
				getDBConfigValue($marketplace.'.mail.originator.name', $mpID)
			),
			'ORIGINATORADRESS' => $orignator,
			'SUBJECT' => fixHTMLUTF8Entities($subject),
			'CONTENT' => $content,
			'BCC' => (
				($recipientAdress == $orignator) ? 
					false :	((getDBConfigValue($marketplace.'.mail.copy', $mpID) == 'true') ? 
						true : false
					)
			)
		));
		if (MAGNA_DEBUG && array_key_exists('DEBUG', $result)) {
			echo print_m($result['DEBUG'], 'Debug');
		}
		return true;
	} catch (MagnaException $e) {
		return false;
	}	
}

function sendTestMail($mpID) {
	require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	$simplePrice = new SimplePrice();
	$simplePrice->setCurrency(DEFAULT_CURRENCY);
	
	$marketplace = magnaGetMarketplaceByID($mpID);
	
	return sendSaleConfirmationMail(
		$mpID,
		getDBConfigValue($marketplace.'.mail.originator.adress', $mpID),
		array(
			'#FIRSTNAME#' => 'Max',
			'#LASTNAME#' => 'Mustermann',
			'#ORDERSUMMARY#' => array(
 				array(
					'quantity' => 2,
					'name' => 'Lorem Ipsum - Das Buch',
					'price' => $simplePrice->setPrice(12.99)->format(),
					'finalprice' => $simplePrice->setPrice(12.99 * 2)->format(),
				),
 				array(
					'quantity' => 1,
					'name' => 'Dolor Sit Amet - Das Nachschlagewerk',
					'price' => $simplePrice->setPrice(22.59)->format(),
					'finalprice' => $simplePrice->setPrice(22.59)->format(),
				)
			),
			'#MARKETPLACE#' => 'marketplace.com',
			'#SHOPURL#' => HTTP_SERVER,
		)
	);
}

function delete_double_orders() {
	$orders_id_array = MagnaDB::gi()->fetchArray('
	    SELECT DISTINCT mo2.orders_id orders_id
	      FROM '. TABLE_MAGNA_ORDERS .' mo1, '. TABLE_MAGNA_ORDERS .' mo2
	     WHERE mo1.orders_id < mo2.orders_id
	           AND mo1.special = mo2.special
	           AND mo1.data = mo2.data
	  ORDER BY mo2.orders_id
	');
	$orders_id_list = '';
	foreach($orders_id_array as $current_order)
		$orders_id_list .= ', '.$current_order['orders_id'];
	$orders_id_list = trim($orders_id_list, ', ');
	if(empty($orders_id_list)) return true;
	$orders_tables = array( 
		TABLE_ORDERS,
		TABLE_ORDERS_PRODUCTS,
		TABLE_ORDERS_PRODUCTS_MEDIA,
		TABLE_ORDERS_STATS,
		TABLE_ORDERS_STATUS_HISTORY,
		TABLE_ORDERS_TOTAL
	);
	foreach($orders_tables as $current_table) {
		MagnaDB::gi()->query('
			DELETE FROM '. $current_table .'
				WHERE orders_id IN ('. $orders_id_list .')
		');
	}
	return true;
}

function delete_double_customers() {
	return true;
	$customers_id_array = MagnaDB::gi()->fetchArray('
	    SELECT DISTINCT ca2.customers_id customers_id
	      FROM '. TABLE_CUSTOMERS_ADDRESSES .' ca1
	 LEFT JOIN '. TABLE_CUSTOMERS .' c1 ON c1.customers_id = ca1.customers_id
	 LEFT JOIN '. TABLE_CUSTOMERS_ADDRESSES .' ca2
	 LEFT JOIN '. TABLE_CUSTOMERS .' c2 ON c2.customers_id = ca2.customers_id
	     WHERE ca1.address_class = \'default\' 
	           AND ca2.address_class = \'default\'
	           AND ca1.customers_firstname = ca2.customers_firstname
	           AND ca1.customers_lastname = ca2.customers_lastname
	           AND c1.customers_email_address = c2.customers_email_address
	           AND ca2.customers_id > ca1.customers_id
	  ORDER BY ca2.customers_id
	');
	$customers_id_list = '';
	foreach ($customers_id_array as $current_customer) {
		$customers_id_list .= ', '.$current_customer['customers_id'];
	}
	$customers_id_list = trim($customers_id_list, ', ');
	if (empty($customers_id_list)) {
		return true;
	}
	$customers_tables = array (
		TABLE_CUSTOMERS,
		TABLE_CUSTOMERS_ADDRESSES,
		TABLE_CUSTOMERS_BASKET
	);
	foreach ($customers_tables as $current_table) {
		MagnaDB::gi()->query('DELETE FROM '. $customers_tables .' WHERE customers_id in ('. $customers_id_list .')');
	}
	return true;
}

function magnaFixOrders() {
	if (isset($_GET['forceDeleteDoubleOrders']) && ($_GET['forceDeleteDoubleOrders'] == 'true')) {
		setDBConfigValue('deletedoubleorders', 0, 'true', true);
	}
	if (getDBConfigValue('deletedoubleorders', '0', 'false') != 'true') {
		return;
	}
	delete_double_orders();
	delete_double_customers();
	setDBConfigValue('deletedoubleorders', 0, 'false', true);
}

function priceToFloat($price, $format = array()) {	
	$r = '/^([0-9\.,]*)$/';
	if (!preg_match($r, $price)) {
		return -1;
	}
	$frac = array();
	if (preg_match('/([\.,]{1,2}[0-9]{1,2})$/', $price, $frac)) {
		$frac = $frac[0];
		$price = substr($price, 0, -strlen($frac));
	} else {
		$frac = '0';
	}
	$price = str_replace(array('.', ','), '', $price).'.'.str_replace(array('.', ','), '', $frac);
	return (float)$price;
}

function generateUniqueProductModels() {
	MagnaDB::gi()->query('
		UPDATE '.TABLE_PRODUCTS.' 
		   SET products_model=CONCAT(\'p\', products_id) 
		 WHERE products_model=\'\' OR products_model IS NULL
	');

	$q = MagnaDB::gi()->query('
	    SELECT products_model, COUNT(products_model) as cnt
	      FROM '.TABLE_PRODUCTS.' 
	     WHERE products_model <> \'\'
	  GROUP BY products_model
	    HAVING cnt > 1'
	);
	$dblProdModel = array();
	while ($row = MagnaDB::gi()->fetchNext($q)) {
		$dblProdModel[] = $row['products_model'];
	}

	$q = MagnaDB::gi()->query('
		SELECT products_id, products_model
		  FROM '.TABLE_PRODUCTS.'
		 WHERE products_model IN (\''.implode('\', \'', $dblProdModel).'\')
	');
	$dblProdModel = array();
	while ($row = MagnaDB::gi()->fetchNext($q)) {
		$dblProdModel[$row['products_model']][] = $row['products_id'];
	}
	//echo print_m($dblProdModel);
	if (!empty($dblProdModel)) {
		foreach ($dblProdModel as $pMod => $pIDs) {
			$i = 1;
			foreach ($pIDs as $pID) {
				MagnaDB::gi()->update(TABLE_PRODUCTS, array(
					'products_model' => $pMod.'_'.($i++)
				), array(
					'products_id' => $pID
				));
			}
		}
	}
}

function getCurrencyFromMarketplace($mpID) {
	global $magnaConfig, $_modules;
	
	$mp = magnaGetMarketplaceByID($mpID);
	if ($mp === false) {
		return false;
	}
	if (!array_key_exists($mp, $_modules)) {
		return false;
	}
	$currency = $_modules[$mp]['settings']['currency'];
	if ($currency != '__depends__') {
		return $currency;
	}
	if ($mp == 'amazon') {
		$cur = getDBConfigValue('amazon.currency', $mpID, false);
		return empty($cur) ? false : $cur;
	}
	if ($mp == 'ebay') {
		$cur = getDBConfigValue('ebay.currency', $mpID, false);
		return empty($cur) ? false : $cur;
	}
	return false;
}

function magnaSKU2pID($sku) {
	$pID = 0;

	$sku = MagnaDB::gi()->escape($sku);
	
	switch (getDBConfigValue('general.keytype', '0')) {
		case 'artNr': {
			$pID = (int)MagnaDB::gi()->fetchOne('
				SELECT products_id FROM '.TABLE_PRODUCTS.'
				 WHERE products_model=\''.$sku.'\' LIMIT 1
			');
			if ($pID > 0) break;
		}
		case 'pID': {
			if (strpos($sku, 'ML') !== false) {
				// Check ob pID auch in DB existiert.
				$pID = (int)MagnaDB::gi()->fetchOne('
					SELECT products_id FROM '.TABLE_PRODUCTS.' 
					 WHERE products_id=\''.str_replace('ML', '', $sku).'\' LIMIT 1
				');
			}
			break;
		}
	}
	return $pID;
}

function magnaPID2SKU($pID) {
	$sku = '';
	//simon seems to be allright 
	switch (getDBConfigValue('general.keytype', '0')) {
		case 'artNr': {
			$sku = MagnaDB::gi()->fetchOne('
				SELECT products_model FROM '.TABLE_PRODUCTS.' 
				 WHERE products_id=\''.$pID.'\' LIMIT 1
			');
			break;
		}
	}
	
	if (empty($sku)) {
		$sku = 'ML'.$pID;
	}
	
	return trim($sku);
}

function renderPagination($currentPage, $pages, $baseURL, $type = 'link') {
	$html = '';
	if ($pages > 23) {
		for ($i = 1; $i <= 5; ++$i) {
			$class = ($currentPage == $i) ? 'class="bold"' : '';
			if ($type == 'submit') {
				$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
			} else {
				$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
			}
		}
		if (($currentPage - 5) < 7) {
			$start = 6;
			$end = 15;
		} else {
			$start = $currentPage - 4;
			$end = $currentPage + 4;
			$html .= ' &hellip; ';
		}
		if (($currentPage + 5) > ($pages - 7)) {
			$start = ($pages - 15);
			$end = $pages;
		}
		for ($i = $start; $i <= $end; ++$i) {
			$class = ($currentPage == $i) ? 'class="bold"' : '';
			if ($type == 'submit') {
				$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
			} else {
				$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
			}
		}
		if ($end != $pages) {
			$html .= ' &hellip; ';
			for ($i = $pages - 5; $i <= $pages; ++$i) {
				$class = ($currentPage == $i) ? 'class="bold"' : '';
				if ($type == 'submit') {
					$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
				} else {
					$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
				}
			}
		}
	} else {
		for ($i = 1; $i <= $pages; ++$i) {
			$class = ($currentPage == $i) ? 'class="bold"' : '';
			if ($type == 'submit') {
				$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
			} else {
				$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
			}
		}
	}
	return $html;
}

function renderCategoryPath($id, $from = 'category') {
	$calculated_category_path_string = '';
	$appendedText = '&nbsp;<span class="cp_next">&gt;</span>&nbsp;';
	$calculated_category_path = MLProduct::gi()->generateCategoryPathOld($id, $from);
	for ($i = 0, $n = sizeof($calculated_category_path); $i < $n; $i ++) {
		for ($j = 0, $k = sizeof($calculated_category_path[$i]); $j < $k; $j ++) {
			$calculated_category_path_string .= fixHTMLUTF8Entities($calculated_category_path[$i][$j]['text']).$appendedText;
		}
		$calculated_category_path_string = substr($calculated_category_path_string, 0, -strlen($appendedText)).'<br>';
	}
	$calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

	if (strlen($calculated_category_path_string) < 1)
		$calculated_category_path_string = ML_LABEL_CATEGORY_TOP;

	return $calculated_category_path_string;
}

function loadConfigForm($lang, $files, $replace = array()) {
	$form = array();
	foreach ($files as $file => $options) {
		$fC = file_get_contents(DIR_MAGNALISTER.'config/'.$lang.'/'.$file);
		if (!empty($replace)) {
			$fC = str_replace(array_keys($replace), array_values($replace), $fC);
		}
		$fC = json_decode($fC, true);
		if (array_key_exists('unset', $options)) {
			foreach ($options['unset'] as $key) {
				unset($fC[$key]);
			}
		}
		$form = array_merge($form, $fC);
	}
	return $form;
}

function getTinyMCEDefaultConfigObject() {
    $langCode = $_SESSION['magna']['selected_language'];
	if (!empty($langCode) && file_exists(DIR_MAGNALISTER.'js/tiny_mce/langs/'.$langCode.'.js')) {
		$langCode = 'language: "'.$langCode.'",';
	} else {
		$langCode = '';
	}
	$fullpage = getDBConfigValue(array('general.editor.fullpage', 'val'), 0, 'false') == 'true';

	return '
if (typeof tinyMCEMagnaDefaultConfig == "undefined") {
	var tinyMCEMagnaDefaultConfig = {
		// General options
		mode : "textareas",
		theme : "advanced",
		'.$langCode.'
		skin : "o2k7",
		skin_variant : "silver",
		editor_selector : "tinymce",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,'.
			'media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,'.
			'template'.($fullpage ? ',fullpage' : '').'",
	
		// Theme options
		theme_advanced_buttons1 : "fullscreen,preview,code,|,undo,redo,|,bold,italic,underline,strikethrough,|,styleprops,|,forecolor,backcolor,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,cleanup,|,charmap,emotions,iespell,media,advhr,|,insertdate,inserttime",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,insertlayer,moveforward,movebackward,absolute,|,visualchars,nonbreaking",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : false,
		// Example content CSS (should be your site CSS)
		//content_css : "style/style.css",
	
		width: "100%",
		valid_elements : "*[*]",
		invalid_elements : "",
		valid_children : "+body[style]",
		extended_valid_elements : "style[width], a[href|#]",
	
		// Drop lists for link/image/media/template dialogs
		//template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",
	
		relative_urls : false,
		document_base_url : "'.DIR_WS_CATALOG.'",
		remove_script_host : false,
		media_strict: false,
		
		gecko_spellcheck : true,
	
		autosave_ask_before_unload : true
	}
}';
}

function magna_wysiwyg($params, $value = '') {
	if (array_key_exists('class', $params)) {
		$params['class'] .= ' tinymce';
	} else {
		$params['class'] = 'tinymce';
	}
	$html = '<textarea';
	foreach ($params as $attr => $val) {
		$html .= ' '.$attr.'="'.$val.'"';
	}
	$html .= '>'.str_replace('<', '&lt;', (string)$value).'</textarea>';

	if ('tinyMCE' == getDBConfigValue('general.editor',0,'tinyMCE')) {

		$html .= '<script type="text/javascript" src="js/tiny_mce/tiny_mce.js"></script>';

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
		<?php echo getTinyMCEDefaultConfigObject(); ?>
			$(document).ready(function() {
				tinyMCE.init(tinyMCEMagnaDefaultConfig);
			});
		/*]]>*/</script><?php
		$html .= ob_get_contents();
		ob_end_clean();
	}
	return $html;
}

function magnaFixRamSize() {
	$nr = defined('ML_DEFAULT_RAM') ? ML_DEFAULT_RAM : '256M';
	$ramsize = @ini_get('memory_limit');
	if (!is_string($ramsize) || empty($ramsize)) {
		return @(bool)ini_set('memory_limit', $nr);
	}
	if (convert2Bytes($ramsize) < convert2Bytes($nr)) {
		return @(bool)ini_set('memory_limit', $nr);
	}
	return false;
}

function magnaFooterDebugTimers() {
	if (!defined('MAGNALISTER_PLUGIN') && file_exists(DIR_FS_DOCUMENT_ROOT.'magnaCallback.php')
		&& defined('MAGNA_DEBUG') && MAGNA_DEBUG
	) {
		global $_magnacallbacktimer, $_executionTime;

		echo '
		<style>
		textarea.apiRequestTime {
			border: 2px solid #ccc;
			background: #f8f8f8;
			color: #f8f8f8;
			border-radius: 5px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			width: 5px !important;
			height: 5px !important;
			padding: 0 !important;
			margin: 0 0 3px 0;
			overflow: hidden;
			font: 11px/1em monospace;
			display: block;
			-moz-box-sizing: content-box;
			-webkit-box-sizing: content-box;
			resize: none;
		}
		textarea.apiRequestTime:hover {
			color: #333;
			width: 100% !important;
			height: 300px !important;
			margin-top: -298px;
			position: relative;
			overflow: auto;
			padding: 3px 3px 0 3px !important;
			box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 2px 2px rgba(0, 0, 0, 0.3);
			-moz-box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 2px 2px rgba(0, 0, 0, 0.3);
			-webkit-box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 2px 2px rgba(0, 0, 0, 0.3);
		}
		div#magnaErrors {
			position: relative;
			width: 100%;
		}
		div#magnaErrors table {
			border-collapse: separate;
			border-spacing: 2px;
			width: 100%;
			position: relative;
			border: 2px solid #f00;
		}
		div#magnaErrors table th,
		div#magnaErrors table td {
			padding: 3px 5px;
			vertical-align: top;
		}
		div#magnaErrors table thead th {
			background: #444;
			color: #fff;
			font-weight: bold;
		}
		div#magnaErrors table tbody.odd td {
			background: #aaa;
			color: #000;
		}
		div#magnaErrors table tbody.even td {
			background: #ccc;
			color: #000;
		}
		div#magnaErrors table tbody td.level {	
			font-weight: bold;
		}
		div#magnaErrors table tbody td.level.notice {	
			background: #C9FF48;
			color: #000;
		}
		div#magnaErrors table tbody td.level.warning {	
			background: #FFB548;
			color: #000;
		}
		div#magnaErrors table tbody td.level.fatal {	
			background: #FF4848;
			color: #000;
		}
		div#magnaErrors table tbody td.message {
			position: relative;
		}
		div#magnaErrors table tbody td.action textarea,
		div#magnaErrors table tbody td.message pre {
			display: none;
			position: absolute;
			background: #fff;
			color: #000;
			border: 2px solid #999;
			z-index: 2;
			padding: 3px;
			margin-top: -2px;
			left: 0;
			width: 100%;
			overflow: auto;
		}
		div#magnaErrors table tbody td.message:hover pre {
			bottom: 1em;
			display: block;
			max-height: 450px;
		}
		div#magnaErrors table tbody td.action:hover textarea {
			display: block;
		    color: #333333;
		    height: 300px !important;
		    overflow: auto;
		    padding: 3px 3px 0 !important;
		    width: 100% !important;
		    font: 11px/1em monospace;
		    margin-top: -330px;
		}
		#magnaFootDebugger {
			text-align: left;
			background: #fff;
			border: 2px solid #999;
			font: 11px/1.2em sans-serif;
			padding: 5px;
		}
		#magnaFootDebugger hr {
			border-top: none;
			border-left: none;
			border-right: none;
			border-bottom: 1px solid #999;
		}
		</style>
		<div id="magnaFootDebugger">
			magnaCallback Time: <b>'.microtime2human($_magnacallbacktimer).'</b><br/><hr/>';
	
		if (class_exists('MagnaDB') && class_exists('MagnaConnector')) {
			$_executionTime = microtime(true) -  $_executionTime;
			$memory = memory_usage();
			echo '
				Entire page served in <b>'.microtime2human($_executionTime).'.</b><br/><hr/>
				API-Request Time: '.microtime2human(MagnaConnector::gi()->getRequestTime()).'. <br/>
				Processing Time: '.microtime2human($_executionTime - MagnaConnector::gi()->getRequestTime()).'. <br/><hr/>
				'.(($memory !== false) ? 'Max. Memory used: <b>'.$memory.'</b>. <br/><hr/>' : '').'
				DB-Stats (only MagnaDB Queries!): <br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Queries used: <b>'.MagnaDB::gi()->getQueryCount().'</b><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Query time: '.microtime2human(MagnaDB::gi()->getRealQueryTime()).'<br/><hr/>
			';
		}
		if (class_exists('MagnaConnector')) {
			$tpR = MagnaConnector::gi()->getTimePerRequest();
			if (!empty($tpR)) {
				echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
				foreach ($tpR as $item) {
					echo print_m($item['request'], microtime2human($item['time']).' ['.$item['status'].']', true)."\n";
				}
				echo '</textarea>';
			}
		}
		if (class_exists('MagnaDB')) {
			$tpR = MagnaDB::gi()->getTimePerQuery();
			if (!empty($tpR)) {
				echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
				foreach ($tpR as $item) {
					echo print_m(ltrim(rtrim($item['query'], "\n"), "\n"), microtime2human($item['time']), true)."\n";
				}
				echo '</textarea>';
			}
		}
		$err = MagnaError::gi()->exceptionsToHTML(false);
		if (!empty($err)) {
			echo '<br/><hr/><div id="magnaErrors"><div>'.$err.'</div></div>';
		}
		
		echo '</div>';
	}
}

function magnaDumpSqlErrorlog() {
	if (!class_exists('MagnaDB')) {
		return;
	}
	$errors = MagnaDB::gi()->getSqlErrors();
	if (empty($errors)) {
		return;
	}
	
	echo '
		<style>
table.sqlerror {
	border: 2px solid #f00 !important;
}
table.sqlerror thead th {
	background: #933 !important;
	color: #fff;
	font-weight: bold;
}
table.sqlerror td {
	position: relative;
	vertical-align: top;
}
table.sqlerror td div.debug {
	position: absolute;
	display: none;
	border: 2px solid rgba(0, 0, 0, 0.5);
	background: rgba(255, 255, 255, 0.9);
	padding: 5px;
	z-index: 2;
}
table.sqlerror td.q:hover div.debug,
table.sqlerror td.q div.debug:hover,
table.sqlerror td.q:hover div.debug:hover {
	display: block;
}
		</style>
		<table class="datagrid autoOddEven sqlerror">
			<thead>
				<tr>
					<th>Query</th>
					<th>ErrNo</th>
					<th>Error</th>
				</tr>
			</thead>
			<tbody>';
	foreach ($errors as $e) {
		echo '
				<tr>
					<td class="q">
						<pre>'.htmlspecialchars($e['Query']).'</pre>
						<div class="debug">
							<pre>'.htmlspecialchars(print_r($e['Backtrace'], true)).'</pre>
						</div>
					</td>
					<td>
						'.$e['ErrNo'].'
					</td>
					<td>
						'.$e['Error'].'
					</td>
				</tr>';
	}
	echo '
			</tbody>
		</table>';
}

function magnaGenerateNavStructure() {
	global $magnaConfig, $_modules;
	
	$alwaysDisplayedModules = array();
	foreach ($_modules as $key => $item) {
		if ($item['displayAlways']) {
			$alwaysDisplayedModules[$key] = true;
		}
	}
	
	if (!isset($magnaConfig['maranon']['Marketplaces']) 
		|| !is_array($magnaConfig['maranon']['Marketplaces'])
	) {
		$magnaConfig['maranon']['Marketplaces'] = array();
	}
	$structure = array();
	
	$doinavtive = true;
	
	if (!empty($magnaConfig['maranon']['Marketplaces'])) {
		foreach ($magnaConfig['maranon']['Marketplaces'] as $mpID => $key) {
			$curItem = array ();

			if (!isset($_modules[$key])) continue;
			$item = $_modules[$key];
	
			$classes = array();
			if (array_key_exists($key, $alwaysDisplayedModules)) {
				unset($alwaysDisplayedModules[$key]);
			}
			if (array_key_exists('mp', $_GET) && ($_GET['mp'] == $mpID)) {
				$classes[] = 'selected';
			}
			$curItem['title'] = isset($item['subtitle']) 
							? $item['subtitle'] 
							: $item['title'];
			$curItem['label'] = getDBConfigValue(array('general.tabident', $mpID), '0', '');
			$curItem['url']   = toURL(array('mp' => $mpID));
			$curItem['image'] = isset($item['logo'])
							? DIR_MAGNALISTER_IMAGES.'logos/'.$item['logo'].'.png'
							: '';
			$curItem['class'] = implode(' ', $classes);
			$curItem['key']   = $key.'_'.$mpID;
			
			$structure[] = $curItem;
		}
	} else {
		$doinavtive = false;
	}
	if (!empty($alwaysDisplayedModules)) {
		$curModule = isset($_GET['module']) ? $_GET['module'] : '';
		foreach ($alwaysDisplayedModules as $key => $null) {
			$item = $_modules[$key];
	
			$classes = array();
			if ($curModule == $key) {
				$classes[] = 'selected';
			}
			if ((!isset($item['type']) || ($item['type'] != 'system')) && $doinavtive) {
				$classes[] = 'inactive';
			}
			$curItem = array();
			
			$curItem['title'] = isset($item['subtitle']) 
							? $item['subtitle'] 
							: $item['title'];
			$curItem['label'] = isset($item['label'])
							? $item['label']
							:  '';
			$curItem['url']   = toURL(array('module' => $key));
			$curItem['image'] = isset($item['logo'])
							? DIR_MAGNALISTER_IMAGES.'logos/'.$item['logo'].'_inactive.png'
							: '';
			$curItem['class'] = implode(' ', $classes);
			$curItem['key']   = $key;
			
			$structure[] = $curItem;
		}
	}
	return $structure;
}

function magnaGenerateSideNav($args) {
	global $magnaConfig;
	
	$structure = magnaGenerateNavStructure();
	$html = '';

	switch (SHOPSYSTEM) {
		case 'gambio': {
			$html .= '
				<div class="leftmenu_head" '.
					'style="background-image:url('.DIR_MAGNALISTER_IMAGES.'magnalister_gambio_icon.png)">'.
					'<a id="FILENAME_MAGNALISTER_TOP" href="' . toURL() . '" class="fav_drag_item ui-draggable">'.
					'Marketing</a></div>
				<div class="leftmenu_collapse leftmenu_collapse_opened"> </div>
				<ul class="leftmenu_box" id="BOX_MAGNALISTER_LINK">';
			if (empty($structure)) {
				$html .= '<li class="leftmenu_body_item">'.
					'<a id="FILENAME_MAGNALISTER" href="' . toURL() . '" class="fav_drag_item ui-draggable">'.
					'magnalister Admin</a></li>';
			} else {
				foreach ($structure as $item) {
					$item['title'] .= !empty($item['label']) 
						? ' :: '.strip_tags(str_replace(
							array('&lt;', '&gt;', '&quot;'),
							array('<', '>', '"'),
							fixHTMLUTF8Entities($item['label'])
						)) 
						: '';
					$html .= '<li class="leftmenu_body_item '.$item['class'].'">'.
						'<a id="BOX_MAGNA_'.strtoupper($item['key']).'" href="'.$item['url'].'" '.
							'class="fav_drag_item ui-draggable" '.
							'title="'.$item['title'].'">'.$item['title'].'</a></li>';
				}
			}
			$html .= '</ul>';
		}
	}
	return $html;

}

function renderDateTimePicker($key, $default = false, $alwaysDalete = false, $class = '') {
	$html = '';

	if (!empty($default)) {
		$default = strtotime($default);
		if ($default > 0) {
			$default = date('Y/m/d H:i:s', $default);
		} else {
			$default = '';
		}
		$default = date('Y/m/d H:i:s', strtotime($default));
		$deleteButton = false;
	} else {
		$deleteButton = true;
	}
	$idkey = str_replace(array('.', ' ', '[', ']'), '_', $key);
	
	$langCode = $_SESSION['language_code'];
	if (empty($langCode)) {
		$langCode = $_SESSION['language_code'] = mlGetLanguageCodeFromID('2');
	}

	$html .= '
		<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
		<input type="text" id="'.$idkey.'_visual" value="" readonly="readonly" '.$class.'/>
		<input type="hidden" id="'.$idkey.'" name="'.$key.'" value="'.str_replace('/', '-', $default).'"/>
		'.(($deleteButton || $alwaysDalete) ? '<span class="gfxbutton small delete '.$class.'" id="'.$idkey.'_reset"></span>' : '').'
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				jQuery.datepicker.setDefaults(jQuery.datepicker.regional[\'\']);
				jQuery.timepicker.setDefaults(jQuery.timepicker.regional[\'\']);
				$("#'.$idkey.'_visual").datetimepicker(
					jQuery.extend(
						jQuery.datepicker.regional[\''.$langCode.'\'],
						jQuery.timepicker.regional[\''.$langCode.'\']
					)
				).datetimepicker("option", {
					onClose:  function(dateText, inst) {
						var d = $("#'.$idkey.'_visual").datetimepicker("getDate"),
							s = jQuery.datepicker.formatDate("yy-mm-dd", d)+\' \'+
							jQuery.datepicker.formatTime("HH:mm:ss", {
								hour: d.getHours(),
								minute: d.getMinutes(),
								second: d.getSeconds()
							}, { ampm: false });
						$("#'.$idkey.'").val(s);
					}
				})'.(!empty($default) ? '.datetimepicker(
					"setDate", new Date(\''.$default.'\')
				)' : '').';
				$("#'.$idkey.'_reset").click(function() {
					$("#'.$idkey.'_visual").val(\'\');
					$("#'.$idkey.'").val(\'\');
				});
			});
		/*]]>*/</script>'."\n";	
	return $html;
}

/** 
 * Returns the offset from the origin timezone to the remote timezone, in seconds.
 * @param $remote_tz
 * @param $origin_tz	If null the servers current timezone is used as the origin.
 * @return 	The offset in seconds as int or false incase of a failure.
 */
function magnaGetTimezoneOffset($remote_tz, $origin_tz = null) {
	if ($origin_tz === null) {
		$origin_tz = @date('T');
	}
	if (!class_exists('DateTimeZone')) {
		global $_MagnaSession;
		if (isset($_MagnaSession['TimezoneOffset'])) {
			return $_MagnaSession['TimezoneOffset'];
		}
		try {
			$response = MagnaConnector::gi()->submitRequest(array (
				'ACTION' => 'GetTimezoneOffset',
				'SUBSYSTEM' => 'Core',
				'FROM' => $remote_tz,
				'TO' => $origin_tz,
			));
			$_MagnaSession['TimezoneOffset'] = (int)$response['DATA'];
			return $_MagnaSession['TimezoneOffset'];
		} catch (MagnaException $me) {
			return false;
		}
	}
	try {
		$origin_dtz = new DateTimeZone($origin_tz);
		$remote_dtz = new DateTimeZone($remote_tz);
	} catch (Exception $e) {
		return false;
	}
	$origin_dt = new DateTime("now", $origin_dtz);
	$remote_dt = new DateTime("now", $remote_dtz);
	$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
	return $offset;
}

/**
 * Returns a local time
 * which matches a given time on the magnalister server
 * If offset cannot be determined, return = input
 * @param $magnaTimeval  (YYYY-mm-dd HH:MM:ss)
 * @return $localTimeval (YYYY-mm-dd HH:MM:ss)
 */
function magnaTimeToLocalTime($magnaTimeval, $reverse = false) {
	$offset = magnaGetTimezoneOffset('Europe/Berlin');
	if (false == $offset) {
		return $magnaTimeval;
	}
	if ($reverse) {
		$offset = (int)((-1) * $offset);
	}
	return date('Y-m-d H:i:s', mktime(
		substr($magnaTimeval, 11,2), substr($magnaTimeval, 14,2), substr($magnaTimeval, 17,2),
		substr($magnaTimeval, 5,2),  substr($magnaTimeval, 8,2),  substr($magnaTimeval, 0,4)
		) + $offset);
}

function localTimeToMagnaTime($localTimeval) {
	# call magnaTimeToLocalTime with reverse == true
	return magnaTimeToLocalTime($localTimeval, true);
}

function mlGetLanguageCodeFromID($iLanguageID) {
	return MagnaDB::gi()->fetchOne("
		SELECT code 
		  FROM ".TABLE_LANGUAGES."
		 WHERE languages_id = '".$iLanguageID."'
	");
}

function mlLanguageIDFromCode($sLanguageCode) {
	return MagnaDB::gi()->fetchOne("
		SELECT languages_id
		  FROM ".TABLE_LANGUAGES."
		 WHERE code = '".MagnaDB::gi()->escape($sLanguageCode)."'
	");
}

function ml_extractBase64(&$in) {
	$matches = array();
	$out = array();
	if (preg_match_all('/="(data:[^"]+)"/', $in, $matches)) {
		$i = 1;
		foreach ($matches[1] as $m) {
			$phold = '{#Base64_'.($i++).'#}';
			$in = str_replace($m, $phold, $in);
			$out[$phold] = $m;
		}
	}
	return $out;
}

function ml_restoreBase64($in, $baseIndex) {
	if (empty($baseIndex)) {
		return $in;
	}
	return str_replace(array_keys($baseIndex), array_values($baseIndex), $in);
}
