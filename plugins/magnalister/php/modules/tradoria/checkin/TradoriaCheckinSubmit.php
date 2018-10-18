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
 * $Id$
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');

class TradoriaCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $regions = array();

	public function __construct($settings = array()) {
		$settings = array_merge(array(
			'itemsPerBatch'   => 1,
		), $settings);
		parent::__construct($settings);
	}
	
	protected function getVariations($pID, $product, &$data) {
		return false; // no variations for veyton currenty!
		
		
		/* This is limited to one VariationTheme. 
		   Start with guessing the "right" one, aka using the one that has the most variations. */
		$pVID = MagnaDB::gi()->fetchRow('
			SELECT pa.options_id, COUNT(pa.options_id) AS rate
			  FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa
			 WHERE pa.products_id = \''.$pID.'\'
		  GROUP BY pa.options_id
		  ORDER BY rate DESC
		');

		if ($pVID === false) return false;
		$variationTheme = MagnaDB::gi()->fetchArray('
		    SELECT po.products_options_name AS VariationTitle,
		           pov.products_options_values_name AS VariationValue,
		           pa.products_attributes_id AS aID,
		           pa.options_values_price AS vPrice,
		           pa.price_prefix AS vPricePrefix,
		           pa.attributes_stock AS Quantity,
		           '.(defined('MAGNA_FIELD_ATTRIBUTES_EAN')
		              ? MAGNA_FIELD_ATTRIBUTES_EAN
		           	  : '\'\''
		           ).' AS EAN
		      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
		           '.TABLE_PRODUCTS_OPTIONS.' po, 
		           '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov, 
		           '.TABLE_LANGUAGES.' l
		     WHERE pa.products_id = \''.$pID.'\'
		           AND pa.options_id='.$pVID['options_id'].'
		           AND po.language_id = l.languages_id
		           AND po.products_options_id = pa.options_id
		           AND po.products_options_name<>\'\'
		           AND pov.language_id = l.languages_id
		           AND pov.products_options_values_id = pa.options_values_id
		           AND pov.products_options_values_name<>\'\'
		           AND pa.attributes_stock IS NOT NULL
		           AND l.directory = \''.$this->settings['languagecode'].'\'
		');
		if ($variationTheme == false) return false;
		
		$tax = $this->simpleprice->getTaxByClassID($product['products_tax_class_id']);
		
		arrayEntitiesToUTF8($variationTheme);
		
		$variations = array();
		foreach ($variationTheme as $v) {
			$vi = array (
				'SKU' => magnaAID2SKU($v['aID']),
				'Price' => $this->calcVariationPrice(
					$data['price'],
					$v['vPrice'] * (($v['vPricePrefix'] == '+') ? 1 : -1), 
					$tax
				),
				'Currency' => $this->settings['currency'],
				'ItemTax' => $data['submit']['ItemTax'],
				'Quantity' => ($this->quantityLumb === false)
					? max(0, $v['Quantity'] - (int)$this->quantitySub)
					: $this->quantityLumb,
				'EAN' => $v['EAN'],
				'Variation' => array (
					'Group' => $v['VariationTitle'],
					'Value' => $v['VariationValue']
				),
			);/*
			if (!empty($v['variation_unit_of_measure']) && !empty($v['variation_volume'])) {
				$vi['VPE'] = array (
					'Unit' => $v['variation_unit_of_measure'],
					'Value' => $v['variation_volume'],
				);
			}//*/
			$variations[] = $vi;
		}
		$data['submit']['Variations'] = $variations;
		return true;
	}
	
	protected function getItemTax($pID, $product, &$data) {
		$taxMatch = getDBConfigValue($this->marketplace.'.checkin.taxmatching', $this->mpID, array());
		if (is_array($taxMatch) && array_key_exists($product['products_tax_class_id'], $taxMatch)) {
			return $taxMatch[$product['products_tax_class_id']];
		}
		/* Fallback. This represents 19%. Should be make configureable in a datastructure. */
		return '1';
	}
	
	protected function prepareOwnShopCategories($pID, $product, &$data) {
		$cPath = $this->generateShopCategoryPath($pID, 'product');
		if (empty($cPath)) {
			return;
		}
		$catIDs = array();
		$finalpaths = array();
		// merge all paths so that each category is only included once.
		foreach ($cPath as $subpath) {
			$subpath = array_values($subpath);
			// only the deepest element of the path is the category id of this product. not the entire path!
			// make it independent of sort-order.
			if (isset($subpath[0]['ParentID'])) {
				$catIDs[] = $subpath[0]['ID'];
			} else if (isset($subpath[0]['ID'])) {
				$catIDs[] = $subpath[count($subpath) - 1]['ID'];
			}
			foreach ($subpath as $c) {
				$finalpaths[$c['ID']] = $c;
			}
		}
		$finalpaths = array_values($finalpaths);
		
		$data['submit']['ShopCategory'] = $catIDs;
		$data['submit']['ShopCategoryStructure'] = $finalpaths;
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		parent::appendAdditionalData($pID, $product, $data);
		
		$data['submit']['ShippingGroup'] = getDBConfigValue($this->marketplace.'.checkin.shippinggroup', $this->mpID, '1');
	}
	
	protected function filterSelection() {
		$b = parent::filterSelection();
		return $b;
	}
	
	protected function processException($e) {
		parent::processException($e);
		
		// in case of an marketplace timeout
		if ($e->getFirstAPIErrorCode() == 'MARKETPLACE_TIMEOUT') {
			// ignore the exception
			$e->setCriticalStatus(false);
			
			// ignore the timeout...
			$this->ajaxReply['ignoreErrors'] = true;
			
			// and try again (with the same product)
			$this->ajaxReply['reprocessSelection'] = true;
			
			// and fix the counters
			$this->submitSession['state']['failed'] -= count($this->selection);
			$this->submitSession['state']['submitted'] -= count($this->selection);
			
		}
	}
	
}
