<?php
/*
 #########################################################################
 #                       Shogate GmbH
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # http://www.shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Rev: 589 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');
include_once 'class.log.php';

class shopgate_api {
	private $data = array();
	
	function __construct($data = array()) {
		if(!defined("DB_PREFIX") || DB_PREFIX==''){
			define('DB_PREFIX','xt');
		}
		
		define('TABLE_SHOPGATE_ORDERS', DB_PREFIX.'_shopgate_orders');
		
		$this->data = $data;
		
		require_once('class.shopgate_config_helper.php');
		$shopgate_config = shopgate_config_helper::getConfig();
		
		try {
			require_once _SRV_WEBROOT."/plugins/xt_shopgate/framework/lib/framework.php";
			ShopgateConfig::setConfig($shopgate_config, false);
		} catch (ShopgateFrameworkException $e) {
			log::error($e->getMessage());
		}
	}
	
	function start($data) {
		$this->data = $data;
		$action = $data["action"];

		switch($action) {
			case "get_items_csv":
			case "get_reviews_csv":
			case "get_log_file":
			case "http_alert":
			case "ping":
			case "get_shop_info":
			case "connect":
				$this->init_framework();
				break;
			case "update_items_csv":
				$config = ShopgateConfig::validateAndReturnConfig();
				$Plugin = ShopgatePluginCore::newInstance($config);
				$Plugin->startCreateItemsCsv();
				break;
		}
	}
	
	function init_framework() {
		try {
			$ShopgateFramework = new ShopgateFramework();
			$ShopgateFramework->start();
		} catch (Exception $e) {
			log::error($e->getMessage());
		}
	}
	
	function setOrderStatus($data) { 
		try {
			global $db;
			
			require_once('class.shopgate_config_helper.php');
			$storeId = $db->GetOne("SELECT `shop_id` FROM `".TABLE_ORDERS."` WHERE `orders_id` = '$data[orders_id]'");
			$shopgate_config = shopgate_config_helper::getConfig($storeId);
			$shippingCompleteId = 33;
			if(!empty($shopgate_config["plugin_order_complete_id"])
			&& $_orderShippingApprovedStatusId = $shopgate_config["plugin_order_complete_id"]) {
				$_orderShippingApprovedStatusId = $db->GetOne("SELECT status_id FROM ".TABLE_SYSTEM_STATUS." WHERE status_class = 'order_status' AND status_id = $_orderShippingApprovedStatusId");
				if(!empty($_orderShippingApprovedStatusId)) $shippingCompleteId = $_orderShippingApprovedStatusId;
			}
			
			if($data["orders_status_id"] == $shippingCompleteId) {
				$sgOrder = $this->getOrderShopgateNumber($data["orders_id"]);
				
				if(empty($sgOrder))
					return false;
					
				$order = new ShopgateOrder();
				$order->setOrderNumber($sgOrder["shopgate_order_number"]);
				
				ShopgateConfig::setConfig($shopgate_config);

				try {
					$orderApi = new ShopgateOrderApi();
					$orderApi->setShippingComplete($order);
					
					$statusArr = array();
					$statusArr['orders_id'] = $data["orders_id"];
					$statusArr['orders_status_id'] = $data["orders_status_id"];
					$statusArr['customer_notified'] = true;
					$statusArr['date_added'] = date("Y-m-d h:i:s");
					$statusArr['comments'] = 'Bestellung wurde bei Shopgate als versendet markiert';
					$statusArr['change_trigger'] = 'shopgate';
					$statusArr['callback_id'] = '0';
					$statusArr['customer_show_comment'] = true;
					
					$valueString = "";
					$keyString = "";
					foreach($statusArr as $key=>$value) {
						if(!empty($keyString)) $keyString.=",";
						if(!empty($valueString)) $valueString.=",";
						$keyString.="`".$key."`";
						$valueString.="'".$value."'";
					}
					
					$qry = "INSERT INTO ".TABLE_ORDERS_STATUS_HISTORY."\n ($keyString)\n VALUES\n ($valueString)";
					$db->Execute($qry);
				} catch (ShopgateFrameworkException $e) {
					$statusArr = array();
					$statusArr['orders_id'] = $data["orders_id"];
					$statusArr['orders_status_id'] = $data["orders_status_id"];
					$statusArr['customer_notified'] = false;
					$statusArr['date_added'] = date("Y-m-d h:i:s");
					$statusArr['comments'] = 'Ein fehler ist aufgetreten: ' . $e->lastResponse["error_text"];
					$statusArr['change_trigger'] = 'shopgate';
					$statusArr['callback_id'] = '0';
					$statusArr['customer_show_comment'] = false;
					
					$valueString = "";
					$keyString = "";
					foreach($statusArr as $key=>$value) {
						if(!empty($keyString)) $keyString.=",";
						if(!empty($valueString)) $valueString.=",";
						$keyString.="`".$key."`";
						$valueString.="'".$value."'";
					}
						
					$qry = "INSERT INTO ".TABLE_ORDERS_STATUS_HISTORY."\n ($keyString)\n VALUES\n ($valueString)";
					$db->Execute($qry);
				}
				
				
			}
		} catch (Exception $e) {
			log::error($e->getMessage());
		}
	}
	
	function getOrderShopgateNumber($ordersId) {
		global $db;
		
		$qry = "
			SELECT * FROM ".TABLE_SHOPGATE_ORDERS." WHERE orders_id = $ordersId LIMIT 1;
		";
		$result = $db->Execute($qry);
		
		return $result->fields;
	}
	
	public function updateProductQuantities() {
		try {
			global $db;
			$products = $this->data["data"];
			
			$items = array();
			
			foreach($products as $product) {
				$article_number = $product['products_model'];
				$quantity = $product['products_quantity'];
	
				
				$qry = "SELECT products_quantity FROM ".TABLE_PRODUCTS." WHERE products_id = ".$product["products_id"];
	
				$result = $db->Execute($qry);
				
				if(empty($result->fields))
					continue;
					
				$quantity = (-1*$quantity) + $result->fields["products_quantity"];
				
				$item = new ShopgateShopItem();
				$item->setItemNumber($article_number);
				$item->setStockQuantity($quantity);
				
				$items[] = $item;
			}
			
			
			$itemApi = new ShopgateItemApi();
			$itemApi->updateMultipleItems($items);
		} catch (Exception $e) {
			log::error($e->getMessage());
		}
	}
	
	
	public function updateProductQuantity() {
		try {
			$article_number = $this->data["data"]['products_model'];
			$quantity = $this->data["data"]['products_quantity'];
			
			$item = new ShopgateShopItem();
			$item->setItemNumber($article_number);
			$item->setStockQuantity($quantity);
			
			$itemApi = new ShopgateItemApi();
			$itemApi->updateItem($article_number, $item);
		} catch (Exception $e) {
			log::error($e->getMessage());
		}
	}
}
?>
