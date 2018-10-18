<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($request['get'] == 'paypal_qr') {
    $result = array();
    $result[] = array('id' => 'color', 'name' => 'color');
    $result[] = array('id' => 'size', 'name' => 'size');
}

if ($request['get'] == 'paypal_qr_type') {
    $result = array();
    $result[] = array('id' => '1', 'name' => 'Label1');
    $result[] = array('id' => '2', 'name' => 'Label2');
    $result[] = array('id' => '3', 'name' => 'Label3');
    $result[] = array('id' => '4', 'name' => 'Label4');
}
?>