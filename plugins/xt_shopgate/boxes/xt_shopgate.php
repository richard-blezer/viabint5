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
 # @version $Rev: 54 $
 #
 # @author Martin Weber, Shopgate GmbH	weber@shopgate.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if( defined("XT_SHOPGATE_ENABLE") && XT_SHOPGATE_ENABLE == "true" ) {

	include_once _SRV_WEBROOT."/plugins/xt_shopgate/classes/constants.php";
	include_once _SRV_WEBROOT."/plugins/xt_shopgate/classes/class.shopgate_config_veyton.php";

	try {
		global $db, $store_handler;

		$shopgateConfigVeyton = new ShopgateConfigVeyton();
		$shopgateConfigVeyton->loadConfigFromDatabase();

		$qrLink = $smarty->get_template_vars("shopgate_qr_code");

		if (!empty($qrLink) && $shopgateConfigVeyton->getShopIsActive() && isset($xtPlugin->active_modules['xt_shopgate']) && XT_SHOPGATE_ID!='' && XT_SHOPGATE_KEY!=''){
			$tpl_data["shopgate_qr_code"] = $qrLink;

			$hasOwnApp = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'has_own_app' AND `shop_id` = " . $store_handler->shop_id);
			$itunesLink = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'itunes_link' AND `shop_id` = " . $store_handler->shop_id);

			$backgroundColor = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'background_color' AND `shop_id` = " . $store_handler->shop_id);
			$foregroundColor = $db->GetOne("SELECT value FROM `" . TABLE_SHOPGATE_CONFIG . "` WHERE `key` = 'foreground_color' AND `shop_id` = " . $store_handler->shop_id);

			$tpl_data["backgroundColor"] = $backgroundColor;
			$tpl_data["foregroundColor"] = $foregroundColor;

			if(($hasOwnApp == '1' || $hasOwnApp == 'on') && !empty($itunesLink)) {
				$tpl_data["shopgate_itunes_url"] = $itunesLink;
				$tpl_data["has_own_app"] = ($hasOwnApp == '1' || $hasOwnApp == 'on' ? '1' : '0');
			} else {
				$tpl_data["shopgate_itunes_url"] = SHOPGATE_ITUNES_URL;
				$tpl_data["has_own_app"] = '0';
			}

			$tpl_data["XT_SHOPGATE_ENABLE"] = "true";

			$show_box = true;
		} else {
			$show_box = false;
		}
	} catch (Exception $e) {}
} else {
	$tpl_data["XT_SHOPGATE_ENABLE"] = "false";
}