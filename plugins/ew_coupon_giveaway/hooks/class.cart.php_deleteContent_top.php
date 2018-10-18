<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Unset giveaway
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */


if (ew_coupon_giveaway::isGiveawaySelf($key)) {
    ew_coupon_giveaway::removeFromSession($key);
}