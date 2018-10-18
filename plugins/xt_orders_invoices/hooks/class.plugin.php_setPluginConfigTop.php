<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// ermittel ob änderung 'separater kreis' von 0 auf 1
if ($data['code'] == "xt_orders_invoices")
{
    /// wir wollen wissen, wann von globalem kreis auf separaten gewechselt wird um den separaten zähler zu reset'ten
    global $store_handler;
    require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/class.xt_orders_invoices.php';
    $xti = new xt_orders_invoices();

    $stores = $store_handler->getStores();
    foreach($stores as $store)
    {
        $old_isSeparateAssignment = $xti->isSeparateAssignmentForShop($store['id']);
        $new_isSeparateAssignment = empty($data['conf_XT_ORDERS_INVOICE_SEPARATE_NUMBER_ASSIGNMENT_shop_'.$store['id']]) ? false:true;

        if(!$old_isSeparateAssignment && $new_isSeparateAssignment)
        {
            // wir könnten es auch abfragen nur auf $old_invoiceNumberAssignment = false
            // aber ich will den db zustand solange wie mgl halten
            $data['conf_XT_ORDERS_INVOICE_SEPARATE_NUMBER_ASSIGNMENT_LAST_USED_shop_'.$store['id']] = 0;
        }
    }

}