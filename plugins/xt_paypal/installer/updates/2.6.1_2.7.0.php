<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db;

$db->Execute("UPDATE `".TABLE_ADMIN_NAVIGATION."` SET `parent`='order' WHERE `text`='paypal_transactions' ");
$db->Execute("UPDATE `".TABLE_ADMIN_NAVIGATION."` SET `parent`='order' WHERE `text`='paypal_refunds' ");