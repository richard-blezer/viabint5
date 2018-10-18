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
 * $Id: productIDTypeIdent.php 132 2012-10-19 12:39:38Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

function isUPC($upc) {
	$upc = str_replace(array('-', ' '), '', $upc);
	if (strlen($upc) != 12) {
		return false;
	}
	$sum = 0;
	for ($i = 1; $i < strlen($upc); ++$i) {
		$factor = ($i % 2) ? 3 : 1;
		$sum += $factor * $upc[$i - 1];
	}
	$check = ((int)ceil($sum / 10) * 10) - $sum;
	return $check == $upc[11];
}

function isEAN13($ean) {
	$ean = str_replace(array('-', ' '), '', $ean);
	if (strlen($ean) != 13) {
		return false;
	}
	$checksum = 0;
	for ($i = 0; $i < strlen($ean); ++$i) {
		$factor = (($i % 2) == 1) ? 3 : 1;
		$checksum += $factor * $ean[$i];
	}
	return (($checksum % 10) == 0);
}

function isISBN10($isbn) {
	$isbn = str_replace(array('-', ' '), '', $isbn);
	if (strlen($isbn) != 10) {
		return false;
	}
	$checksum = 0;
	for ($i = 0; $i < 9; ++$i) {
		$checksum += (10 - $i) * $isbn[$i];
	}
	$checksum = 11 - ($checksum % 11);
	$last = $isbn[$i];
	if (strtolower($last) == 'x') {
		$last = 10;
	}
	return ($last == $checksum);
}

function isISBN13($isbn) {
	$isbn = str_replace(array('-', ' '), '', $isbn);
	if ((strlen($isbn) != 13) || (substr($isbn, 0, 3) != '978')) {
		return false;
	}
	return isEAN13($isbn);
}

function isASIN($amazonsin) {
	return preg_match('/^[0-9A-Z]{10}$/', $amazonsin);
}

function identNumberKind($productID) {
	global $magnaConfig;
	$productIDTypes = array_flip($magnaConfig['amazon']['matching']['productIDTypes']);
	if (isISBN13($productID) || isISBN10($productID)) { /* isISBN13 muss vor isEAN13 kommen, da ISBN13 auch immer EAN13 */
		return $productIDTypes['ISBN'];

	} else if (isEAN13($productID)) {
		return $productIDTypes['EAN'];

	} else if (isUPC($productID)) {
		return $productIDTypes['UPC'];

	} else if (isASIN($productID)) {
		return $productIDTypes['ASIN'];

	}
	/* No Supported Product Identification Number Format */
	return false;
}
?>