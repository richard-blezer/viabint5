<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

include_once 'ignored_pages.inc.php';

if ($value['group_discount_allowed']=='1'/* && !XtCustomersDiscount_orderEditActive()*/) {
    if ($customer_group_discount>0)
    {
        $discount+=$customer_group_discount;
    }
}