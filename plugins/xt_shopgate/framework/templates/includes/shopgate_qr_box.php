<?php
include_once dirname(__FILE__).'/../../lib/framework.php';
include_once dirname(__FILE__).'/../../helper/2d_is.php';
include_once dirname(__FILE__).'/../../ext/phpqrcode/qrlib.php';

function drawShopgateQR($smarty, $product) {
	if(isset($product) && is_object($product)) {
		try {
			$shopgate_web_path = 'shopgate';
			
			$products_id = $product->data["products_id"];
			$config = ShopgateConfig::validateAndReturnConfig();
			
			if(isset($config["shop_is_active"]) && $config["shop_is_active"] == "false") return;
			
			$cache_dir = realpath(dirname(__FILE__).'/../../data/cache/');
			if($config["qr_destination"] == sg_2d_is::SHOP_ITEM_CHECKOUT)
				$url = sg_2d_is::getShopItemCheckoutUrl($config["shop_number"], $products_id);
			else
				$url = sg_2d_is::getShopItemUrl($config["shop_number"], $products_id);
			
			QRcode::png($url, $cache_dir . '/' . $products_id . '.png' );
			
			$smarty->assign('shopagte_template_path', $shopgate_web_path.'/templates');
			$smarty->assign('shopgate_qr_code', $shopgate_web_path.'/data/cache/'.$products_id . '.png');
			
			$smarty->assign(
				"backgroundColor",
				empty($config["background_color"])?"#333":$config["background_color"]);
				
			$smarty->assign(
				"foregroundColor",
				empty($config["foreground_color"])?"#3d3d3d":$config["foreground_color"]);

			$hasOwnApp = isset($config["has_own_app"])?$config["has_own_app"]:false;
			
			$smarty->assign(
				"shopgate_itunes_url",
				!empty($config["itunes_link"]) && $hasOwnApp ? $config["itunes_link"] : SHOPGATE_ITUNES_URL);
			
			$shopgate_qr_box = $smarty->fetch(dirname(__FILE__)."/../shopgate_qr_box.html");
			 
			$smarty->assign('SHOPGATE_QR_BOX', $shopgate_qr_box);
		} catch (ShopgateFrameworkException $e) {  }
	}
}

