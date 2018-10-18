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
 * $Id: EbaySyncInventory.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');


class EbaySyncInventory extends MagnaCompatibleSyncInventory {
	
	protected $syncFixedStock = false;
	protected $syncChineseStock = false;
	protected $syncFixedPrice = false;
	protected $syncChinesePrice = false;
	
	# Bei Varianten kommt dieselbe ItemID mehrmals zurueck,
	# sollte aber nur einmal upgedatet werden
	protected $itemsProcessed = array();
	#protected $variationsForItemCalculated = array();
	protected $totalQuantityForItemCalculated = array();
	
	protected $uqTime = 0.0;
	
	public function __construct($mpID, $marketplace, $limit = 100) {
		global $_MagnaSession;

		# Ensure that $_MagnaSession contains needed data
		if (!isset($_MagnaSession) || !is_array($_MagnaSession)) {
			$_MagnaSession = array (
				'mpID' => $mpID,
				'currentPlatform' => $marketplace
			);
		} else {
			$_MagnaSession['mpID'] = $mpID;
			$_MagnaSession['currentPlatform'] = $marketplace;
		}

		parent::__construct($mpID, $marketplace, $limit);
		
		$this->timeouts['UpdateItems'] = 1;
		$this->timeouts['GetInventory'] = 1800;
		
		$this->startedAtTimestamp = time();
	}

	protected function initMLProduct() {
		parent::initMLProduct();
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
		));
	}

	protected function getConfigKeys() {
		return array (
			'FixedStockSync' => array (
				'key' => 'stocksync.tomarketplace',
				'default' => '',
			),
			'ChineseStockSync' => array (
				'key' => 'chinese.stocksync.tomarketplace',
				'default' => '',
			),
			'FixedPriceSync' => array (
				'key' => 'inventorysync.price',
				'default' => '',
			),
			'ChinesePriceSync' => array (
				'key' => 'chinese.inventorysync.price',
				'default' => '',
			),
			'FixedQuantityType' => array (
				'key' => 'fixed.quantity.type',
				'default' => '',
			),
			'FixedQuantityValue' => array (
				'key' => 'fixed.quantity.value',
				'default' => 0,
			),
			'Lang' => array (
				'key' => 'lang',
				'default' => false,
			),
			'StatusMode' => array (
				'key' => 'general.inventar.productstatus',
				'default' => 'false',
			),
			'SKUType' => array (
				'key' => 'general.keytype',
			),
		);
	}
	
	protected function initQuantitySub() {
		$this->config['FixedQuantitySub'] = 0;
		if ($this->syncStock) {
			if ($this->config['FixedQuantityType'] == 'stocksub') {
				$this->config['FixedQuantitySub'] = $this->config['FixedQuantityValue'];
			}
		}
		$this->config['ChineseQuantitySub'] = 0;
		$this->config['ChineseQuantityType'] = 'lump';
		$this->config['ChineseQuantityValue'] = 1;
	}
	
	protected function uploadItems() {}
	
	protected function extendGetInventoryRequest(&$request) {
		$request['ORDERBY'] = 'DateAdded';
		$request['SORTORDER'] = 'DESC';
	}
	
	protected function postProcessRequest(&$request) {
		$newUqTime = microtime(true);
		$border = 2.0;
		$throttleTime = 5;
		if (($newUqTime - $this->uqTime) < $border) {
			if ($this->_debugLevel >= self::DBGLV_HIGH) {
				$this->log("\n".
					"\n /|\\   Throttle UpdateQuantity requests, because 2 requests were created within ".$border." seconds.".
					"\n/_*_\\  New receive timeout is ".$throttleTime." seconds ".
					          "(old was ".$this->timeouts['UpdateItems']." seconds).\n"
				);
			}
			$this->timeouts['UpdateItems'] = $throttleTime;
		}
		$this->uqTime = $newUqTime;
		$request['ACTION'] = 'UpdateQuantity';
	}
	
	protected function isAutoSyncEnabled() {
		$this->syncFixedStock   = $this->config['FixedStockSync']   == 'auto';
		$this->syncChineseStock = $this->config['ChineseStockSync'] == 'auto';
		$this->syncFixedPrice   = $this->config['FixedPriceSync']   == 'auto';
		$this->syncChinesePrice = $this->config['ChinesePriceSync'] == 'auto';

		/*
		if ($this->_debugDryRun) {
			$this->syncFixedStock = $this->syncChineseStock = $this->syncFixedPrice = $this->syncChinesePrice = true;
		}
		//*/

		if (!($this->syncFixedStock || $this->syncChineseStock || $this->syncFixedPrice || $this->syncChinesePrice)) {
			$this->log('== '.$this->marketplace.' ('.$this->mpID.'): no autosync =='."\n");
			return false;
		}
		$this->log(
			'== '.$this->marketplace.' ('.$this->mpID.'): '.
			'Sync fixed stock: '.($this->syncFixedStock ? 'true' : 'false').'; '.
			'Sync chinese stock: '.($this->syncChineseStock ? 'true' : 'false').'; '.
			'Sync fixed price: '.($this->syncFixedPrice ? 'true' : 'false').'; '.
			'Sync chinese price: '.($this->syncChinesePrice ? 'true' : 'false')." ==\n"
		);
		return true;
	}

	protected function identifySKU() {
		if (!empty($this->cItem['MasterSKU'])) {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['MasterSKU'], true);
		} else {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['SKU']);
		}
	}
	
	/*protected function updateInternalVariations() {
		# Varianten neu berechnen bevor man Anzahl berechnet
		if (MagnaDB::gi()->tableExists(TABLE_MAGNA_VARIATIONS)
			 && !in_array($this->cItem['ItemID'], $this->variationsForItemCalculated)
		) {
			setProductVariations($this->cItem['pID'], $this->config['Lang']);
			$this->variationsForItemCalculated[] = $this->cItem['ItemID'];
		}
	}*/

	protected function calcMainQuantity() {
		# Aktuelle Anzahl berechnen
		if (!isset($this->totalQuantityForItemCalculated[$this->cItem['ItemID']])) {
			$this->totalQuantityForItemCalculated[$this->cItem['ItemID']] = makeQuantity($this->cItem['pID'], $this->cItem['ListingType']);
		}
		return $this->totalQuantityForItemCalculated[$this->cItem['ItemID']];
	}

	protected function calcPrice($isVariation) {
		# Schauen ob Preis-Synchro eingeschaltet
		if (
			!(
				($this->syncFixedPrice   && ('Chinese' != $this->cItem['ListingType']))
			 || ($this->syncChinesePrice && ('Chinese' == $this->cItem['ListingType']))
			)
		) {
			return false;
		}

		if ($this->cItem['ListingType'] != 'Chinese') {
			$priceFrozen = 0.0;
		} else {
			# schauen ob Preis eingefroren
			$priceFrozen = (float)MagnaDB::gi()->fetchOne("
				SELECT Price
				  FROM ".TABLE_MAGNA_EBAY_PROPERTIES."
				 WHERE      mpID = '".$this->mpID."'
				        AND ".(('artNr' == $this->config['SKUType'])
								? "products_model = '".magnaPID2SKU($this->cItem['pID'], true)."'"
								: "products_id = '".$this->cItem['pID']."'"
							)
			);
		}
		
		if ($priceFrozen) {
			return false;
		}

		return makePrice($this->cItem['pID'], $this->cItem['ListingType'], $priceFrozen);
	}
	
	protected function updateItem() {
		if (in_array($this->cItem['ItemID'], $this->itemsProcessed)) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' already processed.');
			return;
		}
		$this->cItem['SKU'] = trim($this->cItem['SKU']);
		if (empty($this->cItem['SKU'])) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' has an emtpy SKU.');
			return;
		}

		@set_time_limit(180);
		$this->identifySKU();

		$articleIdent = 'SKU: '.$this->cItem['SKU'].' ('.$this->cItem['ItemTitle'].'); eBay-ItemID: '.$this->cItem['ItemID'].'; ListingType: '.$this->cItem['ListingType'].' ';
		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n".$articleIdent.' not found');
			return;
		} else {
			$this->log("\n".$articleIdent.' found (pID: '.$this->cItem['pID'].')');
		}
		//prepare product
		MLProduct::gi()->setLanguage($this->config['Lang']);
		MLProduct::gi()->setPriceConfig(
			EbayHelper::getPriceSettingsByListingType($this->mpID, $this->cItem['ListingType'])
		);
		MLProduct::gi()->setQuantityConfig(
			EbayHelper::getQuantitySettingsByListingType($this->mpID, $this->cItem['ListingType'])
		);
		$product =  MLProduct::gi()->getProductById($this->cItem['pID']);
		$process = false;
		$data = array(
			'fixed.stocksync' => $this->config['FixedStockSync'],
			'fixed.pricesync' => $this->config['FixedPriceSync'],
			'chinese.stocksync' => $this->config['ChineseStockSync'],
			'chinese.pricesync' => $this->config['ChinesePriceSync'],
		);
		$listingMasterType = ($this->cItem['ListingType'] == 'Chinese') ? 'chinese' : 'fixed';
		$syncStock = $this->config[ucfirst($listingMasterType).'StockSync'] != 'no';
		$syncPrice = $this->config[ucfirst($listingMasterType).'PriceSync'] != 'no';
		$data['SKU'] = magnaPID2SKU($product['ProductId']);
		$data['ItemID'] = $this->cItem['ItemID'];

		// Check Quantity variants or master
		if ($syncStock) {
			// QuantityTotal is only set if product has variants
			if ((isset($this->cItem['Variations']) && isset($product['Variations'])) && isset($product['QuantityTotal'])) {
				$data['NewQuantity'] = $product['QuantityTotal'];
			} else {
				$data['NewQuantity'] = $product['Quantity'];
			}
			$process = ($process || ($this->cItem['Quantity'] != $data['NewQuantity']));
		}

		// Check Price variants or master
		if ($syncPrice && !isset($this->cItem['Variations'])) {
			$data['Price'] = $product['Price'][$listingMasterType];

			// if PriceReduced is set use this one
			if (isset($product['PriceReduced'][$listingMasterType])) {
				$data['Price'] = $product['PriceReduced'][$listingMasterType];
			}

			// if listing type is chinese check for prepared price
			if ($listingMasterType == 'chinese') {
				$fPrice = magnalisterEbayGetPriceByType($product['ProductId']);
				if ($fPrice != 0.00) {
					$data['Price'] = $fPrice;
				}
			}
			$process = $process || $this->cItem['Price'] != $data['Price'];
		}

		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$data['Variations'] = array();
			foreach ($product['Variations'] as $variantData) {
				$variant = array();
				$variationSpecifics = array();
				foreach ($variantData['Variation'] as $specific) {
					$variationSpecifics[] = array(
						'Name' => $specific['Name'],
						'Value' => $specific['Value'],
					);
				}
				$variant['SKU'] = ($this->config['SKUType'] == 'artNr') ? $variantData['MarketplaceSku'] : $variantData['MarketplaceId'];
				$currentCVariation = array();
				foreach ($this->cItem['Variations'] as $cVariation){
					if ($cVariation['SKU'] == $variant['SKU']) {
						$currentCVariation = $cVariation;
						break;
					}
				}

				$process = ($process || (count($currentCVariation) == 0));
				if ($syncStock) {
					$variant['Quantity'] = $variantData['Quantity'];
					$process = ($process || (count($currentCVariation) > 0 && $currentCVariation['Quantity'] != $variant['Quantity']));
				}
				if ($syncPrice) {
					$variant['StartPrice'] = $variantData['Price'][$listingMasterType];
					// if PriceReduced is set use this one
					if (isset($variantData['PriceReduced'][$listingMasterType])) {
						$variant['StartPrice'] = $variantData['PriceReduced'][$listingMasterType];
					}

					// if listing type is chinese check for prepared price
					if ($listingMasterType == 'chinese') {
						$fPrice = magnalisterEbayGetPriceByType($variantData['VariationId']);
						if ($fPrice != 0.00) {
							$variant['StartPrice'] = $fPrice;
						}
					}
					$process = ($process || (count($currentCVariation) > 0 && $currentCVariation['Price'] != $variant['StartPrice']));
				}
				$variant['Variation'] = $variationSpecifics;
				$data['Variations'][] = $variant;
			}
			
		}
		
		#$this->updateInternalVariations();

		$this->log(
			"\n\teBay Quantity: ".$this->cItem['Quantity'].
			"\n\tShop Main Quantity: ". $data['NewQuantity'].
			#"\n\tShop Variation Quantity: ".(($currVarQty === false) ? 'false' : $currVarQty).
			"\n\teBay Price: ".$this->cItem['Price'].
			"\n\tShop Price: ".((isset($product['PriceReduced'][$listingMasterType])) ? $product['PriceReduced'][$listingMasterType] : $product['Price'][$listingMasterType])
		);

		if ($this->config['StatusMode'] == 'true') {
			$iStatus = MagnaDB::gi()->fetchOne('
				SELECT products_status FROM ' . TABLE_PRODUCTS . '
				WHERE products_id = "' . $this->cItem['pID'] . '"
			');
			if ($iStatus == 0) {//notavailible => noStock
				if (    (0  != $this->cItem['Quantity'])
				     || (isset($this->cItem['Variations']))
				   ) {
					$process = true;
					$this->log(
					"\n\tDeleting Item due to inactive Status"
					);
				}
				$data['NewQuantity'] = 0;
				unset($data['Variations']);
			}
		}

		// log Variations
		if(isset($this->cItem['Variations']) && isset($product['Variations'])){
			$this->log(
				"\n\tVariations:"
			);
			foreach ($this->cItem['Variations'] as $aEBayVariation) {
				foreach ($product['Variations'] as $aShopVariantData) {
					if ($aEBayVariation['SKU'] == $aShopVariantData[(($this->config['SKUType'] == 'artNr') ? 'MarketplaceSku' : 'MarketplaceId')]) {
						$this->log(
							"\n\t\tVariation SKU: ".$aEBayVariation['SKU'].
							"\n\t\teBay Quantity: ".$aEBayVariation['Quantity'].
							"\n\t\tShop Main Quantity: ". $aShopVariantData['Quantity'].
							"\n\t\teBay Price: ".$aEBayVariation['Price'].
							"\n\t\tShop Price: ".((isset($aShopVariantData['PriceReduced'][$listingMasterType])) ? $aShopVariantData['PriceReduced'][$listingMasterType] : $aShopVariantData['Price'][$listingMasterType]).
							"\n"
						);
						break;
					}
				}
			}
		}

		/* {Hook} "EBay_SyncInventory_UpdateItem": Runs during the inventory synchronization from your shop to eBay, directly before the 
			   update will be send to eBay.<br>
			   Variables that can be used: 
			   <ul><li>$this->mpID: The ID of the marketplace.</li>
				   <li>$data (array): The content of the changes of one product (used to generate the <code>UpdateItems</code> request).<br>
					   Supported are <span class="tt">Price</span> and <span class="tt">NewQuantity</span>
				   </li>
				   <li>$this->cItem (array): The current product from the marketplaces inventory including some identification information.
					   <ul><li>SKU: Article number of marketplace</li>
						   <li>pID: products_id of product</li></ul>
				   </li>
				   <li>$currMainQty: Quantity of main product (same as in $data['NewQuantity']). Has to be modified in order to trigger an update.</li>
				   <li>$currPrice: Price of main product (same as in $data['Price'], if set). Has to be modified in order to trigger an update.</li>
			   </ul>
			   <p>Notice: It is only possible to modify products that have been identified by the magnalister plugin!<br>
				  Additionally the eBay inventory synchronisation is very complex. Be carefull, because in case of mistakes all your
				  active autions may be terminated.</p>
		*/
		if (($hp = magnaContribVerify('EBay_SyncInventory_UpdateItem', 1)) !== false) {
			/* Calculate the variations customers who use the EBay_SyncInventory_UpdateItem hook. Has to be done before the hook
			   is executed.
			   Usually the martix only has to be calcullated when there is a definitive update,
			   but the data has to be available for the hook as well. Slows things down.
			*/
			#$data['Variations'] = $this->calcVariationMatrix($blVariations, $currPrice);
			require($hp);
		}

		if ($process) {
			$this->updateItems($data);
			
			$this->itemsProcessed[] = $this->cItem['ItemID'];
		}
	}

	private function getDebugData($products_id) {
		$products_query = "SELECT products_id, products_model,
					products_quantity, products_price, products_status
					FROM ".TABLE_PRODUCTS . " WHERE products_id = ".$products_id;
		$product = MagnaDB::gi()->fetchRow($products_query);

		#$attributes_query = 'SELECT products_attributes_id, options_id, options_values_id';
		#if(MagnaDB::gi()->columnExistsInTable('attributes_model', TABLE_PRODUCTS_ATTRIBUTES))
		#	$attributes_query .= ', attributes_model';
		#if(MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES))
		#	$attributes_query .= ', attributes_stock';
		/*$attributes_query .=', options_values_price, price_prefix
					FROM '.TABLE_PRODUCTS_ATTRIBUTES.' WHERE products_id = '.$products_id;
		$attributes = MagnaDB::gi()->fetchArray($attributes_query);

		$magna_variations_query = 'SELECT variation_products_model, variation_attributes, variation_attributes_text,
					variation_quantity, variation_price, variation_status
					FROM '.TABLE_MAGNA_VARIATIONS.' WHERE products_id = '.$products_id;
		$variations = MagnaDB::gi()->fetchArray($magna_variations_query);*/
		$debugData = array(
			'product' => $product,
			#'attributes' => $attributes,
			#'magna_variations' => $variations
		);
		return $debugData;
	}

	protected function submitStockBatch() {
		// Do nothing, as items are already updated one by one in updateItem().
	}
}
