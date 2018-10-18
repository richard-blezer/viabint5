<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_trusted_shops/classes/constants.php';

global $db;

// tabelle entfernen
$db->Execute("DROP TABLE IF EXISTS " . TABLE_TS_CERTIFICATES);
