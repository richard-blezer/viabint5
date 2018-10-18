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

if (ACTIVATE_XT_BESTSELLER_PRODUCTS_BOX == 'true' && isset($xtPlugin->active_modules['xt_bestseller_products'])) {
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . '/xt_bestseller_products/classes/class.bestseller_products.php';

    if ($params['limit']) {
        $limit = $params['limit'];
    } else {
        $limit = XT_BESTSELLER_PRODUCTS_BOX_LIMIT;
    }

    $bestseller_products_data_array = array
    (
        'limit' => $limit,
        'sorting' => $params['order_by'],
        'paging' => false
    );

    $bestseller_products_box = new bestseller_products($current_category_id);
    $bestseller_products_list = $bestseller_products_box->getbestsellerProductListing($bestseller_products_data_array);

    if (count($bestseller_products_list) != 0) {
        if (ACTIVATE_XT_BESTSELLER_PRODUCTS_PAGE == true) {
            $show_more_link = true;
        } else {
            $show_more_link = false;
        }

        $serv_url = explode('/', $_SERVER['REQUEST_URI']);
        $serv_url_tmp = explode('?', end($serv_url));

        $tpl_data = array
        (
            '_bestseller_products' => $bestseller_products_list,
            '_show_more_link' => $show_more_link,
            'curr_url' => $serv_url_tmp[0]
        );

        $show_box = true;
    } else {
        $show_box = false;
    }
} else {
    $show_box = false;
}
?>