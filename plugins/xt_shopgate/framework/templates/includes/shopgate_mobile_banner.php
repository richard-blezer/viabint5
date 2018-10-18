<?php
include_once dirname(__FILE__).'/../../lib/framework.php';

function drawShopgateMobilBanner() {
	try {
		$config = ShopgateConfig::validateAndReturnConfig();
		if(isset($config["enable_appredirect"]) && $config["enable_appredirect"] == "false") return;
		
		$shopgateOptions = array();
		$shopgateOptions["shop_number"] = $config["shop_number"];
		$shopgateOptions["iphone_standalone"] = 'true';
		
		$itunesLink = "";
		$hasOwnApp = "0";
		
		if(isset($config["itunes_link"])) $itunesLink = $config["itunes_link"]; 
		if(isset($config["has_own_app"])) $hasOwnApp = $config["has_own_app"]; 
		if(!empty($itunesLink) && $hasOwnApp) $shopgateOptions["iphone_url"] = $itunesLink;
		
//		$shopgateOptions["banner_text"] = "";
//		$shopgateOptions["button_text"] = "";
//		$shopgateOptions["android_url"] = "";
//		$shopgateOptions["windows_url"] = "";
		
		echo '
<script type="text/javascript" src="http://static.shopgate.com/api/mobile_header.js"></script>
<script type="text/javascript">
	var shopgateOptions = '.json_encode($shopgateOptions).';

	var shopgateMobileHeader = new ShopgateMobileHeader(shopgateOptions);
	shopgateMobileHeader.create();
</script>
';
	} catch (ShopgateFrameworkException $e) {
//		var_dump($e);
	}
}

drawShopgateMobilBanner();