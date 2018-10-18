<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

$db->Execute("DROP TABLE IF EXISTS " . TABLE_PRINT_BUTTONS );
$db->Execute("DROP TABLE IF EXISTS " . TABLE_PRINT_BUTTONS_LANG );

$db->Execute("DELETE FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text = 'xt_print_buttons'");