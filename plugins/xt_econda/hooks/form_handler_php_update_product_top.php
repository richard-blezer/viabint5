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

if (XT_ECONDA_STATUS=='true') {
    $_prod = array();
    unset($_SESSION['econda_rmv_cart']);
    unset($_SESSION['econda_upd_cart']);

    foreach($data_array['products_key'] as $key => $val) {
        $_prod[$val]=$key;
    }
    // delete products
    if (is_array($data_array['cart_delete'])) {
        foreach($data_array['cart_delete'] as $key => $val) {
            $_data = array();
            $_data['qty']=$data_array['qty'][$_prod[$val]];
            $_data['products_id']=str_replace('_XT','',$val);
            $_SESSION['econda_rmv_cart'][]=$_data;

        }
    }
    // check if qty changed
    foreach ($_SESSION['cart']->content as $key => $val) {
        // check update array
        $upd_qty = $data_array['qty'][$_prod[$val['products_key']]];
        if ($upd_qty>$val['products_quantity']) {
            $_data = array();
            $_data['qty']=$upd_qty-$val['products_quantity'];
            $_data['products_id']=$val['products_id'];
            $_data['type']='add';
            $_SESSION['econda_upd_cart'][]=$_data;
        }
        if ($upd_qty<$val['products_quantity']) {
            $_data = array();
            $_data['qty']=$val['products_quantity']-$upd_qty;
            $_data['products_id']=$val['products_id'];
            $_data['type']='rmv';
            $_SESSION['econda_upd_cart'][]=$_data;
        }
    }
}
?>