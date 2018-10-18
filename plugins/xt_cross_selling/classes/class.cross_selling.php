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


class cross_selling extends products_list {


	/**
	 * add cross selling entry
	 *
	 * @param int $products_id
	 * @param int $cross_sell_id
	 */
	function _addCrossSell($products_id,$cross_sell_id) {
		global $db;

		$insert_data = array();
		$insert_data['products_id']=(int)$products_id;
		$insert_data['products_id_cross_sell'] = (int)$cross_sell_id;
		$db->AutoExecute(TABLE_PRODUCTS_CROSS_SELL,$insert_data,'INSERT');

	}

	/**
	 * add cross selling for whole category
	 *
	 * @param int $categories_id
	 * @param int $cross_sell_id
	 */
	function _addCrossSellBatch($categories_id,$cross_sell_id) {
		global $db;


	}

	/**
	 * Get list with cross selling products
	 *
	 * @param int $products_id
	 * @return array
	 */
	function getCrossSellingProductListing($products_id) {
		global $xtPlugin, $xtLink, $db;


		$products_id = (int)$products_id;
		if ($products_id=='') return false;

        ($plugin_code = $xtPlugin->PluginCode('class.cross_selling.php:getCrossSellingProductListing')) ? eval($plugin_code) : false;
        
		$this->sql_products->setPosition('cross_selling');
		$this->sql_products->setSQL_TABLE("INNER JOIN " . TABLE_PRODUCTS_CROSS_SELL . " pc ON p.products_id = pc.products_id_cross_sell");
		$this->sql_products->setSQL_WHERE("and pc.products_id =?");
		$this->sql_products->setSQL_WHERE("and pc.products_id_cross_sell=p.products_id");
		$query = $this->sql_products->getSQL_query();

		$rs = $db->CacheExecute($query,array((int)$products_id));

		if ($rs->RecordCount()==0) return false;

		$cross_sell_products = array();
		while (!$rs->EOF) {
			$cross_sell_products[] = $rs->fields['products_id'];
			$rs->MoveNext();
		}

		// shuffle if more than max
		if (XT_CROSS_SELLING_MAX_DISPLAY<$rs->RecordCount()) {
			shuffle($cross_sell_products);
			$cross_sell_products = array_slice($cross_sell_products, 0,XT_CROSS_SELLING_MAX_DISPLAY);
		}

		$module_content = array();
		foreach ($cross_sell_products as $key => $val) {
			$size = 'default';
			$product = new product($val,$size);
			$module_content[] = $product->data;
		}
		return $module_content;
	}

	function _display($products_id) {
		global $xtPlugin, $xtLink, $db;


		$products_id = (int)$products_id;
		if ($products_id=='') return false;

		$module_content = $this->getCrossSellingProductListing($products_id);

		if (!$module_content) return false;
		$tpl_data = array('_cross_selling'=>$module_content);

		$tmp_data = '';
		$tpl = 'cross_selling.html';
		$template = new Template();
		$template->getTemplatePath($tpl, 'xt_cross_selling', '', 'plugin');

		$tmp_data = $template->getTemplate('xt_cross_selling_smarty', $tpl, $tpl_data);
		return $tmp_data;

	}

}
?>