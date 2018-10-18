<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// zusätzliche styles im backend
$webdir = $_SERVER['SCRIPT_NAME'];
$webdir = substr($webdir, 1, strripos($webdir, '/'));
$webdir = str_replace('xtAdmin/', '', $webdir);
echo PHP_EOL.'<link rel="stylesheet" type="text/css" href="' . _SYSTEM_BASE_URL . '/' .$webdir . _SRV_WEB_PLUGINS . 'xt_trusted_shops/css/xt_trusted_shops_admin.css"/>'.PHP_EOL;