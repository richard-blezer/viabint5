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

if (ew_2to1_coupon::statusPlugin()) {
    if (!ew_2to1_coupon::status()) {
        ew_2to1_coupon::unset2to1ProductFromStorage();
        ew_2to1_coupon::unset2to1ProductFromCart();
    } elseif (!ew_2to1_coupon::is2to1ProductSet()) {
        ew_2to1_coupon::unset2to1ProductFromCart();
    }
}