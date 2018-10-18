<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION['pp_address_change'] == true){
    $update_address_class = false;
}
?>