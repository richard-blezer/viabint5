<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

define('TABLE_EXPORTIMPORT',$DB_PREFIX.'exportimport');
define('TABLE_EXPORTIMPORT_LOG',$DB_PREFIX.'exportimport_log');
require _SRV_WEBROOT.'plugins/xt_im_export/classes/class.xt_im_export.php';