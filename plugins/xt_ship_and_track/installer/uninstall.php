<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/constants.php';

global $db;

// West Navi entfernen
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_hermes_settings'");
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_ship_and_track'");
$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text = 'xt_hermes_collect'");

// tabelle entfernen
$db->Execute("DROP TABLE IF EXISTS ".TABLE_SHIPPER);
$db->Execute("DROP TABLE IF EXISTS ".TABLE_TRACKING);
$db->Execute("DROP TABLE IF EXISTS ".TABLE_TRACKING_STATUS);
$db->Execute("DROP VIEW IF EXISTS ".VIEW_TRACKING);
$db->Execute("DROP TABLE IF EXISTS ".TABLE_HERMES_ORDER);
$db->Execute("DROP TABLE IF EXISTS ".TABLE_HERMES_COLLECT);
$db->Execute("DROP TABLE IF EXISTS ".TABLE_HERMES_SETTINGS);


// email tpls entfernen
$emailTpls = $db->GetAll("SELECT `tpl_id` FROM ". TABLE_MAIL_TEMPLATES ." WHERE `tpl_type` LIKE 'tracking_%' ");
foreach($emailTpls as $tpl)
{
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES_CONTENT." WHERE `tpl_id` = ".$tpl['tpl_id']);
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES." WHERE `tpl_id` = ".$tpl['tpl_id']);
}


?>