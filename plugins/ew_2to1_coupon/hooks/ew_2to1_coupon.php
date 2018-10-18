<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Display available 2:1 products
*
* @author Jens Albert
* @copyright 8works <info@8works.de>
* @example use in templates like {hook key=ew_2to1_coupon}
*
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/

if (!ew_2to1_coupon::is2to1ProductSet() &&
    (ew_2to1_coupon::statusCartPage() || ew_2to1_coupon::statusCheckoutPage())) {

    $tpl_object = new template();
    $tpl = 'ew_2to1_coupon.html';
    $tpl_object->getTemplatePath($tpl, 'ew_2to1_coupon', 'hooks', 'plugin');

    $products = ew_2to1_coupon::getCartProducts();

    echo $tpl_object->getTemplate('ew_2to1_coupon', $tpl, array(
        'coupon' => ew_2to1_coupon::getCoupon(),
        'products' => $products,
        'productsCount' => count($products),
        'ew_2to1_coupon_product' => ew_2to1_coupon::EW_2TO2_COUPON_PRODUCT,
    ));
}