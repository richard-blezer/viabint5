<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: emsclient.php 4611 2011-03-30 16:39:15Z mzanier $
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

/* */
ini_set ('error_reporting', E_ALL ^ E_NOTICE);
ini_set ('log_errors',1);
ini_set('error_log', 'debug.txt');
/* */



function __mydebug ($data, $txt =  '') {
	return;
	$fout = fopen('debug.txt', 'a+');
	if (!$fout) return;
	fwrite ($fout, date('c') ."($txt):\n");
	fwrite ($fout, print_r ($data,true) . "\n\n");
	fclose ($fout);
}


$xmldata = $_POST['xml'];
if ($xmldata) {
  $xmldata = str_replace ('\"', '"', $xmldata);
  try {
    $xml = new SimpleXMLElement ($xmldata);
    __mydebug ($xml->asXML(), 'asXML');
  }
  catch (exception $e) {
    echo 'ERROR';
    __mydebug ($e, 'ouch parseXML');
    exit (0);
  }

}
else {
  __mydebug ('NO XML');
  echo 'ERROR';
  exit (0);
}



// include 'xtCore/main_slim.php';
include '../../../xtCore/main_slim.php';
$plg_path = _SRV_WEBROOT . 'plugins/xt_ClickandBuy/callback/';

require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.check_fields.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.customer.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.cart.php';

include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'security_handler.php';
include _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'store_handler.php';
include $plg_path . 'main_handler_nohooks.php';
include _SRV_WEBROOT._SRV_WEB_CORE.'form_handler.php';
require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'/classes/class.callback.php';
__mydebug (1);


require_once $plg_path . 'class.callback.php';
__mydebug ('nach callback 2');

$cb = new callback_xt_ClickandBuy (true);
if ($cb->handleEMSPush ($xml)) {
  echo 'OK';
}
else {
  echo 'ERROR';
}
__mydebug ('done.');

?>