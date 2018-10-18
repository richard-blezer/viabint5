<?php

global $db;
$xtp_enabled = $db->GetOne('SELECT status FROM '.TABLE_PAYMENT." WHERE payment_code='xt_payments'");

if ($xtp_enabled && XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && XT_PAYPAL_EXPRESS=='true')
{
    global $db;
    $db->Execute("UPDATE ".TABLE_CONFIGURATION_PAYMENT." SET config_value = 'false' where config_key = 'XT_PAYPAL_EXPRESS';");
    $tmp_link  = $xtLink->_link(array('page'=>'checkout', 'conn'=>'SSL'));
    $xtLink->_redirect($tmp_link);
}