<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$root_dir = dirname(__FILE__);
$root_dir = str_replace('plugins/xt_ship_and_track/classes','',$root_dir);

define('_SRV_WEBROOT',$root_dir);
define('_SRV_WEB', '/');

?>
