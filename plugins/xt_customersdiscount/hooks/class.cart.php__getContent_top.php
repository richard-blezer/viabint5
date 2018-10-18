<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

include_once 'ignored_pages.inc.php';

if ($customers_status->customers_status_discount_flag=='1') {
    $customer_group_discount = 0;

    $total_cart = $this->cart_total_full;
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
}