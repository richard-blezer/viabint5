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
 * $Id MagnaRecalcOrdersTotal.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class MagnaRecalcOrdersTotal {
	const DBGLV_NONE = 0;
	const DBGLV_LOW  = 1;
	const DBGLV_MED  = 2;
	const DBGLV_HIGH = 3;
	
	private $sp = null;

	private $toDelete = array(
		'shipping',
		'magnalister' /* this one is for debugging purposes. */
	);
	
	private $cOrder = array();  // current order
	private $cOTotal = array(); // existing data from orders_total 
	private $cMarketplace = ''; // current marketplace 
	
	/* settings for current order */
	private $displayWithTax = false;
	private $showOTTax = false;
	
	private $taxes = array();   // recalculated taxes array for current order
	private $totalWTax = 0;		// total with tax
	private $totalWOTax = 0;    // total without tax
	private $shipping = array(); // shipping cost for current order
	private $ordersTotalSet = array(); // recalculated orders_total entries for current order
	
	private $magnaDB = null;
	
	private $_debug = false;
	private $_debugLevel = 0;

	protected function determineDebugOptions() {
		$this->_debug = isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] === 'true');
		
		if (!$this->_debug) return;
		
		$ref = new ReflectionClass($this);
    	$dbgLevels = $ref->getConstants();
		$lvl = 'DBGLV_'.(isset($_GET['LEVEL']) ? strtoupper($_GET['LEVEL']) : 'NONE');
		if (!array_key_exists($lvl, $dbgLevels)) {
			$this->_debugLevel = $this->_debug ? self::DBGLV_LOW : self::DBGLV_NONE;
		} else {
			$this->_debugLevel = $dbgLevels[$lvl];
		}
	}

	private function init() {
		
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
		require_once(DIR_MAGNALISTER_CALLBACK.'orders_import.php');
		magnaInitOrderImport();
		
		$this->sp = new SimplePrice();
		
		$this->determineDebugOptions();
		
		if ($this->_debug) {
			require_once(DIR_MAGNALISTER_INCLUDES . 'lib/MagnaTestDB.php');
			$this->magnaDB = MagnaTestDB::gi();
		} else {
			$this->magnaDB = MagnaDB::gi();
		}
	}

	private function cutomerTaxStatusByOrder($orderID) {
		# Display prices with tax included (true) or add the tax at the end (false)
		if (SHOPSYSTEM == 'oscommerce') {
			return true;
		}
	
		# customers_status_show_price_tax = 0 Preise netto anzeigen
		# customers_status_add_tax_ot = 1 MwSt am Ende draufaddieren
		return ((int)(MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_CUSTOMERS_STATUS.' cs, '.TABLE_ORDERS.' o
			 WHERE o.orders_id = \''.$orderID.'\'
			   AND cs.customers_status_id = o.customers_status
			   AND cs.customers_status_show_price_tax = 0
			   AND cs.customers_status_add_tax_ot = 1
		')) == 0);
	}

	private function cleanExistingOrdersTotal() {
		$this->magnaDB->query('
			DELETE FROM '.TABLE_ORDERS_TOTAL.'
			 WHERE orders_id = '.$this->cOrder['orders_id'].'
			   AND class IN ('.implode(', ', $this->toDelete).')
		');
	}

	private function loadExistingOrdersTotal() {
		$otOrigDB = MagnaDB::gi()->fetchArray('
			SELECT * FROM '.TABLE_ORDERS_TOTAL.'
			 WHERE orders_id = '.$this->cOrder['orders_id'].'
			   AND class IN ('.implode(', ', $this->toDelete).')
		');
		$this->cOTotal = array();
		if (!empty($otOrigDB)) {
			foreach ($otOrigDB as $otE) {
				$this->cOTotal[$otE['class']] = $otE;
			}
		}
	}

	private function reset() {
		$this->taxes = array();
		$this->totalWTax = 0;
		$this->totalWOTax = 0;
		$this->shipping = array (
			'brutto' => false,
			'netto'  => false,
			'tax'    => 0
		);

		$this->ordersTotalSet['Shipping'] = array (
			'orders_id' => $this->cOrder['orders_id'],
			'orders_total_key' => 'shipping',
			'orders_total_model' => 'Marketplace',
			'orders_total_name' => 'Shipping',
			'orders_total_price' => 0.00,
			'orders_total_tax' => 0.00,
			'orders_total_tax_class' => 0,
			'orders_total_quantity' => 1.00,
			'allow_tax' => NULL
		);	
	}
	
	private function calcShippingTax($shippingCost) {
		loadDBConfig($this->cOrder['mpID']);
		
		$this->shipping['tax'] = (float)getDBConfigValue($this->cOrder['platform'].'.mwst.shipping', $this->cOrder['mpID'], 19);
		
		$this->shipping['brutto'] = $shippingCost;
		$this->shipping['netto'] = $this->sp->setPrice($this->shipping['brutto'])->removeTax($this->shipping['tax'])->getPrice();
	
		/* add to tax */
		if (!isset($this->taxes[$this->shipping['tax']])) {
			$this->taxes[$this->shipping['tax']] = 0;
		}
		$this->taxes[$this->shipping['tax']] += $this->shipping['brutto'] - $this->shipping['netto'];
	}

	private function fetchShippingDetails() {
		$defaultShippingCost = isset($this->cOTotal['shipping']) 
			? $this->cOTotal['shipping']
			: 0;

		if (((int)$this->cOrder['mpID'] == 0) || empty($this->cOrder['platform'])) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;[GetShippingDetails] No magnalister order.<br>';
			return $defaultShippingCost;
		}
		$request = array (
			'SUBSYSTEM' => $this->cOrder['platform'],
			'MARKETPLACEID' => $this->cOrder['mpID'],
			'ACTION' => 'GetOrdersForDateRange',
			'BEGIN' => '1970-01-01 00:00:00',
			'MOrderIDs' => explode ("\n", $this->cOrder['special']),
		);
		if ($this->_debug && ($this->_debugLevel > 2)) {
			echo print_m(json_indent($request), 'FetchShippingDetails (Request)');
		}
		try {
			$res = MagnaConnector::gi()->submitRequest($request);
		} catch (MagnaException $e) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;[GetShippingDetails] Unable to fetch order from magnalister service ('.$e->getMessage().').<br>';
			$e->setCriticalStatus(false);
			return $defaultShippingCost;
		}
		if ($this->_debug && ($this->_debugLevel > 2)) {
			echo print_m($res, 'FetchShippingDetails (Response)');
		}
		if (!isset($res['DATA'][0]['orderTotal']['Shipping'])) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;[GetShippingDetails] First order does not contain shipping details.<br>';
			return $defaultShippingCost;
		}
		$sT = $res['DATA'][0]['orderTotal']['Shipping'];
		return $sT['orders_total_price'];
	}

	private function calcShippingDetails() {
		if ($this->shipping['brutto'] === false) {
			$shippingCost = $this->fetchShippingDetails();
		} else {
			$shippingCost = $this->shipping['brutto'];
		}
		
		$this->calcShippingTax($shippingCost);
		
		$this->sp->setPrice($this->shipping['brutto']);
		$this->ordersTotalSet['Shipping']['value'] = $this->sp->getPrice();
		$this->ordersTotalSet['Shipping']['text']  = $this->sp->format();
	}

	private function calcProductsTaxes() {
		$oProds = MagnaDB::gi()->fetchArray('
			SELECT * FROM '.TABLE_ORDERS_PRODUCTS.'
			 WHERE orders_id = \''.$this->cOrder['orders_id'].'\'
		');
		foreach ($oProds as $p) {
			if (!isset($p['allow_tax'])) {
				/* OSC: Prices are always saved w/o tax in orders_products */
				$p['allow_tax'] = '0';
			}
			
			if ($p['allow_tax'] == '1') {
				/* products_price includes tax */
				$priceWOTax = $this->sp->setPrice($p['products_price'])->removeTax($p['products_tax'])->getPrice();
				$priceWTax  = $p['products_price'];
			} else {
				/* products_price does not include tax */
				$priceWOTax = $p['products_price'];
				$priceWTax  = $this->sp->setPrice($p['products_price'])->addTax($p['products_tax'])->getPrice();
			}
			$priceWOTax *= $p['products_quantity'];
			$priceWTax  *= $p['products_quantity'];
			
			$this->totalWOTax += $priceWOTax;
			$this->totalWTax  += $priceWTax;

			if (!isset($this->taxes[(float)$p['products_tax']])) {
				$this->taxes[(float)$p['products_tax']] = 0.0;
			}
			$this->taxes[(float)$p['products_tax']] += $priceWTax - $priceWOTax;
		}
	}
	
	private function saveOrder() {
		foreach ($this->ordersTotalSet as $otSet) {
			if (!in_array($otSet['class'], $this->toDelete)
				&& MagnaDB::gi()->recordExists(TABLE_ORDERS_TOTAL, array (
					'orders_id' => $otSet['orders_id'],
					'class' => $otSet['class']
				))
			) {
				$this->magnaDB->update(TABLE_ORDERS_TOTAL, $otSet, array (
					'orders_id' => $otSet['orders_id'],
					'class' => $otSet['class']
				));
			} else {
				$this->magnaDB->insert(TABLE_ORDERS_TOTAL, $otSet);
			}
		}
	}

	private function fixOrder($save = true) {
		$this->cleanExistingOrdersTotal();
		$this->loadExistingOrdersTotal();
				
		$this->sp->setCurrency(MagnaDB::gi()->fetchOne('
			SELECT currency FROM '.TABLE_ORDERS.' 
			 WHERE orders_id=\''.$this->cOrder['orders_id'].'\'
		'));
		
		$this->calcProductsTaxes();
		$this->calcShippingDetails();
		
		if (!$save) return;
				
		$this->saveOrder();
	}

	public function execute($platform = 'ebay') {
		$platform = MagnaDB::gi()->escape($platform);
		$data = MagnaDB::gi()->fetchArray(eecho('
		    SELECT o.orders_id, ot.count, mo.special, mo.platform, mo.mpID
		      FROM '.TABLE_ORDERS.' o
		INNER JOIN '.TABLE_MAGNA_ORDERS.' mo ON o.orders_id=mo.orders_id AND mo.platform=\''.$platform.'\'
		 LEFT JOIN (
		        SELECT COUNT(orders_total_id) AS count, orders_id
		          FROM '.TABLE_ORDERS_TOTAL.' 
		      GROUP BY orders_id
		           ) ot ON ot.orders_id = o.orders_id
			 WHERE count <= 2 OR count IS NULL
		  ORDER BY mo.mpID ASC, o.orders_id ASC
		', false));

		//echo print_m($data, 'Orders to fix');
		
		if (empty($data)) {
			echo 'No orders to fix.';
			return;
		}
		
		$this->init();
		
		foreach ($data as $order) {
			$this->cOrder = $order;
			echo 'Fixing order '.$this->cOrder['orders_id']."<br>\n";

			$this->reset();
			$this->fixOrder();
		}		
	}
	
	public function recalcExistingOrder($orderID, $shippingCost = false, $save = false) {
		$this->cOrder = MagnaDB::gi()->fetchRow(eecho('
		    SELECT o.orders_id, mo.special, mo.platform, mo.mpID
		      FROM '.TABLE_ORDERS.' o
		 LEFT JOIN '.TABLE_MAGNA_ORDERS.' mo 
			    ON o.orders_id = mo.orders_id
			 WHERE o.orders_id = \''.(int)$orderID.'\'
		', false));
		
		if (empty($this->cOrder)) {
			if ($this->_debug) echo __CLASS__.'::'.__METHOD__."(): Order ($orderID) not found. Skipping calculation.\n";
			return;
		}
		
		$this->init();
		
		$this->reset();
		$this->shipping['brutto'] = $shippingCost;
		
		$this->fixOrder(false);
		
		if ($save) {
			$this->cleanExistingOrdersTotal();
			$this->saveOrder();
		}
		
		return $this->ordersTotalSet;
	}
	
}
