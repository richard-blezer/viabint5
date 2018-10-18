<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$db->Execute("DELETE FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text = 'xt_orders_invoices'");
$db->Execute("DELETE FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text = 'xt_orders_invoices_templates'");
$rs = $db->Execute("SELECT * FROM " . TABLE_ADMIN_NAVIGATION . " WHERE parent = 'order'");
if (!$rs->RecordCount())
{
    $db->Execute("UPDATE " . TABLE_ADMIN_NAVIGATION . " SET `type` = 'I' WHERE `text` = 'order'");
}

$group = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION_GROUP . " WHERE group_title = 'XT_ORDERS_INVOICES_TEXT_SETTINGS'");
$groupId = (int)$group->fields['group_id'];

$stores = $db->Execute("SELECT * FROM " . TABLE_MANDANT_CONFIG);
while (!$stores->EOF)
{
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION_MULTI . $stores->fields['shop_id'] . " WHERE group_id=" . $groupId);
    $stores->MoveNext();
}
$stores->Close();


$db->Execute("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE group_id=" . $groupId);

$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES_CONTENT . " WHERE tpl_id IN
    (SELECT tpl_id FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type='send_invoice')
");
$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type='send_invoice'");


$db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX . "_plg_orders_invoices_templates");
$db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX . "_plg_orders_invoices_templates_content");

?>