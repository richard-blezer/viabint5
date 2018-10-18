<?php
    defined('_VALID_CALL') or die('Direct Access is not allowed.');

    include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';
    global $language,$db;
	

	$db->Execute("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_pdf_manager` (
	  `template_id` int(11) NOT NULL AUTO_INCREMENT,
	  `template_name` varchar(255) NOT NULL,
	  `template_type` varchar(64) NOT NULL,
	  `template_pdf_out_name` varchar(512) NOT NULL,
	  `template_use_be_lng` int(1) NOT NULL DEFAULT 0,
	  PRIMARY KEY (`template_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
	
	
	$db->Execute("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_pdf_manager_content` (
			  `template_id` int(11) NOT NULL,
			  `language_code` char(2) NOT NULL DEFAULT '',
			  `template_body` text NOT NULL,
			  PRIMARY KEY (`template_id`,`language_code`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		
	$tblExists = $db->GetOne("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND TABLE_NAME='".DB_PREFIX."_plg_orders_invoices_templates'");
	$tblExists_content = $db->GetOne("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND TABLE_NAME='".DB_PREFIX."_plg_orders_invoices_templates_content'");
	if ($tblExists) {
		$rc = $db->Execute("SELECT * FROM ".DB_PREFIX."_plg_orders_invoices_templates");
		if ($rc->RecordCount()>0){
			while(!$rc->EOF){
				$db->Execute("INSERT INTO " . DB_PREFIX . "_pdf_manager (template_name) VALUES('".$rc->fields['template_name']."');");
				$t = $db->Insert_ID();
				if ($tblExists_content){
					$rc2 = $db->Execute("SELECT * FROM ".DB_PREFIX."_plg_orders_invoices_templates_content WHERE template_id=".$rc->fields['template_id']);
					if ($rc2->RecordCount()>0){
						while(!$rc2->EOF){
							$db->Execute("INSERT INTO " . DB_PREFIX . "_pdf_manager_content (template_id,language_code,template_body) VALUES('".$t."','".$rc2->fields['language_code']."','".mysql_real_escape_string($rc2->fields['template_body'])."');");
							$rc2->MoveNext();
						}
					}$rc2->Close();
				}
				$rc->MoveNext();
			}
		}$rc->Close();
		
		$db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX . "_plg_orders_invoices_templates;");
		$db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX . "_plg_orders_invoices_templates_content;");
	} 
	
	
	