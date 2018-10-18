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
 * $Id: ApplyCategoryView.php 1128 2011-07-06 13:20:48Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class ApplyCategoryView extends SimpleCategoryView {

	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_MagnaSession;

		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'quantity' => null
			)
		), $settings);

		$filter = array();
		
		if (($matchedItems = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT '.(
					(getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id'
				).'
				  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' 
				 WHERE `asin`<>\'\' 
				       AND `asin` IS NOT NULL 
				       AND mpID=\''.$_MagnaSession['mpID'].'\'
			', true)) !== false
		) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$filter[] = array(
					'join' => '',
					'where' => 'p.products_model NOT IN (\''.implode('\', \'', $matchedItems).'\')'
				);
			} else {
				$filter[] = array(
					'join' => '',
					'where' => 'p2c.products_id NOT IN (\''.implode('\', \'', $matchedItems).'\')'
				);
			}
		}
		// No support for variations right now.
		/*
		$hasEANVariations = MagnaDB::gi()->fetchRow('
		    SELECT * FROM '.TABLE_PRODUCTS_ATTRIBUTES.' LIMIT 1
		');*/
		$hasEANVariations = false;
		if (is_array($hasEANVariations)) {
			$aEANKeyType = array_key_exists('gm_ean', $hasEANVariations) ? 'gm_ean' : '';
			$aEANKeyType = array_key_exists('attributes_ean', $hasEANVariations) ? 'attributes_ean' : $aEANKeyType;
			if (!empty($aEANKeyType)) {
				$hasEANVariations = true;
			} else {
				$hasEANVariations = false;
			}
		}
		if ($hasEANVariations && (
			($attrWEAN = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT products_id FROM '.TABLE_PRODUCTS_ATTRIBUTES.' WHERE '.$aEANKeyType.'<>\'\'
			', true)) !== false
		)) {
			$filter[] = array(
				'join' => '',
				'where' => '(p.'.MAGNA_FIELD_PRODUCTS_EAN.'<>\'\' OR p2c.products_id IN (\''.implode('\', \'', $attrWEAN).'\'))'
			);
		}  else {
			$filter[] = array(
				'join' => '',
				'where' => 'p.'.MAGNA_FIELD_PRODUCTS_EAN.'<>\'\''
			);
		}
		$this->setCat2ProdCacheQueryFilter($filter);
		
		parent::__construct($cPath, $settings, $sorting, $search);
		
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td class="matched">'. ML_LABEL_DATA_PREPARED.'</td>';
	}
	
	public function getAdditionalCategoryInfo($cID, $data = false) {
		$itemsApplied = 0;
		$totalItems = 0;
		
		$pIDs = $this->list['categories'][$cID]['allproductsids'];
		if (!empty($pIDs)) {
			$totalItems = count($pIDs);
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$query = '
					SELECT COUNT(DISTINCT pa.products_id) as itemsCount, 
					       SUM(IF(is_incomplete=\'true\', 1, 0)) as incompleteCount
					  FROM '.TABLE_PRODUCTS.' p, '.TABLE_MAGNA_AMAZON_APPLY.' pa
 					 WHERE p.products_id IN (\''.implode('\', \'', $pIDs).'\')
					       AND p.products_model=pa.products_model
					       AND p.products_model<>\'\'
					       AND pa.mpID=\''.$this->_magnasession['mpID'].'\'
				';
			} else {
				$query = '
					SELECT COUNT(DISTINCT products_id) as itemsCount, 
					       SUM(IF(is_incomplete=\'true\', 1, 0)) as incompleteCount
					  FROM '.TABLE_MAGNA_AMAZON_APPLY.'
					 WHERE products_id IN (\''.implode('\', \'', $pIDs).'\')
					       AND mpID=\''.$this->_magnasession['mpID'].'\'
				';
			}
			$itemsApplied = MagnaDB::gi()->fetchRow($query);
			#echo print_m($itemsApplied, '$itemsApplied ('.$totalItems.')');
		}
		if ($itemsApplied !== false) {
			if ($itemsApplied['itemsCount'] == 0) {
				/* Keine Artikel beantragt */
				return '
					<td title="'.ML_AMAZON_LABEL_APPLY_NOT_PREPARED.'">'.
						html_image(DIR_MAGNALISTER_IMAGES . 'status/grey_dot.png', ML_AMAZON_LABEL_APPLY_NOT_PREPARED, 9, 9).
					'</td>';
			}
			if ($itemsApplied['incompleteCount'] == $totalItems) {
				/* Alle Artikel in Kategorie unvollstaendig beantragt */
				return '
					<td title="'.ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE.'">'.
						html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE, 9, 9).
					'</td>';
			}
			if (($itemsApplied['itemsCount'] == $totalItems) && ($itemsApplied['incompleteCount'] == 0)) {
				/* Alle Artikel in Kategorie beantragt */
				return '
					<td title="'.ML_AMAZON_LABEL_APPLY_PREPARE_COMPLETE.'">'.
						html_image(DIR_MAGNALISTER_IMAGES . 'status/green_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_COMPLETE, 9, 9).
					'</td>';
			}
			if ($itemsApplied['itemsCount'] > 0) {
				/* Einige nicht beantragt */
				return '
					<td title="'.ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE.'">'.
						html_image(DIR_MAGNALISTER_IMAGES . 'status/yellow_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE, 9, 9).
					'</td>';
			}
		}
		return '
			<td title="'.ML_ERROR_UNKNOWN.' $itemsApplied:'.print_m($itemsApplied, true).' $totalItems:'.$totalItems.'">'.
				html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
				html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
			'</td>';
	}

	public function getAdditionalProductInfo($pID, $product = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT products_id, is_incomplete
			  FROM '.TABLE_MAGNA_AMAZON_APPLY.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model=\''.$product['products_model'].'\''
						: 'products_id=\''.$pID.'\''
					).'
				   AND mpID=\''.$this->_magnasession['mpID'].'\'
		');
		if ($a !== false) {
			if ($a['is_incomplete'] == 'true') {
				return '
					<td>'.html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE, 9, 9).'</td>';				
			} else {
				return '
					<td>'.html_image(DIR_MAGNALISTER_IMAGES . 'status/green_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_COMPLETE, 9, 9).'</td>';				
			}
		}
		return '
			<td>'.html_image(DIR_MAGNALISTER_IMAGES . 'status/grey_dot.png', ML_AMAZON_LABEL_APPLY_NOT_PREPARED, 9, 9).'</td>';
	}
	
	public function getFunctionButtons() {
		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td class="texcenter inputCell">
						<table class="right"><tbody>
							<tr><td>
								<input type="submit" class="fullWidth ml-button smallmargin" value="'.ML_AMAZON_BUTTON_PREPARE.'" id="apply" name="apply"/><br>
							</td></tr>
						</tbody></table>
					</td>
				</tr>
			</tbody></table>
			<div id="finalInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>
		';
	}

	public function getLeftButtons() {
		return '
			<input type="submit" class="ml-button" value="'.ML_EBAY_BUTTON_UNPREPARE.'" id="removeapply" name="removeapply"/><br>
			<input type="submit" class="ml-button" value="'.ML_EBAY_BUTTON_RESET_DESCRIPTION.'" id="resetapply" name="resetapply"/>';
	}
	
	protected function getEmptyInfoText() {
		return ML_AMAZON_LABEL_APPLY_EMPTY;
	}

}
