<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Unset 2:1 product
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (ew_2to1_coupon::is2to1Product($key)) {
    if (ew_2to1_coupon::unset2to1ProductFromStorage()) {
        global $info;
        $info->_addInfoSession(TEXT_EW_2TO1_COUPON_INFO_PRODUCT_UNSET, 'warning');
    }
}
