<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Translate product fake id to origin product id
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

if (ew_coupon_giveaway::isGiveawaySelf($this->pID)) {
    $this->{ew_coupon_giveaway::className()} = $this->pID;
    $this->pID = ew_coupon_giveaway::getProductsIdByFakeId($this->pID);
    $this->qty = ew_coupon_giveaway::QTY;
}
