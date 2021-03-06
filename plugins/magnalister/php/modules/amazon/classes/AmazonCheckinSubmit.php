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
 * $Id: AmazonCheckinSubmit.php 546 2014-11-13 15:50:10Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/AmazonHelper.php');

class AmazonCheckinSubmit extends CheckinSubmit {
	private $checkinDetails = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession;
		/* Setzen der Currency nicht noetig, da Preisberechnungen bereits in 
		   der AmazonSummaryView Klasse gemacht wurden.
		 */
		$settings = array_merge(array(
			'mlProductsUseLegacy' => !defined('MAGNA_VEYTON_MULTIVARIANTS') || MAGNA_VEYTON_MULTIVARIANTS !== true,
			'language' => getDBConfigValue($_MagnaSession['currentPlatform'].'.lang', $_MagnaSession['mpID']),
			'itemsPerBatch' => 100,
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'skuAsMfrPartNo' => getDBConfigValue(array('amazon.checkin.SkuAsMfrPartNo', 'val'), $_MagnaSession['mpID'], false),
		), $settings);
		
		parent::__construct($settings);
	}
	
	public function makeSelectionFromErrorLog() {}
	
	protected function generateRequestHeader() {
		return array(
			'ACTION' => 'AddItems',
			'MODE' => $this->submitSession['mode']
		);
	}
	
	protected function markAsFailed($sku) {
		MagnaDB::gi()->insert(
			TABLE_MAGNA_AMAZON_ERRORLOG,
			array (
				'mpID' => $this->_magnasession['mpID'],
				'batchid' => '-',
				'errormessage' => ML_GENERIC_ERROR_UNABLE_TO_LOAD_PREPARE_DATA,
				'dateadded' => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize(array(
					'SKU' => $sku
				))
			)
		);
		$this->badItems[] = $pID;
		unset($this->selection[$pID]);
	}

	protected function getProduct($pID) {
		if (!$this->settings['mlProductsUseLegacy']) {
			MLProduct::gi()
				->setPriceConfig(AmazonHelper::loadPriceSettings($this->mpID))
				->setQuantityConfig(AmazonHelper::loadQuantitySettings($this->mpID))
				->setOptions(array('sameVariationsToAttributes' => true))
			;
		}
		return parent::getProduct($pID);
	}
	
	protected function appendMatchingData($pID, $product, &$data) {
		$productMatching = MagnaDB::gi()->fetchRow("
			SELECT * 
			FROM `".TABLE_MAGNA_AMAZON_PROPERTIES."`
			WHERE 
				asin<>'' 
				AND ".(
					($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.$product['ProductsModel'].'"'
			            : 'products_id="'.$pID.'"'
			    )."
				AND mpID='".$this->_magnasession['mpID']."'
			LIMIT 1
		");
		if ($productMatching === false) {
			return false;
		}
		$data['submit']['ASIN'] = $productMatching['asin'];
		$data['submit']['ConditionType'] = empty($productMatching['item_condition']) ? $data['submit']['ConditionType'] : $productMatching['item_condition'];
		$data['submit']['WillShipInternationally'] = $productMatching['will_ship_internationally'];
		$data['submit']['ConditionNote'] = sanitizeProductDescription($productMatching['item_note']);
		if ($productMatching['leadtimeToShip'] > 0) {
			$data['submit']['LeadtimeToShip'] = $productMatching['leadtimeToShip'];
		}
		$tax = $product['TaxPercent'];
		if(($tax - (int)$tax) > 0) {
			$decimalPlaces = 2;
		} else {
			$decimalPlaces = 0;
		}
		$taxStr = number_format($tax, $decimalPlaces, '.', '');
		$finalMWSTstr = str_replace('##', $taxStr, getDBConfigValue('amazon.mwstplaceholder', $this->_magnasession['mpID']));
		
		$data['submit']['ConditionNote'] = trim(substr(
			$data['submit']['ConditionNote'], 0, 
			(2000 - strlen($finalMWSTstr) - 1)
		).' '.$finalMWSTstr);
		$productVariations = isset($product['Variations']) && is_array($product['Variations'])? $product['Variations'] : array();
		$preparedVariations = array();
		
		foreach ($productVariations as $variation) {
			$variationProduct = array(
				'ProductsModel' => $variation['MarketplaceSku'], 
				'TaxPercent' => $product['TaxPercent']
			);
			$variationData = $data;
			$variationData['submit']['SKU'] = ($this->settings['keytype'] == 'artNr')
				? $variation['MarketplaceSku']
				: $variation['MarketplaceId'];
			if ($this->appendMatchingData($variation['VariationId'], $variationProduct, $variationData)) {
				unset($variationData['submit']['Variations']);
				$preparedVariations[] = $variationData['submit'];
			}
		}
		$data['submit']['Variations'] = empty($preparedVariations) ? array() : $preparedVariations;
		return true;
	}
	
	protected function appendApplyData($pID, $product, &$data) {
		$productApply = MagnaDB::gi()->fetchRow("
			SELECT data, leadtimeToShip
			  FROM `".TABLE_MAGNA_AMAZON_APPLY."`
			 WHERE data<>''
			       AND ".(($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.$product['ProductsModel'].'"'
			            : 'products_id="'.$pID.'"'
			       )."
			       AND is_incomplete='false'
			       AND mpID='".$this->_magnasession['mpID']."'
			 LIMIT 1
		");
		if ($productApply === false) {
			return false;
		}
		$productApply['data'] = @unserialize(@base64_decode($productApply['data']));
		if (empty($productApply['data']) || !is_array($productApply['data'])) {
			$productApply['data'] = array();
		}
		$productApply['category'] = @unserialize(@base64_decode($productApply['category']));
		if (empty($productApply['category']) || !is_array($productApply['category'])) {
			$productApply['category'] = array();
		}
		
		$productApply['data'] = array_merge($productApply['category'], $productApply['data']);
		if (empty($productApply['data'])) {
			return false;
		}
		
		$data['submit'] = array_merge($data['submit'], $productApply['data']);

		$data['submit']['SKU'] = ($this->settings['keytype'] == 'artNr')
			? $product['MarketplaceSku']
			: $product['MarketplaceId'];
		
		if (!empty($data['submit']['BrowseNodes'])) {
			foreach ($data['submit']['BrowseNodes'] as $i => $bn) {
				if ($bn == 'null') {
					unset($data['submit']['BrowseNodes'][$i]);
				}
			}
		}
		
		if (!empty($product['Attributes'])) {
			$data['submit']['CustomAttributes'] = array();
			foreach ($product['Attributes'] as $attribSet) {
				$data['submit']['CustomAttributes'][$attribSet['Name']] = $attribSet['Value'];
			}
		}
		
		$imagePath = getDBConfigValue('amazon.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
		$imagePath = trim($imagePath, '/ ').'/';
		$images = array();
		if (!empty($data['submit']['Images'])) {
			foreach ($data['submit']['Images'] as $image => $use) {
				if ($use == 'true') {
					$images[] = $imagePath.$image;
				}
			}
			$data['submit']['Images'] = $images;
		}

		if ($productApply['leadtimeToShip'] > 0) {
			$data['submit']['LeadtimeToShip'] = $productApply['leadtimeToShip'];
		}
			
		if (isset($product['Weight']) && is_array($product['Weight'])) {
			$data['submit']['Weight'] = $product['Weight'];
		}
		$data['submit']['Variations'] = (isset($product['Variations']) && is_array($product['Variations'])) ? $product['Variations'] : array();
		foreach ($data['submit']['Variations'] as &$vItem) {
			$vItem['SKU'] = ($this->settings['keytype'] == 'artNr')
				? $vItem['MarketplaceSku']
				: $vItem['MarketplaceId'];
				
			if ($productApply['leadtimeToShip'] > 0) {
				$vItem['LeadtimeToShip'] = $productApply['leadtimeToShip'];
			}
			if (
				(!isset($vItem['ManufacturerPartNumber']) || empty($vItem['ManufacturerPartNumber']))
				&& $this->settings['skuAsMfrPartNo']
			) {
				$vItem['ManufacturerPartNumber'] = $vItem['SKU'];
			}
			
			if (isset($vItem['Images']) && !empty($vItem['Images'])) {
				foreach ($vItem['Images'] as $imgKey => $imgVal) {
					$vItem['Images'][$imgKey] = $imagePath.$imgVal;
				}
			} else {
				unset($vItem['Images']);
			}
			
		}
		
		if (
			(!isset($data['submit']['ManufacturerPartNumber']) || empty($data['submit']['ManufacturerPartNumber']))
			&& $this->settings['skuAsMfrPartNo']
		) {
			$data['submit']['ManufacturerPartNumber'] = $data['submit']['SKU'];
		}
		
		return true;
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		if ($this->settings['mlProductsUseLegacy']) {
			return $this->appendAdditionalDataOld($pID, $product, $data); 
		}
		#echo print_m(func_get_args(), __METHOD__);
		
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}
		
		$data['submit']['Quantity'] = $data['quantity'];
		$data['submit']['SKU'] = magnaPID2SKU($pID);
		$data['submit']['Price'] = $data['price'];
		
		#VPE
		if ((isset($product['BasePrice'])) && is_array($product['BasePrice'] > 0)) {
			$data['submit']['BasePrice'] = $product['BasePrice'];
		}
		
		$data['submit']['ConditionType'] = getDBConfigValue('amazon.itemCondition', $this->_magnasession['mpID']);
		if (false === $this->appendMatchingData($pID, $product, $data)) {
			if (false === $this->appendApplyData($pID, $product, $data)) {
				$data['submit'] = array();
				$this->markAsFailed(magnaPID2SKU($pID));
				return;
			}
		}
	}
	
	protected function appendAdditionalDataOld($pID, $product, &$data) {
		#echo print_m(func_get_args(), __METHOD__);
		$conditionType = getDBConfigValue('amazon.itemCondition', $this->_magnasession['mpID']);
		
		$productMatching = $productApply = false;
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}

		if (($productMatching = MagnaDB::gi()->fetchRow('
			SELECT * FROM `'.TABLE_MAGNA_AMAZON_PROPERTIES.'`
			 WHERE asin<>"" AND 
			      '.(($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.$product['products_model'].'"'
			            : 'products_id="'.$pID.'"'
			        ).' AND
			       mpID="'.$this->_magnasession['mpID'].'"
			 LIMIT 1
		')) !== false) {
			$data['submit']['SKU'] = magnaPID2SKU($pID);
			$data['submit']['ASIN'] = $productMatching['asin'];
			$data['submit']['ConditionType'] = empty($productMatching['item_condition']) ? $conditionType : $productMatching['item_condition'];
			$data['submit']['Price'] = $data['price'];
			$data['submit']['Quantity'] = $data['quantity'];
			$data['submit']['WillShipInternationally'] = $productMatching['will_ship_internationally'];
			$data['submit']['ConditionNote'] = sanitizeProductDescription($productMatching['item_note']);
			if ($productMatching['leadtimeToShip'] > 0) {
				$data['submit']['LeadtimeToShip'] = $productMatching['leadtimeToShip'];
			}

		} else if (($productApply = MagnaDB::gi()->fetchRow('
			SELECT data, leadtimeToShip
			 FROM `'.TABLE_MAGNA_AMAZON_APPLY.'`
			 WHERE data<>""
			       AND '.(($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.$product['products_model'].'"'
			            : 'products_id="'.$pID.'"'
			       ).'
			       AND is_incomplete="false"
			       AND mpID="'.$this->_magnasession['mpID'].'"
			 LIMIT 1
		')) !== false) {
			$productApply['data'] = @unserialize(@base64_decode($productApply['data']));
			if (empty($productApply['data']) || !is_array($productApply['data'])) {
				$productApply['data'] = array();
			}
			$productApply['category'] = @unserialize(@base64_decode($productApply['category']));
			if (empty($productApply['category']) || !is_array($productApply['category'])) {
				$productApply['category'] = array();
			}
			
			$productApply['data'] = array_merge($productApply['category'], $productApply['data']);
			
			if (empty($productApply['data'])) {
				$this->markAsFailed(magnaPID2SKU($pID));
				return;
			}

			##########################################################################################
			#echo print_m($productApply['data']); die();
			if ($productApply['Attributes']['Color'] == 'Keine Auswahl') $productApply['Attributes']['Color'] = '';
			if ($productApply['Attributes']['BB_Material'] == 'Keine Auswahl') $productApply['Attributes']['BB_Material'] = '';
			###########################################################################################

			$data['submit'] = array_merge(
				array(
					'SKU' => magnaPID2SKU($pID),
					'Price' => $data['price'],
					'Quantity' => $data['quantity'],
					'ConditionType' => $conditionType,
				),
				$productApply['data']
			);
			if (!empty($data['submit']['BrowseNodes'])) {
				foreach ($data['submit']['BrowseNodes'] as $i => $bn) {
					if ($bn == 'null') {
						unset($data['submit']['BrowseNodes'][$i]);
					}
				}
			}
			$imagePath = getDBConfigValue('amazon.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
			$imagePath = trim($imagePath, '/ ').'/';
			$images = array();
			if (!empty($data['submit']['Images'])) {
				foreach ($data['submit']['Images'] as $image => $use) {
					if ($use == 'true') {
						$images[] = $imagePath.$image;
					}
				}
				$data['submit']['Images'] = $images;
			}

			if ($productApply['leadtimeToShip'] > 0) {
				$data['submit']['LeadtimeToShip'] = $productApply['leadtimeToShip'];
			}
			
			if ((float)$product['products_weight'] > 0) {
				$data['submit']['Weight'] = array (
					'Unit' => 'kg',
					'Value' => $product['products_weight'],
				);
			}
		} else {
			/**
			 * @todo check if ((masterArticle && haveVariants)||normalArticle)
			 */
			$this->markAsFailed(magnaPID2SKU($pID));
			return;
		}
		
		#VPE
		if ((isset($product['products_vpe_name'])) && ($product['products_vpe_value'] > 0)) {
			$data['submit']['BasePrice'] = array (
				'Unit'  => htmlspecialchars(trim($product['products_vpe_name'])),
				'Value' => $product['products_vpe_value'],
			);
		}

		if ($productApply === false) {
			return;
		}
		
		$tax = $this->simpleprice->getTaxByClassID($product['products_tax_class_id']);

		if(($tax - (int)$tax) > 0) {
			$decimalPlaces = 2;
		} else {
			$decimalPlaces = 0;
		}
		$taxStr = number_format($tax, $decimalPlaces, '.', '');
		$finalMWSTstr = str_replace('##', $taxStr, getDBConfigValue('amazon.mwstplaceholder', $this->_magnasession['mpID']));
		
		$data['submit']['ConditionNote'] = trim(substr(
				$data['submit']['ConditionNote'], 0, 
				(2000 - strlen($finalMWSTstr) - 1)
			).' '.$finalMWSTstr);

		if ($productApply !== false) {
			$variationTheme = array();
			// No variations support right now.
			if (false && defined('MAGNA_FIELD_ATTRIBUTES_EAN') 
				&& MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES)
			) {
				$variationTheme = MagnaDB::gi()->fetchArray('
				    SELECT po.products_options_name AS VariationTitle,
				           pov.products_options_values_name AS VariationValue,
				           pa.products_attributes_id AS aID,
				           pa.options_values_price AS aPrice,
				           pa.price_prefix AS aPricePrefix,
				           pa.attributes_stock AS Quantity,
				           '.MAGNA_FIELD_ATTRIBUTES_EAN.' AS EAN
				      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
				           '.TABLE_PRODUCTS_OPTIONS.' po, 
				           '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
				     WHERE pa.products_id = "'.$pID.'"
				           AND po.language_code = "'.$this->settings['languagecode'].'"
				           AND po.products_options_id = pa.options_id
				           AND po.products_options_name<>""
				           AND pov.language_code = l.languages_code
				           AND pov.products_options_values_id = pa.options_values_id
				           AND pov.products_options_values_name<>""
				           AND pa.attributes_stock IS NOT NULL
				');
				arrayEntitiesToUTF8($variationTheme);
				#print_r($variationTheme);
				$quantityType = getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.quantity.type',
					$this->_magnasession['mpID']
				);
				$quantityValue = getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.quantity.value',
					$this->_magnasession['mpID'],
					0
				);
			}
	
			if (!empty($variationTheme)) {
				foreach ($variationTheme as &$item) {
					$item['SKU'] = magnaAID2SKU($item['aID']);
					unset($item['aID']);
					switch ($quantityType) {
						case 'stock': {
							# Already set.
							break;
						}
						case 'stocksub': {
							$item['Quantity'] = (int)$item['Quantity'] - $quantityValue;
							break;
						}
						default: {
							$item['Quantity'] = $quantityValue;
						}
					}
					if ($item['Quantity'] < 0) {
						$item['Quantity'] = 0;
					}
					$this->simpleprice->setPrice($data['price'])->removeTax($tax);
	
					if ($item['aPricePrefix'] == '+') {
						$this->simpleprice->addLump($item['aPrice']);
					} else {
						$this->simpleprice->subLump($item['aPrice']);
					}
	
					$this->simpleprice->addTax($tax);
					if (getDBConfigValue(
							$this->_magnasession['currentPlatform'].'.price.addkind', 
							$this->_magnasession['mpID']
						) == 'percent'
					) {
						$this->simpleprice->addTax((float)getDBConfigValue(
							$this->_magnasession['currentPlatform'].'.price.factor',
							$this->_magnasession['mpID']
						));
					} else if (getDBConfigValue(
							$this->_magnasession['currentPlatform'].'.price.addkind',
							$this->_magnasession['mpID']
						) == 'addition'
					) {
						$this->simpleprice->addLump((float)getDBConfigValue(
							$this->_magnasession['currentPlatform'].'.price.factor',
							$this->_magnasession['mpID']
						));
					}
	
					$item['Price'] = $this->simpleprice->roundPrice()->makeSignalPrice(
							getDBConfigValue($this->_magnasession['currentPlatform'].'.price.signal', $this->_magnasession['mpID'], '')
					    )->getPrice();
					unset($item['aPrice']);
					unset($item['aPricePrefix']);
				}
			}
			$data['submit']['Variations'] = $variationTheme;
			#echo print_m($variationTheme);
		}
	}

	protected function processSubmitResult($result) { }

	protected function filterSelection() {
		#echo print_m($this->selection, __METHOD__.'{L:'.__LINE__.'}');
		/*
		foreach ($this->selection as $pID => &$data) {
			if ((int)$data['submit']['Quantity'] == 0) {
				unset($this->selection[$pID]);
				$this->disabledItems[] = $pID;
			}
		}
		*/
	}

	protected function postSubmit() {
		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			//*/
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();			
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode' => 'listings',
		), true);
	}
}
