<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

define('TABLE_ORDERS_INVOICES', DB_PREFIX . '_plg_orders_invoices');
define('COL_INVOICE_ID', 'invoice_id');
define('COL_INVOICE_NUMBER', 'invoice_number');
define('COL_INVOICE_PREFIX', 'invoice_prefix');
define('COL_INVOICE_COMMENT', 'invoice_comment');

define('TABLE_ORDERS_INVOICES_TEMPLATES', DB_PREFIX . '_pdf_manager');
define('COL_TEMPLATE_TYPE', 'template_type');
define('COL_TEMPLATE_PDF_OUT_NAME', 'template_pdf_out_name');
define('COL_USE_BACKEND_LANG', 'template_use_be_lng');

define('TABLE_ORDERS_INVOICES_PRODUCTS', DB_PREFIX . '_plg_orders_invoices_products');

define('XT_ORDERS_INVOICES_DEFAULT_TEMPLATE_TYPE','invoice');

// print buttons

define('TABLE_PRINT_BUTTONS', DB_PREFIX . '_print_buttons');
define('COL_PRINT_BUTTONS_ID', 'buttons_id');
define('COL_PRINT_BUTTONS_TEMPLATE_TYPE', COL_TEMPLATE_TYPE);

define('TABLE_PRINT_BUTTONS_LANG', DB_PREFIX . '_print_buttons_lng');
define('COL_PRINT_BUTTONS_LANG_ID', 'id');
define('COL_PRINT_BUTTONS_CAPTION', 'caption');
define('COL_PRINT_BUTTONS_LANG_CODE', 'language_code');

define('COL_PRINT_BUTTONS_TEMPALTE_TYPE', 'tmpl_type');
