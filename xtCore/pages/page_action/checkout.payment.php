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


      if (count($_SESSION['cart']->content)==0) {
        $xtLink->_redirect($xtLink->_link(array('page'=>'cart')));
    }

    $_SESSION['cart']->_checkCustomersStatusRange('payment');
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'cart')),TEXT_CART);
    if($_SESSION['cart']->type != 'virtual'){
    	$brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout','paction'=>'shipping', 'conn'=>'SSL')),TEXT_SHIPPING_METHOD);
    }
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout','paction'=>'payment', 'conn'=>'SSL')),TEXT_PAYMENT_METHOD);
    if (isset($_SESSION['selected_payment_sub'])) unset($_SESSION['selected_payment_sub']);


    if(empty($_SESSION['selected_shipping']) && $_SESSION['cart']->type != 'virtual'){
        $info->_addInfo(ERROR_NO_SHIPPING_SELECTED);
        $checkout_data['page_action'] = 'shipping';
    }

    unset($_SESSION['conditions_accepted']);
    unset($_SESSION['rescission_accepted']);
?>