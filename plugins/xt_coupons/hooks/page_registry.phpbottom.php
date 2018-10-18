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

define('TABLE_COUPONS', DB_PREFIX . '_coupons');
define('TABLE_COUPONS_DESCRIPTION', DB_PREFIX . '_coupons_description');
define('TABLE_COUPONS_PRODUCTS', DB_PREFIX . '_coupons_products');
define('TABLE_COUPONS_CUSTOMERS', DB_PREFIX . '_coupons_customers');
define('TABLE_COUPONS_CATEGORIES', DB_PREFIX . '_coupons_categories');
define('TABLE_COUPONS_REDEEM', DB_PREFIX . '_coupons_redeem');
define('TABLE_COUPONS_TOKEN', DB_PREFIX . '_coupons_token');
define('TABLE_COUPONS_IM_EXPORT', DB_PREFIX . '_coupons_im_export');
define('TABLE_COUPONS_GENERATOR', DB_PREFIX . '_coupons_generator');
define('TABLE_COUPONS_PERMISSION', DB_PREFIX . '_coupons_permission');


require _SRV_WEBROOT . 'plugins/xt_coupons/classes/class.xt_coupons.php';
?>