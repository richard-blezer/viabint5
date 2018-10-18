<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db;

// FIX uninstaller
$uninstallerSQL = '
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = \'xt_im_export\'");
if($this->_FieldExists(\'module\', TABLE_SYSTEM_LOG))
{
    $db->Execute("DELETE FROM ".TABLE_SYSTEM_LOG." WHERE module = \'xt_im_export\'");
}
else if($this->_FieldExists(\'message_source\', TABLE_SYSTEM_LOG))
{
    $db->Execute("DELETE FROM ".TABLE_SYSTEM_LOG." WHERE message_source = \'xt_im_export\'");
}
$db->Execute("DELETE FROM ".TABLE_LANGUAGE_CONTENT." WHERE plugin_key = \'xt_im_export\'");
$db->Execute("DROP TABLE ".DB_PREFIX."_exportimport");
$db->Execute("DROP TABLE ".DB_PREFIX."_exportimport_log");
';

$db->Execute("UPDATE ".TABLE_PLUGIN_SQL." SET uninstall=?
WHERE plugin_id=(SELECT plugin_id FROM ".TABLE_PLUGIN_PRODUCTS." WHERE code='xt_im_export')", array($uninstallerSQL));

// FIX west navi
$exists = $db->GetOne("SELECT 1 FROM ".TABLE_ADMIN_NAVIGATION." WHERE text='xt_im_export'");
if(!$exists)
{
    $db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_im_export', 'images/icons/arrow_refresh.png', '&plugin=xt_im_export', 'adminHandler.php', '4000', 'contentroot', 'I', 'W');");
}
