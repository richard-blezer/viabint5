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
$sys_dir = str_replace('export/', '', $sys_dir);

define('_SRV_WEBROOT',$root_dir);
define('_SRV_WEB', $sys_dir);

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
include _SRV_WEBROOT.'conf/database.php';
include _SRV_WEBROOT.'conf/paths.php';
include _SRV_WEBROOT.'conf/cache_times.php';

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'function_handler.php'; 

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'database_handler.php';


//--------------------------------------------------------------------------------------
// Loading Config Tab
//--------------------------------------------------------------------------------------

	_buildDefine($db, TABLE_CONFIGURATION);
    if (!defined('E_DEPRECATED')) define('E_DEPRECATED','8192'); 
	include _SRV_WEBROOT."conf/debug.php";

//--------------------------------------------------------------------------------------
// Loading Plugins
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'plugin_handler.php';

//--------------------------------------------------------------------------------------
// Files needed Include before Session
//--------------------------------------------------------------------------------------
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.countries.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.currency.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.sql_query.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.timer.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.LogHandler.php';




require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/smarty/Smarty.class.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.customers_status.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.language_content.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.language.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.tax.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.links.php';
	
$xtLink = new xtLink();

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.product_sql_query.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.product.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.seo_regenerate.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.products_list.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.category_sql_query.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.nested_set.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.category.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.manufacturer_sql_query.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.manufacturer.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.split_page.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.seo.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.email.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.system_status.php';
$system_status = new system_status();

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.price.php';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.order.php';
($plugin_code = $xtPlugin->PluginCode('store_main.php:bottom')) ? eval($plugin_code) : false;
?>