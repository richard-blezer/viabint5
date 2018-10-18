<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
				
    if(is_data($_POST['conditions_accepted_paypal']) && $_POST['conditions_accepted_paypal'] == 'on'){
        $_SESSION['conditions_accepted_paypal'] = 'true';
    }				

    $tmp_link  = $xtLink->_link(array('page'=>'checkout', '', 'conn'=>'SSL'));

}
?>