<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Initial hookpoint
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

$f = _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'ew_coupon_giveaway/classes/class.ew_coupon_giveaway.php';
if (file_exists($f)) {
    require_once $f;
}