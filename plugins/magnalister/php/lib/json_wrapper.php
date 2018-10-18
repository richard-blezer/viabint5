<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: json_wrapper.php 132 2012-10-19 12:39:38Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# In PHP 5.2 or higher we don't need to bring this in
if (!function_exists('json_encode')) {
	if (!class_exists('Services_JSON')) require_once('json/json.php');
	
	function json_encode($arg) {
		global $services_json;
		if (!isset($services_json)) {
			$services_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		}
		return $services_json->encodeUnsafe($arg);
	}
}

# In PHP 5.2 or higher we don't need to bring this in
if (!function_exists('json_decode')) {
	if (!class_exists('Services_JSON')) require_once('json/json.php');

	function json_decode($arg) {
		global $services_json;
		if (!isset($services_json)) {
			$services_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		}
		return $services_json->decode($arg);
	}
}
