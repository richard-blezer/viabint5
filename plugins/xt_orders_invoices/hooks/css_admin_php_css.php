<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

echo '
    .xt_orders_invoices-overdue {
        color: #fa8072;
        font-weight: bold !important;
    }
    
    .xt_orders_invoices_edit {
        background-image: url(images/icons/table_edit.png) !important;
    }
    
    .xt_orders_invoices_view {
        background-image: url(images/icons/table_go.png) !important;
    }
    
    .xt_orders_invoices_cancel {
        background-image: url(images/icons/table_delete.png) !important;
    }
    
    .xt_orders_invoices_email {
        background-image: url(images/icons/email.png) !important;
    }
		
	.xt_orders_invoices_print_dummy {background-image: url(images/icons/eye.png) !important;}
';

?>