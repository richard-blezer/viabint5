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

global $emos,$econda;

echo "\n<!-- Econda-Monitor -->\n";


// add breadcrump
$emos->addContent($econda->_getBreadCrump());
// global watchers
if (isset($_SESSION['econda']['login'])) { // LOGIN TRACKER
	if ($_SESSION['econda']['login']==0) { // success login
		$emos->addLogin($_SESSION['customer']->customers_id,0);
	} else {
		$emos->addLogin(time(),$_SESSION['econda']['login']);
	}
	
	unset($_SESSION['econda']['login']);
}

if (isset($_SESSION['econda']['register'])) { // Register TRACKER
	if ($_SESSION['econda']['register']==1) { // success register
		$emos->addRegister($_SESSION['customer']->customers_id,0);
	} 
	unset($_SESSION['econda']['register']);
}

// switch pages
global $page;
switch ($page->page_name) {

	case 'product': // product page
		
		// product view
		if (!isset($_POST['add_product'])) {
			$item = $econda->_actionProductView();
			$emos->addDetailView($item);
		} 
		break;

	case 'content':
		global $shop_content_data;
		if ($shop_content_data['content_hook'] == '5') {
			$emos->addContact($shop_content_data['content_title']);
		} else {
			if ($shop_content_data['econda_tracking']!='') {
				$emos->addContent($shop_content_data['econda_tracking']);
			}
		}
		break;

	case 'cart':
		$emos->addOrderProcess("1_Warenkorb");
		if (isset($_SESSION['econda_add_cart_qty']) && isset($_SESSION['econda_add_cart_id'])) {
			$item = $econda->product_to_EMOSItem($_SESSION['econda_add_cart_id'],$_SESSION['econda_add_cart_qty']);
			$emos->addToBasket($item);
			unset($_SESSION['econda_add_cart_qty']);
			unset($_SESSION['econda_add_cart_id']);
		}
		if (isset($_SESSION['econda_rmv_cart'])) {
			
			foreach ($_SESSION['econda_rmv_cart'] as $key => $val) {
				$item = $econda->product_to_EMOSItem($val['products_id'],$val['qty']);
				$emos->removeFromBasket($item);
			}
			unset($_SESSION['econda_rmv_cart']);
		}
		if (isset($_SESSION['econda_upd_cart'])) {
			foreach ($_SESSION['econda_upd_cart'] as $key => $val) {
				$item = $econda->product_to_EMOSItem($val['products_id'],$val['qty']);
				if ($val['type']=='rmv') {
					$emos->removeFromBasket($item);
				} else {
					$emos->addToBasket($item);
				}
			}
			unset($_SESSION['econda_upd_cart']);
		}
		
		break;
		
	case 'search': // search result
		global $search,$filter;
		$emos->addSearch($filter->_filter($_GET['keywords']),count($search->search_data));
		break;

	case 'customer':
		
		switch ($page->page_action) {
			case 'login':
				$emos->addOrderProcess("2_Login");
				break;
		}
		
		break;

	case 'checkout': // Checkoutvorgang

		switch ($page->page_action) {
			case 'shipping':
				$emos->addOrderProcess("3_Versand");
				break;
			case 'payment':
				$emos->addOrderProcess("4_Zahlung");
				break;
			case 'confirmation':
				$emos->addOrderProcess("5_Bestaetigung");
				break;
			case 'success':
				$emos->addOrderProcess("6_Erfolg");

				// cart für econda

				global $success_order;
				// billing
				$emos->addEmosBillingPageArray($success_order->order_data['orders_id'],
				$success_order->order_data['customers_id'],
				sprintf("%0.2f",$success_order->order_total['total']['plain']),
				$success_order->order_data['delivery_country_code'],
				$success_order->order_data['delivery_postcode'],
				$success_order->order_data['delivery_city']);
				// track cart
				$basket = array();
				if (is_object($success_order)) {
				foreach ($success_order->order_products as $key => $val) {
					
					$_item = $econda->product_to_EMOSItem($val['products_id'],1);
	
					$item = new EMOS_Item();
					$item->productID = $val['products_id'];
					$item->productName = $val['products_name'];
					$item->price = $val['products_final']['plain'];
					$item->productGroup = $_item->productGroup;
					$item->quantity = (int)$val['products_quantity'];
					$basket[]=$item;
						
				}
				$emos->addEmosBasketPageArray($basket);
				}
				break;
		}

		break;

}



echo $emos->toString();

echo "\n<!-- Econda-Monitor -->\n";
?>