<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

$db->Execute("DELETE FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text = 'xt_orders_invoices'");
$db->Execute("DELETE FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text = 'xt_orders_invoices_templates'");
$rs = $db->Execute("SELECT * FROM " . TABLE_ADMIN_NAVIGATION . " WHERE parent = 'order'");
if (!$rs->RecordCount()) {
    $db->Execute("UPDATE " . TABLE_ADMIN_NAVIGATION . " SET `type` = 'I' WHERE `text` = 'order'");
}

$group = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION_GROUP . " WHERE group_title = 'XT_ORDERS_INVOICES_TEXT_SETTINGS'");
$groupId = (int)$group->fields['group_id'];

$stores = $db->Execute("SELECT * FROM " . TABLE_MANDANT_CONFIG);
while (!$stores->EOF) {
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION_MULTI . $stores->fields['shop_id'] . " WHERE group_id=" . $groupId);
    $stores->MoveNext();
}
$stores->Close();

$db->Execute("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE group_id=" . $groupId);

$db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE config_key='_INVOICE_NUMBER_GLOBAL_LAST_USED'");

$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES_CONTENT . " WHERE tpl_id IN
    (SELECT tpl_id FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type='send_invoice')
");
$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type='send_invoice'");

    $db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX . "_plg_orders_invoices");
    $db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX . "_plg_orders_invoices_products");
	global $db;
	$tblExists = $db->GetOne("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND TABLE_NAME='".DB_PREFIX."_pdf_manager_content'");
	if ($tblExists){
		
		$db->Execute("DELETE FROM " . DB_PREFIX . "_pdf_manager_content WHERE template_id=(SELECT template_id FROM ".DB_PREFIX . "_pdf_manager WHERE template_name='Order invoice' LIMIT 0,1) or 
      template_id=(SELECT template_id FROM ". DB_PREFIX . "_pdf_manager WHERE template_name='Delivery note' LIMIT 0,1)");
	
	 }
	$tblExists2 = $db->GetOne("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND TABLE_NAME='".DB_PREFIX."_pdf_manager'");
	if ($tblExists2){
		$db->Execute("DELETE FROM " . DB_PREFIX . "_pdf_manager WHERE template_name='Order invoice' or template_name='Delivery note'");
	}



?>