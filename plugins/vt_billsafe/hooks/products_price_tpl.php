<?php

global $p_info;

$price = $p_info->data['products_price']['plain']*100;
$price = round($price,0);


if (VT_BILLSAFE_ACTIVATE_INSTALLMENTS=='true') {
    echo '<script type="text/javascript" src="http://www.billsafe.de/installments/interest-calculator/'.VT_BILLSAFE_CLIENTID.'/'.VT_BILLSAFE_PASSCODE.'"></script>';
    echo '<div style="display:none;" class="bsic bsic-amount-'.$price.' billsafe"></div>';
}