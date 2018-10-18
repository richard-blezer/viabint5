<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Change normal product to 2:1 product
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (ew_2to1_coupon::request2to1Product($data, $this->_checkAddType($data))) {
    global $info;
    $qty = $data['qty'];
    if (($data['qty'] = ew_2to1_coupon::getMaxQtyFor2to1Product($qty)) < $qty) {
        global $info;
        $info->_addInfoSession(sprintf(TEXT_EW_2TO1_COUPON_ERROR_TO_MANY, $data['qty']), 'error');
    }
    $data['product'] = ew_2to1_coupon::getFakeProductsId();
    $info->_addInfoSession(TEXT_EW_2TO1_COUPON_INFO_PRODUCT_SET, 'info');
} elseif (ew_2to1_coupon::is2to1ProductSet()) {
    global $info;
    $info->_addInfoSession(TEXT_EW_2TO1_COUPON_ERROR_PRODUCT_ALREADY_SET, 'info');
}