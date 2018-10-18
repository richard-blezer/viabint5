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


require_once('../../../xtFramework/admin/main.php');

// safe admin loading
if (!$xtc_acl->isLoggedIn()) {
    die('login required');
}

$data=array(
    'auth'        => '229ed4d535bf74e9eba9f7e9e40e020b',
    'method'    => 'email',
    'email'        => XT_EKOMI_LOGIN_MAIL,
    'password'    => XT_EKOMI_LOGIN_PASS
);
//__debug($data);
$ch = curl_init('http://api.ekomi.de/manage/accountAuth.php');
curl_setopt($ch, CURLOPT_POST      ,1);
curl_setopt($ch, CURLOPT_POSTFIELDS    ,http_build_query($data));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
curl_setopt($ch, CURLOPT_HEADER      ,0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
$result = curl_exec($ch);
$arr=json_decode($result);

if (isset($arr->success)) {
    header('Location: '.$arr->success);
} else {
    echo $arr->error;
}

?>