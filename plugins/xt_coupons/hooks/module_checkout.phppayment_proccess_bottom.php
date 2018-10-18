<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $price;

require_once(_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'admin/classes/class.adminDB_DataSave.php');

$arr_coupon = $_SESSION['sess_coupon'];
if (is_array($arr_coupon)) {

    $data = array();
    $data['coupon_id'] = $arr_coupon['coupon_id'];
    $data['coupon_token_id'] = $arr_coupon['coupons_token_id'];
    $data['redeem_date'] = $order->order_data['date_purchased_plain'];
    $data['redeem_ip'] = $order->order_data['customers_ip'];
    $data['customers_id'] = $order->customer;
    $data['order_id'] = $order->oID;
    $data['redeem_amount'] = $_SESSION['cart']->total_discount;

    if ($arr_coupon['coupon_free_shipping'] == 1) {
        $coupon_sub = $_SESSION['cart']->sub_content['xt_coupon'];

        $price_o = $coupon_sub['products_price'];
        $taxclass = $coupon_sub['products_tax_class'];
        // brutto berechnen !!

        $tax_data = $tax->data[$taxclass];

        $price_o = $price->_AddTax($price_o, $tax_data);

        if ($price_o < 0) $price_o *= -1;
        $data['redeem_amount'] = $price_o;
    }

    if ($arr_coupon['coupon_amount'] > 0) {
        $data['redeem_amount'] = $arr_coupon['coupon_amount'];
    }


    $obj = new stdClass;
    $o = new adminDB_DataSave(TABLE_COUPONS_REDEEM, $data, false, __CLASS__);
    $obj = $o->saveDataSet();

    if ($arr_coupon['coupons_token_id'] > 0) {
        unset($data);
        $data = array();
        $data['coupons_token_id'] = $arr_coupon['coupons_token_id'];
        $data['coupon_id'] = $arr_coupon['coupon_id'];
        $data['coupon_token_code'] = $arr_coupon['coupon_token_code'];
        $data['coupon_token_order_id'] = $order->oID;
        $data['coupon_token_status'] = 1;
        $obj = new stdClass;
        $o = new adminDB_DataSave(TABLE_COUPONS_TOKEN, $data, false, __CLASS__);
        $obj = $o->saveDataSet();
    }

    $sql = "select count(coupon_id) as c from " . TABLE_COUPONS_REDEEM . " where coupon_id = ?;";
    $rs = $db->execute($sql,array($arr_coupon['coupon_id']));
    $count = $rs->fields('c');
    $sql = "update " . TABLE_COUPONS . " set coupon_order_ordered = ? where coupon_id = ?;";
    $db->execute($sql,array((int)$count,$arr_coupon['coupon_id'] ));


    unset($_SESSION['sess_coupon']);
}
?>