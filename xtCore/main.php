<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

define('_VALID_CALL','true');
define('USER_POSITION', 'store');

$_SYSTEM_INSTALL_SUCCESS = 'false';

// sql log
define('_SYSTEM_SQLLOG','false');

//--------------------------------------------------------------------------------------
// Error Reporting  until DB initialization
//--------------------------------------------------------------------------------------
if (!defined('E_DEPRECATED')) define('E_DEPRECATED','8192');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", "1");

//--------------------------------------------------------------------------------------
// Define Root Paths
//--------------------------------------------------------------------------------------
$root_dir = dirname(__FILE__);
$root_dir = str_replace('xtCore','',$root_dir);

include $root_dir.'conf/ssl.php';

$script_name = $_SERVER['SCRIPT_NAME'];
// fix for strato ssl proxy
if ((_SYSTEM_SSL_PROXY=='true') && (stristr($_SERVER['REQUEST_URl'], 'xtadmin/') === FALSE)) {
    $_dir = explode('/',$script_name);
    unset($_dir[0]);
    unset($_dir[1]);
    $script_name='/'.implode('/',$_dir);
    
}
$sys_dir = $script_name;
$sys_dir = substr($sys_dir, 0, strripos($sys_dir, '/')+1);
$sys_dir = str_replace('xtAdmin/', '', $sys_dir);
$check = substr($sys_dir,0,1);

define('_SRV_WEBROOT',$root_dir);
define('_SRV_WEB', $sys_dir);
include _SRV_WEBROOT.'conf/debug.php';
if (_SYSTEM_PHPLOG=='true') {
    ini_set("log_errors" , '1');
    ini_set("error_log" , _SRV_WEBROOT.'xtLogs/phpErrors.txt');
}
//--------------------------------------------------------------------------------------
// cleanup GET
//--------------------------------------------------------------------------------------
if (count($_GET)>0) {
    foreach ($_GET as $get_key=>$get_var) {
        if (substr($get_key,0,4)=='amp;') {
           unset($_GET[$get_key]);
           $_GET[str_replace('amp;','',$get_key)]=$get_var; 
        }
    }
}
//--------------------------------------------------------------------------------------
// Load Config Files
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT.'conf/config.php';
include _SRV_WEBROOT.'conf/config_charsets.php';
$installer_warning = false;
if($_SYSTEM_INSTALL_SUCCESS != 'true' && !defined('XT_WIZARD_STARTED')){
	header('Location: ' . _SRV_WEB.'xtWizard/index.php');
} else {
    // check if installer dir is still there
    if (file_exists(_SRV_WEBROOT.'xtWizard/index.php')) {
        $installer_warning = true;
    }
}

if($_SYSTEM_INSTALL_SUCCESS != 'true' && defined('XT_WIZARD_STARTED')) {
	return;
}

include _SRV_WEBROOT.'conf/database.php';
include _SRV_WEBROOT.'conf/paths.php';
include _SRV_WEBROOT.'conf/cache_times.php';


//--------------------------------------------------------------------------------------
// Files needed Include before Session
//--------------------------------------------------------------------------------------


require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.countries.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.currency.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.check_fields.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.customer.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.cart.php';


//--------------------------------------------------------------------------------------
// Required Functions and Helpers
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'function_handler.php';

//--------------------------------------------------------------------------------------
// Database Connection & Session
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'database_handler.php';

//--------------------------------------------------------------------------------------
// Security & Links
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'security_handler.php';

//--------------------------------------------------------------------------------------
// Loading Config Tab
//--------------------------------------------------------------------------------------

	_buildDefine($db, TABLE_CONFIGURATION);

	include _SRV_WEBROOT."conf/debug.php";

//--------------------------------------------------------------------------------------
// Loading Store Handling
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'store_handler.php';


//--------------------------------------------------------------------------------------
// Loading Needed Classes
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'main_handler.php';

include _SRV_WEBROOT._SRV_WEB_CORE.'form_handler.php';


if (!function_exists("json_encode") || !function_exists("json_decode")) 
{
    include_once(PHP_EXTJS_DOC_ROOT . "/Lib/json.php");
    $jsonEncoder = new Services_JSON();
    if (!function_exists("json_encode"))
    {
        function json_encode($value) 
        {
            global $jsonEncoder;
            return $jsonEncoder->encode($value);
        }
    }    
    if (!function_exists("json_decode")) 
    {
        function json_decode($value) 
        {
            global $jsonEncoder;
            return $jsonEncoder->decode($value);
        }
    }
}

($plugin_code = $xtPlugin->PluginCode('store_main.php:bottom')) ? eval($plugin_code) : false;     
?>