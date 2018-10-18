<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Redirect to checkout/confirmation when 2:1 product is added on checkout page
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (ew_2to1_coupon::is2to1ProductSet() &&
    $page->page_name == 'checkout' &&
    isset($data_array[ew_2to1_coupon::EW_2TO2_COUPON_PRODUCT]) &&
    ew_2to1_coupon::is2to1ProductOrigin($data_array['product'])) {

    $link_array = array('page'=>'checkout', 'paction' => 'confirmation');
}