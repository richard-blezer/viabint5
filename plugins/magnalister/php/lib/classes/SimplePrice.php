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
 * $Id: SimplePrice.php 346 2014-05-21 17:33:33Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
// veyton
class SimplePrice {
	private $price = 0.0;
	private $actualCurr = '';
	private $currencies = array();
	
	private $isSpecialPrice = false;
	private $isGroupPrice   = false;
	
	private $addedTax = 0;
	
	protected static $cache = array();
	
	public function __construct($price = null, $actualCurr = null) {
		$currencies_query = MagnaDB::gi()->query('
			SELECT code, title, prefix, suffix, dec_point, thousands_sep, decimals, value_multiplicator
			  FROM '.TABLE_CURRENCIES.'
		');

		while ($currency = MagnaDB::gi()->fetchNext($currencies_query)) {
			$this->currencies[$currency['code']] = array (
				'title' => $currency['title'],	
				'symbol_left' => fixHTMLUTF8Entities($currency['prefix']),
				'symbol_right' => fixHTMLUTF8Entities($currency['suffix']),
				'decimal_point' => $currency['dec_point'],
				'thousands_point' => $currency['thousands_sep'],
				'decimal_places' => (int)$currency['decimals'],
				'value' => (float)$currency['value_multiplicator']
			);
		}
		if ($actualCurr != null) {
			$this->setCurrency($actualCurr);
			if ($price != null) {
				$this->setPrice((float)$price);
			}
		}
	}

	private function triggerError($msg) {
		trigger_error($msg);
		if (MAGNA_DEBUG) {
			echo print_m(prepareErrorBacktrace(2));
		}
		return $this;
	}

	public function currencyExists($cur) {
		return array_key_exists($cur, $this->currencies);
	}

	public function setCurrency($actualCurr) {
		if (empty($actualCurr)) {
			return $this;
		}
		if (!$this->currencyExists($actualCurr)) {
			return $this->triggerError(__METHOD__.': This currency ('.$actualCurr.') is not yet available in your shop.');
		}
		$this->actualCurr = $actualCurr;
		return $this;
	}

	public function setPrice($price) {
		if ($this->actualCurr == null) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		$this->isSpecialPrice = false;
		$this->isGroupPrice   = false;

		$this->price = (float)$price;
		return $this;
	}

	public function setPriceAndCurrency($price, $actualCurr) {
		$this->isSpecialPrice = false;
		$this->isGroupPrice   = false;

		$this->setCurrency($actualCurr);
		$this->setPrice((float)$price);
		return $this;
	}

	public function getCurrency() {
		return $this->actualCurr;
	}

	public function getGroupPrice($groupID, $productID) {
		if (!MagnaDB::gi()->tableExists(TABLE_PRODUCTS_PRICE_GROUP.$groupID)) {
			return 0.0;
		}
		return (float)MagnaDB::gi()->fetchOne('
			   SELECT price
			     FROM '.TABLE_PRODUCTS_PRICE_GROUP.$groupID.' 
			    WHERE products_id = "'.$productID.'" 
			          AND (discount_quantity = 1 OR discount_quantity = 0 OR discount_quantity = "" OR discount_quantity IS NULL)
			 ORDER BY id DESC
			    LIMIT 1
		');
	}

	public function getSpecialOffer($pID, $priceGroup) {
		if ($priceGroup > 0) {
			$sqlTextPriceGroup = ' OR group_permission_' . $priceGroup . ' = 1';
		} else {
			$sqlTextPriceGroup = '';
		}
		return (float)MagnaDB::gi()->fetchOne('
		    SELECT specials_price
		      FROM ' . TABLE_PRODUCTS_PRICE_SPECIAL . '
		     WHERE products_id = "' . $pID . '"
		           AND status = 1
		           AND date_available < NOW()
		           AND date_expired > NOW()
		           AND (group_permission_all = 1 '. $sqlTextPriceGroup . ')
		');
	}

	public static function loadPriceSettings($mpId, $extra = '') {
		$mp = magnaGetMarketplaceByID($mpId);
		
		# extra name extensions like 'chinese' or 'fixed' for eBay
		if (!empty($extra) && is_string($extra)) {
			$extra = '.'.trim($extra, '.');
		} else {
			$extra = '';
		}
		
		return array (
			'AddKind' => getDBConfigValue($mp.$extra.'.price.addkind', $mpId, 'percent'),
			'Factor'  => (float)getDBConfigValue($mp.$extra.'.price.factor', $mpId, 0),
			'Signal'  => getDBConfigValue($mp.$extra.'.price.signal', $mpId, ''),
			'Group'   => getDBConfigValue($mp.$extra.'.price.group', $mpId, ''),
			'UseSpecialOffer' => getDBConfigValue(array($mp.$extra.'.price.usespecialoffer', 'val'), $mpId, false),
		);
	}
	
	protected static function isValidPriceConfig($pConfig) {
		return is_array($pConfig)
			&& isset($pConfig['AddKind']) && isset($pConfig['Factor'])
			&& isset($pConfig['Signal']) && isset($pConfig['Group'])
			&& isset($pConfig['UseSpecialOffer']);
	}

	public function setPriceFromDB($pID, $mpID, $extra = '') {
		if ($this->actualCurr == null) {
			$this->setCurrency(getCurrencyFromMarketplace($mpID));
		}
		$this->isSpecialPrice = false;
		$this->isGroupPrice   = false;
		$this->addedTax = 0.0;

		if (self::isValidPriceConfig($extra)) {
			$pConfig = $extra;
		} else {
			$pConfig = self::loadPriceSettings($mpID, $extra);
		}
		
		if ($pConfig['UseSpecialOffer'] && (($price = $this->getSpecialOffer($pID, $pConfig['Group'])) > 0)) {
			$this->price = $price;
			$this->isSpecialPrice = true;
			return $this;
		}
		
		if (((int)$pConfig['Group'] > 0)
			&& (($price = $this->getGroupPrice((int)$pConfig['Group'], $pID)) > 0)
		) {
			$this->price = $price;
			$this->isGroupPrice = true;
			return $this;
		}

		$this->price = (float)MagnaDB::gi()->fetchOne('
		    SELECT products_price 
		      FROM '.TABLE_PRODUCTS.'
		     WHERE products_id="'.$pID.'"
		');
		return $this;
	}

	public function finalizePrice($pID, $mpID, $extra = '') {
		if (self::isValidPriceConfig($extra)) {
			$pConfig = $extra;
		} else {
			$pConfig = self::loadPriceSettings($mpID, $extra);
		}
		
		$this->addTaxByPID($pID)->calculateCurr();
		
		switch ($pConfig['AddKind']) {
			case 'percent': {
				$this->addTax((float)$pConfig['Factor']);
				break;
			}
			case 'addition': {
				$this->addLump((float)$pConfig['Factor']);
				break;
			}
			case 'constant': {
				$this->price = (float)$pConfig['Factor'];
				break;
			}
		}
		
		$this->roundPrice()->makeSignalPrice($pConfig['Signal']);
		return $this;
	}

	public function setFinalPriceFromDB($pID, $mpID, $extra = '') {
		if (!self::isValidPriceConfig($extra)) {
			$extra = self::loadPriceSettings($mpID, $extra);
		}
		
		$this->setPriceFromDB($pID, $mpID, $extra)->finalizePrice($pID, $mpID, $extra);
		return $this;
	}

	public function isSpecialPrice() {
		return $this->isSpecialPrice;
	}

	public function isGroupPrice() {
		return $this->isGroupPrice;
	}

	public function addTax($tax) {
		$this->addedTax = (float)$tax;
		if ($this->addedTax == 0.0) {
			return $this;
		}
		$this->price = $this->price + $this->price / 100 * $tax;
		return $this;
	}

	public function getTaxValue($tax) {
		return $this->price / 100 * $tax;
	}

	public function getTaxValueBrutto($tax) {
		return $this->price - ($this->price / (1 + ($tax / 100)));
	}

	public function removeTax($tax = false) {
		if ($tax === false) {
			$tax = $this->addedTax;
		}
		$this->addedTax = 0.0;
		if ($tax == 0.0) {
			return $this;
		}
		$this->price = ($this->price / (($tax + 100) / 100));
		return $this;
	}
	
	protected static function queryCache($query, $invalidate = false) {
		$sMd5 = md5($query);
		if (!isset(self::$cache[$sMd5]) || $invalidate) {
			self::$cache[$sMd5] = MagnaDB::gi()->fetchOne($query);
		}
		return self::$cache[$sMd5];
	}
	
	public static function getTaxByClassID($taxClassID, $countryCode = false) {
		$taxRate = false;
		if ($countryCode === false) {
			# Fallback to shop default
			$countryCode = _STORE_COUNTRY; 
		} else {
			$countryCode = strtoupper($countryCode);
			$taxRate = self::queryCache(eecho('
				SELECT MAX(tax_rate)
				  FROM '.TABLE_TAX_RATES.' tr, '.TABLE_COUNTRIES.' c 
				 WHERE tr.tax_class_id="'.$taxClassID.'"
				       AND tr.tax_zone_id=c.zone_id
				       AND c.countries_iso_code_2="'.$countryCode.'"
				 LIMIT 1
			', false));
		}
		if ($taxRate === false) {
			$taxRate = self::queryCache(eecho('
				SELECT MAX(tax_rate)
				  FROM '.TABLE_TAX_RATES.' tr, '.TABLE_COUNTRIES.' c 
				 WHERE tr.tax_class_id="'.$taxClassID.'"
				       AND tr.tax_zone_id=c.zone_id
				       AND c.countries_iso_code_2="'._STORE_COUNTRY.'"
				 LIMIT 1
			', false));
		}
		return (float)$taxRate;
	}

	public function addTaxByTaxID($taxClassID) {
		return $this->addTax(
			self::getTaxByClassID($taxClassID)
		);
	}

	public function removeTaxByTaxID($taxClassID) {
		return $this->removeTax(
			self::getTaxByClassID($taxClassID)
		);
	}

	public static function getTaxByPID($pID) {
		$taxClassID = MagnaDB::gi()->fetchOne('
			SELECT products_tax_class_id
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id="'.$pID.'"
			 LIMIT 1
		');
		if (($taxClassID === false) || ($taxClassID === null)) {
			return 0;
		}
		return self::getTaxByClassID($taxClassID);
	}

	public function addTaxByPID($pID) {
		return $this->addTax(
			self::getTaxByPID($pID)
		);
	}

	public function removeTaxByPID($pID) {
		return $this->removeTax(
			self::getTaxByPID($pID)
		);
	}

	public function addLump($add) {
		$this->price += (float)$add;
		return $this;
	}
	
	public function subLump($sub) {
		$this->price -= (float)$sub;
		return $this;
	}

	/** Does not exist for veyton */
	public function addAttributeSurcharge($aID) {
		return $this;
	}

	/**
	 * Geht immer von DEFAULT_CURRENCY mit Umrechnungsfaktor == 1.0 aus
	 */
	public function calculateCurr() {
		$this->price = $this->currencies[$this->actualCurr]['value'] * $this->price;
		return $this;
	}

	/**
	 * Geht immer von DEFAULT_CURRENCY mit Umrechnungsfaktor == 1.0 aus
	 */	
	public function updateCurrency($val) {
		if (empty($this->actualCurr)) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		$val = (float)$val;
		if ($val <= 0) {
			return $this;
		}

		MagnaDB::gi()->update(TABLE_CURRENCIES, array(
			'value_multiplicator' => $val,
		), array (
			'code' => $this->actualCurr
		));
		$this->currencies[$this->actualCurr]['value'] = $val;
		return $this;
	}
	
	public function updateCurrencyByService(&$success = false) {
		if (empty($this->actualCurr)) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		if ($this->actualCurr == DEFAULT_CURRENCY) {
			$success = true;
			return $this;
		}
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetExchangeRate',
				'SUBSYSTEM' => 'Core',
				'FROM' => strtoupper(DEFAULT_CURRENCY),
				'TO' => strtoupper($this->getCurrency()),
			));
			if ($result['EXCHANGERATE'] > 0) {
				$this->updateCurrency($result['EXCHANGERATE']);
			}
		} catch (MagnaException $e) { 
			$success = false;
			return $this;
		}
		$success = true;
		return $this;
	}
	
	public function getCurrencyValue() {
		if (empty($this->actualCurr)) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		return $this->currencies[$this->actualCurr]['value'];
	}
	

	public function roundPrice() {
		$this->price = round($this->price, $this->currencies[$this->actualCurr]['decimal_places']);
		return $this;
	}

	public function makeSignalPrice($decimalDigits) {
		if (empty($decimalDigits) || !ctype_digit($decimalDigits)) {
			return $this;
		}
		$this->price = floor($this->price) + (int)$decimalDigits/100;
		return $this;
	}

	public function getPrice() {
		return $this->price;
	}

	public function format($default = false) {
		return trim(
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['symbol_left'].
			' '.$this->formatWOCurrency($default).' '.
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['symbol_right']
		);
	}

	public function formatWOCurrency($default = false) {
		$format = $this->getFormatOptions($default);
		return trim(number_format($this->price, $format[0], $format[1], $format[2]));
	}

	public function getFormatOptions($default = false) {
		return array (
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['decimal_places'],
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['decimal_point'],
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['thousands_point']
		);
	}
}
