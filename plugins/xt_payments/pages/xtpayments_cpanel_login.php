<?php
 
include_once dirname(__FILE__).'/../../../xtFramework/admin/main.php';
include_once dirname(__FILE__).'/../classes/class.XTPaymentsRegistration.php';

if (!$xtc_acl->isLoggedIn()) {
    die('login required');
}

$xtRegister = new XTPaymentsRegistration();
$result = $xtRegister->xtPaymentsCPanelLoginCredentials();

die($result);
?>