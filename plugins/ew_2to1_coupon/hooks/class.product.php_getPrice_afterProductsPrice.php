<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Modify 2:1 product price
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (isset($this->originalPid) &&
    ew_2to1_coupon::is2to1ProductSelf($this->originalPid)) {
    $special_price_otax = ew_2to1_coupon::get2to1ProductPrice();
    $special_price = $price->_AddTax($special_price_otax, $products_tax);
    $format_type = 'special';
}