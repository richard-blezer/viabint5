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

class tax{

	protected $_table = TABLE_TAX_RATES;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'tax_rates_id';

	function tax()
	{
		global $xtPlugin, $order_edit_controller;

		($plugin_code = $xtPlugin->PluginCode('class.tax.php:tax_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		if (isset($_SESSION['registered_customer'])) {
			$this->country_code = $_SESSION['customer']->customer_shipping_address['customers_country_code'];
			$this->zone_id = $_SESSION['customer']->customer_shipping_address['customers_zone'];
		} else {
			$this->country_code = _STORE_COUNTRY;
			$this->zone_id = _STORE_ZONE;
		}

		$order_edit_controller->hook_tax_build($this);

		($plugin_code = $xtPlugin->PluginCode('class.tax.php:tax_build')) ? eval($plugin_code) : false;
		$this->_buildData();
	}

	function setValues($data){
		$this->country_code = $data['country_code'];
		$this->zone_id = $data['zone'];
	}

	function _buildData(){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.tax.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT tax_class_id FROM ".TABLE_TAX_CLASS);
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					
					$data[$record->fields['tax_class_id']] = $this->_getTaxRates($record->fields['tax_class_id']);
					
					$record->MoveNext();
				}$record->Close();
				($plugin_code = $xtPlugin->PluginCode('class.tax.php:_buildData_bottom')) ? eval($plugin_code) : false;
				$this->data = $data;
			}else{
				return false;
			}
	}

	function _getTaxRates($class_id){
		global $db, $xtPlugin, $currency;

		($plugin_code = $xtPlugin->PluginCode('class.tax.php:_getTaxRates_top')) ? eval($plugin_code) : false;

		if(isset($plugin_return_value))
			return $plugin_return_value;

	    $tax_record = $db->Execute(
			"SELECT tax_rate, tax_rate_countries FROM " . TABLE_TAX_RATES . " where tax_class_id = ? and tax_zone_id=?",
			array($class_id, $this->zone_id)
		);

		if($tax_record->RecordCount() > 0){
			$tax_multiplier = 1.0;
			$currentCountryCode = $this->country_code;

			if (isset($_SESSION['customer']) && isset($_SESSION['customer']->customer_payment_address) && isset($_SESSION['customer']->customer_payment_address['customers_country_code'])) {
				$currentCountryCode = $_SESSION['customer']->customer_payment_address['customers_country_code'];
			}

			while(!$tax_record->EOF){
				$countries = explode(',', $tax_record->fields['tax_rate_countries']);
					
				if (!empty($tax_record->fields['tax_rate_countries'])  && !in_array($currentCountryCode, $countries)) {
					$tax_record->MoveNext();
					continue;
				}

				$tax_multiplier *= 1.0 + ( ($tax_record->fields['tax_rate']) / 100);
				($plugin_code = $xtPlugin->PluginCode('class.tax.php:_getTaxRates_data')) ? eval($plugin_code) : false;
				$tax_record->MoveNext();
			}$tax_record->Close();

			($plugin_code = $xtPlugin->PluginCode('class.tax.php:_getTaxRates_bottom')) ? eval($plugin_code) : false;
			$tax_res = ($tax_multiplier - 1.0) * 100;
			$tax_res = round($tax_res, $currency->decimals);
			return $tax_res;
		}else {
			return 0;
	    }
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header[$this->_master_key] = array('type' => 'hidden');

		$header['tax_class_id'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=tax_classes'
		);

		$header['tax_zone_id'] = array(
			'type' => 'dropdown', 								// you can modyfy the auto type
			'url'  => 'DropdownData.php?get=tax_zones'
		);

		$header['tax_rate_countries'] = array(
			'type' => 'dropdown',
			'url' => 'DropdownData.php?get=countries',
		);

		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
 		$params['languageTab']    = false;

		$params['include']        = array ('tax_rates_id', 'tax_class_id', 'tax_rate', 'tax_zone_id', 'tax_rate_countries');
		$params['exclude']        = array ('');
		$params['GroupField']     = "tax_class_id";
		$params['SortField']      = $this->_master_key;
		$params['SortDir']        = "ASC";

		return $params;
	}
	
	public function get_tax_rate_countries() {
		global $db;
		$edit_id = $this->url_data['tax_rates_id'];
		$obj = new stdClass();
		$obj->topics = array();
		$obj->totalCount = 0;
	
		if (!empty($edit_id)) {
			$query = "SELECT tax_rate_countries FROM " . TABLE_TAX_RATES . " WHERE tax_rates_id=?";
			$record = $db->Execute($query, array((int)$edit_id));
			if($record->RecordCount() > 0) {
				$zones_array = explode(',', $record->fields['tax_rate_countries']);
	
				if (!empty($zones_array)) {
					$countries = new countries();
					foreach ($zones_array as $code) {
						if (isset($countries->countries_list[$code])) {
							$obj->topics[] = array('id' => $code, 'name' => $countries->countries_list[$code]['countries_name'], 'desc' => '');
						}
					}
					$obj->totalCount = count($obj->topics);
				}
			}
			return json_encode($obj);
		}
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data'])
        $data = $table_data->getData();
        elseif($ID)
        $data = $table_data->getData($ID);
        else
		$data = $table_data->getHeader();

		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type='edit'){
		global $db,$language,$filter;

		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();
		return $obj;
	}

	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
	}
}