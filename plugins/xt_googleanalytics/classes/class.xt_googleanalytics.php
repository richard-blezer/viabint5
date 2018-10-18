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

class google_analytics {


	function _getCode() {

		if (XT_GOOGLE_ANALYTICS_UA!='') {
			if ($_GET['page']=='checkout' && $_GET['page_action']=='success') {
				if (XT_GOOGLE_ANALYTICS_ECOM=='true') {
					global $success_order;
					echo $this->_getEcommerceCode();
				} else {
					echo $this->_getStandardCode();
				}

			} else {
				echo $this->_getStandardCode();
			}
		}

	}

	function _getStandardCode($loadEcommerce = false) {
		global $client_detect;

		$js = '<script type="text/javascript">
		(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
		ga("create", "' . XT_GOOGLE_ANALYTICS_UA . '", "auto");';

		if ($loadEcommerce) {
			$js .= 'ga("require", "ecommerce");';
		}

		if (XT_GOOGLE_ANALYTICS_ANON=='true') {
			$js .= "ga('set', 'anonymizeIp', true);";
		}

		if ($client_detect->mobile == true) {
			$js .= "ga('set', 'dimension11', 'mobile');";
		} elseif ($client_detect->tablet == true) {
			$js .= "ga('set', 'dimension11', 'table');";
		} else {
			$js .= "ga('set', 'dimension11', 'desktop');";
		}
		$js .= "ga('require', 'GTM-MZ2ZW6K');";

		$js .= 'ga("send", "pageview");</script> ';

		return $js;
	}


	function _getEcommerceCode() {
		global $success_order;

		$js = $this->_getStandardCode(true);

		$tax = $success_order->order_total['total']['plain']-$success_order->order_total['total_otax']['plain'];
		$js .= '<script type="text/javascript">
			ga("require", "ecommerce");
			ga("ecommerce:addTransaction", {
			  "id": "' . $success_order->order_data['orders_id'] . '", // Transaction ID. Required.
			  "affiliation": "' . $success_order->order_data['shop_id'] . '", // Affiliation or store name.
			  "revenue": "' . $success_order->order_total['total']['plain'] . '", // Grand Total.
			  "tax": "' . $tax . '", // Tax.
			  "currency" : "'. $success_order->order_data['currency_code'] . '"
			});
		';

		// add products
		foreach ($success_order->order_products as $key => $arr) {

			$js .= '
			 ga("ecommerce:addItem", {
				"id": "' . $success_order->order_data['orders_id'] . '",
				"name": "' . addslashes($arr['products_name']) . '",
				"sku": "' . addslashes($arr['products_model']) . '",
				"price": "' . $arr['products_price']['plain'] . '",
				"quantity": "' . $arr['products_quantity'] . '",
				"currency" : "'. $success_order->order_data['currency_code'] . '"
			  });
			';

		}

		$js .= 'ga("ecommerce:send");'."\n";
		$js .= '</script>';
		return $js;
	}
}