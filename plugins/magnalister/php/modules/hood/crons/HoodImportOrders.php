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

class HoodImportOrders extends MagnaCompatibleImportOrders {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}

	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
//		$keys['OrderStatusUnpaid'] = array (
//			'key' => 'orderstatus.unpaid',
//			'default' => '1',
//		);
//		$keys['ImportUnpaid'] = array (
//			'key' => 'import.unpaid',
//			'default' => 'false',
//		);
		return $keys;
	}

	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MOrderID'];
	}

	/**
	 * Returns the status that the order should have as string.
	 * Use $this->o['order'].
	 *
	 * @return String	The order status for the currently processed order.
	 */
	protected function getOrdersStatus() {
//		return $this->o['orderInfo']['PaymentCompleted'] ? $this->config['OrderStatusOpen'] : $this->config['OrderStatusUnpaid'];
		return $this->config['OrderStatusOpen'];
	}

	/**
	 * Returns the comment for orders.comment (Database).
	 * E.g. the comment from the customer or magnalister related information.
	 * Use $this->o['order'].
	 *
	 * @return String	The comment for the order.
	 */
	protected function generateOrderComment() {
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->getMarketplaceOrderID()."\n\n".
			$this->comment
		);
	}

	/**
	 * Returns the comment for orders_status.comment (Database).
	 * E.g. the comment from the customer or magnalister related information.
	 * May differ from self::generateOrderComment()
	 * Use $this->o['order'].
	 *
	 * @return String	The comment for the order.
	 */
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment();
	}

	/**
	 * Returs the shipping method for the current order.
	 * @return string
	 */
	protected function getShippingMethod() {
		if ($this->config['ShippingMethod'] == 'matching') {
			return $this->o['order']['shipping_code'];
		}
		return $this->config['ShippingMethod'];
	}

	/**
	 * Returs the payment method for the current order.
	 * @return string
	 */
	protected function getPaymentMethod() {
		// There is no matching. We simply return what hood submitted to us as payment method.
		if ($this->config['PaymentMethod'] == 'matching') {
			return $this->o['order']['payment_code'];
		}
		return $this->config['PaymentMethod'];
	}

	/**
	 * In child classes this method can be used to extend the data for the DB-table
	 * magnalister_orders before it is inserted.
	 *
	 * @return Array	Associative array that will be stored serialized
	 * 					in magnalister_orders.internaldata (Database)
	 */
	protected function doBeforeInsertMagnaOrder() {
		$sDay = '';

		$this->o['orderInfo']['ShippingTimeMin'] = (int)$this->o['orderInfo']['ShippingTimeMin'];
		$this->o['orderInfo']['ShippingTimeMax'] = (int)$this->o['orderInfo']['ShippingTimeMax'];

		if ($this->o['orderInfo']['ShippingTimeMax'] > 0) {
			if (   ($this->o['orderInfo']['ShippingTimeMin'] > 0)
				&& ($this->o['orderInfo']['ShippingTimeMin'] != $this->o['orderInfo']['ShippingTimeMax'])
			) {
				$sDay = $this->o['orderInfo']['ShippingTimeMin'].' - ';
			}

			$sDay .= $this->o['orderInfo']['ShippingTimeMax'];
		}

		if (!empty($sDay)) {
			$this->o['magnaOrders']['ML_LABEL_MARKETPLACE_SHIPPING_TIME'] = sprintf(
				ML_LABEL_MARKETPLACE_SHIPPING_TIME_VALUE, $sDay
			);
		}

		return array();
	}

	/**
	 * Converts the tax value to an ID
	 *
	 * @parameter mixed $tax	Something that represents a tax value
	 * @return float			The actual tax value
	 * @TODO: Save the ID2Tax Array somewhere more globally or ask the allmigty API for it.
	 */
	protected function getTaxValue($tax) {
		if ($tax < 0) {
			return (float)$this->config['MwStFallback'];
		}

		return $tax;
	}
}
