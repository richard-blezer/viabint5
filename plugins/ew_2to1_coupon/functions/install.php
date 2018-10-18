<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* DB Installation
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
* @example $db->Execute("ALTER TABLE `".DB_PREFIX."_categories` ADD `teaser_status` int(1) default '0' AFTER `products_sorting2`");
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

//class loader
if (!class_exists('ew_2to1_coupon')) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.ew_2to1_coupon.php';
}

// add config column to coupons table
ew_2to1_coupon::mysqlAddColumn('xt_coupons', ew_2to1_coupon::EW_2TO1_COUPON_COUPON_STATUS, 'TINYINT(1) unsigned NULL');
