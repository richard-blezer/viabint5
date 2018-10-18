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
 # @version $Rev: 558 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class shopgate_qr_generator {
	public static function generateProductQR($products_id) {
		global $db, $store_handler;
		
		include_once _SRV_WEBROOT.'plugins/xt_shopgate/framework/ext/phpqrcode/qrlib.php';
		include_once _SRV_WEBROOT.'plugins/xt_shopgate/framework/helper/2d_is.php';
		include_once _SRV_WEBROOT.'plugins/xt_shopgate/classes/class.shopgate_config_helper.php';
		
		$config = shopgate_config_helper::getConfig();
		if(empty($config)) return null;
		
		$tmp = _SRV_WEBROOT.'cache/shopgate_qr';
		
		if(!is_dir($tmp))
			mkdir($tmp);
		
		$_relative_path = 'cache/shopgate_qr/'. $products_id . '.png';
		
		$shopNumber = $config["shop_number"];
		
		$qrDst = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'qr_destination' AND `shop_id` = " . $store_handler->shop_id);
		
		if($qrDst == sg_2d_is::SHOP_ITEM_CHECKOUT)
			$url = sg_2d_is::getShopItemCheckoutUrl($shopNumber, $products_id);
		else
			$url = sg_2d_is::getShopItemUrl($shopNumber, $products_id);
		
		QRcode::png($url, $tmp . '/'. $products_id . '.png' );

		return $_relative_path;
	}
}