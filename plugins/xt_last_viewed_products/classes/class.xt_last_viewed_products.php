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

/**
 * Class for Last viewed Products of Customers on SESSION
 * 
 * */
class last_viewed_products {
	
	/**
	 * get List of Products from $_SESSION['last_viewed_products']
	 *
	 * @param int $products_id
	 * @return bool
	 */	
	public function getLastViewedProductListing () 
	{
		if(is_array($_SESSION['last_viewed_products']))
		{
			$last_viewed_products_reverse = array_reverse($_SESSION['last_viewed_products']);
			$count = count($last_viewed_products_reverse);
			for ($i = 0; $i < $count;$i++) {
				$size = 'default';
				$product = new product($last_viewed_products_reverse[$i],$size);
				if ($product->is_product) {
					$module_content[] = $product->data;
				}			
			}
		}
		else 
		{
			$module_content = array();
		}
		return $module_content; 
	}

	/**
	 * add products_id into $_SESSION['last_viewed_products']
	 *
	 * @param int $products_id
	 * @return bool
	 */	
	public function _addLastViewedProduct($products_id)
	{
		global $xtPlugin, $xtLink, $db;  
		
		if(!empty($products_id) )
		{
			if(!empty($_SESSION['last_viewed_products']))
			{
				//check last viewed products in SESSION < XT_LAST_VIEWED_PRODUCTS_MAX (in xml)
				if(count($_SESSION['last_viewed_products']) < XT_LAST_VIEWED_PRODUCTS_MAX){
					//check exists in session
					
					if(in_array($products_id,$_SESSION['last_viewed_products']))
					{
						$key = array_search($products_id,$_SESSION['last_viewed_products']);
						unset($_SESSION['last_viewed_products'][$key]);
						$_SESSION['last_viewed_products'] = array_values($_SESSION['last_viewed_products']);
					}
					$_SESSION['last_viewed_products'][] = $products_id;
					return true;
				} 
				else
				{
					if(in_array($products_id,$_SESSION['last_viewed_products']))
					{
						$key = array_search($products_id,$_SESSION['last_viewed_products']);
						unset($_SESSION['last_viewed_products'][$key]);
						$_SESSION['last_viewed_products'] = array_values($_SESSION['last_viewed_products']);
					}
					$a_pop = array_shift($_SESSION['last_viewed_products']);
					$_SESSION['last_viewed_products'][] = $products_id;
					return true;
				}
			}
			else 
			{
				$_SESSION['last_viewed_products'][] = $products_id;
				return true;
			}
		}
		return false;
	}
}