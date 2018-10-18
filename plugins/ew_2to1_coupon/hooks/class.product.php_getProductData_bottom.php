<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Modify 2:1 product data initial state after database response
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (ew_2to1_coupon::is2to1ProductOrigin($this->pID) &&
    isset($this->originalPid) &&
    ew_2to1_coupon::is2to1ProductSelf($this->originalPid)) {

    $data = array_merge($data, array(
        'products_name' => defined('TEXT_EW_2TO1_COUPON_PRODUCTS_NAME') ? sprintf(TEXT_EW_2TO1_COUPON_PRODUCTS_NAME, $data['products_name']) : $data['products_name'],
        'products_price' => ew_2to1_coupon::get2to1ProductPrice(),
        ew_2to1_coupon::EW_2TO2_COUPON_PRODUCT => 'true',
    ));
}
