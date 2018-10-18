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

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/adodb.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/adodb-pager.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/xtcommerce-pager.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/adodb-errorhandler.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/tohtml.inc.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/session/adodb-session2.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/adodb5/adodb-active-record.inc.php';
include _SRV_WEBROOT.'conf/debug.php';

$ADODB_CACHE_DIR = _SRV_WEBROOT.'cache';
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$session_name = substr(md5($_SERVER['HTTP_USER_AGENT']),0,5);

if (isset($_GET['sess_name']) && isset($_GET['sess_id'])) {   // TODO add security token for auth
	$param ='/[^a-zA-Z0-9_-]/';
	$session_name=preg_replace($param,'',$_GET['sess_name']);
	$session_name = substr($session_name,2,5);
	session_id($_GET['sess_id']);
}


$options['table'] = TABLE_SESSIONS;
ADOdb_Session :: config('mysql', _SYSTEM_DATABASE_HOST, _SYSTEM_DATABASE_USER, _SYSTEM_DATABASE_PWD, _SYSTEM_DATABASE_DATABASE, $options);
ADOdb_session :: Persist($connectMode = false);
@ini_set( 'session.hash_function', 0); // 0 = md5 1 = sha-1
@ini_set( 'session.hash_bits_per_character', 4); // 4 bits/char: 32 char SID
@ini_set( 'session.use_trans_sid', 1 );


session_name('ax'.$session_name);
session_start();


$db = ADONewConnection('mysql');
$db->Connect(_SYSTEM_DATABASE_HOST, _SYSTEM_DATABASE_USER, _SYSTEM_DATABASE_PWD, _SYSTEM_DATABASE_DATABASE);
if (_SYSTEM_SQLLOG=='true') $db->LogSQL();
$db->cacheSecs = 5; // cache 24 hours 3600*24
$db->Execute("SET NAMES '"._SYSTEM_DB_CHARSET."'");
$db->Execute("SET CHARACTER_SET_CLIENT="._SYSTEM_DB_CHARSET);
$db->Execute("SET CHARACTER_SET_RESULTS="._SYSTEM_DB_CHARSET);

ADOdb_Active_Record::SetDatabaseAdapter($db)  ;

//echo '::2::'.session_id();
?>