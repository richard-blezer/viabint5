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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');

class RicardoSyncInventory extends MagnaCompatibleSyncInventory {
	protected function updateQuantity() {
		if (!$this->syncStock) return false;

		$data = false;
		$curQty = $this->calcNewQuantity();

		if (!isset($this->cItem['Quantity'])) {
			$this->cItem['Quantity'] = 0;
		}

		if (isset($this->cItem['Quantity']) && (int)$curQty < (int)$this->cItem['Quantity']) {
			$data = array (
				'Mode' => 'SET',
				'Value' => (int)$curQty
			);
			$this->log("\n\t".
				'Quantity changed (old: '.$this->cItem['Quantity'].'; new: '.$curQty.')'
			);

		} else {
			$this->log("\n\t".
				'Quantity not changed ('.$curQty.')'
			);
		}
		return $data;
	}

	function updatePrice() {
		if (!$this->syncPrice) return false;

		$data = false;

		$price = $this->simplePrice
			->setPriceFromDB($this->cItem['pID'], $this->mpID)
			->finalizePrice($this->cItem['pID'], $this->mpID)
			->getPrice();

		if ($price > 0 && (float)$price < (float)$this->cItem['Price']) {
			$this->log("\n\t".
				'Price changed (old: '.$this->cItem['Price'].'; new: '.$price.')'
			);
			$data = $price;
		} else {
			$this->log("\n\t".
				'Price not changed ('.$price.')'
			);
		}
		return $data;
	}
}
