<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';

if(XT_PAYMENTS_PAYPAL_EXPRESS_ENABLED=='true' && $_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true && $page->page_action!='success' && $page->page_action!='process'){

    include _SRV_WEBROOT.'xtCore/pages/page_action/checkout.confirmation.php';
    $tpl_data = array_merge($tpl_data, $checkout_data);

    global $template;
    $tpl = 'xt_paypal_checkout_page.html';
    $template->getTemplatePath($tpl, 'xt_payments', '', 'plugin');

    $brotkrumen = new brotkrumen();
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'index', 'exclude'=>'cat_info_coID_mnf')),TEXT_HOME);
    $brotkrumen->_addItem($xtLink->_link(array('page'=>'checkout', '')),TEXT_PAYMENTS_PAYPAL_EXPRESS);

}
?>