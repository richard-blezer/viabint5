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

class shopgate_config_helper {
	public static function getConfig($storeId = null) {
		$shopNumber = "";
		
		global $store_handler;

		if(empty($storeId)) {
			$storeId = $store_handler->shop_id;
			
			if(isset($_REQUEST["shopgate"]) && $_REQUEST["shopgate"] == "shopgate"
			&& isset($_REQUEST["shop_number"]) && $_REQUEST["shop_number"] != "")
				$storeId = shopgate_config_helper::getStoreId($_REQUEST["shop_number"]);
			if(empty($storeId)) return null;
		}
		
		$_config = shopgate_config_helper::getStoreConfig($storeId);
		$config = array(
			'apikey' => $_config["XT_SHOPGATE_APIKEY"],
			'customer_number' => $_config["XT_SHOPGATE_CUSTOMERNUMBER"],
			'shop_number' => $_config["XT_SHOPGATE_SHOPNUMBER"],
			'server' => 'live',
			'plugin' => 'veyton',
			'plugin_root_dir' => _SRV_WEBROOT,
			'enable_ping' => true, 
			'enable_get_shop_info' => true,
  			'enable_http_alert' => $_config["XT_SHOPGATE_ENABLE_HTTPALERT"]=='true',
  			'enable_connect' => $_config["XT_SHOPGATE_ENABLE_CONNECT"]=='true',
			'enable_get_reviews_csv' => $_config["XT_SHOPGATE_ENABLE_REVIEWS_CSV"]=='true',
			'enable_get_items_csv' => true,
			'enable_get_pages_csv' => false,
			'enable_get_log_file' => true,
			'enable_app_redirect' => $_config["XT_SHOPGATE_ENABLE_APPREDIRECT"]=='true',
			'generate_items_csv_on_the_fly' => $_config["XT_SHOPGATE_ENABLE_CSV_ONTHEFLY"]=='live',
			'plugin_language' => 'DE',
			'plugin_currency' => 'EUR',
			'plugin_shop_id' => $storeId,
			'plugin_shop_is_active' => $_config["XT_SHOPGATE_SHOP_IS_ACTIVE"]=='true',
			'plugin_order_complete_id' => $_config["XT_SHOPGATE_SHIPPING_APPROVED"],
			'plugin_order_import_status' => $_config["XT_SHOPGATE_IMPORT_STATUS"],
			'plugin_show_mobile_header' => $_config["XT_SHOPGATE_ENABLE_APPREDIRECT"] == 'true',
			'plugin_use_stock' => $_config["XT_SHOPGATE_USE_STOCK"] == 'true',
		);
		
		return $config;
	}
	
	private function getStoreId($shopNumber) {
		global $db;
		
		$qry = "
			SELECT pc.shop_id 
			FROM ".TABLE_PLUGIN_CONFIGURATION." pc
			JOIN ".TABLE_PLUGIN_PRODUCTS." pp ON pp.plugin_id = pc.plugin_id
			WHERE pp.code = 'xt_shopgate'
			  AND pc.config_key = 'XT_SHOPGATE_SHOPNUMBER'
			  AND pc.config_value = '$shopNumber'
		";
		
		return $db->GetOne($qry);
	}
	
	private function getStoreConfig($shopId) {
		global $db;
		
		$qry = "
			SELECT pc.* 
			FROM ".TABLE_PLUGIN_CONFIGURATION." pc
			JOIN ".TABLE_PLUGIN_PRODUCTS." pp ON pp.plugin_id = pc.plugin_id
			WHERE pp.code = 'xt_shopgate'
			  AND pc.shop_id = '$shopId'
		";
		
		$result = $db->Execute($qry);
		
		$config = array();
		while(!$result->EOF) {
			$row = $result->fields;
			$config[$row["config_key"]] = $row["config_value"];
			
			$result->MoveNext();
		}
		
		return $config;
	}
}