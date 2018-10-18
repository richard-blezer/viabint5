<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Modify product data initial state after database response
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
    ew_coupon_giveaway::isGiveawayConfiguredByModel($data['products_model'])
) {

    $data = array_merge(
        $data, array(
        'products_name'                 => defined('TEXT_EW_COUPON_GIVEAWAY_PRODUCTS_NAME') ? sprintf(TEXT_EW_COUPON_GIVEAWAY_PRODUCTS_NAME, $data['products_name']) : $data['products_name'],
        'products_price'                => ew_coupon_giveaway::FAKE_PRODUCTS_PRICE,
        ew_coupon_giveaway::className() => 'true',
    ));
}
