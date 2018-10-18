<?php

if ($_payment=='xt_payments'){

    // store the correlation fields for later use
    include_once('plugins/xt_payments/classes/class.xt_payments.php');
    $apm = xt_payments::getApm($_payments[1]);
    $correlations = array();
    $idx = 0;
    if (isset($apm->correlations)) {
        foreach($apm->correlations as $correlation){
            // we have 15 fields max
            if ($idx>14 || !isset($_POST['paymentCorrelation_'.$_payments[1].'_'.$idx])) break;
            if (preg_match('/'.$correlation->ValidationRegex.'/',$_POST['paymentCorrelation_'.$_payments[1].'_'.$idx])!=1){
                $info->_addInfoSession("{$correlation->FieldCaption}: {$correlation->ValidationMessage}");
                $tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment', 'conn'=>'SSL'));
                $xtLink->_redirect($tmp_link);
            }
            $correlations[$correlation->AccountInfoFieldName] = $_POST['paymentCorrelation_'.$_payments[1].'_'.$idx];
            $idx++;
        }
        $_SESSION['pppAdditionalParameters'] = $correlations;
    }
}

