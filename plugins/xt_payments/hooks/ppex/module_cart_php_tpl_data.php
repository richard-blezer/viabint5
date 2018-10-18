<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if($_SESSION['reshash']['ACK']=='Failure' || $_SESSION['reshash']['missingAck'] ){
    require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';
    $pp_error = new paypal_express();
    $pp_error_data = $pp_error->_addErrorMessage(); 
    $tpl_data = array_merge($tpl_data, array('message'=>$pp_error_data));
}
?>