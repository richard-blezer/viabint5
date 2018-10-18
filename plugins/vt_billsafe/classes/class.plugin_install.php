<?php
/*
 ##############################################################################
 #	Plugin for xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 # @version $Id: class.vt_billsafe.php 936 2010-05-12 08:57:50Z ak $
 # @copyright:   found in /lic/copyright.txt
 #
 ##############################################################################
 */

	defined('_VALID_CALL') or die('Direct Access is not allowed.');

	class plugin_install extends plugin {
	    function  __construct() {
    	}

    	//--------------------------------------------------------------------------
    	// INSTALL / UNINSTALL PLUGIN:
    	//--------------------------------------------------------------------------
    	public function installPlugin($payment_id){
			global $db, $store_handler, $language;

			//ALWAYS USE:
    		//if (!$this->_FieldExists('fieldname',TABLE_.....))
    		//CREATE TABLE IF NOT EXISTS ".DB_PREFIX."

			if (!$this->_FieldExists('billsafe_shipped', TABLE_ORDERS_PRODUCTS))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_PRODUCTS." ADD billsafe_shipped INT(11) DEFAULT '0'");

			if (!$this->_FieldExists('billsafe_payed', TABLE_ORDERS_PRODUCTS))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_PRODUCTS." ADD billsafe_payed INT(11) DEFAULT '0'");

			if (!$this->_FieldExists('billsafe_billed_status', TABLE_ORDERS_TOTAL))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_TOTAL." ADD billsafe_billed_status TINYINT DEFAULT '0'");

			if (!$this->_FieldExists('billsafe_shipped_status', TABLE_ORDERS_TOTAL))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_TOTAL." ADD billsafe_shipped_status TINYINT DEFAULT '0'");

			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 24, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 25, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 26, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 27, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 28, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 29, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 30, '', 0, 10000.00, 0, 1);");
			$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 31, '', 0, 10000.00, 0, 1);");

			$db->Execute("CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_plg_vt_billsafe_log (
							log_id INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
							orders_id INT(11) unsigned NOT NULL,
							products_id INT(11) unsigned NOT NULL,
							products_quantity DOUBLE unsigned NOT NULL,
							log_type VARCHAR(32) NOT NULL,
							log_date DATETIME NOT NULL
						  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

			$status_values = 'a:1:{s:4:"data";a:5:{s:15:"enable_download";i:1;s:7:"visible";s:1:"1";s:13:"visible_admin";i:1;s:19:"calculate_statistic";i:1;s:12:"reduce_stock";i:0;}}';
			$db->Execute("INSERT INTO ".TABLE_SYSTEM_STATUS." (status_class, status_values) VALUES('order_status', '".$status_values."')");
			$db->Execute("INSERT INTO ".TABLE_SYSTEM_STATUS_DESCRIPTION." (status_id, language_code, status_name, status_image) VALUES(".$db->Insert_ID().", 'de', 'BILLSAFE - Zahlung erfolgreich', NULL)");
			$db->Execute("UPDATE ".TABLE_CONFIGURATION_PAYMENT." SET config_value=".$db->Insert_ID()." WHERE config_key='VT_BILLSAFE_STATUS_SUCCESS'");

			$status_values = 'a:1:{s:4:"data";a:5:{s:15:"enable_download";i:0;s:7:"visible";s:1:"1";s:13:"visible_admin";i:1;s:19:"calculate_statistic";i:0;s:12:"reduce_stock";i:0;}}';
			$db->Execute("INSERT INTO ".TABLE_SYSTEM_STATUS." (status_class, status_values) VALUES('order_status', '".$status_values."')");
			$db->Execute("INSERT INTO ".TABLE_SYSTEM_STATUS_DESCRIPTION." (status_id, language_code, status_name, status_image) VALUES(".$db->Insert_ID().", 'de', 'BILLSAFE - Zahlung fehlgeschlagen', NULL)");
			$db->Execute("UPDATE ".TABLE_CONFIGURATION_PAYMENT." SET config_value=".$db->Insert_ID()." WHERE config_key='VT_BILLSAFE_STATUS_FAILED'");

			return true;
		}

		public function uninstallPlugin() {
			global $db;

			//ALWAYS USE:
			//if (!$this->_FieldExists('fieldname',TABLE_.....))
			//DROP TABLE IF EXISTS

			if ($this->_FieldExists('billsafe_shipped', TABLE_ORDERS_PRODUCTS))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_PRODUCTS." DROP billsafe_shipped");

			if ($this->_FieldExists('billsafe_payed', TABLE_ORDERS_PRODUCTS))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_PRODUCTS." DROP billsafe_payed");

			if ($this->_FieldExists('billsafe_billed_status', TABLE_ORDERS_TOTAL))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_TOTAL." DROP billsafe_billed_status");

			if ($this->_FieldExists('billsafe_shipped_status', TABLE_ORDERS_TOTAL))
				$db->Execute("ALTER TABLE ".TABLE_ORDERS_TOTAL." DROP billsafe_shipped_status");

			$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_plg_vt_billsafe_log");

			return true;
		}
	}
?>