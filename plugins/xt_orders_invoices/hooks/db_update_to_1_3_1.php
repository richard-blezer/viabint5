<?php
			require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';
			
			$db->Execute("INSERT IGNORE INTO " . TABLE_CONFIGURATION . " " .
				" (`config_key`, `config_value`, `group_id`, `sort_order`, `date_added`, `type`)" .
				" VALUES ('_INVOICE_NUMBER_GLOBAL_LAST_USED', '0', 0, 5, '0000-00-00 00:00:00', 'hidden' )"
			);

		
			$tblExists = $db->GetOne("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND TABLE_NAME='".TABLE_ORDERS_INVOICES."'");
			if ($tblExists)
			{
				// erzeugen von und kopieren der alten ID nach COL_INVOICE_NUMBER
				$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='".COL_INVOICE_NUMBER."' AND TABLE_NAME='".TABLE_ORDERS_INVOICES."'");
				if (!$colExists)
				{
					$db->Execute("ALTER TABLE `".TABLE_ORDERS_INVOICES."` ADD `".COL_INVOICE_NUMBER."` int(11) NOT NULL AFTER `".COL_INVOICE_ID."`");
					$db->Execute("UPDATE `".TABLE_ORDERS_INVOICES."` SET `".COL_INVOICE_NUMBER."` = `".COL_INVOICE_ID."`");
				}
			
				// erzeugen COL_INVOICE_PREFIX
				$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='".COL_INVOICE_PREFIX."' AND TABLE_NAME='".TABLE_ORDERS_INVOICES."'");
				if (!$colExists)
				{
					$db->Execute("ALTER TABLE `".TABLE_ORDERS_INVOICES."` ADD `".COL_INVOICE_PREFIX."` varchar(64) AFTER `".COL_INVOICE_NUMBER."`");
				}
				
				// erzeugen COL_INVOICE_COMMENT
				$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='".COL_INVOICE_COMMENT."' AND TABLE_NAME='".TABLE_ORDERS_INVOICES."'");
				if (!$colExists)
				{
					$db->Execute("ALTER TABLE `".TABLE_ORDERS_INVOICES."` ADD `".COL_INVOICE_COMMENT."` varchar(1024)");
				}
			
				// erzeugen invoice_total_otax (v1.2?)
				$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='invoice_total_otax' AND TABLE_NAME='".TABLE_ORDERS_INVOICES."'");
				if (!$colExists)
				{
					$db->Execute("ALTER TABLE `".TABLE_ORDERS_INVOICES."` ADD `invoice_total_otax` decimal(15,4) NOT NULL");
				}
			}
			else {
				$db->Execute("
				CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "_plg_orders_invoices (
					`".COL_INVOICE_ID."` int(11) NOT NULL AUTO_INCREMENT,
					`".COL_INVOICE_NUMBER."` int(11) NOT NULL,
					`".COL_INVOICE_PREFIX."` varchar(64),
					`orders_id` int(11) NOT NULL,
					`customers_id` int(11) NOT NULL,
					`invoice_firstname` varchar(64) NOT NULL,
					`invoice_lastname` varchar(64) NOT NULL,
					`invoice_issued_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`invoice_due_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
					`invoice_ordered_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
					`invoice_total` decimal(15,4) NOT NULL,
					`invoice_total_otax` decimal(15,4) NOT NULL,
					`invoice_currency` char(3) NOT NULL,
					`invoice_shipping_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
					`invoice_shipping_tax_rate` decimal(7,4) NOT NULL DEFAULT '0.0000',
					`invoice_shipping_currency` char(3) NOT NULL,
					`invoice_packaging_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
					`invoice_packaging_tax_rate` decimal(7,4) NOT NULL DEFAULT '0.0000',
					`invoice_packaging_currency` char(3) NOT NULL,
					`invoice_paid` tinyint(1) NOT NULL DEFAULT '0',
					`invoice_sent` tinyint(1) NULL DEFAULT '0',
					`invoice_sent_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
					`invoice_payment` varchar(25) NOT NULL DEFAULT '',
					`invoice_status` tinyint(4) NOT NULL DEFAULT '1',

					`".COL_INVOICE_COMMENT."` varchar(1024),
					PRIMARY KEY (`".COL_INVOICE_ID."`),
					KEY `invoice_number` (`".COL_INVOICE_NUMBER."`),
					KEY `orders_id` (`orders_id`),
					KEY `customers_id` (`customers_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			");

				$db->Execute("
				CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "_plg_orders_invoices_products (
					`invoice_product_id` int(11) NOT NULL AUTO_INCREMENT,
					`".COL_INVOICE_ID."` int(11) NOT NULL,
					`products_id` int(11) NOT NULL,
					`products_model` varchar(64) NOT NULL,
					`products_name` varchar(255) NOT NULL,
					`products_quantity` decimal(15,2) NOT NULL,
					`products_price` decimal(15,4) NOT NULL,
					`products_discount_rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
					`products_tax_rate` decimal(7,4) NOT NULL DEFAULT '0.0000',
					`products_currency` char(3) NOT NULL,
					PRIMARY KEY (`invoice_product_id`),
					KEY `products_id` (`products_id`),

					KEY `".COL_INVOICE_ID."` (`".COL_INVOICE_ID."`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			");
			}
			
			$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type='send_invoice' ");	
			
			$db->Execute("INSERT IGNORE INTO " . TABLE_MAIL_TEMPLATES . " (`tpl_type`) VALUES ('send_invoice');");
			$emailTemplateId = $db->GetOne("SELECT tpl_id FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type='send_invoice'");
			
			
			$languages = $language->_getLanguageList();
			if (count($languages)) {
				$db->Execute("TRUNCATE TABLE  " . DB_PREFIX . "_plg_orders_invoices_templates_content;");
				$tpl = file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/tpl_invoice.html', true);
				foreach ($languages as $key => $val) {
					if ($val['code'] == 'de') {
						$db->Execute(
							"INSERT INTO " . DB_PREFIX . "_plg_orders_invoices_templates_content " .
								" (`template_id`, `language_code`, `template_body`)" .

								" VALUES (1, '" . $val['code'] . "', '$tpl')"
						);

						$tplBodyHtml = file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/'.$val['code'].'/tpl_de_mail_body_html.html', true);
						$tplBodyTxt =  file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/'.$val['code'].'/tpl_de_mail_body_txt.html', true);
						$tplSubject =  file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/'.$val['code'].'/tpl_de_mail_subject.html', true);

						$insert_array = array();
						$insert_array['tpl_id'] = $emailTemplateId;
						$insert_array['language_code'] = $val['code'];


						$insert_array['mail_body_html'] = $tplBodyHtml;
						$insert_array['mail_body_txt'] = $tplBodyTxt;
						$insert_array['mail_subject'] = $tplSubject;
						
						$db->AutoExecute(TABLE_MAIL_TEMPLATES_CONTENT, $insert_array);
					
					} else {
						$db->Execute(
							"INSERT INTO " . DB_PREFIX . "_plg_orders_invoices_templates_content " .
								" (`template_id`, `language_code`, `template_body`)" .

								" VALUES (1, '" . $val['code'] . "', '$tpl')"
						);

						$tplBodyHtml = file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/en/tpl_en_mail_body_html.html', true);
						$tplBodyTxt =  file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/en/tpl_en_mail_body_txt.html', true);
						$tplSubject =  file_get_contents(_SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_orders_invoices/installer/tpl/en/tpl_en_mail_subject.html', true);

						$insert_array = array();
						$insert_array['tpl_id'] = $emailTemplateId;
						$insert_array['language_code'] = $val['code'];



						$insert_array['mail_body_html'] = $tplBodyHtml;
						$insert_array['mail_body_txt'] = $tplBodyTxt;
						$insert_array['mail_subject'] = $tplSubject;
						$db->AutoExecute(TABLE_MAIL_TEMPLATES_CONTENT, $insert_array);
					}
				}
			}
			
?>