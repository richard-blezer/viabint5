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

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
define('_VALID_CALL','true');
define('USER_POSITION', 'admin');
define('SESSION_REFRESH_TIME', 60); // in sec
define('_SEO_MOD_REWRITE',false);
define('_SYSTEM_SQLLOG','true');

ini_set('display_errors', 'Off');

//--------------------------------------------------------------------------------------
// Define Root Paths
//--------------------------------------------------------------------------------------

$cancel_path = array('xtFramework/admin', 'xtFramework\admin');

$root_dir = dirname(__FILE__);
$root_dir = str_replace($cancel_path,'',$root_dir);

$sys_dir = $_SERVER['SCRIPT_NAME'];
$sys_dir = substr($sys_dir, 0, strripos($sys_dir, '/')+1);
$sys_dir = str_replace('xtFramework/admin', '', $sys_dir);

define('_SRV_WEBROOT',$root_dir);
define('_SRV_WEBROOT_ADMIN',_SRV_WEBROOT.'xtAdmin/');
define('_SRV_WEB', $sys_dir);

$upload_dir = str_replace('xtAdmin/', '', _SRV_WEB);
//$upload_dir = str_replace('xtadmin/', '', _SRV_WEB);
define('_SRV_WEB_UPLOAD', $upload_dir );
//--------------------------------------------------------------------------------------
// Load Config Files
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT.'conf/config.php';
include _SRV_WEBROOT.'conf/config_charsets.php';
include _SRV_WEBROOT.'conf/database.php';
include _SRV_WEBROOT.'conf/paths.php';
include _SRV_WEBROOT.'conf/cache_times.php';
//--------------------------------------------------------------------------------------
// Files needed Include before Session
//--------------------------------------------------------------------------------------

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.FileHandler.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.countries.php';
//require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.zones.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.currency.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.check_fields.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.customer.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.cart.php';



//--------------------------------------------------------------------------------------
// Database Connection & Session
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/database_handler.php';


//--------------------------------------------------------------------------------------
// Required Functions and Helpers
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'function_handler.php';

//--------------------------------------------------------------------------------------
// Security & Links
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'security_handler.php';

//--------------------------------------------------------------------------------------
// Loading Config Tab
//--------------------------------------------------------------------------------------

_buildDefine($db, TABLE_CONFIGURATION);

//--------------------------------------------------------------------------------------
// Error Reporting
//--------------------------------------------------------------------------------------
if (!defined('E_DEPRECATED')) define('E_DEPRECATED','8192');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
include _SRV_WEBROOT."conf/debug.php";

if (_SYSTEM_PHPLOG=='true') {
    ini_set("log_errors" , '1');
    ini_set("error_log" , _SRV_WEBROOT.'xtLogs/phpErrors.txt');
}

//--------------------------------------------------------------------------------------
// Loading Plugins
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'plugin_handler.php';


//--------------------------------------------------------------------------------------
// Loading Store Handling
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'store_handler.php';


//--------------------------------------------------------------------------------------
// Loading Needed Classes
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'main_handler.php';

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.plugin.php';

//	$dbNav = new class_dbNav();

function outpre($l) {
	echo "<pre>".print_r($l, true)."</pre>";
}

//include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/auth_pear.php';

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/auth.php';

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/functions.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/classes/getAdminDropdownData.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/default_lang_definitions.inc.php';

include _SRV_WEBROOT._SRV_WEB_CORE.'form_handler.php';
($plugin_code = $xtPlugin->PluginCode('admin_main.php:bottom')) ? eval($plugin_code) : false;

//require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/classes/class.grid.js.php');
require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/classes/class.navigation.php');

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.database_check.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.image.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.upload.php';
require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/classes/class.adminDB_DataSave.php');
require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/classes/class.adminDB_DataRead.php');
require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.default_table.php');


require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaImageList.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaImageSearch.php';
if (function_exists("json_encode")) {
	return json_encode($value);
} else {
	include_once(PHP_EXTJS_DOC_ROOT . "/Lib/json.php");
	$jsonEncoder = new Services_JSON();
	function json_encode($value) {
		global $jsonEncoder;
		return $jsonEncoder->encode($value);

	}
}

function versionCheck() {
    global $db;

    $check_array = array();
    $check_array['shop_version']=_SYSTEM_VERSION;
    $check_array['domain']=_SYSTEM_BASE_HTTP;

    // get plugin versions
    $rs = $db->Execute("SELECT code,version FROM ".TABLE_PLUGIN_PRODUCTS." WHERE plugin_status='1'");
    $check_array['plugins'] = array();
    while (!$rs->EOF) {
        $check_array['plugins'][$rs->fields['code']]=$rs->fields['version'];
        $rs->MoveNext();
    }

    $_lic = _SRV_WEBROOT . 'lic/license.txt';
    if (!file_exists($_lic))
        die('- main lic missing -');
    $val_line = '';
    $bline = '';
    $_file_content = file($_lic);
    foreach ($_file_content as $bline_num => $bline) {
        if (preg_match('/key:/', $bline)) {
            $val_line = $bline;
            break;
        }
    }

    $val_line = explode(':', $val_line);
    $_shop_lic = '';
    $_shop_lic = trim($val_line[1]);
    $check_array['lic_key']=$_shop_lic;

    $postfields = json_encode($check_array);

    require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/nusoap/nusoap.php';

    $call = array('user'=>'public','password'=>'public','data'=>$postfields);

    $endpoint = 'http://api.xt-commerce.com/service/service.php';

    $client = new nusoap_client($endpoint);

    $response = $client->call('updateCheck',$call);

    return $response;

}

/**
 * Some helper functions
 */
function random_color_part() {
	return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

function random_color() {
	return '#' . random_color_part() . random_color_part() . random_color_part();
}
/**
 * End some helper functions
 */



?>