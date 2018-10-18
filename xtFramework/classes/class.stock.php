<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class stock {

	/**
	 * add stock to products
	 *
	 * @param int $products_id
	 * @param int $qty
	 */
	public function addStock($products_id, $qty)
	{
		global $db,$xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.stock.php:_addStock')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		$products_id = (int)$products_id;
		if (_SYSTEM_STOCK_HANDLING === 'true')
		{
			$db->Execute(
				'UPDATE '.TABLE_PRODUCTS.' SET products_quantity = products_quantity+'.(int)$qty.' WHERE products_id = ?',
				array($products_id)
			);
		}
	}

	/**
	 * set stock to given amount
	 *
	 * @param mixed $products_id products id
	 * @param mixed $qty stock
	 */
	public function setStock($products_id, $qty)
	{
		global $db;
		$db->Execute('UPDATE '.TABLE_PRODUCTS.' SET products_quantity = ?', array((int)$qty));
	}

	/**
	 * remove stock from product
	 *
	 * @param int $products_id
	 * @param int $qty
	 */
	public function removeStock($products_id, $qty)
	{
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.stock.php:_removeStock')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		$products_id = (int)$products_id;
		if (_SYSTEM_STOCK_HANDLING === 'true')
		{
			$db->Execute('UPDATE '.TABLE_PRODUCTS.' SET products_quantity = products_quantity-'.(int) $qty.' WHERE products_id = ?', array($products_id));
		}
	}

	/**
	 * check if qty is > than current stock
	 *
	 * @param object $cart_product
	 * @param int $qty
	 * @param boolean $add_session
	 * @return int/boolean
	 */
	public function stockCheck(&$cart_product, $qty, $add_session = true)
	{
		global $info, $xtPlugin, $db, $language, $order_edit_controller;

		if ($order_edit_controller->isActive() && _SYSTEM_ORDER_EDIT_ALLOW_NEGATIVE_STOCK === 'true')
		{
			return $qty;
		}

		($plugin_code = $xtPlugin->PluginCode('class.stock.php:_stockCheck')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		if (_STORE_STOCK_CHECK_BUY === 'false' && ! empty($cart_product->qty))
		{

			$record = $db->Execute('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_status = 1 AND products_id = ?', array((int) $cart_product->pID));
			$current_stock = ($record->RecordCount() == 1)
				? $record->fields['products_quantity']
				: 0;

			// negative quantity in stock
			if ($current_stock < 0)
			{
				if (_STORE_STOCK_CHECK_BUY === 'false')
				{
					$current_stock = 0;
				} else {
					return $qty;
				}
			}

			if ( ! empty($cart_product->data['products_name']))
			{
				$pname = $cart_product->data['products_name'];
			} else {
				$record = $db->Execute(
					"SELECT products_name FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE products_id = ? AND language_code = ?",
					array((int)$cart_product->pID, $language->code)
				);
				$pname = ($record->RecordCount() == 1)
					? $record->fields['products_name']
					: '';
			}

			if (empty($current_stock))
			{
				if ($add_session)
				{
					$info->_addInfoSession(sprintf(ERROR_STOCK_REDUCED_AMOUNT, $pname, $qty), 'info');
				} else {
					$info->_addInfo(sprintf(ERROR_STOCK_REDUCED_AMOUNT, $pname, $qty), 'info');
				}

				return $current_stock;
			}

			if ($qty > $current_stock)
			{
				$stock_reduced = $qty - $current_stock;
				$qty = $current_stock;
				if ($add_session)
				{
					$info->_addInfoSession(sprintf(ERROR_STOCK_REDUCED_AMOUNT, $pname, $stock_reduced), 'info');
				} else {
					$info->_addInfo(sprintf(ERROR_STOCK_REDUCED_AMOUNT, $pname, $stock_reduced), 'info');
				}

				return round($qty);
			}
		}

		return $qty;
	}

	public function makeReservation($products_id, $qty, $customer, $order_id)
	{
		global $db;
	}

	public function clearReservation($orderid = '')
	{
		global $db;
	}
}