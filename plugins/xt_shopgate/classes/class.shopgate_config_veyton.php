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
 # @version $Rev: 78 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */
//defined('_VALID_CALL') or die('Direct Access is not allowed.');
include_once dirname(__FILE__).'/../shopgate_library/shopgate.php';

class ShopgateConfigVeyton extends ShopgateConfig {
	
	const EXPORT_DESCRIPTION = 0;
	const EXPORT_SHORTDESCRIPTION = 1;
	const EXPORT_DESCRIPTION_SHORTDESCRIPTION = 2;
	const EXPORT_SHORTDESCRIPTION_DESCRIPTION = 3;
	
	protected $export_description_type;
	protected $order_status_open;
	protected $order_status_shipped;
	protected $order_status_shipping_blocked;
	protected $order_status_cancled;
	protected $plugin_shop_id;
	
	public function startup(){
		
		// overwrite some library defaults
		$this->plugin_name = 'Veyton';
		$this->enable_redirect_keyword_update = 24;
		$this->enable_ping = 1;
		$this->enable_add_order = 1;
		$this->enable_update_order = 1;
		$this->enable_get_orders = 0;
		$this->enable_get_customer = 1;
		$this->enable_get_items_csv = 1;
		$this->enable_get_categories_csv = 1;
		$this->enable_get_reviews_csv = 1;
		$this->enable_get_pages_csv = 0;
		$this->enable_get_log_file = 1;
		$this->enable_mobile_website = 1;
		$this->enable_cron = 1;
		$this->enable_clear_logfile = 1;
		$this->encoding = 'UTF-8';
		
		// set Veyton specific file paths
		$this->export_folder_path = _SRV_WEBROOT._SRV_WEB_EXPORT;
		$this->log_folder_path = _SRV_WEBROOT._SRV_WEB_LOG;
		$this->cache_folder_path = _SRV_WEBROOT._SRV_WEB_PLUGIN_CACHE;
		
		// initialize plugin specific stuff
		$this->export_description_type = ShopgateConfigVeyton::EXPORT_DESCRIPTION;
		$this->order_status_open = 16;
		$this->order_status_shipped = 33;
		$this->order_status_cancled = 34;
		
	}
	
	public function loadConfigFromDatabase($storeId = null) {
		$shopNumber = "";

		global $store_handler, $db, $language, $price, $currency, $product;

		if(empty($storeId)) {
			$storeId = $store_handler->shop_id;

			if(isset($_REQUEST["shopgate"]) && $_REQUEST["shopgate"] == "shopgate"
			&& isset($_REQUEST["shop_number"]) && $_REQUEST["shop_number"] != "")
				$storeId = $this->getStoreId($_REQUEST["shop_number"]);
			if(empty($storeId)) return null;
		}

		$_config = $this->getStoreConfig($storeId);

		$config = array(
			'apikey' => trim($_config["XT_SHOPGATE_APIKEY"]),
			'customer_number' => $_config["XT_SHOPGATE_CUSTOMERNUMBER"],
			'shop_number' => $_config["XT_SHOPGATE_SHOPNUMBER"],
			'alias' => $_config['XT_SHOPGATE_ALIAS'],
			'cname' => $_config['XT_SHOPGATE_CNAME'],
			'server' => $_config["XT_SHOPGATE_SERVER"],
			'plugin_name' => 'veyton',
			'enable_ping' => true,
			'enable_get_shop_info' => true,
  			'enable_add_order' => true,
			'enable_update_order' => true,
			'enable_get_orders' => false,
  			'enable_get_customer' => true,
			'enable_get_reviews_csv' => true,
			'enable_get_items_csv' => true,
			'enable_get_pages_csv' => false,
			'enable_get_log_file' => true,
			'enable_mobile_website' => true,
			'plugin_shop_id' => $storeId,
			'shop_is_active' => true,
			'export_description_type' => $_config["XT_SHOPGATE_EXPORT_DESCRIPTION_TYPE"],
			'order_status_open' => $_config["XT_SHOPGATE_ORDER_STATUS_OPEN"],
			'order_status_shipping_blocked' => $_config["XT_SHOPGATE_ORDER_STATUS_SHIPPING_BLOCKED"],
			'order_status_cancled' => $_config["XT_SHOPGATE_ORDER_STATUS_CANCLED"],
			'order_status_shipped' => $_config["XT_SHOPGATE_ORDER_STATUS_SHIPPED"],
// 			'plugin_order_import_status' => $_config["XT_SHOPGATE_IMPORT_STATUS"],
			'api_url' => $_config["XT_SHOPGATE_SERVER_URL"],
			'items_csv_filename' => "shopgate_items_{$storeId}.csv",
			'categories_csv_filename' => "shopgate_categories_{$storeId}.csv",
			'reviews_csv_filename' => "shopgate_reviews_{$storeId}.csv",
			'pages_csv_filename' => "shopgate_reviews_{$storeId}.csv",
			'error_log_filename' => "shopgate_error_{$storeId}.log",
			'access_log_filename' => "shopgate_access_{$storeId}.log",
			'request_log_filename' => "shopgate_request_{$storeId}.log",
			'debug_log_filename' => "shopgate_debug_{$storeId}.log",
			'redirect_keyword_cache_filename' => "shopgate_redirect_keywords_{$storeId}.txt",
			'redirect_skip_keyword_cache_filename' => "shopgate_skip_redirect_keywords_{$storeId}.txt",
			'always_use_ssl' => 0,
		);
		
		if (file_exists(SHOPGATE_BASE_DIR.DS.'config'.DS.'devconfig.php')) {
			require SHOPGATE_BASE_DIR.DS.'config'.DS.'devconfig.php';
			$config = array_merge($config, $_shopgate_config);
		}

		try {
			$this->loadArray($config);
		} catch(Exception $e) {
			$this->log("CONFIG: \n". print_r($config, true));
		}

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

	public function getExportDescriptionType() {
		return $this->export_description_type;
	}
	
	public function getOrderStatusOpen() {
		return $this->order_status_open;
	}
	
	public function getOrderStatusShipped() {
		return $this->order_status_shipped;
	}
	
	public function getOrderStatusShippingBlocked() {
		return $this->order_status_shipping_blocked;
	}
	
	public function getOrderStatusCancled() {
		return $this->order_status_cancled;
	}
	
	public function getPluginShopId() {
		return $this->plugin_shop_id;
	}
	
	public function setExportDescriptionType($value) {
		$this->export_description_type = $value;
	}
	
	public function setOrderStatusOpen($value) {
		$this->order_status_open = $value;
	}
	
	public function setOrderStatusShipped($value) {
		$this->order_status_shipped = $value;
	}
	
	public function setOrderStatusShippingBlocked($value) {
		$this->order_status_shipping_blocked = $value;
	}
	
	public function setOrderStatusCancled($value) {
		$this->order_status_cancled = $value;
	}
	
	public function setPluginShopId($value) {
		$this->plugin_shop_id = $value;
	}
	
	/**
	 * Override to return null if $this->cname is set to "0"
	 * (which is a dirty hack for veyton not allowing empty input
	 * fields when saving the configuration).
	 *
	 * @see ShopgateConfig::getCname()
	 */
	public function getCname() {
		if (empty($this->cname)) {
			return null;
		}
		
		return parent::getCname();
	}
}