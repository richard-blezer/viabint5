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


class econda {



	/**
	 * create new EMOS_Item Object for given products_id
	 *
	 * @param int $products_id
	 * @param int $qty
	 * @return EMOS_Item
	 */
	function product_to_EMOSItem($products_id,$qty) {

		$p_info = new product($products_id,'full', $qty, '', 'product_info');

		$item = new EMOS_Item();
		$item->productID = $p_info->data['products_id'];
		$item->productName = $p_info->data['products_name'];
		$item->price = number_format($p_info->data['products_price']['plain'], 2, '.', '');
		$item->productGroup = $this->_getCategorieName($products_id);
		$item->quantity = (int)$qty;
		return $item;

	}

	private function _getCategorieName($pID) {
		global $db,$language;

		$rs=$db->Execute("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE master_link=1 and products_id='".(int)$pID."'");
		if ($rs->RecordCount()==1) {
			$cat = new category($rs->fields['categories_id']);
			return $cat->data['url_text'];	
		}
	}

	/**
	 * VIEW product
	 *
	 * @return EMOS_Item
	 */
	function _actionProductView() {
		global $p_info,$category;

		$item = new EMOS_Item();
		$item->productID = $p_info->data['products_id'];
		$item->productName = $p_info->data['products_name'];
		$item->price = number_format($p_info->data['products_price']['plain'], 2, '.', '');
		$item->productGroup = $category->data['categories_name'];
		$item->quantity = 1;
		return $item;
	}

	/**
	 * format breadrumb
	 *
	 * @return string
	 */
	function _getBreadCrump() {
		global $brotkrumen;
		$content = '';
		if (is_array($brotkrumen->krumen)) {
			$i=0;
			foreach ($brotkrumen->krumen as $key => $val) {

				if ($i>0) $content.='/';
				$content.=htmlspecialchars($val['name']);  
				$i++;
			}
				
		}
		return $content;
	}

}
?>