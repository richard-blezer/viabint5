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
 # @version $Id: admin_dropdown.phpdropdown.php 4547 2011-03-09 09:13:47Z mzanier $
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

if ($request['get'] == 'refunds_type') {
    $result = array();
    $result[] = array('id' => 'Full', 'name' => TEXT_REFUNDS_TYPE_FULL);
    $result[] = array('id' => 'Partial', 'name' => TEXT_REFUNDS_TYPE_PARTIAL);
}

if ($request['get'] == 'xt_paypal_ssl_version') {
    $result = array();
    $result[] = array('id' => 'autodetect', 'name' => TEXT_PAYPAL_AUTODETECT);
    $result[] = array('id' => '1', 'name' => 'NSS');
    $result[] = array('id' => CURL_SSLVERSION_TLSv1, 'name' => 'TLS');
}
if ($request['get'] == 'xt_paypal_cipher_list') {
    $result = array();
    $result[] = array('id' => 'autodetect', 'name' => TEXT_PAYPAL_AUTODETECT);
    $result[] = array('id' => 'TLSv1', 'name' => 'TLSv1');
}
?>