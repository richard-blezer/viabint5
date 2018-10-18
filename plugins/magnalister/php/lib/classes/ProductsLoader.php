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

class ProductsLoader {
	private $lang = '';
	
	private $ccAttrName = array();
	
	public function __construct($lang) {
		$this->lang = $lang;
	}
	
	private function processVariationsData(&$data) {
		foreach ($data as &$row) {
			if (!array_key_exists($row['attributes_parent_id'], $this->ccAttrName)) {
				$this->ccAttrName[$row['attributes_parent_id']] = MagnaDB::gi()->fetchOne(eecho('
					SELECT attributes_name 
					  FROM xt_plg_products_attributes_description 
					 WHERE attributes_id=\''.$row['attributes_parent_id'].'\'
					       AND language_code = \''.$this->lang.'\'
				', false));
			}
			$row['attributes_parent_name'] = $this->ccAttrName[$row['attributes_parent_id']];
		}
	}

	public function getVariationForSingleProduct($pID) {
		$data = MagnaDB::gi()->fetchArray('
			SELECT *
			  FROM xt_plg_products_to_attributes p2a,
			       xt_plg_products_attributes pa,
			       xt_plg_products_attributes_description pad
			 WHERE p2a.products_id=\''.$pID.'\'
			       AND pa.attributes_id = p2a.attributes_id
			       AND pad.attributes_id = p2a.attributes_id
			       AND pad.language_code = \''.$this->lang.'\'
		');
		if ($data === false) return false;
		$this->processVariationsData($data);
		return $data;
	}
	
	public function convertVariation2MagnaVariation($data) {
		if ($data === false) return false;
		$nData = array();
		$cc = array();
		foreach ($data as $row) {
			$key = $row['products_id'];
			if (!array_key_exists($row['products_id'], $cc)) {
				$cc[$row['products_id']] = MagnaDB::gi()->fetchRow('
					SELECT products_ean, products_quantity, products_shippingtime, products_model, products_price
					  FROM xt_products
					 WHERE products_id=\''.$row['products_id'].'\'
				');
			}
			$prod = $cc[$row['products_id']];

			if ($prod === false) continue;
			if (!isset($nData[$key])) {
				$nData[$key] = array (
					'PID' => $row['products_id'],
					'EAN' => $prod['products_ean'],
					'Quantity' => $prod['products_quantity'],
					'ShippingTime' => $prod['products_shippingtime'],
					'SKU' => empty($prod['products_model']) ? 'ML'.$row['products_id'] : $prod['products_model'],
					'Price' => $prod['products_price'],
					'Variation' => array (
						array (
							'Group' => $row['attributes_parent_name'],
							'Value' => $row['attributes_name'],
						)
					)
				);
			} else {
				$nData[$row['products_id']]['Variation'][] = array (
					'Group' => $row['attributes_parent_name'],
					'Value' => $row['attributes_name'],
				);
			}
		}
		return $nData;
	}
	
	public function getMasterPID($pID) {
		$p = MagnaDB::gi()->fetchRow('
			SELECT products_master_model, products_master_flag
			  FROM xt_products
			 WHERE products_id=\''.$pID.'\'
		');
		if (($p['products_master_flag'] == '1') || empty($p['products_master_model'])) {
			return $pID;
		}
		$npID = MagnaDB::gi()->fetchOne('
			SELECT products_id
			  FROM xt_products
			 WHERE products_model=\''.$p['products_master_model'].'\'
			       AND products_master_flag=1
		', true);
		if ($npID === false) return $pID;
		return $npID;
	}
	
	public function getSlaveIDs($pID) {
		$spIDs = MagnaDB::gi()->fetchArray('
			SELECT products_id
			  FROM xt_products
			 WHERE products_master_model=(
			          SELECT products_model
			            FROM xt_products
			           WHERE products_id=\''.$pID.'\'
			       )
		', true);
		return $spIDs;
	}

	public function getProduct($pID, $addQuery = '') {
		$product = MagnaDB::gi()->fetchRow('
		    SELECT p.products_id, p.products_ean, p.products_quantity, p.products_quantity,
		           p.products_shippingtime, p.products_model, p.products_master_model, p.products_master_flag,
		           p.products_image, p.products_price, p.date_added, p.products_weight, p.products_tax_class_id,
		           p.products_vpe, ssd.status_name AS products_vpe_name, p.products_vpe_status, p.products_vpe_value,
		           pd.products_name, pd.products_description, pd.products_short_description, pd.products_keywords, 
		           date_format(p.date_available, \'%Y-%m-%d\') AS products_date_available 
		      FROM ('.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd)
		 LEFT JOIN '.TABLE_SYSTEM_STATUS_DESCRIPTION.' ssd ON ssd.status_id = p.products_vpe AND ssd.language_code = pd.language_code
		     WHERE p.products_id = \''.(int)$pID.'\'
		           AND p.products_id = pd.products_id
		           AND pd.language_code = \''.$this->lang.'\'
		       '.$addQuery.'
		');
		if (!is_array($product) || empty($product)) return false;


		if ($product['products_image']) {
			$product['products_allimages'] = array($product['products_image']);
		} else {
			$product['products_allimages'] = array();
		}

		$product['products_allimages'] = array_merge(
			$product['products_allimages'],
			(array)MagnaDB::gi()->fetchArray('
				SELECT m.file
				  FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml 
				 WHERE ml.link_id = \''.$product['products_id'].'\' 
				       AND ml.m_id = m.id
				       AND ml.class = \'product\'
			  ORDER BY  ml.sort_order
			', true)
		);
		return $product;
	}
	
	public function getVariations($pID, &$pIDs, $isAlreadyMaster = false) {
		if (!$isAlreadyMaster) {
			$pID = $this->getMasterPID($pID);
		}
		$spIDs = $this->getSlaveIDs($pID);
		$nspIDs = array_diff($spIDs, $pIDs);
		if (!empty($nspIDs)) {
			foreach ($nspIDs as $nID) {
				$idx = array_search($nID, $spIDs);
				if ($idx === false) continue;
				unset($spIDs[$idx]);
			}
		}
		$spIDs[] = $pID;
		$data = MagnaDB::gi()->fetchArray('
			SELECT * 
			  FROM xt_plg_products_to_attributes p2a,
			       xt_plg_products_attributes pa,
			       xt_plg_products_attributes_description pad
			 WHERE p2a.products_id IN (\''.implode('\', \'', $spIDs).'\')
			       AND pa.attributes_id = p2a.attributes_id
			       AND pad.attributes_id = p2a.attributes_id
			       AND pad.language_code = \''.$this->lang.'\'
		');
		if ($data === false) return false;
		$this->processVariationsData($data);
		return $this->convertVariation2MagnaVariation($data);
	}

	public function getProductAndVariations($pID, &$pIDs) {
		$npID = $this->getMasterPID($pID);
		$product = $this->getProduct($npID);
		$variations = $this->getVariations($npID, $pIDs, true);
		if ($variations !== false) {
			$product['Variations'] = array_values($variations);
		}
		return $product;
	}
}
