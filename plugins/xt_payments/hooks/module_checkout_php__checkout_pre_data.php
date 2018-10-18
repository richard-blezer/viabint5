<?php

if (is_a($payment_module_data, 'xt_payments')){
    //if ($payment_module_data)
    // tweak subpayment is allowed
    // the selected subpayment is allowed since it was provided by safecharge
    $payment_module_data->allowed_subpayments = array($_SESSION['selected_payment_sub']);


    // set a nice apm name for confirmation page
    $apm = xt_payments::getApm($_SESSION['selected_payment_sub']);
    define("TEXT_PAYMENT_".strtoupper($_payments[1]), $apm->APMName);
}

