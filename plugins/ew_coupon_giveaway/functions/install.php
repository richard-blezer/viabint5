<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * DB Installation
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 * @example   $db->Execute("ALTER TABLE `".DB_PREFIX."_categories` ADD `teaser_status` int(1) default '0' AFTER
 *            `products_sorting2`");
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

//class loader
if (!class_exists('ew_coupon_giveaway')) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.ew_coupon_giveaway.php';
}

// add config column to coupons table
ew_coupon_giveaway::mysqlAddColumn('xt_coupons', ew_coupon_giveaway::EW_COUPON_GIVEAWAY_PRODUCTS_FIELD, 'VARCHAR(255) NULL');
