<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

if(XT_ORDERS_INVOICE_AUTO_SEND_ORDERS=='true'){
    $srv_web = str_replace('xtAdmin/', '', _SRV_WEB);

    if($status == XT_ORDERS_INVOICE_STATUS_GENERATE){

        require_once _SRV_WEBROOT . 'plugins/xt_orders_invoices/classes/class.xt_orders_invoices.php';
        $invoice = new xt_orders_invoices();

        $invoice->autoGenerate($this->oID,$this->order_data['customers_email_address']);

    }

}
?>