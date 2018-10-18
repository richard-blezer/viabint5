<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops_ui_settings.php';
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. '/xt_trusted_shops/classes/trusted_shops.php';

$show_box = false;

global $language, $store_handler;
$uiSettings = trusted_shops_ui_settings::getSettings($language->content_language, SHOP_DOMAIN_HTTP, $params);

// in verbindung mit hooks/smarty_function_box.php_top.php
if ($uiSettings[COL_TS_CERTS_SHOW_RATING] && $params['box'] == 'ratings')
{
    $show_box = true;
    $tpl_data = array('ts_ui'=>$uiSettings);
	}
else if ($uiSettings[COL_TS_CERTS_SHOW_SEAL] && $params['box'] == 'seal')
{
    $show_box = true;
    $tpl_data = array('ts_ui'=>$uiSettings);
	}
else if ($uiSettings[COL_TS_CERTS_SHOW_VIDEO] && $params['box'] == 'video')
{
	$show_box = true;
    $tpl_data = array('ts_ui'=>$uiSettings);
}
else if ($uiSettings[COL_TS_CERTS_SHOW_RICH_SNIPPETS] && $uiSettings[COL_TS_CERTS_RATING_ENABLED] && $params['box'] == 'rich_snippet')
{
    $xml = trusted_shops::getXml($uiSettings['certificate_key']);
    if ($xml)
    {
        $show_box = true;

        $xPath = "/shop/ratings/result[@name='average']";
        $result = $xml->xpath($xPath);
        $av = (string) $result[0];
        $count = (string) $xml->ratings["amount"];
        $shopName = (string) $xml->name;

        $tpl_data = array(
            'tsId'=>$uiSettings['certificate_key'],
            'max' => TS_RICH_SNIPPET_MAX_RATINGS,
            'count' => $count,
            'result' => $av,
            'shopName' => $shopName);
    }

}
