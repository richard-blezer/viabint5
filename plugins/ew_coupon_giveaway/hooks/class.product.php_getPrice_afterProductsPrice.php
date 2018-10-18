<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Modify product price
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */

if (isset($this->{ew_coupon_giveaway::className()}) &&
    ew_coupon_giveaway::isGiveawaySelf($this->{ew_coupon_giveaway::className()}) &&
    ew_coupon_giveaway::isGiveawayConfiguredByModel(ew_coupon_giveaway::getProductsModelByFakeId($this->{ew_coupon_giveaway::className()}))
) {

    $special_price_otax = ew_coupon_giveaway::FAKE_PRODUCTS_PRICE;
    $special_price = $price->_AddTax($special_price_otax, $products_tax);
    $format_type = 'special';
}
