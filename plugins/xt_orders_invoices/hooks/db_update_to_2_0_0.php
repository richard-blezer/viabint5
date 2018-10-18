<?php
    defined('_VALID_CALL') or die('Direct Access is not allowed.');
   
    include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';
    global $language,$db;

    $db->Execute("ALTER TABLE `".TABLE_ORDERS_INVOICES."` MODIFY COLUMN `".COL_INVOICE_COMMENT."` text ");
  
    $db->Execute("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_pdf_manager` (
      `template_id` int(11) NOT NULL AUTO_INCREMENT,
      `template_name` varchar(255) NOT NULL,
      `".COL_TEMPLATE_TYPE."` varchar(64) NOT NULL,
      `".COL_TEMPLATE_PDF_OUT_NAME."` varchar(512) NOT NULL,
      `template_use_be_lng` int(1) NOT NULL DEFAULT 0,
      PRIMARY KEY (`template_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8; ");
      
    $db->Execute("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_pdf_manager_content` (
      `template_id` int(11) NOT NULL,
      `language_code` char(2) NOT NULL DEFAULT '',
      `template_body` text NOT NULL,
      PRIMARY KEY (`template_id`,`language_code`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ");
   
   
   $colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='".COL_TEMPLATE_TYPE."' AND TABLE_NAME='".DB_PREFIX."_pdf_manager'");
   if (!$colExists){
		 $db->Execute("ALTER TABLE `" . DB_PREFIX . "_pdf_manager` ADD `".COL_TEMPLATE_TYPE."` varchar(64) NOT NULL");
   }
   
   $colExists2 = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='".COL_TEMPLATE_PDF_OUT_NAME."' AND TABLE_NAME='".DB_PREFIX."_pdf_manager'");
   if (!$colExists2){
		 $db->Execute("ALTER TABLE `" . DB_PREFIX . "_pdf_manager` ADD `".COL_TEMPLATE_PDF_OUT_NAME."` varchar(512) NOT NULL");
   }
   $colExists3 = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='template_use_be_lng' AND TABLE_NAME='".DB_PREFIX."_pdf_manager'");
   if (!$colExists3){
		 $db->Execute("ALTER TABLE `" . DB_PREFIX . "_pdf_manager` ADD `template_use_be_lng` int(1) NOT NULL DEFAULT 0");
   }
   
   $tpl = $db->GetOne("SELECT template_name FROM " . DB_PREFIX . "_pdf_manager  WHERE template_name='Order invoice'");
  
   if (!$tpl){
		
		$db->Execute(
        "INSERT INTO " . DB_PREFIX . "_pdf_manager " .
            " ( `template_name`, `".COL_TEMPLATE_TYPE."`, `".COL_TEMPLATE_PDF_OUT_NAME."`) " .
            " VALUES ( 'Order invoice','invoice','{\$template_type}_{\$shop_name}_order-{\$orders_id}_invoice-{\$invoice_number}_')"
		);
   }
   $tpl2 = $db->GetOne("SELECT template_name FROM " . DB_PREFIX . "_pdf_manager  WHERE template_name='Delivery note'");
   if (!$tpl2){
		$db->Execute(
        "INSERT INTO " . DB_PREFIX . "_pdf_manager " .
            " ( `template_name`, `".COL_TEMPLATE_TYPE."`, `".COL_TEMPLATE_PDF_OUT_NAME."`) " .
            " VALUES ( 'Delivery note','delivery-note','{\$template_type}_{\$shop_name}_order-{\$orders_id}_')"
		);
   }
   
    $pdfId = $db->Insert_ID();
    
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

    $languages = $language->_getLanguageList();
    if (count($languages)) {
        $tpl = file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/tpl_invoice.html', true);
        $tpl2 = file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/tpl_delivery_note.html', true);
        foreach ($languages as $key => $val) {

                $db->Execute(
                    "INSERT INTO " . DB_PREFIX . "_pdf_manager_content " .
                        " (`template_id`, `language_code`, `template_body`)" .
                        " VALUES ((SELECT template_id FROM ".DB_PREFIX . "_pdf_manager WHERE template_name='Order invoice' LIMIT 0,1), '" . $val['code'] . "', '$tpl')"
                );

                $db->Execute("INSERT INTO " . TABLE_PRINT_BUTTONS_LANG . " " .
                    " (`".COL_PRINT_BUTTONS_ID."`,`".COL_PRINT_BUTTONS_LANG_CODE."`,`".COL_PRINT_BUTTONS_CAPTION."`)" .
                    " VALUES ('$insertId', '".$val['code']."', 'Lieferschein drucken' )"
                );

                 // insert id 2 taken from fixed id in db_install
               $db->Execute(
                    "INSERT INTO " . DB_PREFIX . "_pdf_manager_content " .
                    " (`template_id`, `language_code`, `template_body`)" .
                    " VALUES ((SELECT template_id FROM ".DB_PREFIX . "_pdf_manager WHERE template_name='Delivery note' LIMIT 0,1), '" . $val['code'] . "', '$tpl2')"
                );

        }
    }
	
	$menu = $db->GetOne("SELECT pid FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text='xt_print_buttons'");
	if (!$menu){
	
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
	}