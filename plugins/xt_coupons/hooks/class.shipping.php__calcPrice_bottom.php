<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $price;

if(!isset($_REQUEST['tmp_coupon']))
{
    $cpns = new xt_coupons();
    $_REQUEST['tmp_coupon'] = $cpns->_check_coupon_in_cart();
}
if ($_REQUEST['tmp_coupon'] && ($_REQUEST['tmp_coupon']['coupon_free_shipping'] == '1' || ($_REQUEST['tmp_coupon']['coupon_percent'] == '100' && $_REQUEST['tmp_coupon']['coupon_free_on_100_status'] == '1')))
{
    if ($shipping_price['plain'] > 0)
    {
        $new_price = $price->_getPrice(array('price' => 0, 'qty' => 1, 'tax_class' => $data['shipping_tax_class'], 'format' => true, 'curr' => true, 'format_type' => 'default'));
        $shipping_price['formated'] = "<span class='coupon_free_shipping'><span class='coupon_free_shipping_old_price' style='text-decoration: line-through;'>" . trim($shipping_price['formated']) . "</span>&nbsp;<span class='coupon_free_shipping_new_price'>" . trim($new_price['formated']) . "</span></span>";
    }
}