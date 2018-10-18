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

global $store_handler;
$plugin = new plugin();
$stores = $store_handler->getStores();

$db->Execute("DROP TABLE " . DB_PREFIX . "_startpage_products;");
$db->Execute("DELETE FROM " . DB_PREFIX . "_acl_nav WHERE text='xt_startpage_products'");

foreach ($stores as $store) {
    if ($plugin->_FieldExists('products_startpage_' . $store['id'], DB_PREFIX . '_products'))
        $db->Execute("ALTER TABLE " . DB_PREFIX . "_products DROP `products_startpage_" . $store['id'] . "`");

    if ($plugin->_FieldExists('products_startpage_sort_' . $store['id'], DB_PREFIX . '_products'))
        $db->Execute("ALTER TABLE " . DB_PREFIX . "_products DROP `products_startpage_sort_" . $store['id'] . "`");
}