<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
    $link_array = array('page'=>$page->page_name, 'paction'=>'', 'conn'=>'SSL');
}
                
?>