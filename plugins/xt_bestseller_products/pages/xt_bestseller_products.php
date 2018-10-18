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

if (ACTIVATE_XT_BESTSELLER_PRODUCTS_PAGE == 'true' && isset($xtPlugin->active_modules['xt_bestseller_products'])) {
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . '/xt_bestseller_products/classes/class.bestseller_products.php';

    if (!empty($params['limit'])) {
        $limit = (int)$params['limit'];
    } else {
        $limit = XT_BESTSELLER_PRODUCTS_PAGE_LIMIT;
    }

    ($plugin_code = $xtPlugin->PluginCode('plugin_xt_bestseller_products.php:data_array')) ? eval($plugin_code) : false;

    $bestseller_products_page_data_array = array
    (
        'limit' => $limit,
        'paging' => true
    );

    $bestseller_products_page = new bestseller_products($current_category_id);
    $bestseller_products_page_list = $bestseller_products_page->getbestsellerProductListing($bestseller_products_page_data_array);

    $tpl_data = array
    (
        'heading_text' => TEXT_BESTSELLER_PRODUCTS,
        'product_listing' => $bestseller_products_page_list,
        'NAVIGATION_COUNT' => $bestseller_products_page->navigation_count,
        'NAVIGATION_PAGES' => $bestseller_products_page->navigation_pages
    );

    $tpl = XT_BESTSELLER_PRODUCTS_PAGE_TPL;

    if (!empty($params['tpl'])) {
        $tpl = $params['tpl'];
    } else {
        $params['tpl'] = $tpl;
    }

    if (is_object($brotkrumen))
        $brotkrumen->_addItem($xtLink->_link(array('page' => 'bestseller_products')), TEXT_BESTSELLER_PRODUCTS);

    $template = new Template();
    ($plugin_code = $xtPlugin->PluginCode('plugin_xt_bestseller_products.php:tpl_data')) ? eval($plugin_code) : false;
    $page_data = $template->getTemplate('xt_bestseller_products_smarty', '/' . _SRV_WEB_CORE . 'pages/product_listing/' . $tpl, $tpl_data);
} else {
    $show_page = false;
}
?>