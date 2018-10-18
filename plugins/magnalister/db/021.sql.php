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


function mlDbUpdate_getIndexInfos_21($sTable) {
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

function mlUpdateIndexProductsTable_21() {
	$sTable = TABLE_PRODUCTS;
	
	if ( MagnaDB::gi()->columnExistsInTable('products_master_model', TABLE_PRODUCTS) ) {
		$toIndex = array('products_model', 'products_master_model');
	} else {
		$toIndex = array('products_model');
	}
	
	$indexes = mlDbUpdate_getIndexInfos_21($sTable);
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


$functions[] = 'mlUpdateIndexProductsTable_21';
