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
 * $Id: EbayImportOrders.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class EbayImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
	
	protected function initImport() {
		parent::initImport();
		MagnaConnector::gi()->setTimeOutInSeconds(10);
	}
	
	protected function completeImport() {
		MagnaConnector::gi()->resetTimeOut();
	}
	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		
		$keys['OrderStatusClosed'] = array (
			'key' => 'orderstatus.closed',
			'default' => array(),
		);
		
		$keys['ShippingProfiles'] = array (
			'key' => 'shippingprofiles',
			'default' => null,
		);
		$keys['ShippingProfileIdLocal'] = array (
			'key' => 'default.shippingprofile.local',
			'default' => 0,
		);
		$keys['ShippingProfileIdIternational'] = array (
			'key' => 'default.shippingprofile.international',
			'default' => 0,
		);
		$keys['ShippingProfileDiscountUseLocal'] = array (
			'key' => array('shippingdiscount.local', 'val'),
			'default' => true,
		);
		$keys['ShippingProfileDiscountUseIternational'] = array (
			'key' => array('shippingdiscount.international', 'val'),
			'default' => true,
		);
		return $keys;
	}
	
	protected function initConfig() {
		parent::initConfig();
		
		if (!is_array($this->config['OrderStatusClosed'])) {
			$this->config['OrderStatusClosed'] = array();
		}
	}
	
	protected function doBeforeInsertProduct() {
		if (isset($this->p['products_shipping_time'])
			|| ('0' == $this->p['products_shipping_time'])
		) {
			if ('de' == strtolower($this->config['StoreLanguage'])) {
				$this->p['products_shipping_time'] = getDBConfigValue('ebay.DispatchTimeMax', $this->mpID, 0).' Werktage';
			} else {
				$this->p['products_shipping_time'] = getDBConfigValue('ebay.DispatchTimeMax', $this->mpID, 0).' days';
			}
		}
	}
	
	/**
	 * How many hours, days, weeks or whatever we go back in time to request older orders?
	 * @return time in seconds
	 */ 
	protected function getPastTimeOffset() {
		return 60 * 60 * 24 * 7 * 30 * 4;
	}
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['eBayOrderID'];
	}
	
	protected function getOrdersStatus() {
		return $this->config['OrderStatusOpen'];
	}
	
	protected function generateOrderComment() {
		if (!empty($this->o['orderInfo']['eBayBuyerUsername'])) {
			$buyer = "\n".'eBay User:   '.$this->o['orderInfo']['eBayBuyerUsername'];
		} else {
			$buyer = '';
		}
		
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			'eBayOrderID: '.$this->getMarketplaceOrderID().$buyer."\n\n".
			$this->comment
		);
	}
	
	protected function generateOrdersStatusComment() {
		if (!empty($this->o['orderInfo']['eBayBuyerUsername'])) {
			$buyer = "\n".'eBay User:   '.$this->o['orderInfo']['eBayBuyerUsername'];
		} else {
			$buyer = '';
		}
		
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP, $this->marketplaceTitle)."\n".
			'eBayOrderID: '.$this->getMarketplaceOrderID().$buyer."\n\n".
			$this->comment
		);
	}
	
	/**
	 * Returs the shipping method for the current order.
	 * @return string
	 */
	protected function getShippingMethod() {
		if ($this->config['ShippingMethod'] == 'standart') {
			return $this->o['order']['shipping_code'];
		}
		return $this->config['ShippingMethod'];
	}

	protected function getPaymentMethod() {
		// There is no matching. We simply return what marketplace submitted to us as payment method.
		if ($this->config['PaymentMethod'] == 'matching') {
			return $this->o['order']['payment_code'];
		}
		return $this->config['PaymentMethod'];
	}
	
	protected function doInsertOrder() {
		$this->doBeforeInsertOrder();
		
		if (empty($this->config['OrderStatusClosed'])) {
			$existingOpenOrder = false;
		} else {
			$existingOpenOrder = MagnaDB::gi()->fetchRow(eecho('
			    SELECT o.orders_id, mo.special, mo.data
			      FROM '.TABLE_ORDERS.' o, '.TABLE_MAGNA_ORDERS.' mo
			     WHERE o.customers_id = '.$this->o['order']['customers_id'].'
			           AND o.customers_email_address = \''.$this->o['order']['customers_email_address'].'\' 
			           AND o.orders_status NOT IN ("'.implode('", "', $this->config['OrderStatusClosed']).'")
			           AND mo.mpID = '.$this->mpID.'
			           AND o.orders_id = mo.orders_id 
			  ORDER BY o.orders_id DESC LIMIT 1
			', $this->verbose));
		}
		
		if ($this->verbose) echo var_dump_pre($existingOpenOrder, '$existingOpenOrder');

		# If magna order is found we add this order to it.
		if (false != $existingOpenOrder) {
			# We found the order to which we can add this order and make it merged.
			$this->cur['OrderID'] = (int)$existingOpenOrder['orders_id'];
			$magnaOrdersDataArr = unserialize($existingOpenOrder['data']);

			# Merge order to merged or single order.
			if (!is_array($magnaOrdersDataArr['eBayOrderID'])) {
				$magnaOrdersDataArr['eBayOrderID'] = array(
					$magnaOrdersDataArr['eBayOrderID'],
					$this->o['magnaOrders']['eBayOrderID']
				);
			} else {
				$magnaOrdersDataArr['eBayOrderID'][] = $this->o['magnaOrders']['eBayOrderID'];
			}
			$magnaOrdersData = serialize($magnaOrdersDataArr);
			$magnaOrdersSpecial = $existingOpenOrder['special']."\n".$this->getMarketplaceOrderID();
			
			# Update the shipping code
			MagnaDB::gi()->update(TABLE_ORDERS, array (
				'shipping_code' => $this->o['order']['shipping_code'],
			), array (
				'orders_id' => $this->cur['OrderID'],
			));
		} else {
			# We didn't find an order to which we can add this order.
			$this->db->insert(TABLE_ORDERS, $this->o['order']);
			$this->cur['OrderID'] = $this->db->getLastInsertID();
			$magnaOrdersData = serialize($this->o['magnaOrders']);
			$magnaOrdersSpecial = $this->getMarketplaceOrderID();
		}
		
		$this->db->insert(TABLE_MAGNA_ORDERS, array(
			'mpID' => $this->mpID,
			'orders_id' => $this->cur['OrderID'],
			'orders_status' => $this->o['order']['orders_status'],
			'data' => $magnaOrdersData,
			'internaldata' => '',
			'special' => $magnaOrdersSpecial,
			'platform' => $this->marketplace
		), true);
	}
	
	protected function isDomestic($countryISO) {
		if (strtolower($countryISO) == $this->config['StoreLanguage']) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Recalculates the shipping cost for orders that are going to be merged.
	 */
	protected function calculateShippingCost($existingShippingCost, $currItemShippingCost, $totalNumberOfItems, $currProductsCount, $countryISO) {
		/* 
			-schau wie die konfigurierten Kosten aussehen,
			-schau wieviele Artikel drin sind,
			-daraus die Kosten fuer den ersten + je weiteren
			-schau ob uebermittelte Kosten > Kosten fuer den ersten
			-wenn ja, neue Kosten = uebermittelte Kosten + bisherige Anzahl * addcost fuer uebermittelte Versandart (anhand Kosten)
			-wenn nein, neue Kosten = alte Kosten + addcost fuer alte Versandart
		*/
		
		if ((0 == $existingShippingCost) && (0 == $currItemShippingCost)) {
			return 0.0;
		}
		if (empty($this->config['ShippingProfiles'])) {
			$localAddCost         = 0.0;
			$internationalAddCost = 0.0;
		} else {
			if (empty($this->config['ShippingProfileIdLocal'])) {
				$localAddCost = 0.0;
			} else {
				$localAddCost = (float)$this->config['ShippingProfiles']['Profiles'][$this->config['ShippingProfileIdLocal']]['EachAdditionalAmount'];
			}
			if (empty($this->config['ShippingProfileIdIternational'])) {
				$internationalAddCost = 0.0;
			} else {
				$internationalAddCost = (float)$this->config['ShippingProfiles']['Profiles'][$this->config['ShippingProfileIdIternational']]['EachAdditionalAmount'];
			}
			if (array_key_exists('PromotionalShippingDiscount', $this->config['ShippingProfiles'])) {
				if (   array_key_exists('DiscountName',$this->config['ShippingProfiles']['PromotionalShippingDiscount'])
					&& array_key_exists('ShippingCost',$this->config['ShippingProfiles']['PromotionalShippingDiscount'])
				) {
					if ('MaximumShippingCostPerOrder' == $this->config['ShippingProfiles']['PromotionalShippingDiscount']['DiscountName']) {
						$maximumShippingCostPerOrder = (float)$this->config['ShippingProfiles']['PromotionalShippingDiscount']['ShippingCost'];
					}
				}
			}
		}
		$domestic = $this->isDomestic($countryISO);
		if ($domestic) {
			$addcost = $localAddCost;
			if (!$this->config['ShippingProfileDiscountUseLocal'] && isset($maximumShippingCostPerOrder)) {
				unset($maximumShippingCostPerOrder);
			}
		} else {
			$addcost = $internationalAddCost;
			if (!$this->config['ShippingProfileDiscountUseIternational'] && isset($maximumShippingCostPerOrder)) {
				unset($maximumShippingCostPerOrder);
			}
		}
		# existingAddCost: ausser dem ersten Item und aktueller Bestellung
		$existingAddCost = ($totalNumberOfItems - 1 - $currProductsCount) * $addcost;
		$firstItemShippingCost = $existingShippingCost - $existingAddCost;
		# currSingleItemShippingCost: erstes Stueck der aktuellen Bestellung
		$currSingleItemShippingCost = $currItemShippingCost - (($currProductsCount - 1) * $addcost);
		$totalAddCost = $existingAddCost + ($currProductsCount * $addcost);
		if ($firstItemShippingCost > $currSingleItemShippingCost) {
			$totalShippingCost = $firstItemShippingCost + $totalAddCost;
		} else {
			$totalShippingCost = $currSingleItemShippingCost + $totalAddCost;
		}
		if (isset($maximumShippingCostPerOrder)) {
			$totalShippingCost = min($totalShippingCost, $maximumShippingCostPerOrder);
		}
		return $totalShippingCost;
	}
	
	/**
	 * Calculates the shipping costs if an existing order will be merged
	 * before calculating the shipping tax.
	 */
	protected function proccessShippingTax() {
		if (!array_key_exists('Shipping', $this->o['orderTotal'])) {
			return;
		}
		
		$existingShippingCost = (float)MagnaDB::gi()->fetchOne(eecho('
		    SELECT orders_total_price
		      FROM '.TABLE_ORDERS_TOTAL.'
		     WHERE orders_id = '.$this->cur['OrderID'].'
		           AND orders_total_key = "shipping"
		  ORDER BY orders_total_price DESC 
		     LIMIT 1
		', $this->verbose));
		$productsCount = (int)MagnaDB::gi()->fetchOne(eecho('
			SELECT SUM(products_quantity)
			  FROM '.TABLE_ORDERS_PRODUCTS.'
			 WHERE orders_id = '.$this->cur['OrderID'].'
		', $this->verbose));
		
		if (($existingShippingCost > 0) || ($productsCount > $this->o['_processingData']['ProductsCount'])) {
			/* Merged order */
			$this->o['orderTotal']['Shipping']['orders_total_price'] = $this->calculateShippingCost(
				$existingShippingCost,
				$this->o['orderTotal']['Shipping']['orders_total_price'],
				$productsCount,
				$this->o['_processingData']['ProductsCount'],
				$this->o['order']['billing_country_iso_code_2']
			);
			if ($this->verbose) {
				echo "\n".'Merged ShippingCost: '.$this->o['orderTotal']['Shipping']['orders_total_price']."\n";
			}
		}
		
		parent::proccessShippingTax();
	}
	
}
