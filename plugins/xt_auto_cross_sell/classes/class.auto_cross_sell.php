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

class auto_cross_sell extends products_list {


	/**
	 * Get list with cross selling products(based on previous orders)
	 *
	 * @param int $products_id
	 * @return array
	 */
	function getAutoCrossSellProductListing($products_id='') {
		global $xtPlugin, $xtLink, $db;

		$products_id = (int)$products_id;        
		if ($products_id=='') return false;

		$query = "SELECT bop.products_id FROM ".TABLE_ORDERS_PRODUCTS." aop, ".TABLE_ORDERS_PRODUCTS." bop WHERE aop.products_id=? and aop.products_id!=bop.products_id and aop.orders_id = bop.orders_id group by bop.products_id";
		$rs = $db->CacheExecute($query,array((int)$products_id));
		if ($rs->RecordCount()==0) return false;

		$products = array();
		while (!$rs->EOF) {
			$products[] = $rs->fields['products_id'];
			$rs->MoveNext();
		}

		// shuffle if more than max
		if (XT_ALSO_PURCHASED_MAX_DISPLAY<$rs->RecordCount()) {
			shuffle($products);
			$products = array_slice($products, 0,XT_AUTO_CROSS_SELL_MAX_DISPLAY);
		}

		$module_content = array();
		foreach ($products as $key => $val) {
			$size = 'default';
			$product = new product($val,$size);
            if ($product->is_product)
			    $module_content[] = $product->data;
		}
		return $module_content;
	}
    
    /**
    * auto cross selling for shopping cart display
    * 
    */
    function getAutoCrossSellProductListingCart() {
        global $xtPlugin, $xtLink, $db;

        if (count($_SESSION['cart']->content)<1) return false;
        
        $ids = array();
        foreach ($_SESSION['cart']->content as $key => $val) {
        	if($val['products_id']!='')
            $ids[]=(int)$val['products_id'];
        }
        
        $query = "SELECT bop.products_id FROM ".TABLE_ORDERS_PRODUCTS." aop, ".TABLE_ORDERS_PRODUCTS." bop 
                WHERE aop.products_id IN (?) and aop.products_id!=bop.products_id and aop.orders_id = bop.orders_id group by bop.products_id";
        $rs = $db->CacheExecute($query,array(implode(',',$ids)));

        if ($rs->RecordCount()==0) return false;

        $products = array();
        while (!$rs->EOF) {
            if (!in_array($rs->fields['products_id'],$ids)) $products[] = $rs->fields['products_id'];
            $rs->MoveNext();
        }

        // shuffle if more than max
        if (XT_ALSO_PURCHASED_MAX_DISPLAY<$rs->RecordCount()) {
            shuffle($products);
            $products = array_slice($products, 0,XT_AUTO_CROSS_SELL_MAX_DISPLAY);
        }

        $module_content = array();
        foreach ($products as $key => $val) {
            $size = 'default';
            $product = new product($val,$size);
            if ($product->is_product)
                $module_content[] = $product->data;
        }
        return $module_content;
    }

	function _display($products_id='',$cart=false) {
		global $xtPlugin, $xtLink, $db;
        if (!$cart) {
            $products_id = (int)$products_id;
            if ($products_id=='') return false;
            $module_content = $this->getAutoCrossSellProductListing($products_id);
        } else {
            $module_content = $this->getAutoCrossSellProductListingCart();
        }
		if (!$module_content) return false;
		$tpl_data = array('_auto_cross_sell'=>$module_content);

		$tmp_data = '';
		$tpl = 'auto_cross_sell.html';
		$template = new Template();
		$template->getTemplatePath($tpl, 'xt_auto_cross_sell', '', 'plugin');

		$tmp_data = $template->getTemplate('xt_auto_cross_sell_smarty', $tpl, $tpl_data);
		return $tmp_data;

	}

}
?>
