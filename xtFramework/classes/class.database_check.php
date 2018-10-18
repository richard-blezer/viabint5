<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

 class database_check{
 	var $group_table_array;
 	var $store_table_array;

 	function database_check($check_all='false') {
		global $xtPlugin;

		$this->table_array = array(TABLE_CATEGORIES, TABLE_PRODUCTS, TABLE_CONTENT, TABLE_PRODUCTS_PRICE_SPECIAL);
		$this->store_table_array = array(TABLE_LANGUAGES, TABLE_CURRENCIES, TABLE_CURRENCIES, TABLE_MAIL_TEMPLATES_ATTACHMENT, TABLE_CONTENT, TABLE_MAIL_TEMPLATES);

		($plugin_code = $xtPlugin->PluginCode('class.database_check.php:_database_check_data')) ? eval($plugin_code) : false;

		if($check_all == 'true'){
			$this->CheckAllGroups();
			$this->CheckAllStores();
		}
 	}

 	function CheckAllGroups(){
		for ($i=0; $i<sizeof($this->table_array); $i++) {
			$this->GroupCheckTable($this->table_array[$i]);
		}
 	}

 	function CheckAllStores(){
		for ($i=0; $i<sizeof($this->store_table_array); $i++) {
			$this->StoreCheckTable($this->store_table_array[$i]);
		}
 	}

 	function GroupCheckTable($table) {
 		global $db;

			$query = "SELECT customers_status_id FROM ". TABLE_CUSTOMERS_STATUS ."";
			$record = $db->Execute($query);

			$table_col_last = '';
			while(!$record->EOF){

			 	$this->isPermission($table, 'group_permission_'.$record->fields['customers_status_id'],$table_col_last);
			 	$table_col_last = 'group_permission_'.$record->fields['customers_status_id'];

				$record->MoveNext();
			}$record->Close();

			$this->checkPermission($table, 'group_permission_', TABLE_CUSTOMERS_STATUS, 'customers_status_id');

 	}

  	function PriceCheckTable($table) {
 		global $db;

			$query = "SELECT customers_status_id FROM ". TABLE_CUSTOMERS_STATUS ."";
			$record = $db->Execute($query);

			$table_col_last = '';
			while(!$record->EOF){

			 	$this->isPermission($table, 'price_flag_graduated_'.$record->fields['customers_status_id'],$table_col_last);
			 	$table_col_last = 'price_flag_graduated_'.$record->fields['customers_status_id'];

				$record->MoveNext();
			}$record->Close();

			$this->checkPermission($table, 'price_flag_graduated_', TABLE_CUSTOMERS_STATUS, 'customers_status_id');

 	}

 	function StoreCheckTable($table) {
 		global $db;

			$query = "SELECT shop_id FROM ". TABLE_MANDANT_CONFIG ." ";
			$record = $db->Execute($query);

			$table_col_last = '';
			while(!$record->EOF){

			 	$this->isPermission($table, 'shop_'.$record->fields['shop_id'],$table_col_last);
			 	$table_col_last = 'shop_'.$record->fields['shop_id'];

				$record->MoveNext();
			}$record->Close();

			$this->checkPermission($table, 'shop_', TABLE_MANDANT_CONFIG, 'shop_id');
 	}

	function isPermission ($table, $tablecol ,$tablecol_last='') {
		global $db;

		$query = "SHOW FULL FIELDS FROM ".$table." LIKE '".$tablecol."'";
		$record = $db->Execute($query);
		if($record->RecordCount() > 0){
			return false;
		}else{

			if($tablecol_last != ''){
				$db->Execute("ALTER TABLE ".$table." ADD ".$tablecol." INT( 1 ) NOT NULL DEFAULT '0' AFTER ".$tablecol_last."");
			}else{
				$db->Execute("ALTER TABLE ".$table." ADD ".$tablecol." INT( 1 ) NOT NULL DEFAULT '0'");
			}
		}
	}

	function checkPermission($table, $tablecol, $permtable, $field_id){
		global $db;

		$query = "SHOW FULL FIELDS FROM ".$table." LIKE '".$tablecol."%'";
		$record = $db->Execute($query);

    	if ($record->RecordCount() > 0) {
    		while(!$record->EOF){

				$tmp_fields = array();
				$tmp_fields = explode('_', $record->fields['Field']);
				$tmp_fields = array_reverse($tmp_fields);

				$this->cleanPermission($table, $tablecol, $permtable, (int)$tmp_fields[0], $field_id);

    			$record->MoveNext();
    		} $record->Close();
    	}

	}

	function cleanPermission($table, $tablecol, $permtable, $permID, $field_id){
		global $db;

		$query = "SELECT ".$field_id." FROM ". $permtable ." where ".$field_id."=?";
		$record = $db->Execute($query, array($permID));

    	if ($record->RecordCount() > 0) {
			return false;
    	}else{
   			$del_query = "SHOW FULL FIELDS FROM ".$table." LIKE '".$tablecol.$permID."'";
			$del_record = $db->Execute($del_query);

			if($del_record->RecordCount() > 0){
				$db->Execute("ALTER TABLE  " . $table . " DROP  " . $tablecol.$permID . " ");
			}
    	}
	}
 }