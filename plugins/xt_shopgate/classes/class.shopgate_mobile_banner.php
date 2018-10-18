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
 # @version $Rev: 563 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class shopgate_mobile_banner {
	
	private $shopgate_config = array();
	
	function __construct() {
		require_once('class.shopgate_config_helper.php');
		require_once('xt_shopgate_constants.php');
		require_once('xt_shopgate_database.php');
		
		$this->shopgate_config = shopgate_config_helper::getConfig();
	}
	
	public function mobileBanner() {
		global $db, $store_handler;
		
		if(!$this->shopgate_config["plugin_show_mobile_header"]) return;
		
		$scripUrl = "http://static.shopgate.com/api/mobile_header.js";
		
		$shopIsActive = $this->shopgate_config["plugin_shop_is_active"];
		$shopNumber = $this->shopgate_config["shop_number"];
		
		if(!$shopIsActive || empty($shopNumber)) return;
		
		$hasOwnApp = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'has_own_app' AND `shop_id` = " . $store_handler->shop_id);
		$itunesLink = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'itunes_link' AND `shop_id` = " . $store_handler->shop_id);
		
		$shopgateOptions = array();
		$shopgateOptions["shop_number"] = $shopNumber;
		$shopgateOptions["iphone_standalone"] = ($hasOwnApp?"true":"false");
		
		if(!empty($itunesLink) && $hasOwnApp) $shopgateOptions["iphone_url"] = $itunesLink;
		
//		$shopgateOptions["banner_text"] = "";
//		$shopgateOptions["button_text"] = "";
//		$shopgateOptions["android_url"] = "";
//		$shopgateOptions["windows_url"] = "";
		
		echo '
<script type="text/javascript" src="'.$scripUrl.'"></script>
<script type="text/javascript">
	var shopgateOptions = '.json_encode($shopgateOptions).';

	var shopgateMobileHeader = new ShopgateMobileHeader(shopgateOptions);
	shopgateMobileHeader.create();
</script>
';
	}

}
?>
