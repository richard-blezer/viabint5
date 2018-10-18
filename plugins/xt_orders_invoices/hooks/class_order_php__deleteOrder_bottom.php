<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

$query = "SELECT ".COL_INVOICE_ID. " FROM " . DB_PREFIX . "_plg_orders_invoices WHERE orders_id = '" . (int)$orders_id . "'";

$rs = $db->Execute($query);

if ($rs->RecordCount() > 0) {
    $invoice_id = $rs->fields[COL_INVOICE_ID];
    $db->Execute("DELETE FROM " . DB_PREFIX . "_plg_orders_invoices WHERE ".COL_INVOICE_ID."='" . (int)$invoice_id . "'");
    $db->Execute("DELETE FROM " . DB_PREFIX . "_plg_orders_invoices_products WHERE WHERE ".COL_INVOICE_ID."='" . (int)$invoice_id . "'");
}

?>