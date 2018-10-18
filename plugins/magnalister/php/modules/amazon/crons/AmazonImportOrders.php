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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class AmazonImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusFBA'] = array (
			'key' => 'orderstatus.fba',
			'default' => '',
		);
		return $keys;
	}
	
	/**
	 * How many hours, days, weeks or whatever we go back in time to request older orders?
	 * @return time in seconds
	 */ 
	protected function getPastTimeOffset() {
		return 60 * 60 * 24 * 7 * 30 * 4;
	}
	
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MOrderID'];
	}
	
	protected function doBeforeInsertMagnaOrder() {
		return array (
			'FulfillmentChannel' => $this->o['orderInfo']['FulfillmentChannel']
		);
	}
	
	private function getMarketplaceTitle() {
		return $this->marketplaceTitle.(
			($this->o['orderInfo']['FulfillmentChannel'] == 'AFN')
				? 'FBA'
				: ''
		);
	}
	
	protected function getOrdersStatus() {
		if ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN') {
			return $this->config['OrderStatusFBA'];
		}
		return $this->config['OrderStatusOpen'];
	}
	
	protected function generateOrderComment() {
		$comment = str_replace('GiftMessageText', ML_AMAZON_LABEL_GIFT_MESSAGE, $this->comment);
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->getMarketplaceTitle())."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->getMarketplaceOrderID()."\n\n".
			$comment
		);
	}
	
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment();
	}
	
	protected function insertProduct() {
		$this->p['products_name'] = str_replace('GiftWrapType', ML_AMAZON_LABEL_GIFT_PAPER, $this->p['products_name']);
		parent::insertProduct();
	}
	
	/**
	 * Returns true if the stock of the imported and identified item has to be reduced.
	 * @return bool
	 */
	protected function hasReduceStock() {
		return (($this->config['StockSync.FromMarketplace'] != 'no') && ($this->o['orderInfo']['FulfillmentChannel'] != 'AFN'))
			|| (($this->config['StockSync.FromMarketplace'] == 'fba') && ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN'));
	}
	
}
