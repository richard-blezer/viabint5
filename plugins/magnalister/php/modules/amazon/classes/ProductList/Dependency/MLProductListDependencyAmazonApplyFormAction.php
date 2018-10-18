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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependency.php';
class MLProductListDependencyAmazonApplyFormAction extends MLProductListDependency {
	public function getActionBottomLeftTemplate(){
		return 'amazonapplyformleft';
	}
	
	public function getActionBottomRightTemplate(){
		return 'amazonapplyformright';
	}
	
	public function getDefaultConfig() {
		return array(
			'selectionname' => 'general'
		);
	}
	
	public function executeAction() {
		$aRequest = $this->getActionRequest();
		if (isset($aRequest['removeapply'])) {
			$this->removeApply();
		} elseif (isset($aRequest['resetapply'])) {
			$this->resetApply();
		}
		return $this;
	}
	
	protected function removeApply() {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID 
			 FROM '.TABLE_MAGNA_SELECTION.'
			WHERE mpID = \''.$this->getMagnaSession('mpID').'\'
			      AND selectionname = \''.$this->getConfig('selectionname').'\'
			      AND session_id = \''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue('amazon.lang', $this->getMagnaSession('mpID'), $_SESSION['magna']['selected_language']))->getProductById($pID);
				$sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr' ? 'MarketplaceSku' : 'VariationId');
				$aProducts = array((($sKeyType == 'MarketplaceSku') ? $aProduct['ProductsModel'] : $aProduct['ProductId']));
				foreach ($aProduct['Variations'] as $aVariant) {
					$aProducts[] = $aVariant[$sKeyType];
				}
				$sAdd = " AND products_".(($sKeyType == 'MarketplaceSku')? 'model' : 'id')." in ('".implode("', '",$aProducts)."')";
				$where = array ('mpID' => $this->getMagnaSession('mpID'));
				MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_APPLY, $where, $sAdd);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $this->getMagnaSession('mpID'),
					'selectionname' => $this->getConfig('selectionname'),
					'session_id' => session_id()
				));
			}
		}
	}
	
	protected function resetApply() {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID 
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = \''.$this->getMagnaSession('mpID').'\'
			       AND selectionname = \''.$this->getConfig('selectionname').'\'
			       AND session_id = \''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$aProducts = MagnaDB::gi()->fetchArray('
					SELECT aa.products_id AS PID,
					       aa.products_model AS PModel,
					       aa.*
					  FROM '.TABLE_MAGNA_AMAZON_APPLY.' aa
					 WHERE aa.products_id IN (\''.implode('\', \'', $pIDs).'\')
			'	);
			} else {
				$aProducts = MagnaDB::gi()->fetchArray('
					SELECT p.products_id AS PID,
					       p.products_model AS PModel,
					       aa.*
					  FROM '.TABLE_MAGNA_AMAZON_APPLY.' aa
				INNER JOIN '.TABLE_PRODUCTS.' p ON p.products_model = aa.products_model
					 WHERE p.products_id IN (\''.implode('\', \'', $pIDs).'\')
				');
			}
			foreach ($aProducts as $aRow) {
				$aRow['category'] = unserialize(base64_decode($aRow['category']));
				$aRow['data'] = unserialize(base64_decode($aRow['data']));
				if (!is_array($aRow['data']) || empty($aRow['data'])) {
					continue;
				}
				#echo print_m($aRow);
			
				$aNewRow = populateGenericData($aRow['PID']);
				#echo print_m($aNewRow);
				$aNewRow['MainCategory'] = $aRow['data']['MainCategory'];
				$aNewRow['ProductType'] = $aRow['data']['ProductType'];
				$aNewRow['BrowseNodes'] = $aRow['data']['BrowseNodes'];
				$aNewRow['Attributes'] = $aRow['data']['Attributes'];
				if ($aRow['leadtimeToShipFrozen'] > 0) {
					$aNewRow['LeadtimeToShip'] = $aRow['leadtimeToShip'];
				}
			
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => $aRow['PModel'])
					: array ('products_id'    => $aRow['PID']);
				$where['mpID'] = $this->getMagnaSession('mpID');
				
				MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY, array (
					'products_id' => $aRow['PID'],
					'products_model' => $aRow['PModel'],
					'data' => base64_encode(serialize($aNewRow)),
					'leadtimeToShip' => $aNewRow['LeadtimeToShip']
				), $where);
			
				
				$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue('amazon.lang', $this->getMagnaSession('mpID'), $_SESSION['magna']['selected_language']))->getProductById($aRow['PID']);
				$sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr' ? 'MarketplaceSku' : 'VariationId');
				foreach ($aProduct['Variations'] as $aVariant) {
					$where ['products_'.(($sKeyType=='MarketplaceSku') ? 'model' : 'id')] = $aVariant[$sKeyType];
					MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY, array (
						'products_id' => $aVariant['VariationId'],
						'products_model' => $aVariant['MarketplaceSku'],
						'data' => base64_encode(serialize($aNewRow)),
						'leadtimeToShip' => $aNewRow['LeadtimeToShip']
					), $where);
				}
				
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $aRow['PID'],
					'mpID' => $this->getMagnaSession('mpID'),
					'selectionname' => $this->getConfig('selectionname'),
					'session_id' => session_id()
				));
			}
		}
	}
	
}
