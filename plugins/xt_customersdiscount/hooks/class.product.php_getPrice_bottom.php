<?php
/*
#########################################################################
#                       xt:Commerce  4.2 Shopsoftware
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
# This file may not be redistributed in whole or significant part.
# Content of this file is Protected By International Copyright Laws.
#
# ~~~~~~ xt:Commerce  4.2 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
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

include_once 'ignored_pages.inc.php';

global $customers_status;

$customer_group_discount = 0;

$total_cart = 0;
if($_SESSION['cart'])
{
    $total_cart = $_SESSION['cart']->cart_total_full;
}

if (strstr($customers_status->customers_discount,'#')) {
	$discounts = explode(';',$customers_status->customers_discount);

	$discounts = array_reverse(array_filter($discounts));
    for ($i=0;$i<sizeof($discounts);$i++)
    {
        $dsc = explode('#',$discounts[$i]);
        if ($total_cart>=$price->_calcCurrency($dsc[0])) {
            $customer_group_discount = $dsc[1];
			break;
		}
	}
} else { // only single discount
	if ($customers_status->customers_discount>0) $customer_group_discount = $customers_status->customers_discount;
}

if (XtCustomersDiscount_orderEditActive())
{
    if ($cheapest_price)
    {
        $old_price_plain_otax = $price->_removeTax($price_plain,$products_tax);
    }
    else if ($special_price)
    {
        $old_price_plain_otax = $this->data['products_price'];
    }
    else {
        $old_price_plain_otax = $this->data['products_price'];
        $price_plain = $price->_AddTax($old_price_plain_otax,$products_tax);
    }


    $format_array = array(
        'price' => $price_data['plain'],
        'price_otax' => $price_data['plain_otax'],
        'old_price' => $price_plain,
        'old_price_otax' => $old_price_plain_otax,
        'format' => true,
        'format_type' => $customer_group_discount > 0 ? 'special' : 'special',
        'date_available' => $date_available,
        'date_expired' => $date_expired
    );

    $price_data = $price->_Format($format_array);
    $a = 0;
}
else if ($customer_group_discount > 0)
{
        $price_plain = $price_data['plain'];
        $discounted_price = $price_plain - ($price_plain*($customer_group_discount/100));
        $discounted_price = $price->_calcCurrency($discounted_price);

        $format_array = array(
            'price' => $discounted_price,
            'price_otax' => $price_data['plain_otax'],
            'old_price' => $price_plain,
            'old_price_otax' => $price_data['plain_otax'],
            'format' => true,
            'format_type' => 'special',
            'date_available' => $date_available,
            'date_expired' => $date_expired
        );

        $price_data = $price->_Format($format_array);

}
