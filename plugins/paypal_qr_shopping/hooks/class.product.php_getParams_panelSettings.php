<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$header['paypal_qr_status'] = array('type' => 'hidden');
$header['paypal_qr_url'] = array('type' => 'hidden');
$header['paypal_qr_shipping_price'] = array('type' => 'hidden');
$header['paypal_qr_price'] = array('type' => 'hidden');

$header['paypal_qr_type'] = array('type' => 'dropdown','url'=>'DropdownData.php?get=paypal_qr_type&plugin_code=paypal_qr_shopping');


$groupingPosition = 'paypal_qr';
$grouping['paypal_qr_type'] = array('position' => $groupingPosition);
/*
$grouping['paypal_qr_price'] = array('position' => $groupingPosition);
$grouping['paypal_qr_shipping_price'] = array('position' => $groupingPosition);
$grouping['paypal_qr_url'] = array('position' => $groupingPosition);
*/
?>