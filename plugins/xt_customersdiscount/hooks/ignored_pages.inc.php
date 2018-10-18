<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

function XtCustomersDiscount_orderEditActive()
{
    $oePages = array('addOrderItem','removeOrderItem','applyExistingAddress','apply','updateOrderItem');

    if($_GET['plugin']=='order_edit' && in_array($_GET['pg'], $oePages))
    {
        return true;
    }
    return false;

}