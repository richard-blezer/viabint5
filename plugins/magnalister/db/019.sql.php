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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/**
 * update amazon-data for multivariant structure
 * - delete not successfull prepared entries
 * - updates TABLE_MAGNA_AMAZON_APPLY (only master will be prepared, all variants as dummy if master is not prepared itself, use data from first prepared variant)
 * - updates TABLE_MAGNA_AMAZON_PROPERTIES 
 * -- if article is prepared in TABLE_MAGNA_AMAZON_APPLY (delete them from properies)
 * -- if article is not prepared in TABLE_MAGNA_AMAZON_APPLY (only variants are prepared, if any variant is prepared, create master as dummy)
 */
class magna_updateAmazonForMultivariants {
	
	/**
	 * keytype is in all depending tables the same (TABLE_PRODUCTS, TABLE_MAGNA_AMAZON_PROPERTIES, TABLE_MAGNA_AMAZON_APPLY)
	 * @var string $sKeyType (products_model or products_id)
	 */
	protected $sKeyType = null;
	
	protected $iMasterCount = 0;
	
	/**
	 * update TABLE_MAGNA_AMAZON_PROPERTIES, TABLE_MAGNA_AMAZON_APPLY
	 */
	public function __construct($iStart = null, $iLimit = null) {
		$sKeyType = MagnaDB::gi()->fetchOne('SELECT value FROM magnalister_config WHERE mkey="general.keytype"');
		if (!empty($sKeyType) && MagnaDB::gi()->columnExistsInTable('products_master_model', TABLE_PRODUCTS)) {// not complete configured
			$this->sKeyType = 'products_'.(($sKeyType == 'artNr') ? 'model' : 'id');
			$this->deletePreparedWithError(TABLE_MAGNA_AMAZON_PROPERTIES);
			$this->deletePreparedWithError(TABLE_MAGNA_AMAZON_APPLY);
			$aArticles = $this->getArticles($iStart, $iLimit);
			$this->iMasterCount = count($aArticles);
			$aProcessedMasters = array();
			foreach (array(TABLE_MAGNA_AMAZON_PROPERTIES, TABLE_MAGNA_AMAZON_APPLY) as $sTable) {
				foreach ($aArticles as $sMasterModel => $aArticle) {
					$aArticle = $this->addMartketPlaces($sTable, $aArticle);
					foreach ($aArticle['master']['marketplaces'] as $iMp) {
						if (isset ($aProcessedMasters[$sMasterModel][$iMp]) ) {
							$this->cleanTable($sTable, $aArticle, $iMp);
						} else {
							$aProcessedMasters[$sMasterModel][$iMp] = $iMp;
							$this->fillTable($sTable, $aArticle, $iMp);
						}
					}
				}
			}
		}
	}
	
	public function getMasterCount () {
		return $this->iMasterCount;
	}
	
	/**
	 * returns a list of all products which are in use for amazon
	 * structure:
	 * array(
	 *		$sKeyTypeValue => array(
	 *			'master' => array(
	 *				'products_id' => (int), 
	 *				'products_model' => (string),
	 *				'marketplaces' => array((int), ...)
	 *			)
	 *			'variants' => array(
	 *				$sKeyTypeValue => array(
	 *					'products_id' => (int), 
	 *					'products_model' => (string),
	 *					'marketplaces' => array((int), ...)
	 *				)
	 *			)
	 *		),
	 *		...
	 * ) 
	 * @return array 
	 */
	protected function getArticles ($iStart = null, $iLimit = null) {
		$aMasters = array();
		$sLimitSql = ($iStart === null && $iLimit === null) ? '' : ' LIMIT '.((int)$iStart).', '.((int)$iLimit);
		foreach (MagnaDB::gi()->fetchArray("
			(
				SELECT DISTINCT ".$this->sKeyType."
				FROM ".TABLE_MAGNA_AMAZON_PROPERTIES."
				".$sLimitSql."
			)
			UNION DISTINCT
			(
				SELECT DISTINCT ".$this->sKeyType."
				FROM ".TABLE_MAGNA_AMAZON_APPLY."
				".$sLimitSql."
			)
		", true) as $sKeyValue){
			$sMasterModel = MagnaDB::gi()->fetchOne("
				SELECT products_master_model 
				FROM ".TABLE_PRODUCTS." 
				WHERE ".$this->sKeyType."='".$sKeyValue."';"
			);
			if (empty($sMasterModel)) {
				$sMasterKeyValue = $sKeyValue;
				$sMasterModel = (($this->sKeyType == 'products_model') ? $sKeyValue : MagnaDB::gi()->fetchOne("SELECT products_model from ".TABLE_PRODUCTS." where products_id = '".$sKeyValue."'"));
			} else {
				if ($this->sKeyType == 'products_model') {
					$sMasterKeyValue = $sMasterModel;
				} else {
					$sMasterKeyValue = MagnaDb::gi()->fetchOne("SELECT products_id FROM ".TABLE_PRODUCTS." WHERE products_model = '".$sMasterModel."'");
				}
			}
			if (!isset($aMasters[$sMasterKeyValue])) {
				$aVariants = array();
				if (!empty($sMasterModel)) {
					foreach (MagnaDb::gi()->fetchArray("SELECT DISTINCT products_id, products_model FROM ".TABLE_PRODUCTS." where products_master_model = '".$sMasterModel."'") as $aVariant) {
						$aVariants[$aVariant[$this->sKeyType]] = $aVariant;
					}
				}
				$aMasters[$sMasterKeyValue] = array(
					'master' => MagnaDb::gi()->fetchRow("SELECT products_id, products_model FROM ".TABLE_PRODUCTS." where ".$this->sKeyType."='".$sMasterKeyValue."'"),
					'variants' => $aVariants
				);
				if (empty($aMasters[$sMasterKeyValue]['master'])) {
					unset($aMasters[$sMasterKeyValue]);
				}
			}
		}
		return $aMasters;
	}
	
	/**
	 * delete prepared article from marketplace
	 * @param string $sTable
	 * @param array $aArticle
	 * @param int $iMp
	 */
	protected function cleanTable ($sTable, $aArticle, $iMp) {
		$aIdents = ((empty($aArticle['variants']) ? array() : array_keys($aArticle['variants'])));
		$aIdents[] = $aArticle['master'][$this->sKeyType];//getArticles() are distinct masters, so this master is in other table (eg.  amazon_*)
		MagnaDB::gi()->delete($sTable, array('mpID' => $iMp), "AND ".$this->sKeyType." IN ('".implode("', '",$aIdents)."')");
	}
	
	/**
	 * fills talbe for multivariants, add dummydata
	 * @param string $sTable
	 * @param array $aArticle
	 * @param int $iMp
	 */
	protected function fillTable ($sTable, $aArticle, $iMp) {
		if ($sTable == TABLE_MAGNA_AMAZON_APPLY) {
			/**
			 * apply
			 * only master are prepared, all variants as dummy
			 * if master is not prepared, use data from first variant
			 */
			if(false === ($aPrepared = MagnaDb::gi()->fetchRow("SELECT * FROM ".$sTable." WHERE mpID='".$iMp."' AND ".$this->sKeyType."='".$aArticle['master'][$this->sKeyType]."'"))) {
				//master not prepared - get first prepared variant and insert as master in apply
				$aPrepared = MagnaDb::gi()->fetchRow("SELECT * FROM ".$sTable." WHERE mpID='".$iMp."' AND ".$this->sKeyType." in ('".implode("', '", array_keys($aArticle['variants']))."')");
				$aPrepared['products_id'] = $aArticle['master']['products_id'];
				$aPrepared['products_model'] = $aArticle['master']['products_model'];
				MagnaDB::gi()->insert($sTable, $aPrepared);
			}
			foreach ($aArticle['variants'] as $sVariantKey => $aVariant) {
				if (0 == MagnaDb::gi()->fetchOne("SELECT count(*) FROM ".$sTable." WHERE mpID='".$iMp."' AND ".$this->sKeyType." = '".$sVariantKey."'")) {
					// variant not prepared
					$aPrepared['products_id'] = $aVariant['products_id'];
					$aPrepared['products_model'] = $aVariant['products_model'];
					MagnaDb::gi()->insert($sTable, $aPrepared);
				}
			}
		} else {
			/**
			 * matching:
			 * only variants are prepared 
			 * if any variant is prepared, create master as dummy
			 */
			if(false === MagnaDb::gi()->fetchRow("SELECT * FROM ".$sTable." WHERE mpID='".$iMp."' AND ".$this->sKeyType."='".$aArticle['master'][$this->sKeyType]."'")) {
				MagnaDB::gi()->insert(TABLE_MAGNA_AMAZON_PROPERTIES, array(
					'products_id' =>  $aArticle['master']['products_id'],
					'products_model' =>  $aArticle['master']['products_model'],
					'mpID' => $iMp,
					'asin' => 'dummyMasterArticle'
				));
			}	
		}
	}
	
	/**
	 * add marketplaces to article depending by table (preparetype)
	 * @param string $sTable
	 * @param array $aArticle
	 * @return array
	 */
	protected function addMartketPlaces ($sTable, $aArticle) {
		$aKeys =  ((empty($aArticle['variants']) ? array() : array_keys($aArticle['variants'])));
		$aKeys[] = $aArticle['master'][$this->sKeyType];
		$aArticle['master']['marketplaces'] = MagnaDb::gi()->fetchArray("SELECT DISTINCT mpID FROM ".$sTable." where ".$this->sKeyType." in('".implode("', '", $aKeys)."')", true);
		foreach ($aArticle['variants'] as $sKeyType => &$aVariant) {
			$aVariant['marketplaces'] = MagnaDb::gi()->fetchArray("SELECT DISTINCT mpID FROM ".$sTable." where ".$this->sKeyType." = '".$sKeyType."' ", true);
		}
		return $aArticle;
	}
	
	/**
	 * deletes entries wich are not complete prepared
	 * @param string $sTable
	 */
	protected function deletePreparedWithError ($sTable) {
		MagnaDB::gi()->delete($sTable, ($sTable == TABLE_MAGNA_AMAZON_APPLY) ? array('is_incomplete' => 'true') : array('asin' => ''));
	}
	
}

class magna_updateEbayForMultivariants extends magna_updateAmazonForMultivariants {
	public function __construct($iStart = null, $iLimit = null) {
		$sKeyType = MagnaDB::gi()->fetchOne('SELECT value FROM magnalister_config WHERE mkey="general.keytype"');
		if (!empty($sKeyType) && MagnaDB::gi()->columnExistsInTable('products_master_model', TABLE_PRODUCTS)) {// not complete configured
			$this->sKeyType = 'products_'.(($sKeyType == 'artNr') ? 'model' : 'id');
			$sTable = TABLE_MAGNA_EBAY_PROPERTIES;
			$this->deletePreparedWithError($sTable);
			$aArticles = $this->getArticles($iStart, $iLimit);
			$this->iMasterCount = count($aArticles);
			foreach ($aArticles as $sMasterModel => $aArticle) {
				$aArticle = $this->addMartketPlaces($sTable, $aArticle);
				foreach ($aArticle['master']['marketplaces'] as $iMp) {
					$this->fillTable($sTable, $aArticle, $iMp);
				}
			}
		}
	}	
	
	protected function fillTable ($sTable, $aArticle, $iMp) {
		/**
		 * only master are prepared
		 * if master is not prepared, use data from first variant
		 * all variants will deleted
		 */
		if(false === ($aPrepared = MagnaDb::gi()->fetchRow("SELECT * FROM ".$sTable." WHERE mpID='".$iMp."' AND ".$this->sKeyType."='".$aArticle['master'][$this->sKeyType]."'"))) {
			//master not prepared - get first prepared variant and insert as master in apply
			$aPrepared = MagnaDb::gi()->fetchRow("SELECT * FROM ".$sTable." WHERE mpID='".$iMp."' AND ".$this->sKeyType." in ('".implode("', '", array_keys($aArticle['variants']))."')");
			$aPrepared['products_id'] = $aArticle['master']['products_id'];
			$aPrepared['products_model'] = $aArticle['master']['products_model'];
			MagnaDB::gi()->insert($sTable, $aPrepared);
		}
		if (!empty($aArticle['variants'])) {
			MagnaDB::gi()->query("DELETE FROM ".$sTable." WHERE ".$this->sKeyType." in('".implode("', '", array_keys($aArticle['variants']))."')");
		}
		
	}
	
	protected function deletePreparedWithError ($sTable) {
		MagnaDb::gi()->query("DELETE FROM ".$sTable." WHERE Verified != 'OK'");
	}
	
	protected function getArticles ($iStart = null, $iLimit = null) {
		$sLimitSql = ($iStart === null && $iLimit === null) ? '' : ' LIMIT '.((int)$iStart).', '.((int)$iLimit);
		$aMasters = array();
		foreach (MagnaDB::gi()->fetchArray("SELECT DISTINCT ".$this->sKeyType." FROM ".TABLE_MAGNA_EBAY_PROPERTIES.$sLimitSql, true) as $sKeyValue){
			$sMasterModel = MagnaDB::gi()->fetchOne("
				SELECT products_master_model 
				FROM ".TABLE_PRODUCTS." 
				WHERE ".$this->sKeyType."='".$sKeyValue."';"
			);
			if (empty($sMasterModel)) {
				$sMasterKeyValue = $sKeyValue;
				$sMasterModel = (($this->sKeyType == 'products_model') ? $sKeyValue : MagnaDB::gi()->fetchOne("SELECT products_model from ".TABLE_PRODUCTS." where products_id = '".$sKeyValue."'"));
			} else {
				if ($this->sKeyType == 'products_model') {
					$sMasterKeyValue = $sMasterModel;
				} else {
					$sMasterKeyValue = MagnaDb::gi()->fetchOne("SELECT products_id FROM ".TABLE_PRODUCTS." WHERE products_model = '".$sMasterModel."'");
				}
			}
			if (!isset($aMasters[$sMasterKeyValue])) {
				$aVariants = array();
				if (!empty($sMasterModel)) {
					foreach (MagnaDb::gi()->fetchArray("SELECT DISTINCT products_id, products_model FROM ".TABLE_PRODUCTS." where products_master_model = '".$sMasterModel."'") as $aVariant) {
						$aVariants[$aVariant[$this->sKeyType]] = $aVariant;
					}
				}
				$aMasters[$sMasterKeyValue] = array(
					'master' => MagnaDb::gi()->fetchRow("SELECT products_id, products_model FROM ".TABLE_PRODUCTS." where ".$this->sKeyType."='".$sMasterKeyValue."'"),
					'variants' => $aVariants
				);
				if (empty($aMasters[$sMasterKeyValue]['master'])) {
					unset($aMasters[$sMasterKeyValue]);
				}
			}
		}
		return $aMasters;
	}
	
}

/**
 * 1. fülle amazon-apply mit master, sobald eine variante vorhanden ist
 * 2. lösche aus amazon-matching arti
 */
function magna_updateAmazonForMultivariants() {
	$dbVersion = (int)MagnaDB::gi()->fetchOne("SELECT value FROM magnalister_config WHERE mkey = 'CurrentDBVersion' AND mpId = '0'");
	if ($dbVersion < 19) {
		mlDbUpdateBackupTables_19(array(TABLE_MAGNA_AMAZON_APPLY, TABLE_MAGNA_AMAZON_PROPERTIES));
		new magna_updateAmazonForMultivariants();
	}
	return;
}
function magna_updateEbayForMultivariants() {
	$dbVersion = (int)MagnaDB::gi()->fetchOne("SELECT value FROM magnalister_config WHERE mkey = 'CurrentDBVersion' AND mpId = '0'");
	if ($dbVersion < 19) {
		mlDbUpdateBackupTables_19(array(TABLE_MAGNA_EBAY_PROPERTIES));
		new magna_updateEbayForMultivariants();
	}
	return;
}

function mlDbUpdate_getIndexInfos_19($sTable) {
	$rIndexes = array(
		'ByIndexName' => array(),
		'ByColumnName' => array(),
	);
	
	$aIndexes = MagnaDB::gi()->fetchArray('SHOW INDEX FROM '.$sTable);
	if (!is_array($aIndexes)) {
		return $rIndexes;
	}

	foreach($aIndexes as $aIndex) {
		if (!isset($rIndexes['ByIndexName'][$aIndex['Key_name']])) {
			$rIndexes['ByIndexName'][$aIndex['Key_name']] = array();
		}
		$rIndexes['ByIndexName'][$aIndex['Key_name']][] = $aIndex['Column_name'];
		
		if (!isset($rIndexes['ByColumnName'][$aIndex['Column_name']])) {
			$rIndexes['ByColumnName'][$aIndex['Column_name']] = array();
		}
		$rIndexes['ByColumnName'][$aIndex['Column_name']][] = $aIndex['Key_name'];
		
	}
	
	return $rIndexes;
}

function mlDbUpdate_AddIndexesToPrepareTables_19() {
	$mlTables = MagnaDB::gi()->getAvailableTables('/^magnalister_.*/', true);
	
	$toIndex = array('mpID', 'products_id', 'products_model');
	
	foreach ($mlTables as $sTable) {
		$indexes = mlDbUpdate_getIndexInfos_19($sTable);
		#echo print_m($indexes, $sTable);
		
		foreach ($toIndex as $column) {
			if (!MagnaDB::gi()->columnExistsInTable($column, $sTable)) {
				continue;
			}
			$createIndex = !isset($indexes['ByColumnName'][$column]);
			if (!$createIndex) {
				$createIndex = true;
				foreach ($indexes['ByColumnName'][$column] as $idxName) {
					if (count($indexes['ByIndexName'][$idxName]) == 1) {
						$createIndex = false;
						break;
					}
				}
			}
			
			if ($createIndex) {
				$q = 'ALTER TABLE `'.$sTable.'` ADD INDEX `'.$column.'` ( `'.$column.'` )';
				#echo print_m($q)."\n";
				MagnaDB::gi()->query($q);
			}
		}
	}
}

function mlDbUpdate_FixHoodUniqueKeyIndexName_19() {
	$indexes = mlDbUpdate_getIndexInfos_19(TABLE_MAGNA_HOOD_PROPERTIES);
	if (isset($indexes['ByIndexName']['mpID']) && (count($indexes['ByIndexName']['mpID']) > 0)
		&& !isset($indexes['ByIndexName']['UniqueEntry'])
	) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'`
			    DROP INDEX `mpID`,
			    ADD UNIQUE `UniqueEntry` ( `mpID` , `products_id` , `products_model` )
		');
	}
}

function mlUpdateIndexProductsTable_19() {
	$sTable = TABLE_PRODUCTS;
	if ( MagnaDB::gi()->columnExistsInTable('products_master_model', TABLE_PRODUCTS) ) {
		$toIndex = array('products_model', 'products_master_model');
	} else {
		$toIndex = array('products_model');
	}
	$indexes = mlDbUpdate_getIndexInfos_19($sTable);
	#echo print_m($indexes, $sTable);
	
	foreach ($toIndex as $column) {
		if (!MagnaDB::gi()->columnExistsInTable($column, $sTable)) {
			continue;
		}
		$createIndex = !isset($indexes['ByColumnName'][$column]);
		if (!$createIndex) {
			$createIndex = true;
			foreach ($indexes['ByColumnName'][$column] as $idxName) {
				if (count($indexes['ByIndexName'][$idxName]) == 1) {
					$createIndex = false;
					break;
				}
			}
		}
		
		if ($createIndex) {
			$q = 'ALTER TABLE `'.$sTable.'` ADD INDEX `'.$column.'` ( `'.$column.'` )';
			#echo ($q)."\n";
			MagnaDB::gi()->query($q);
		}
	}
}

function mlDbUpdateBackupTables_19($aTables){
	foreach ($aTables as $sTable) {
		MagnaDB::gi()->query("DROP TABLE IF EXISTS ".$sTable."_bak");
		MagnaDB::gi()->query("CREATE TABLE ".$sTable."_bak LIKE ".$sTable);
		MagnaDB::gi()->query("REPLACE INTO ".$sTable."_bak SELECT * FROM ".$sTable);
	}
}

$functions[] = 'mlUpdateIndexProductsTable_19';

$functions[] = 'mlDbUpdate_FixHoodUniqueKeyIndexName_19';
$functions[] = 'mlDbUpdate_AddIndexesToPrepareTables_19';

$functions[] = 'magna_updateAmazonForMultivariants';
$functions[] = 'magna_updateEbayForMultivariants';
