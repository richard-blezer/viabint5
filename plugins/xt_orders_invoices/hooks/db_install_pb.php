<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';

$db->Execute("
    CREATE TABLE IF NOT EXISTS " . TABLE_PRINT_BUTTONS . " (
        `".COL_PRINT_BUTTONS_ID."` int(11) NOT NULL AUTO_INCREMENT,
        `".COL_PRINT_BUTTONS_TEMPLATE_TYPE."` varchar(64) NOT NULL,

        PRIMARY KEY (`".COL_PRINT_BUTTONS_ID."`)
    ) DEFAULT CHARSET=utf8;
");

$db->Execute("INSERT INTO " . TABLE_PRINT_BUTTONS . " " .
    " (`".COL_PRINT_BUTTONS_TEMPLATE_TYPE."`)" .
    " VALUES ('delivery-note' )"
);

$insertId = $db->lastInsID;

$db->Execute("
    CREATE TABLE IF NOT EXISTS " . TABLE_PRINT_BUTTONS_LANG . " (
        `".COL_PRINT_BUTTONS_ID."` int(11) NOT NULL,
        `".COL_PRINT_BUTTONS_LANG_CODE."` varchar(3),
        `".COL_PRINT_BUTTONS_CAPTION."` varchar(64),

        PRIMARY KEY (`".COL_PRINT_BUTTONS_ID."`,`".COL_PRINT_BUTTONS_LANG_CODE."`)
    ) DEFAULT CHARSET=utf8;
");

global $language;
$languages = $language->_getLanguageList();
if (count($languages)) {
    // button caption
    foreach ($languages as $key => $val) {
        $db->Execute("INSERT INTO " . TABLE_PRINT_BUTTONS_LANG . " " .
            " (`".COL_PRINT_BUTTONS_ID."`,`".COL_PRINT_BUTTONS_LANG_CODE."`,`".COL_PRINT_BUTTONS_CAPTION."`)" .
            " VALUES ('$insertId', '".$val['code']."', 'Lieferschein drucken' )"
        );
    }

    // pdf template 'delivery-note'
    $tpl = file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/tpl_delivery_note.html', true);
    foreach ($languages as $key => $val)
    {
        // insert id 2 taken from fixed id in db_install
       $db->Execute(
            "INSERT INTO " . DB_PREFIX . "_pdf_manager_content " .
            " (`template_id`, `language_code`, `template_body`)" .
            " VALUES ((SELECT template_id FROM ".DB_PREFIX . "_pdf_manager WHERE template_name='Delivery note'), '" . $val['code'] . "', '$tpl')"
        );
    }
}

$db->Execute("INSERT INTO " . TABLE_ADMIN_NAVIGATION . "
        (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`)
    VALUES (NULL ,
        'xt_print_buttons',
        'images/icons/table_gear.png',
        '&plugin=xt_orders_invoices&load_section=xt_print_buttons',
        'adminHandler.php',
        '221000',
        'contentroot',
        'I',
        'W'
    );
");