<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_im_export'");
if($this->_FieldExists('module', TABLE_SYSTEM_LOG))
{
    $db->Execute("DELETE FROM ".TABLE_SYSTEM_LOG." WHERE module = 'xt_im_export'");
}
else if($this->_FieldExists('message_source', TABLE_SYSTEM_LOG))
{
    $db->Execute("DELETE FROM ".TABLE_SYSTEM_LOG." WHERE message_source = 'xt_im_export'");
}
$db->Execute("DELETE FROM ".TABLE_LANGUAGE_CONTENT." WHERE plugin_key = 'xt_im_export'");
$db->Execute("DROP TABLE ".DB_PREFIX."_exportimport");
$db->Execute("DROP TABLE ".DB_PREFIX."_exportimport_log");