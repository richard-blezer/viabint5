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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $ADODB_THROW_EXCEPTIONS;
$ADODB_THROW_EXCEPTIONS = false;

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/adodb.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/adodb-pager.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/xtcommerce-pager.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/xtcommerce-errorhandler.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/tohtml.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/session/adodb-session2.php';
include _SRV_WEBROOT.'conf/debug.php';

$ADODB_CACHE_DIR = _SRV_WEBROOT.'cache';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


// use connection before session start for plugin _pre_includes
$db = ADONewConnection('mysql');
$db->debug = false;
if ($db->debug===true) {
    include_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.db_logger.php';
    include_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.timer.php';
    $dbLogger=new db_logger();
}
//$db->debug = 99;
$db->Connect(_SYSTEM_DATABASE_HOST, _SYSTEM_DATABASE_USER, _SYSTEM_DATABASE_PWD, _SYSTEM_DATABASE_DATABASE);
if (_SYSTEM_SQLLOG=='true') $db->LogSQL();
$db->cacheSecs = _CACHETIME_DEFAULT; // cache 24 hours 3600*24
$db->Execute("SET NAMES 'utf8'");
$db->Execute("SET CHARACTER_SET_CLIENT=utf8");
$db->Execute("SET CHARACTER_SET_RESULTS=utf8");



//--------------------------------------------------------------------------------------
// Loading Config Tab
//--------------------------------------------------------------------------------------

	_buildDefine($db, TABLE_CONFIGURATION);



//--------------------------------------------------------------------------------------
// Loading Plugins
//--------------------------------------------------------------------------------------

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'plugin_handler.php';	
	
	
$options['table'] = TABLE_SESSIONS;
ADOdb_Session :: config('mysql', _SYSTEM_DATABASE_HOST, _SYSTEM_DATABASE_USER, _SYSTEM_DATABASE_PWD, _SYSTEM_DATABASE_DATABASE, $options);
ADOdb_session :: Persist($connectMode = false);
/*
ADOdb_Session :: driver('mysql');
ADOdb_Session :: database(_SYSTEM_DATABASE_DATABASE);
ADOdb_Session :: table(TABLE_SESSIONS);
ADOdb_Session :: _conn($db);
ADOdb_session :: Persist($connectMode = false);
*/
@ini_set( 'session.hash_function', 0); // 0 = md5 1 = sha-1
@ini_set( 'session.hash_bits_per_character', 4); // 4 bits/char: 32 char SID
@ini_set( 'session.use_trans_sid', 0 );

$SessName = 'x'.substr(md5($_SERVER['HTTP_USER_AGENT']),0,5);

if (isset ($_POST[$SessName])) {
	$_GET[$SessName] = $_POST[$SessName];
}

if (isset($_GET[$SessName])) {
	session_id($_GET[$SessName]);
}

session_name($SessName);
session_start();

if(!isset($_COOKIE[session_name()])){
   $_COOKIE[session_name()] = session_id();
}

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.agent_check.php';
$agent_check = new agent_check();

if (isset($_SESSION['agent_check'])) {
    if ($_SESSION['agent_check']=='true') { 
	    $remove_session = 'true';
    } else {
        $remove_session = 'false';    
    }
} else {
	$remove_session = 'false';
	if ($agent_check->isBot()=='true') $remove_session = 'true';
	$_SESSION['agent_check'] = $remove_session;
}
define('_RMV_SESSION',$remove_session);

?>