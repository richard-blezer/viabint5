<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_update.php 69 2011-10-17 15:12:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class nd_affiliate_update {
	var $plugin_id = 0;
	var $plugin_code = 'nd_affiliate';
	var $version = '';
	
	function nd_affiliate_update() {
		global $db;
		// get the plugin_id
		$plugin = $db->Execute("SELECT plugin_id, version FROM " . TABLE_PLUGIN_PRODUCTS . " WHERE code='nd_affiliate'");
		$this->plugin_id = $plugin->fields['plugin_id'];
		$this->version = $plugin->fields['version'];
	}
	
	function processUpdate() {
		$message = '';
		$updated = false;
		
		if($this->version == '1.0.2' || $this->version == '1.0.0') {
			$message .= $this->update_102_110();
			$updated = true;
		}
		
		if($this->version == '1.1.0') {
			$message .= $this->update_110_120();
			$updated = true;
		}
		
		if($this->version == '1.2.0') {
			$message .= $this->update_120_130();
			$updated = true;
		}
		
		if($this->version == '1.3.0') {
			$message .= $this->update_130_131();
			$message .= $this->update_131_132();
			$updated = true;
		}
		
		if($this->version == '1.3.1') {
			$message .= $this->update_131_132();
			$updated = true;
		}
		
		if($this->version == '1.3.2') {
			$message .= $this->update_132_133();
			$updated = true;
		}
		
		if($updated) {
			$message .= 'Update processed! Your Plugin is now Up To Date.';
		} else {
			$message .= 'No Update needed! Plugin Up To Date.';
		}
		
		return $message;
	}
	
	function update_102_110 () {
		global $db;
		$message = '<b>Updating nd_affiliate Version 1.0.2 to 1.1.0</b><br />';
		$i = 0;
		$j = 0;
		
		// ----------------- Update ----------------------
		// Insert Value for the new Configvar AFFILIATE_TIER_SHOW_SUBPARTNER
		$check = $db->Execute("SELECT id FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key = 'AFFILIATE_TIER_SHOW_SUBPARTNER'");
		if($check->RecordCount() == 0) {
			$stores = $db->Execute("SELECT shop_id FROM " . TABLE_MANDANT_CONFIG);
			while(!$stores->EOF) {
				$sql_data_array = array('config_key' => 'AFFILIATE_TIER_SHOW_SUBPARTNER',
										'config_value' => 'true',
										'plugin_id' => $this->plugin_id,
										'type' => 'dropdown',
										'url' => 'conf_truefalse',
										'group_id' => '0',
										'shop_id' => $stores->fields['shop_id'],
										'last_modified' => $db->BindTimestamp(time()),
										'date_added' => $db->BindTimestamp(time()));
				$db->AutoExecute(TABLE_PLUGIN_CONFIGURATION, $sql_data_array, 'INSERT');
				$stores->MoveNext();
				$i++;
			}
			// Insert Language-Code for the new Configvar AFFILIATE_TIER_SHOW_SUBPARTNER
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TIER_SHOW_SUBPARTNER_TITLE',
									'language_value' => 'Subpartner anzeigen',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TIER_SHOW_SUBPARTNER_TITLE',
									'language_value' => 'Show Subaffiliates',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TIER_SHOW_SUBPARTNER already exists!<br />';
			$j++;
		}
		
		// Insert Value for new TAB in Affiliate-Details in Admin Backend
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_SUBAFFILIATETREE'");
		if($check->RecordCount() == 0) {
			// Update Hook for row_actions.php
			$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			'if (\$_GET[''type'']==''nd_affiliate_summary'') {
				require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''nd_affiliate/classes/class.nd_affiliate_summary.php''); 
				\$_summary = new nd_affiliate_summary();
				echo \$_summary->displaySummary(''admin'');
			}
			if (\$_GET[''type'']==''nd_affiliate_subaffiliatetree'') {
				require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''nd_affiliate/classes/class.nd_affiliate_affiliate.php''); 
				\$_affiliate = new nd_affiliate_affiliate((int)\$_GET[''aID'']);
				echo \$_affiliate->getSubaffiliateTree();
			}
			if (\$_GET[''type'']==''nd_affiliate_update'') {
	    		require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''nd_affiliate/classes/class.nd_affiliate_update.php''); 
	    		\$_update = new nd_affiliate_update();
	    		echo \$_update->processUpdate();
	    	}'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'row_actions.php:actions'");
			$i++;
			// Insert Language-Code for the new TAB in Affiliates-Menu
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_SUBAFFILIATETREE',
									'language_value' => 'Subpartner',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_SUBAFFILIATETREE',
									'language_value' => 'Subaffiliates',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_SUBAFFILIATETREE already exists!<br />';
			$j++;
		}
		
		// Insert Language-Value for Password-Field in Adminbackend
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_PASSWORD'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_PASSWORD',
									'language_value' => 'Passwort',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_PASSWORD',
									'language_value' => 'Password',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_PASSWORD already exists!<br />';
			$j++;
		}
		
		$uninstall_sql = <<<SQL_ENDE
			\$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text like ''nd_affiliate%''");
		
			\$db->Execute("DELETE FROM ".TABLE_ADMIN_ACL_AREA." WHERE area_name like ''nd_affiliate%''");
			
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_affiliate");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_inventory");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_inventory_history");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_clickthroughs");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_payment");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_payment_status");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_payment_status_history");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_sales");
			
			\$cb = \$db->Execute("SELECT block_id FROM " . TABLE_CONTENT_BLOCK . " WHERE block_tag = ''nd_affiliate''");
			\$cid = \$db->Execute("SELECT content_id FROM " . TABLE_CONTENT_TO_BLOCK . " WHERE block_id = " . \$cb->fields[''block_id'']);
			while(!\$cid->EOF) {
				\$db->Execute("DELETE FROM " . TABLE_CONTENT . " WHERE content_id=" . \$cid->fields[''content_id'']);
				\$db->Execute("DELETE FROM " . TABLE_CONTENT_ELEMENTS . " WHERE content_id=" . \$cid->fields[''content_id'']);
				\$db->Execute("DELETE FROM " . TABLE_CONTENT_TO_BLOCK . " WHERE content_id=" . \$cid->fields[''content_id'']);
				\$cid->MoveNext();
			}
			\$db->Execute("DELETE FROM " . TABLE_CONTENT_BLOCK . " WHERE block_tag=''nd_affiliate''");
			
			\$db->Execute("ALTER TABLE " . TABLE_PRODUCTS . " DROP affiliate_provision");
			\$db->Execute("ALTER TABLE " . TABLE_CATEGORIES . " DROP affiliate_provision");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS_STATUS . " DROP affiliate_provision");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " DROP affiliate_referrer");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " DROP affiliate_date");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " DROP affiliate_duration");
			
			\$tpl = \$db->Execute("SELECT tpl_id FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type like ''nd_affiliate%''");
			while(!\$tpl->EOF) {
				\$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_id = " . \$tpl->fields[''tpl_id'']);
				\$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES_CONTENT . " WHERE tpl_id = " . \$tpl->fields[''tpl_id'']);
				\$tpl->MoveNext();
			}
SQL_ENDE;
		
		$db->Execute("UPDATE " . TABLE_PLUGIN_SQL . " SET uninstall = '" . $uninstall_sql . "', version = '1.1.0' WHERE plugin_id = " . $this->plugin_id);
		
		// change the products-, categories- and customersstatusprovision to varchar fields
		$db->Execute("ALTER TABLE " . TABLE_PRODUCTS . " CHANGE affiliate_provision affiliate_provision VARCHAR(3) NULL DEFAULT NULL");
		$db->Execute("ALTER TABLE " . TABLE_CATEGORIES . " CHANGE affiliate_provision affiliate_provision VARCHAR(3) NULL DEFAULT NULL");
		$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS_STATUS . " CHANGE affiliate_provision affiliate_provision VARCHAR(3) NULL DEFAULT NULL");
		$i++;
		
		// insert stuff for Lifetimeprovision
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_LIFETIME_PROVISION_TITLE'");
		if($check->RecordCount() == 0) {
			// Alter some Tables
			$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " ADD affiliate_referrer int");
			$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " ADD affiliate_date datetime");
			$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " ADD affiliate_duration int");
			$db->Execute("ALTER TABLE " . TABLE_AFFILIATE . " ADD affiliate_lifetime_duration VARCHAR(6) AFTER affiliate_commission_percent");
			// Insert the hooks
			$sql_data_array = array('plugin_id' => $this->plugin_id,
									'hook' => 'class.customer.php:_getParams_header',
									'code' => "\$header['affiliate_referrer'] = array('type' => 'dropdown', 'url'  => 'DropdownData.php?get=affiliates');",
									'code_status' => '1',
									'plugin_code' => $this->plugin_code,
									'sortorder' => '1');
			$db->AutoExecute(TABLE_PLUGIN_CODE, $sql_data_array, 'INSERT');
			$sql_data_array = array('plugin_id' => $this->plugin_id,
									'hook' => 'admin_dropdown.php:dropdown',
									'code' => "switch (\$request['get']) {
											   case 'affiliates':
											   require_once _SRV_WEBROOT.'plugins/nd_affiliate/classes/class.nd_affiliate_affiliate.php';
											   \$affiliate = new nd_affiliate_affiliate();
											   \$result = \$affiliate->_getAffiliates();
											   break;
											   }",
									'code_status' => '1',
									'plugin_code' => $this->plugin_code,
									'sortorder' => '1');
			$db->AutoExecute(TABLE_PLUGIN_CODE, $sql_data_array, 'INSERT');
			$stores = $db->Execute("SELECT shop_id FROM " . TABLE_MANDANT_CONFIG);
			while(!$stores->EOF) {
				$sql_data_array = array('config_key' => 'AFFILIATE_LIFETIME_PROVISION',
										'config_value' => 'false',
										'plugin_id' => $this->plugin_id,
										'type' => 'dropdown',
										'url' => 'conf_truefalse',
										'group_id' => '0',
										'shop_id' => $stores->fields['shop_id'],
										'last_modified' => $db->BindTimestamp(time()),
										'date_added' => $db->BindTimestamp(time()));
				$db->AutoExecute(TABLE_PLUGIN_CONFIGURATION, $sql_data_array, 'INSERT');
				$sql_data_array = array('config_key' => 'AFFILIATE_LIFETIME_DURATION',
										'config_value' => '365',
										'plugin_id' => $this->plugin_id,
										'type' => '',
										'url' => '',
										'group_id' => '0',
										'shop_id' => $stores->fields['shop_id'],
										'last_modified' => $db->BindTimestamp(time()),
										'date_added' => $db->BindTimestamp(time()));
				$db->AutoExecute(TABLE_PLUGIN_CONFIGURATION, $sql_data_array, 'INSERT');
				$stores->MoveNext();
				$i++;
			}
			// Insert Language-Code for the new Configvar AFFILIATE_LIFETIME_PROVISION
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_LIFETIME_PROVISION_TITLE',
									'language_value' => 'Lifetime Provision',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_LIFETIME_PROVISION_TITLE',
									'language_value' => 'Lifetime Provision',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			// Insert Language-Code for the new Configvar AFFILIATE_LIFETIME_DURATION
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_LIFETIME_DURATION_TITLE',
									'language_value' => 'Lifetime-Dauer',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_LIFETIME_DURATION_TITLE',
									'language_value' => 'Lifetime-Duration',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			// Insert Language-Code for the new Customerfield TEXT_AFFILIATE_REFERRER
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_REFERRER',
									'language_value' => 'Affiliate',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_REFERRER',
									'language_value' => 'Affiliate',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			// Insert Language-Code for the new Customerfield TEXT_AFFILIATE_DURATION
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_DURATION',
									'language_value' => 'Lifetime-Dauer',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_DURATION',
									'language_value' => 'Lifetime-Duration',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			// Insert Language-Code for the new Table-Field TEXT_AFFILIATE_LIFETIME_DURATION
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_LIFETIME_DURATION',
									'language_value' => 'Lifetime-Dauer',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_LIFETIME_DURATION',
									'language_value' => 'Lifetime-Duration',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_LIFETIME_PROVISION_TITLE already exists!<br />';
			$j++;
		}
		
		// Alter Table Payment for Shop-Dependant-Payments
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE_PAYMENT . " ADD affiliate_shop_id INT(11) NOT NULL DEFAULT '1' AFTER affiliate_payment_status");
		$i++;
		
		// Delete language for Homepage-Error
		$db->Execute("DELETE FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_ERROR_HOMEPAGE' AND plugin_key = 'nd_affiliate'");
		$i++;
		
		// insert language for TEXT_AFFILIATE_PAYMENT_BANK_IBAN
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_PAYMENT_BANK_IBAN'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_PAYMENT_BANK_IBAN',
									'language_value' => 'IBAN',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_PAYMENT_BANK_IBAN',
									'language_value' => 'IBAN',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_PAYMENT_BANK_IBAN already exists!<br />';
			$j++;
		}
		
		// Alter Hook for admin_main.php:bottom
		$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			'if(AFFILIATE_ACTIVATE_PLUGIN == ''true'') {
		    define(''TABLE_AFFILIATE'', DB_PREFIX . ''_affiliate_affiliate'');
		    define(''TABLE_AFFILIATE_INVENTORY'', DB_PREFIX . ''_affiliate_inventory'');
		    define(''TABLE_AFFILIATE_INVENTORY_HISTORY'', DB_PREFIX . ''_affiliate_inventory_history'');
		    define(''TABLE_AFFILIATE_CLICKTHROUGHS'', DB_PREFIX . ''_affiliate_clickthroughs'');
		    define(''TABLE_AFFILIATE_SALES'', DB_PREFIX . ''_affiliate_sales'');
		    define(''TABLE_AFFILIATE_PAYMENT'', DB_PREFIX . ''_affiliate_payment'');
		    define(''TABLE_AFFILIATE_PAYMENT_STATUS'', DB_PREFIX . ''_affiliate_payment_status'');
		    define(''TABLE_AFFILIATE_PAYMENT_STATUS_HISTORY'', DB_PREFIX . ''_affiliate_payment_status_history'');
		}
		require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''/nd_affiliate/classes/class.nd_affiliate_inventory.php'');'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'admin_main.php:bottom'");
		$i++;
		
		$db->Execute("Update " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.1.0' WHERE plugin_id = " . $this->plugin_id);
		$this->version = '1.1.0';
		// ----------------- Update ----------------------
		
		$message .= $i . ' Values updated<br />';
		$message .= $j . ' Values already exists<br />';
		$message .= '<b>Update nd-affiliate Version 1.0.2 to 1.1.0 successfull<br />';
		$message .= '______________________________________________________________________<br /><br />';
		
		return $message;
	}
	
	function update_110_120() {
		global $db;
		$message = '<b>Updating nd_affiliate Version 1.1.0 to 1.2.0</b><br />';
		$i = 0;
		$j = 0;
		
		// ----------------- Update ----------------------
		
		// Insert Hook for row_actions in affiliate-details
		$check = $db->Execute("SELECT id FROM " . TABLE_PLUGIN_CODE . " WHERE hook = 'css_admin.php:css' AND plugin_id = " . $this->plugin_id);
		if($check->RecordCount() == 0) {
			$sql_data_array = array('plugin_id' => $this->plugin_id,
									'hook' => 'css_admin.php:css',
									'code' => "echo '.nd_affiliate_subaffiliatetree {background-image: url(images/icons/chart_organisation.png) !important;}';
											   echo '.nd_affiliate_payment {background-image: url(images/icons/money.png) !important;}';
											   echo '.nd_affiliate_sales {background-image: url(images/icons/basket.png) !important;}';
											   echo '.nd_affiliate_clicks {background-image: url(images/icons/mouse.png) !important;}';
											   echo '.nd_affiliate_summary {background-image: url(images/icons/chart_curve.png) !important;}';",
									'code_status' => '1',
									'plugin_code' => $this->plugin_code,
									'sortorder' => '1');
			$db->AutoExecute(TABLE_PLUGIN_CODE, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'Hook for css_admin.php:css already exists!<br />';
			$j++;
		}
		
		
		// Insert languagecode for Paymentprocess
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_PROCESS_PAYMENT'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_PROCESS_PAYMENT',
									'language_value' => 'Partnerabrechnung starten',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_PROCESS_PAYMENT',
									'language_value' => 'Start Affiliatepayment',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			// Update Hook for row_actions.php
			$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			'if (\$_GET[''type'']==''nd_affiliate_summary'') {
				require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''nd_affiliate/classes/class.nd_affiliate_summary.php''); 
				\$_summary = new nd_affiliate_summary();
				\$_summary->buildSummary();
				echo \$_summary->displaySummary();
			}
			if (\$_GET[''type'']==''nd_affiliate_update'') {
	    		require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''nd_affiliate/classes/class.nd_affiliate_update.php''); 
	    		\$_update = new nd_affiliate_update();
	    		echo \$_update->processUpdate();
	    	}'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'row_actions.php:actions'");
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_PROCESS_PAYMENT already exists!<br />';
			$j++;
		}
		
		// Insert Language-Value for Subaffiliatetree in Adminbackend
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_ND_AFFILIATE_SUBAFFILIATETREE'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_ND_AFFILIATE_SUBAFFILIATETREE',
									'language_value' => 'Subpartner',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_ND_AFFILIATE_SUBAFFILIATETREE',
									'language_value' => 'Subaffiliates',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_ND_AFFILIATE_SUBAFFILIATETREE already exists!<br />';
			$j++;
		}
		
		// change Affiliate-Table because telephone and homepage is not longer mandatory
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE . " CHANGE affiliate_homepage affiliate_homepage VARCHAR( 96 ) NULL");
		$i++;
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE . " CHANGE affiliate_telephone affiliate_telephone VARCHAR( 96 ) NULL");
		$i++;
		
		// change Inventory-Table because target and url can be left blank in the form
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE_INVENTORY . " CHANGE inventory_target inventory_target VARCHAR( 16 ) NULL");
		$i++;
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE_INVENTORY . " CHANGE inventory_url inventory_url VARCHAR( 255 ) NULL");
		$i++;
		
		$db->Execute("Update " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.2.0' WHERE plugin_id = " . $this->plugin_id);
		$this->version = '1.2.0';
		// ----------------- Update ----------------------
		
		$message .= $i . ' Values updated<br />';
		$message .= $j . ' Values already exists<br />';
		$message .= '<b>Update nd-affiliate Version 1.1.0 to 1.2.0 successfull<br />';
		$message .= '______________________________________________________________________<br /><br />';
		
		return $message;
	}
	
	function update_120_130() {
		global $db;
		$message = '<b>Updating nd_affiliate Version 1.2.0 to 1.3.0</b><br />';
		$i = 0;
		$j = 0;
		
		// ----------------- Update ----------------------
		// Insert Value for the new Configvar AFFILIATE_NOTIFY_AFTER_BILLING
		$check = $db->Execute("SELECT id FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key = 'AFFILIATE_NOTIFY_AFTER_BILLING'");
		if($check->RecordCount() == 0) {
			$stores = $db->Execute("SELECT shop_id FROM " . TABLE_MANDANT_CONFIG);
			while(!$stores->EOF) {
				$sql_data_array = array('config_key' => 'AFFILIATE_NOTIFY_AFTER_BILLING',
										'config_value' => 'true',
										'plugin_id' => $this->plugin_id,
										'type' => 'dropdown',
										'url' => 'conf_truefalse',
										'group_id' => '0',
										'shop_id' => $stores->fields['shop_id'],
										'last_modified' => $db->BindTimestamp(time()),
										'date_added' => $db->BindTimestamp(time()));
				$db->AutoExecute(TABLE_PLUGIN_CONFIGURATION, $sql_data_array, 'INSERT');
				$stores->MoveNext();
				$i++;
			}
			// Insert Language-Code for the new Configvar AFFILIATE_NOTIFY_AFTER_BILLING
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_NOTIFY_AFTER_BILLING_TITLE',
									'language_value' => 'Payments benachrichtigen',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_NOTIFY_AFTER_BILLING_TITLE',
									'language_value' => 'Notify Payments',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_NOTIFY_AFTER_BILLING already exists!<br />';
			$j++;
		}
		
		// insert language for TEXT_AFFILIATE_LAST_MODIFIED
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_LAST_MODIFIED'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_LAST_MODIFIED',
									'language_value' => 'Letzte Änderung',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_LAST_MODIFIED',
									'language_value' => 'last modified',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_LAST_MODIFIED already exists!<br />';
			$j++;
		}
		
		// change Affiliate-Payment-Table because of Status-Problems with veyton-Framework
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE_PAYMENT . " CHANGE affiliate_payment_status affiliate_payment_status TINYINT( 1 ) NOT NULL DEFAULT '0'");
		$i++;
		
		// Modification for new PPL-Feature
		// insert lead-table
		$db->Execute("
			CREATE TABLE IF NOT EXISTS ".DB_PREFIX."_affiliate_leads (
			  affiliate_leads_id int(11) NOT NULL auto_increment,
			  affiliate_id int(11) NOT NULL default '0',
			  affiliate_date datetime NOT NULL default '0000-00-00 00:00:00',
			  affiliate_date_valid datetime NOT NULL default '0000-00-00 00:00:00',
			  affiliate_payment decimal(15,2) NOT NULL default '0.00',
			  affiliate_clickthroughs_id int(11) NOT NULL default '0',
			  affiliate_lead_target varchar(255) default NULL,
			  affiliate_billing_status int(5) NOT NULL default '0',
			  affiliate_payment_date datetime default NULL,
			  affiliate_payment_id int(11) default NULL,
			  affiliate_salesman int(11) NOT NULL default '0',
			  affiliate_level tinyint(4) NOT NULL default '0',
			  affiliate_shop_id int(11) NOT NULL default '1',
			  PRIMARY KEY  (`affiliate_leads_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		");
		// update uninstall-SQL
		$uninstall_sql = <<<SQL_ENDE
			\$db->Execute("DELETE FROM ".TABLE_ADMIN_NAVIGATION." WHERE text like ''nd_affiliate%''");
	
			\$db->Execute("DELETE FROM ".TABLE_ADMIN_ACL_AREA." WHERE area_name like ''nd_affiliate%''");
			
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_affiliate");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_inventory");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_inventory_history");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_clickthroughs");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_leads");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_payment");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_payment_status");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_payment_status_history");
			\$db->Execute("DROP TABLE IF EXISTS ".DB_PREFIX."_affiliate_sales");
			
			\$cb = \$db->Execute("SELECT block_id FROM " . TABLE_CONTENT_BLOCK . " WHERE block_tag = ''nd_affiliate''");
			\$cid = \$db->Execute("SELECT content_id FROM " . TABLE_CONTENT_TO_BLOCK . " WHERE block_id = " . \$cb->fields[''block_id'']);
			while(!\$cid->EOF) {
				\$db->Execute("DELETE FROM " . TABLE_CONTENT . " WHERE content_id=" . \$cid->fields[''content_id'']);
				\$db->Execute("DELETE FROM " . TABLE_CONTENT_ELEMENTS . " WHERE content_id=" . \$cid->fields[''content_id'']);
				\$db->Execute("DELETE FROM " . TABLE_CONTENT_TO_BLOCK . " WHERE content_id=" . \$cid->fields[''content_id'']);
				\$cid->MoveNext();
			}
			\$db->Execute("DELETE FROM " . TABLE_CONTENT_BLOCK . " WHERE block_tag=''nd_affiliate''");
			
			\$db->Execute("ALTER TABLE " . TABLE_PRODUCTS . " DROP affiliate_provision");
			\$db->Execute("ALTER TABLE " . TABLE_CATEGORIES . " DROP affiliate_provision");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS_STATUS . " DROP affiliate_provision");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " DROP affiliate_referrer");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " DROP affiliate_date");
			\$db->Execute("ALTER TABLE " . TABLE_CUSTOMERS . " DROP affiliate_duration");
			
			\$tpl = \$db->Execute("SELECT tpl_id FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_type like ''nd_affiliate%''");
			while(!\$tpl->EOF) {
				\$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES . " WHERE tpl_id = " . \$tpl->fields[''tpl_id'']);
				\$db->Execute("DELETE FROM " . TABLE_MAIL_TEMPLATES_CONTENT . " WHERE tpl_id = " . \$tpl->fields[''tpl_id'']);
				\$tpl->MoveNext();
			}
SQL_ENDE;
		
		$db->Execute("UPDATE " . TABLE_PLUGIN_SQL . " SET uninstall = '" . $uninstall_sql . "', version = '1.3.0' WHERE plugin_id = " . $this->plugin_id);
		
		// Insert Admin-Navigation
		$check = $db->Execute("SELECT pid FROM " . TABLE_ADMIN_NAVIGATION . " WHERE text = 'nd_affiliate_leads'");
		if($check->RecordCount() == 0) {
			$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'nd_affiliate_leads', 'images/icons/door_in.png', '&plugin=nd_affiliate', 'adminHandler.php', '6006', 'nd_affiliate', 'I', 'W');");
			$i++;
		} else {
			$message .= 'Admin-Navigation for Leads already exists!<br />';
			$j++;
		}
		
		// Update some Hookpoints
		// page_registry.php:bottom
		$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			'if(AFFILIATE_ACTIVATE_PLUGIN == ''true'') {
			    define(''PAGE_AFFILIATE_AFFILIATE'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_affiliate.php'');
			    define(''PAGE_AFFILIATE_SUMMARY'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_summary.php'');
			    define(''PAGE_AFFILIATE_ACCOUNT'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_account.php'');
			    define(''PAGE_AFFILIATE_PAYMENT'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_payment.php'');
			    define(''PAGE_AFFILIATE_CLICKS'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_clicks.php'');
			    define(''PAGE_AFFILIATE_SALES'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_sales.php'');
			    define(''PAGE_AFFILIATE_LEADS'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_leads.php'');
			    define(''PAGE_AFFILIATE_INVENTORY'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_inventory.php'');
			    define(''PAGE_AFFILIATE_CONTACT'', _SRV_WEB_PLUGINS.''nd_affiliate/pages/affiliate_contact.php'');
	    	}'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'page_registry.php:bottom'");
		$i++;
		// store_main.php:bottom
		$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			'if(AFFILIATE_ACTIVATE_PLUGIN == ''true'') {
				define(''TABLE_AFFILIATE'', DB_PREFIX . ''_affiliate_affiliate'');
			    define(''TABLE_AFFILIATE_INVENTORY'', DB_PREFIX . ''_affiliate_inventory'');
			    define(''TABLE_AFFILIATE_INVENTORY_HISTORY'', DB_PREFIX . ''_affiliate_inventory_history'');
			    define(''TABLE_AFFILIATE_CLICKTHROUGHS'', DB_PREFIX . ''_affiliate_clickthroughs'');
			    define(''TABLE_AFFILIATE_LEADS'', DB_PREFIX . ''_affiliate_leads'');
			    define(''TABLE_AFFILIATE_SALES'', DB_PREFIX . ''_affiliate_sales'');
			    define(''TABLE_AFFILIATE_PAYMENT'', DB_PREFIX . ''_affiliate_payment'');
			    define(''TABLE_AFFILIATE_PAYMENT_STATUS'', DB_PREFIX . ''_affiliate_payment_status'');
			    define(''TABLE_AFFILIATE_PAYMENT_STATUS_HISTORY'', DB_PREFIX . ''_affiliate_payment_status_history'');
			    
			    if(!isset(\$_SESSION[''affiliate_ref''])) {
			    	require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''nd_affiliate/classes/class.nd_affiliate_clicks.php''); 
			    	\$_click = new nd_affiliate_clicks();
			    	\$_click->newClick();
			    }
			}'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'store_main.php:bottom'");
		$i++;
		// admin_main.php:bottom
		$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			'define(''TABLE_AFFILIATE'', DB_PREFIX . ''_affiliate_affiliate'');
			define(''TABLE_AFFILIATE_INVENTORY'', DB_PREFIX . ''_affiliate_inventory'');
			define(''TABLE_AFFILIATE_INVENTORY_HISTORY'', DB_PREFIX . ''_affiliate_inventory_history'');
			define(''TABLE_AFFILIATE_CLICKTHROUGHS'', DB_PREFIX . ''_affiliate_clickthroughs'');
			define(''TABLE_AFFILIATE_LEADS'', DB_PREFIX . ''_affiliate_leads'');
			define(''TABLE_AFFILIATE_SALES'', DB_PREFIX . ''_affiliate_sales'');
			define(''TABLE_AFFILIATE_PAYMENT'', DB_PREFIX . ''_affiliate_payment'');
			define(''TABLE_AFFILIATE_PAYMENT_STATUS'', DB_PREFIX . ''_affiliate_payment_status'');
			define(''TABLE_AFFILIATE_PAYMENT_STATUS_HISTORY'', DB_PREFIX . ''_affiliate_payment_status_history'');
			
			require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.''/nd_affiliate/classes/class.nd_affiliate_inventory.php'');'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'admin_main.php:bottom'");
		$i++;
		// css_admin.php:css
		$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			' echo ''.nd_affiliate_subaffiliatetree {background-image: url(images/icons/chart_organisation.png) !important;}'';
		    echo ''.nd_affiliate_payment {background-image: url(images/icons/money.png) !important;}'';
		    echo ''.nd_affiliate_sales {background-image: url(images/icons/basket.png) !important;}'';
		    echo ''.nd_affiliate_clicks {background-image: url(images/icons/mouse.png) !important;}'';
		    echo ''.nd_affiliate_leads {background-image: url(images/icons/door_in.png) !important;}'';
		    echo ''.nd_affiliate_summary {background-image: url(images/icons/chart_curve.png) !important;}'';'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'css_admin.php:css'");
		$i++;
		// insert some Language-Vars
		// insert language for TEXT_ND_AFFILIATE_LEADS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_ND_AFFILIATE_LEADS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_ND_AFFILIATE_LEADS',
									'language_value' => 'Partner-Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_ND_AFFILIATE_LEADS',
									'language_value' => 'Affiliate Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_ND_AFFILIATE_LEADS already exists!<br />';
			$j++;
		}
		// insert language for HEADING_ND_AFFILIATE_LEADS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'HEADING_ND_AFFILIATE_LEADS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'HEADING_ND_AFFILIATE_LEADS',
									'language_value' => 'Partner-Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'HEADING_ND_AFFILIATE_LEADS',
									'language_value' => 'Affiliate Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'HEADING_ND_AFFILIATE_LEADS already exists!<br />';
			$j++;
		}
		// insert language for TEXT_AFFILIATE_LEADS_ID
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_LEADS_ID'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_LEADS_ID',
									'language_value' => 'Lead-Nr.',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_LEADS_ID',
									'language_value' => 'Lead-No.',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_LEADS_ID already exists!<br />';
			$j++;
		}
		// insert language for TEXT_AFFILIATE_DATE_VALID
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_DATE_VALID'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_DATE_VALID',
									'language_value' => 'Auszahlung ab',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_DATE_VALID',
									'language_value' => 'Payout at',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_DATE_VALID already exists!<br />';
			$j++;
		}
		// insert language for TEXT_AFFILIATE_LEAD_TARGET
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_LEAD_TARGET'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_LEAD_TARGET',
									'language_value' => 'Lead-Ziel',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_LEAD_TARGET',
									'language_value' => 'Lead-Target',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_LEAD_TARGET already exists!<br />';
			$j++;
		}
		// insert language for AFFILIATE_TEXT_TOTAL_LEADS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_TOTAL_LEADS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_TOTAL_LEADS',
									'language_value' => 'Ihre momentane Provision beträgt %s bei %s Leads.',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_TOTAL_LEADS',
									'language_value' => 'Your actual Provision is %s at %s Leads.',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_TOTAL_LEADS already exists!<br />';
			$j++;
		}
		// insert language for AFFILIATE_TEXT_LEAD_TARGET
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_LEAD_TARGET'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_LEAD_TARGET',
									'language_value' => 'Lead-Ziel',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_LEAD_TARGET',
									'language_value' => 'Lead-Target',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_LEAD_TARGET already exists!<br />';
			$j++;
		}
		
		// insert language for TEXT_AFFILIATE_SALESMAN
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_SALESMAN'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_SALESMAN',
									'language_value' => 'Vertreter',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_SALESMAN',
									'language_value' => 'Salesman',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_SALESMAN already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_LEADS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_LEADS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_LEADS',
									'language_value' => 'Partner-Leads',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_LEADS',
									'language_value' => 'Leads Report',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_LEADS already exists!<br />';
			$j++;
		}
		
		// insert language for TEXT_AFFILIATE_LEADS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_LEADS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_LEADS',
									'language_value' => 'Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_LEADS',
									'language_value' => 'Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_LEADS already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_LEADS_PROVISION
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_LEADS_PROVISION'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_LEADS_PROVISION',
									'language_value' => 'Leadsbetrag',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_LEADS_PROVISION',
									'language_value' => 'Leadsvalue',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_LEADS_PROVISION',
									'language_value' => 'Leadsbetrag',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_LEADS_PROVISION',
									'language_value' => 'Leadsvalue',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_LEADS_PROVISION already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_LEADS_NUMBERS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_LEADS_NUMBERS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_LEADS_NUMBERS',
									'language_value' => 'Anzahl Leads',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_LEADS_NUMBERS',
									'language_value' => 'Number of Leads',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_LEADS_NUMBERS',
									'language_value' => 'Anzahl Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_LEADS_NUMBERS',
									'language_value' => 'Number of Leads',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_LEADS_NUMBERS already exists!<br />';
			$j++;
		}
		
		// change language for AFFILIATE_TEXT_PERSONAL_LEVEL
		$db->Execute("UPDATE " . TABLE_LANGUAGE_CONTENT . " SET language_value = 'eigene Ebene' WHERE language_key = 'AFFILIATE_TEXT_PERSONAL_LEVEL' AND language_code = 'de'");
		$db->Execute("UPDATE " . TABLE_LANGUAGE_CONTENT . " SET language_value = 'own level' WHERE language_key = 'AFFILIATE_TEXT_PERSONAL_LEVEL' AND language_code = 'en'");
		
		$db->Execute("Update " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.3.0' WHERE plugin_id = " . $this->plugin_id);
		$this->version = '1.3.0';
		// ----------------- Update ----------------------
		
		$message .= $i . ' Values updated<br />';
		$message .= $j . ' Values already exists<br />';
		$message .= '<b>Update nd-affiliate Version 1.2.0 to 1.3.0 successfull<br />';
		$message .= '______________________________________________________________________<br /><br />';
		
		return $message;
	}
	
	function update_130_131 () {
		global $db;
		$message = '<b>Updating nd_affiliate Version 1.3.0 to 1.3.1</b><br />';
		$i = 0;
		$j = 0;
		
		$db->Execute("Update " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.3.1' WHERE plugin_id = " . $this->plugin_id);
		$i++;
		$this->version = '1.3.1';
		// ----------------- Update ----------------------
		
		$message .= $i . ' Values updated<br />';
		$message .= $j . ' Values already exists<br />';
		$message .= '<b>Update nd-affiliate Version 1.3.0 to 1.3.1 successfull<br />';
		$message .= '______________________________________________________________________<br /><br />';
		
		return $message;
	}
	
	function update_131_132 () {
		global $db;
		$message = '<b>Updating nd_affiliate Version 1.3.1 to 1.3.2</b><br />';
		$i = 0;
		$j = 0;
		
		$db->Execute("Update " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.3.2' WHERE plugin_id = " . $this->plugin_id);
		$i++;
		$this->version = '1.3.2';
		// ----------------- Update ----------------------
		
		$message .= $i . ' Values updated<br />';
		$message .= $j . ' Values already exists<br />';
		$message .= '<b>Update nd-affiliate Version 1.3.1 to 1.3.2 successfull<br />';
		$message .= '______________________________________________________________________<br /><br />';
		
		return $message;
	}
	
	function update_132_133 () {
		global $db;
		$message = '<b>Updating nd_affiliate Version 1.3.2 to 1.3.3</b><br />';
		$i = 0;
		$j = 0;
		
		// Add Affiliate-Status to the database
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE . " ADD affiliate_status TINYINT( 1 ) NOT NULL DEFAULT '0'");
		$i++;
		
		// Insert Value for the new Configvar AFFILIATE_AUTO_ACTIVATE
		$check = $db->Execute("SELECT id FROM " . TABLE_PLUGIN_CONFIGURATION . " WHERE config_key = 'AFFILIATE_AUTO_ACTIVATE'");
		if($check->RecordCount() == 0) {
			$stores = $db->Execute("SELECT shop_id FROM " . TABLE_MANDANT_CONFIG);
			while(!$stores->EOF) {
				$sql_data_array = array('config_key' => 'AFFILIATE_AUTO_ACTIVATE',
										'config_value' => 'true',
										'plugin_id' => $this->plugin_id,
										'type' => 'dropdown',
										'url' => 'conf_truefalse',
										'group_id' => '0',
										'shop_id' => $stores->fields['shop_id'],
										'last_modified' => $db->BindTimestamp(time()),
										'date_added' => $db->BindTimestamp(time()));
				$db->AutoExecute(TABLE_PLUGIN_CONFIGURATION, $sql_data_array, 'INSERT');
				$stores->MoveNext();
				$i++;
			}
			// Insert Language-Code for the new Configvar AFFILIATE_AUTO_ACTIVATE_TITLE
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_AUTO_ACTIVATE_TITLE',
									'language_value' => 'Partner aktivieren',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_AUTO_ACTIVATE_TITLE',
									'language_value' => 'Affiliate activate',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_AUTO_ACTIVATE already exists!<br />';
			$j++;
		}
		
		// insert language for TEXT_AFFILIATE_STATUS
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_STATUS'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_STATUS',
									'language_value' => 'Aktiv',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_STATUS',
									'language_value' => 'Active',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_STATUS already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_LOGIN_NOT_ACTIVATED
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_LOGIN_NOT_ACTIVATED'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_LOGIN_NOT_ACTIVATED',
									'language_value' => 'Ihre Anmeldung konnte nicht erfolgreich durchgeführt werden, da Ihr Account noch nicht freigeschaltet wurde. Bitte versuchen Sie es später erneut.',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_LOGIN_NOT_ACTIVATED',
									'language_value' => 'Your login could not be processed successfully due to a not activated account. Please try again later.',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_LOGIN_NOT_ACTIVATED already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_REGISTER_PENDING
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_REGISTER_PENDING'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_REGISTER_PENDING',
									'language_value' => 'Ihre Daten wurden erfolgreich eingetragen. Ihr Account wird nach Prüfung durch uns freigeschaltet.',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_REGISTER_PENDING',
									'language_value' => 'Your data were inserted successfully. Your Account will be activated after it is checked.',
									'class' => 'store',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_REGISTER_PENDING already exists!<br />';
			$j++;
		}
		
		// Update some Hooks
		// css_admin.php:css
		$db->Execute("UPDATE " . TABLE_PLUGIN_CODE . " SET code = 
			' echo ''.nd_affiliate_subaffiliatetree {background-image: url(images/icons/chart_organisation.png) !important;}'';
		    echo ''.nd_affiliate_payment {background-image: url(images/icons/money.png) !important;}'';
		    echo ''.nd_affiliate_sales {background-image: url(images/icons/basket.png) !important;}'';
		    echo ''.nd_affiliate_clicks {background-image: url(images/icons/mouse.png) !important;}'';
		    echo ''.nd_affiliate_leads {background-image: url(images/icons/door_in.png) !important;}'';
		    echo ''.nd_affiliate_summary {background-image: url(images/icons/chart_curve.png) !important;}'';
		    echo ''.nd_affiliate_printpayment {background-image: url(images/icons/page_white_acrobat.png) !important;}'';'
			WHERE plugin_id = " . $this->plugin_id . " AND hook = 'css_admin.php:css'");
		$i++;
		
		// Add Affiliate-VAT-entitled to the database
		$db->Execute("ALTER TABLE " . TABLE_AFFILIATE . " ADD affiliate_vat_entitled TINYINT( 1 ) NOT NULL DEFAULT '0'");
		$i++;
		
		// insert language for TEXT_AFFILIATE_VAT_ENTITLED
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_VAT_ENTITLED'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_VAT_ENTITLED',
									'language_value' => 'USt.-Ausweisbar',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_VAT_ENTITLED',
									'language_value' => 'VAT entitled',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_VAT_ENTITLED already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_SUM_OT
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_SUM_OT'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_SUM_OT',
									'language_value' => 'Zwischensumme',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_SUM_OT',
									'language_value' => 'sub total',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_SUM_OT already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_TAX
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_TAX'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_TAX',
									'language_value' => 'USt. (%s %)',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_TAX',
									'language_value' => 'VAT (%s %)',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_TAX already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_SUM_TOTAL
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_SUM_TOTAL'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_SUM_TOTAL',
									'language_value' => 'Summe',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_SUM_TOTAL',
									'language_value' => 'total',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_SUM_TOTAL already exists!<br />';
			$j++;
		}
		
		// insert language for AFFILIATE_TEXT_PRINTPAYMENT_TITLE
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'AFFILIATE_TEXT_PRINTPAYMENT_TITLE'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'AFFILIATE_TEXT_PRINTPAYMENT_TITLE',
									'language_value' => 'Auszahlung Nr. %s',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'AFFILIATE_TEXT_PRINTPAYMENT_TITLE',
									'language_value' => 'Payment No. %s',
									'class' => 'both',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'AFFILIATE_TEXT_PRINTPAYMENT_TITLE already exists!<br />';
			$j++;
		}
		
		// insert language for TEXT_AFFILIATE_PRINTPAYMENT
		$check = $db->Execute("SELECT language_content_id FROM " . TABLE_LANGUAGE_CONTENT . " WHERE language_key = 'TEXT_AFFILIATE_PRINTPAYMENT'");
		if($check->RecordCount() == 0) {
			$sql_data_array = array('language_code' => 'de',
									'language_key' => 'TEXT_AFFILIATE_PRINTPAYMENT',
									'language_value' => 'Auszahlung drucken',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
			$sql_data_array = array('language_code' => 'en',
									'language_key' => 'TEXT_AFFILIATE_PRINTPAYMENT',
									'language_value' => 'print payment',
									'class' => 'admin',
									'plugin_key' => $this->plugin_code);
			$db->AutoExecute(TABLE_LANGUAGE_CONTENT, $sql_data_array, 'INSERT');
			$i++;
		} else {
			$message .= 'TEXT_AFFILIATE_PRINTPAYMENT already exists!<br />';
			$j++;
		}
		
		$db->Execute("Update " . TABLE_PLUGIN_PRODUCTS . " SET version = '1.3.3' WHERE plugin_id = " . $this->plugin_id);
		$i++;
		$this->version = '1.3.3';
		// ----------------- Update ----------------------
		
		$message .= $i . ' Values updated<br />';
		$message .= $j . ' Values already exists<br />';
		$message .= '<b>Update nd-affiliate Version 1.3.2 to 1.3.3 successfull<br />';
		$message .= '______________________________________________________________________<br /><br />';
		
		return $message;
	}
}