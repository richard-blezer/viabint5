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

class customer extends check_fields{

	public $customers_id;
	public $customers_status;
	public $customer_info = array('account_type'=>0);
	public $customer_default_address = array();
	public $customer_shipping_address = array();
	public $customer_payment_address = array();
	public $error = false;

	public $_master_key = 'customers_id';
	public $_master_key_add = 'address_book_id';
	public $_table = TABLE_CUSTOMERS;
	public $_table_add = TABLE_CUSTOMERS_ADDRESSES;
	public $password_special_signs = 3;

	public $master_id = 'customers_id';

	function customer($customer_id=''){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:customer_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($customer_id)){
			$this->customers_id = $customer_id;
			$this->_customer($customer_id);
		}elseif(!empty($_SESSION['registered_customer'])){
			$this->customers_id = $_SESSION['registered_customer'];
			$this->_customer($_SESSION['registered_customer']);
		}else{
			$this->customers_id = 0;
			$this->customers_status = _STORE_CUSTOMERS_STATUS_ID_GUEST;
		}
	}

	function _customer($customer_id){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_customer_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($customer_id)){
			$this->customers_id = $customer_id;
			$this->customer_info = $this->_buildData($customer_id);
			$this->customers_status = $this->customer_info['customers_status'];
			$this->customer_default_address = $this->_buildAddressData($customer_id, 'default');
			$this->customer_payment_address = $this->_buildAddressData($customer_id, 'payment');
			$this->customer_shipping_address = $this->_buildAddressData($customer_id, 'shipping');
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_customer_bottom')) ? eval($plugin_code) : false;

	}

	function _buildData($cID){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM " . TABLE_CUSTOMERS . " where customers_id=?", array($cID));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildData_data')) ? eval($plugin_code) : false;
				unset($record->fields['customers_password']);

				if($record->fields['account_type']=='1'){
					$record->fields['customers_status'] = _STORE_CUSTOMERS_STATUS_ID_GUEST;
				}

				$data = $record->fields;
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildData_bottom')) ? eval($plugin_code) : false;
			return $data;
		}else{
			return false;
		}
	}


	function   _buildAddressData($cID, $type='', $id=''){
		global $db, $xtPlugin, $countries, $system_status, $language;

		if (!is_object($countries)) {
			$countries = new countries('true','store');
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildAdressData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($type) && !empty($id)){
			$sql_qry = "and address_book_id='".(int)$id."'";
		}elseif(!empty($type) && empty($id)){
			$sql_qry = "and address_class=".$db->Quote($type)."";
		}
               
		$record = $db->Execute("SELECT * FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=? ".$sql_qry."", array($cID));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildAdressData_data')) ? eval($plugin_code) : false;

				$country  = $countries->_getCountryData($record->fields['customers_country_code']);

				$record->fields['customers_dob'] = date_short($record->fields['customers_dob'], _STORE_ACCOUNT_DOB_FORMAT);
				
				$record->fields['customers_country'] = $country['countries_name'];
				
				if(_STORE_ACCOUNT_FEDERAL_STATES == 'true'){
					if ($record->fields['customers_federal_state_code']>0){
						$fst_record = $db->Execute(
							"SELECT fsd.*, fs.states_code FROM ".TABLE_FEDERAL_STATES_DESCRIPTION." fsd INNER JOIN ".TABLE_FEDERAL_STATES." fs ON fs.states_id=fsd.states_id WHERE fsd.states_id = ? AND fsd.language_code = ? LIMIT 1",
							array($record->fields['customers_federal_state_code'], $language->code)
						);
						$record->fields['customers_country'] = $fst_record->fields['state_name'].', '.$record->fields['customers_country'];
						$record->fields['customers_federal_state_code_iso'] = $fst_record->fields['states_code'];
					}
				}
				
				$record->fields['customers_zone'] = $country['zone_id'];
				
				$data = $record->fields;
				$data['customers_age'] = current_age($record->fields['customers_dob']);
				$data['allow_change'] = true;
				$record->MoveNext();
			}$record->Close();
			($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildAdressData_bottom')) ? eval($plugin_code) : false;
			return $data;
		}else{
			$record = '';
			$record = $db->Execute("SELECT * FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=? and address_class='default'", array($cID));
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildAdressData_data')) ? eval($plugin_code) : false;

					$record->fields['customers_dob'] = date_short($record->fields['customers_dob'], _STORE_ACCOUNT_DOB_FORMAT);

					$country  = $countries->_getCountryData($record->fields['customers_country_code']);

					$record->fields['customers_country'] = $country['countries_name'];
		
					if(_STORE_ACCOUNT_FEDERAL_STATES == 'true'){
						if ($record->fields['customers_federal_state_code']>0){
							$fst_record = $db->Execute(
								"SELECT fsd.*, fs.states_code FROM ".TABLE_FEDERAL_STATES_DESCRIPTION." fsd INNER JOIN ".TABLE_FEDERAL_STATES." fs ON fs.states_id=fsd.states_id WHERE fsd.states_id = ? AND fsd.language_code = ? LIMIT 1",
								array($record->fields['customers_federal_state_code'], $language->code)
							);
							$record->fields['customers_country'] = $fst_record->fields['state_name'].', '.$record->fields['customers_country'];
							$record->fields['customers_federal_state_code_iso'] = $fst_record->fields['states_code'];
						}
					}
					
					$record->fields['customers_zone'] = $country['zone_id'];

					$data = $record->fields;
					
					$data['customers_age'] = current_age($record->fields['customers_dob']);
					$data['allow_change'] = true;
					$record->MoveNext();
				}$record->Close();
				($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildAdressData_bottom')) ? eval($plugin_code) : false;
				return $data;
			}else{
				return false;
			}
		}
	}

	function _getAdressList($cID){
		global $db, $xtPlugin, $countries, $language;

		if (!is_object($countries)) {
			$countries = new countries('true','store');
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getAdressList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=?", array($cID));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getAdressList_data')) ? eval($plugin_code) : false;

				$country  = $countries->_getCountryData($record->fields['customers_country_code']);

				$record->fields['customers_country'] = $country['countries_name'];
				$record->fields['id'] = $record->fields['address_book_id'];

				$record->fields['text'] = $record->fields['customers_company'].' '. $record->fields['customers_firstname'].' '.$record->fields['customers_lastname'].' ('.$record->fields['customers_street_address'].' '.$record->fields['customers_postcode'] .'  '.$record->fields['customers_city'] .')';
				$record->fields['allow_change'] = true;
				($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getAdressList_data_bottom')) ? eval($plugin_code) : false;
				$data[] = $record->fields;
				$record->MoveNext();
			}$record->Close();
			
			if(_STORE_ACCOUNT_FEDERAL_STATES == 'true'){			
				foreach ($data as $key => $value){
					if ($value['customers_federal_state_code']>0){
						$parent_country = substr($value['customers_country_code'], 0, 2);
						unset ($record);
						$record = $db->Execute(
							"SELECT * FROM ".TABLE_FEDERAL_STATES_DESCRIPTION." fsd WHERE fsd.states_id = ? AND fsd.language_code = ? LIMIT 1",
							array($value['customers_federal_state_code'], $language->code)
						);
						$data[$key]['customers_country'] = $record->fields['state_name'].', '.$value['customers_country'];
					}
				}
			}
			
			($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getAdressList_bottom')) ? eval($plugin_code) : false;
			return $data;
		}else{
			return false;
		}
	}

	function _registerCustomer($data, $register_type='both', $add_type = 'insert', $check_data=true, $login_customer=true){
		global $db, $xtPlugin, $store_handler, $countries, $xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_registerCustomer_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;


		$this->error = false;
		$data["cust_info"]['customers_email_address'] = trim($data["cust_info"]['customers_email_address']);
		$data["cust_info"]['customers_email_address_confirm'] = trim($data["cust_info"]['customers_email_address_confirm']);
        // transform dob (date pickers etc)
        //if (isset($data['default_address']['customers_dob'])) {
		if ($data['default_address']['customers_dob']!='') {
            $dob=trim($data['default_address']['customers_dob']);
            $data['default_address']['customers_dob'] = date('d.m.Y', strtotime($dob));
        }

		if(is_array($data['cust_info'])){

			if(_STORE_ALLOW_GUEST_ORDERS == 'true'){
				if(empty($data['cust_info']['customers_password'])){
					$data['cust_info']['guest'] = 1;
				}else{
					$data['cust_info']['password_required'] = 1;
				}
			}else{
				$data['cust_info']['password_required'] = 1;
			}

			$this->_checkCustomerData($data);

		}

		if(is_array($data['default_address'])){
			$this->_checkCustomerAddressData($data['default_address']);
		}

		if(is_array($data['shipping_address'])){
			$this->_checkCustomerAddressData($data['shipping_address']);
		}

		if(is_array($data['payment_address'])){
			$this->_checkCustomerAddressData($data['payment_address']);
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_registerCustomer_address')) ? eval($plugin_code) : false;

		if($this->error == true){
			$data['error'] = true;
			return $data;
		}

		if(is_array($data['cust_info'])){
			$this->_buildCustomerData($data);
			$data['cust_info'] = $this->customerData;
		}

		if(is_array($data['default_address'])){
			$this->_buildCustomerAddressData($data['default_address']);
			$data['default_address'] = $this->customerAdressData['default'];
		}

		if(is_array($data['shipping_address'])){
			$this->_buildCustomerAddressData($data['shipping_address']);
			$data['shipping_address'] = $this->customerAdressData['shipping'];
		}

		if(is_array($data['payment_address'])){
			$this->_buildCustomerAddressData($data['payment_address']);
			$data['payment_address'] = $this->customerAdressData['payment'];
		}


		if($data['cust_info']['guest'] !=1)
		$this->_sendAccountMail();

		if($login_customer == true){
			$_SESSION['registered_customer'] = $this->data_customer_id;
			$this->_customer($_SESSION['registered_customer']);
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_registerCustomer_bottom')) ? eval($plugin_code) : false;

		$data['success'] = true;

		return $data;
	}

	function _updateAddressClass($id, $customer, $class){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_updateAddressClass_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$update_record = array('address_class'=>$class);
		$db->AutoExecute(TABLE_CUSTOMERS_ADDRESSES, $update_record, 'UPDATE', "customers_id=".(int)$customer." and address_book_id =".(int)$id);
	}

	function _checkCustomerData($data, $add_type = 'insert', $check_data=true){
		global $db, $xtPlugin, $store_handler;

		$form_data = $data;

		if(is_array($data['cust_info']))
		$data = $data['cust_info'];

		$this->error = false;
		$data['error'] = false;

		if($check_data == true){
			$this->_checkLenght($data['customers_email_address'], _STORE_EMAIL_ADDRESS_MIN_LENGTH, ERROR_EMAIL_ADDRESS);
			$this->_checkLenght($data['customers_email_address_confirm'], _STORE_EMAIL_ADDRESS_MIN_LENGTH, ERROR_EMAIL_ADDRESS);
			$this->_checkMatch($data['customers_email_address'], $data['customers_email_address_confirm'], ERROR_EMAIL_ADDRESS_NOT_MATCHING);
			$this->_checkEmailAddress($data['customers_email_address'], ERROR_EMAIL_ADDRESS_SYNTAX);

			if ($add_type=='insert') {
				$this->_checkVatId($form_data,ERROR_VAT_ID);
			}

			if ($add_type === 'update')
			{
				$form_data['cust_info']['customers_vat_id'] = $form_data["customers_vat_id"];
				$form_data['default_address']['customers_country_code'] = $_SESSION['customer']->customer_default_address["customers_country_code"];
				$this->_checkVatId($form_data,ERROR_VAT_ID);
			}
			else
			{
				$this->_checkExist($data['customers_email_address'], 'customers_email_address', TABLE_CUSTOMERS, "account_type = 0 and shop_id = ".$store_handler->shop_id, ERROR_EMAIL_ADDRESS_EXISTS);
			}

			if($data['guest']!=1 && $data['password_required']==1){
				$this->_checkLenght($data['customers_password'], _STORE_PASSWORD_MIN_LENGTH, TEXT_PASSWORD_ERROR);
				$this->_checkMatch($data['customers_password'], $data['customers_password_confirm'], ERROR_PASSWORD_NOT_MATCHING);
			}elseif($add_type == 'update' && $data['customers_password']!=''){
				$this->_checkLenght($data['customers_password'], _STORE_PASSWORD_MIN_LENGTH, TEXT_PASSWORD_ERROR);
				$this->_checkMatch($data['customers_password'], $data['customers_password_confirm'], ERROR_PASSWORD_NOT_MATCHING);
				$this->_checkCurrentPassword($data['customers_password_current'],$data['customers_id'], ERROR_CURRENT_PASSWORD_NOT_MATCHING);
			}else{
				if(!empty($data['customers_password']))
				$this->_checkMatch($data['customers_password'], $data['customers_password_confirm'], ERROR_PASSWORD_NOT_MATCHING);

			}

			($plugin_code = $xtPlugin->PluginCode('class.customer.php:_CustomerData_check')) ? eval($plugin_code) : false;

		}

		if($this->error == true){
			$data['error'] = true;
			return false;
		}

	}


	function _buildCustomerData($data, $add_type = 'insert', $check_data=true){
		global $db, $xtPlugin, $store_handler, $language, $currency;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildCustomerData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$form_data = $data;

		if(is_array($data['cust_info']))
		$data = $data['cust_info'];

		if($data['guest']==1)
		$data['customers_status'] = _STORE_CUSTOMERS_STATUS_ID_GUEST;

		if ($add_type=='insert') {

			if ($data['customers_status'] == 0 || !$data['customers_status'])
			$data['customers_status'] = _STORE_CUSTOMERS_STATUS_ID;

		// vat ID check

			if (isset($data['customers_vat_id']) && $data['customers_vat_id']!='') {
				$vat_check = $this->_checkVatId($form_data,'',$return_val = true);
				if ($vat_check==true) {
					if (_STORE_VAT_CHECK_MOVE=='true') {
						if ($form_data['default_address']['customers_country_code']!=_STORE_COUNTRY) {
							$data['customers_status'] = _STORE_VAT_CHECK_STATUS_OUT;
						} else {
							$data['customers_status'] = _STORE_VAT_CHECK_STATUS_IN;
						}
					}
					$data['customers_vat_id_status'] = '1';
				} else {
					$data['customers_vat_id_status'] = '0';
				}
			}
		}
		elseif ($add_type === 'update')
		{
			if (isset($data['customers_vat_id']) && $data['customers_vat_id'] != '')
			{
				$data['cust_info']['customers_vat_id'] = $data['customers_vat_id'];
				$data['default_address']['customers_country_code'] = $_SESSION['customer']->customer_default_address["customers_country_code"];
				$vat_check = $this->_checkVatId($data,'',$return_val = true);

				if ($vat_check == true)
				{
					if (_STORE_VAT_CHANGE_CLIENT_GROUP_ON_VAT_CHANGE=='true')
					{
						$data['customers_status'] = ($_SESSION['customer']->customer_default_address["customers_country_code"] != _STORE_COUNTRY)
							? _STORE_VAT_CHECK_STATUS_OUT
							: _STORE_VAT_CHECK_STATUS_IN;
					}

					$data['customers_vat_id_status'] = '1';
				} else {
					$data['customers_vat_id_status'] = '0';
				}
			}
		}

        $currencies = $currency->_getCurrencyList();
        foreach($currencies as $k => $v)
        {
            $currencies[$k] = $v['code'];
        }
        if(empty($form_data['customers_default_currency']) || !in_array($form_data['customers_default_currency'],$currencies))
        {
            $form_data['customers_default_currency'] = $currency->default_currency;
        }
        $languages = $language->_getLanguageList();
        foreach($languages as $k => $v)
        {
            $languages[$k] = $v['code'];
        }
        if(empty($form_data['customers_default_language']) || !in_array($form_data['customers_default_language'],$languages))
        {
            $form_data['customers_default_language'] = $language->default_language;
        }

		$customer_data_array = array (
			'customers_gender'  => $data['customers_gender'],
			'customers_vat_id' => $data['customers_vat_id'],
			'customers_vat_id_status' => $data['customers_vat_id_status'],
			'customers_email_address' => $data['customers_email_address'],
			'customers_default_currency' => $form_data['customers_default_currency'],
			'customers_default_language' => $form_data['customers_default_language'],
			'shop_id' => $data['shop_id']
		);

		if (empty($customer_data_array['shop_id']))
		$customer_data_array['shop_id'] = $store_handler->shop_id;

		$customer_data_array['customers_id'] = $data['customers_id'];

		if(!empty($data['customers_status']))
			$customer_data_array['customers_status'] = $data['customers_status'];

		if($data['guest']==1){
			$customer_data_array['account_type'] = 1;
		}else{
			if(!empty($data['customers_password']))
			$customer_data_array['customers_password'] = $data['customers_password'];
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildCustomerData_bottom')) ? eval($plugin_code) : false;
		$this->customerData = $data;

		$this->_writeCustomerData($customer_data_array, $add_type);
		$this->customerData['success'] = true;
	}

	function _checkCustomerAddressData($data, $add_type = 'insert', $check_data=true){
		global $db, $xtPlugin;

		$data['error'] = false;

		if($check_data == true){
			if (_STORE_ACCOUNT_GENDER == 'true')
			$this->_checkGender($data['customers_gender']);

            // check date of birth and phone number if activated
            if (_STORE_ACCOUNT_DOB == 'true') {
            	if (!defined('_STORE_ACCOUNT_DOB_FORMAT')) define('_STORE_ACCOUNT_DOB_FORMAT', 'dd.mm.yyyy');
            	$this->_checkDate($data['customers_dob'], _STORE_ACCOUNT_DOB_FORMAT, ERROR_DATE_SYNTAX);
            }
            if (_STORE_TELEPHONE_MIN_LENGTH > 0) {
            	$this->_checkLenght($data['customers_phone'], _STORE_TELEPHONE_MIN_LENGTH, ERROR_TELEPHONE_NUMBER);
            }
			
			if (_STORE_MOBILE_PHONE_MIN_LENGTH > 0) {
				$this->_checkLenght($data['customers_mobile_phone'], _STORE_MOBILE_PHONE_MIN_LENGTH, ERROR_MOBILE_PHONE_NUMBER);
			}
			
           // var_dump($data); exit();
            if($data['old_address_class']== 'default' && $data['address_class'] != 'default'){
				$this->_checkDefaultAddress($data['customers_id'], ERROR_DEFAULT_ADDRESS);
            }
            // end check date and phone

			$this->_checkLenght($data['customers_firstname'], _STORE_FIRST_NAME_MIN_LENGTH, ERROR_FIRST_NAME);
			$this->_checkLenght($data['customers_lastname'], _STORE_LAST_NAME_MIN_LENGTH, ERROR_LAST_NAME);
			$this->_checkLenght($data['customers_street_address'], _STORE_STREET_ADDRESS_MIN_LENGTH, ERROR_STREET_ADDRESS);
			$this->_checkLenght($data['customers_postcode'], _STORE_POSTCODE_MIN_LENGTH, ERROR_POST_CODE);
			$this->_checkLenght($data['customers_city'], _STORE_CITY_MIN_LENGTH, ERROR_CITY);
			
			if (defined('_STORE_COMPANY_MIN_LENGTH') && _STORE_COMPANY_MIN_LENGTH > 0) {
				$this->_checkLenght($data['customers_company'], _STORE_COMPANY_MIN_LENGTH, ERROR_COMPANY);
			}
			
			if (defined('_STORE_FAX_MIN_LENGTH') && _STORE_FAX_MIN_LENGTH > 0) {
				$this->_checkLenght($data['customers_fax'], _STORE_FAX_MIN_LENGTH, ERROR_FAX);
			}

			($plugin_code = $xtPlugin->PluginCode('class.customer.php:_CustomerAddressData_check')) ? eval($plugin_code) : false;
		}

		if($this->error == true){
			$data['error'] = true;
			return false;
		}

	}


	function _buildCustomerAddressData($data, $add_type = 'insert', $check_data=true){
		global $db, $xtPlugin;
	
		$update_address_class = true;
		
		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_buildCustomerAddressData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($data['customers_id']))
		$data['customers_id'] = $this->data_customer_id;

		if($data['customers_dob']){
			$data['customers_dob'] = strtotime($data['customers_dob']);
			$data['customers_dob'] = date('Y-m-d', $data['customers_dob']);
		}
		if (_STORE_SHOW_PHONE_PREFIX=='true'){
            $countries = new countries(true,'store');
            if ($data['customers_phone_prefix']!='')
                $data['customers_phone'] = $data['customers_phone_prefix'].$countries->phone_delimiter.$data['customers_phone'];
            if ($data['customers_mobile_phone_prefix']!='')
                $data['customers_mobile_phone'] = $data['customers_mobile_phone_prefix'].$countries->phone_delimiter.$data['customers_mobile_phone'];
            if ($data['customers_fax_prefix']!='')
                $data['customers_fax'] = $data['customers_fax_prefix'].$countries->phone_delimiter.$data['customers_fax'];  
        }
		$address_data_array = array (
			'customers_id'  => $data['customers_id'],
			'customers_gender' => $data['customers_gender'],
			'customers_dob' => $data['customers_dob'],
			'customers_phone' => $data['customers_phone'],
			'customers_mobile_phone' => $data['customers_mobile_phone'],
			'customers_fax' => $data['customers_fax'],
			'customers_company' => $data['customers_company'],
			'customers_company_2' => $data['customers_company_2'],
			'customers_company_3' => $data['customers_company_3'],
			'customers_firstname' => $data['customers_firstname'],
			'customers_lastname' => $data['customers_lastname'],
			'customers_street_address' => $data['customers_street_address'],
			'customers_suburb' => $data['customers_suburb'],
			'customers_postcode' => $data['customers_postcode'],
			'customers_city' => $data['customers_city'],
			'customers_state' => $data['customers_state'],
			'customers_country_code' => $data['customers_country_code'],
			'customers_federal_state_code' => $data['customers_federal_state_code'],
			'customers_federal_state_code_iso' => $data['customers_federal_state_code_iso'],
			'address_class' => $data['address_class']
		);


		if(!empty($data['address_book_id']))
		$address_data_array['address_book_id'] = $data['address_book_id'];

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_registerCustomer_AddressData_bottom')) ? eval($plugin_code) : false;

		$this->customerAdressData[$data['address_class']] = $data;

		$this->_writeAddressData($address_data_array, $add_type);
		if($update_address_class == true){
			$this->_updateAddressClass($this->address_book_id, $data['customers_id'] , $data['address_class']);
		}
		$this->customerAdressData[$data['address_class']]['address_book_id'] = $this->address_book_id;
		$this->customerAdressData[$data['address_class']]['success'] = true;
	}

	function _writeCustomerData($data, $type='insert'){
		global $db, $xtPlugin, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_writeCustomerData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($data['customers_password'])){
			$data['customers_password'] = md5($data['customers_password']);
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_writeCustomerData_bottom')) ? eval($plugin_code) : false;

		if($type=='insert'){
			$insert_record = array('date_added'=>$db->BindTimeStamp(time()));
			$record = array_merge($insert_record, $data);
			$db->AutoExecute(TABLE_CUSTOMERS, $record, 'INSERT');
			$this->data_customer_id = $db->Insert_ID();
		}elseif($type=='update'){
			$update_record = array('last_modified'=>$db->BindTimeStamp(time()));
			$record = array_merge($update_record, $data);
			unset($record["customers_email_address"]);
			$db->AutoExecute(TABLE_CUSTOMERS, $record, 'UPDATE', "customers_id=".(int)$data['customers_id']."");
			$this->data_customer_id = $data['customers_id'];
		}

	}

	function _writeAddressData($data, $type='insert'){
		global $db, $xtPlugin, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_writeAddressData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_writeAddressData_bottom')) ? eval($plugin_code) : false;

		if($type=='insert'){
			$insert_record = array('date_added'=>$db->BindTimeStamp(time()));
			$record = array_merge($insert_record, $data);
			$db->AutoExecute(TABLE_CUSTOMERS_ADDRESSES, $record, 'INSERT');
			$this->address_book_id = $db->Insert_ID();
		}elseif($type=='update'){
			$update_record = array('last_modified'=>$db->BindTimeStamp(time()));
			$record = array_merge($update_record, $data);
			$db->AutoExecute(TABLE_CUSTOMERS_ADDRESSES, $record, 'UPDATE', "address_book_id=".$data['address_book_id']."");
			$this->address_book_id = $data['address_book_id'];
		}

	}

	function _deleteAddressData($id, $cid){
		global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_deleteAddressData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$data['error'] = false;

		$record = $db->Execute("SELECT * FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=?", array($cid));
		if($record->RecordCount() == 1){
			$data['error']='true';
			$data['message']=ERROR_DELETE_LAST_ADDRESS;
			$data['message_type']='error';
			return $data;
		}else{
            $result=$record->GetAll();
            $filtered=array_filter($result,create_function('$item','$ret=false;if($item[\'address_class\']=="default"){$ret=true;}return $ret;'));
            $rs = $db->Execute("SELECT address_class FROM " . TABLE_CUSTOMERS_ADDRESSES . " where address_book_id =?", array($id));
            if ($rs->RecordCount() > 0) {
                if ($rs->fields['address_class'] == 'default' && count($filtered)<=1) {
                    $data['error'] = 'true';
                    $data['message'] = ERROR_DELETE_DEFAULT_ADDRESS;
                    $data['message_type'] = 'error';
                    return $data;
                }
            }

        }

		$db->Execute(
			"DELETE FROM ". TABLE_CUSTOMERS_ADDRESSES ." WHERE address_book_id = ? and customers_id=?",
			array($id, $cid)
		);

		$data['success'] = 'true';
		$data['message']=SUCCESS_DELETE_ADDRESS;
		$data['message_type']='success';
		return $data;
	}

	function _sendAccountMail(){
		global $db, $xtPlugin, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_sendAccountMail_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$mail = new xtMailer('create_account');
		$mail->_addReceiver($this->customerData['customers_email_address'],$this->customerAdressData['default']['customers_lastname'].' '.$this->customerAdressData['default']['customers_firstname']);
		$mail->_assign('address_data',$this->customerAdressData);
		$mail->_assign('customers_data',$this->customerData);
		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_sendAccountMail_bottom')) ? eval($plugin_code) : false;
		$mail->_sendMail();
	}

	function _sendPasswordOptIn() {
		global $db,$xtPlugin, $store_handler,$xtLink;

		$request_key = $this->generateRandomString(32,0);
		$db->Execute(
			"UPDATE ".TABLE_CUSTOMERS." SET password_request_key=? WHERE customers_id=?",
			array($request_key, $this->customers_id)
		);
		
		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_sendPasswordOptIn')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;
		
		$mail = new xtMailer('password_optin');
		$mail->_addReceiver($this->customer_info['customers_email_address'],$this->customer_default_address['customers_lastname'].' '.$this->customer_default_address['customers_firstname']);
		$mail->_assign('address_data',$this->customer_default_address);
		$mail->_assign('customers_data',$this->customerData);

		$remember_link = $xtLink->_link(array('page'=>'customer', 'paction'=>'login','params'=>'action=check_code&remember='.$this->customers_id.':'.$request_key,'conn'=>'SSL'));

		$mail->_assign('remember_link',$remember_link);
		$mail->_sendMail();

	}
	

	function _sendNewPassword($password='') {
		global $db,$xtPlugin, $store_handler,$xtLink;
		
		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_sendNewPassword')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

		$password = $this->generateRandomString(_STORE_PASSWORD_MIN_LENGTH,$this->password_special_signs);
		$db->Execute(
			"UPDATE ".TABLE_CUSTOMERS." SET password_request_key='',customers_password=? WHERE customers_id=?",
			array(md5($password), $this->customers_id)
		);

		$mail = new xtMailer('new_password');
		$mail->_addReceiver($this->customer_info['customers_email_address'],$this->customer_default_address['customers_lastname'].' '.$this->customer_default_address['customers_firstname']);
		$mail->_assign('address_data',$this->customer_default_address);
		$mail->_assign('customers_data',$this->customerData);
		$mail->_assign('NEW_PASSWORD',$password);
		$mail->_sendMail();

	}
	
	/**
	 * generate more secure token/passwords
	 * @param number $length
	 * @param number $specialSigns
	 * @return string
	 */
	function generateRandomString($length=32,$specialSigns = 0) {
	
		$newpass = "";
		$laenge=$length;
		$laengeS = $specialSigns;
		$string="ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789";
		$stringS = "!#$%&()*+,-./";
	
		mt_srand((double)microtime()*1000000);
	
		for ($i=1; $i <= $laenge; $i++) {
			$newpass .= substr($string, mt_rand(0,strlen($string)-1), 1);
		}
		for ($i = 1; $i <= $laengeS; $i++) {
			$newpass .= substr($stringS, mt_rand(0, strlen($stringS) - 1), 1);
		}
		$newpass_split = str_split($newpass);
		shuffle($newpass_split);
		$newpass = implode($newpass_split);
		return $newpass;
	}

	function _setAdress($id,$type){
		global $db, $xtPlugin, $store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_setAdress_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$data = $this->_buildAddressData($this->customers_id, '', $id);
	
		if($type=='payment')
		$this->customer_payment_address = $data;

		if($type=='shipping')
		$this->customer_shipping_address = $data;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_setAdress_bottom')) ? eval($plugin_code) : false;

	}
	
	/**
	 * query total amount for given order status of customer, or amount of all orders
	 *
	 * @param int $status
	 * @return decimal
	 */
	function _getTotalOrderAmount($status = '') {
		global $db,$store_handler;
		
		if ($status == '') {
			$query = "SELECT os.orders_stats_price,o.currency_value FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_STATS." os WHERE o.orders_id=os.orders_id and o.customers_id=?";
			$rs = $db->Execute($query, array($this->customers_id));
		} else {
			$status = (int)$status;
			$query = "SELECT os.orders_stats_price,o.currency_value FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_STATS." os WHERE o.orders_id=os.orders_id and o.customers_id=? and o.orders_status=?";
			$rs = $db->Execute($query, array($this->customers_id, $status));
		}
		
		if ($rs->RecordCount()>0) {
			$total = 0;
			while (!$rs->EOF) {
				$total+=$rs->fields['orders_stats_price']/$rs->fields['currency_value'];
				$rs->MoveNext();
			}$rs->Close();
			return $total;
		} else {
			return 0;
		}
	}
	
	/**
	 * query total count for given order status of customer, or count of all orders
	 *
	 * @param int $status
	 * @return int
	 */
	function _getTotalOrderCount($status = '') {
		global $db,$store_handler;
		
		if ($status == '') {
			$query = "SELECT count(*) as count FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_STATS." os WHERE o.orders_id=os.orders_id and o.customers_id=?";
			$rs = $db->Execute($query, array($this->customers_id));
		} else {
			$status = (int)$status;
			$query = "SELECT count(*) as count FROM ".TABLE_ORDERS." o, ".TABLE_ORDERS_STATS." os WHERE o.orders_id=os.orders_id and o.customers_id=? and o.orders_status=?";
			$rs = $db->Execute($query, array($this->customers_id, $status));
		}
		
		return $rs->fields['count'];
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getParams_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$params = array();

		$header['customers_id'] = array('type' => 'hidden');
		$header['customers_password_old'] = array(
			'type' => 'hidden'
		);

		$header['customers_status'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=customers_status'
		);

		$header['customers_vat_id_status'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=status_truefalse'
		);

		$header['customers_admin_status'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=status_truefalse'
		);

		$header['shop_id'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=stores'
		);

		$header['campaign_id'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?systemstatus=campaign'
		);

		$header['customers_default_currency'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=currencies'
		);

		$header['customers_default_language'] = array(
			'type' => 'dropdown',
			'url'  => 'DropdownData.php?get=language_codes'
		);

		$header['customers_gender'] = array('renderer' => 'genderRenderer');

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getParams_header')) ? eval($plugin_code) : false;

		$rowActions[] = array('iconCls' => 'address', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ADDRESS);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($customers_id)."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('customers_id');\n";

          $js .= "addTab('adminHandler.php?load_section=address&pg=overview&adID='+edit_id,'".TEXT_ADDRESS." ('+edit_name+')')";

		$rowActionsFunctions['address'] = $js;

		$rowActions[] = array('iconCls' => 'orders', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ORDERS);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($customers_id)."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('customers_id');\n";
          $js .= "addTab('adminHandler.php?load_section=order&pg=overview&c_oID='+edit_id,'".TEXT_ORDERS." ('+edit_name+')')";

		$rowActionsFunctions['orders'] = $js;

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getParams_row_actions')) ? eval($plugin_code) : false;

		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;

		$params['header']         = $header;
		$params['master_key']     = $this->master_id;
		$params['default_sort']   = $this->master_id;
 		$params['languageTab']    = false;
		$params['edit_masterkey'] = false;
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol']  = true;
		//$params['display_newBtn'] = false;

		$params['display_searchPanel']  = true;

		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
			$params['include'] = array ('customers_id','customers_status','customers_gender', 'customers_company', 'customers_email_address','customers_firstname', 'customers_lastname','shop_id');
		}else{
			$params['exclude'] = array('customers_parent_id', 'password_request_key', 'refferers_id', 'date_added', 'last_modified', 'account_type', 'external_id');
		}

		// open shop
		if(!$this->url_data['edit_id'] && $this->url_data['new'] != true){
            $adminUser = $_SESSION['admin_user'];
            $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
            if ($adminUser && $adminUser['user_id'])
            {
                global $language;
                $lang = $_SESSION['selected_language'] ? $_SESSION['selected_language'] : $language->default_language;

                $url_backend = _SRV_WEB.'adminHandler.php?openRemoteWindow=addProducts&plugin=order_edit&load_section=order_edit_new_order&pg=openNewOrderTabBackend&customers_id=';
                $js_backend  = "var customers_id = record.data.customers_id;\n";
                $js_backend .= "addTab('".$url_backend."' + customers_id,'".TEXT_NEW_ORDER." ' + record.data.customers_email_address);\n";
                $js_backend .= "var a = 0;\n";

                $js_frontend  = "var customers_id = record.data.customers_id;\n";
                $url_frontend = _SRV_WEB. "adminHandler.php?plugin=order_edit&load_section=order_edit_new_order".$add_to_url."&pg=openNewOrderWindowFrontend&customers_email=";
                $js_frontend .= "window.open('".$url_frontend."'+record.data.customers_email_address+'&customers_id='+customers_id,'_blank');\n";

                $rowActionsFunctions['NEW_ORDER'] = (_SYSTEM_ORDER_EDIT_NEW_ORDER_IN_FRONTEND === 'true')
                    ? $js_frontend
                    : $js_backend;

                $rowActions[] = array('iconCls' => 'NEW_ORDER', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_NEW_ORDER);

                $params['rowActions']             = $rowActions;
                $params['rowActionsFunctions']    = $rowActionsFunctions;
            }
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getParams_bottom')) ? eval($plugin_code) : false;
		return $params;
	}

	function _getSearchIDs($search_data) {
		global $xtPlugin,$filter, $db;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getSearchIDs_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

	    $customer_sql_tablecols = array('customers_email_address','customers_id','customers_cid');

	   	$address_sql_tablecols = array(
			'customers_company',
			'customers_firstname',
			'customers_lastname',
			'customers_street_address',
			'customers_postcode',
			'customers_city'
		);

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getSearchIDs_arrays')) ? eval($plugin_code) : false;

		// SEARCH IN CUSTOMERS
        foreach ($customer_sql_tablecols as $customer_tablecol) {
           $customer_sql_where[]= "(".$customer_tablecol." LIKE '%".$search_data."%')";
        }

		$customer_sql_data_array = "(".implode(' or ', $customer_sql_where).")";		
		
		$rs = $db->Execute("SELECT customers_id FROM " . $this->_table . " where ".$customer_sql_data_array."");
		if($rs->RecordCount() > 0){
			while (!$rs->EOF) {
				$customer_search_data[] = $rs->fields['customers_id'];
				$rs->MoveNext();
			}$rs->Close();			
		}	    							   

	    // SEARCH IN ADDRESS BOOK 							   

        foreach ($address_sql_tablecols as $address_tablecol) {
           $address_sql_where[]= "(".$address_tablecol." LIKE '%".$search_data."%')";
        }

		$address_sql_data_array = "(".implode(' or ', $address_sql_where).")";		

		$record = $db->Execute("SELECT customers_id FROM " . $this->_table_add . " where ".$address_sql_data_array."");
		if($record->RecordCount() > 0){
			while (!$record->EOF) {
				$address_search_data[] = $record->fields['customers_id'];
				$record->MoveNext();
			}$record->Close();
		}		
		
		$search_array = array();
		
		if(is_array($customer_search_data))
		$search_array = array_merge($search_array, $customer_search_data);
		
		if(is_array($address_search_data))
		$search_array = array_merge($search_array, $address_search_data);
		
		if(is_array($search_data))
		array_unique($search_array);
		
		if(!is_array($search_array) || count($search_array)==0){
			$search_array[0] = '0';
		}		
		
		$sql_where = " customers_id IN (".implode(',', $search_array).")";

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_getSearchIDs_bottom')) ? eval($plugin_code) : false;
		return $sql_where;
	}

	function _get($ID = 0,$searched='') {
		global $xtPlugin, $db, $language;
		$obj = new stdClass;
		if ($this->position != 'admin') return false;

        ($plugin_code = $xtPlugin->PluginCode('class.customer.php:_get_top')) ? eval($plugin_code) : false;

		if ($ID === 'new') {
               $ID = $this->url_data['edit_id'];
		}
		if ($searched!='') $sql_where = 'customers_id IN ('.$searched.')';
		if($this->url_data['query']){
				$sql_where = $this->_getSearchIDs($this->url_data['query']);
		}		
		
		$table_data = new adminDB_DataRead($this->_table, NULL, NULL, $this->_master_key, $sql_where, $this->sql_limit);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
			
			if(is_array($data)){
				foreach ($data as $d_key=>$d_val){
					$_address_data = array();
					$_address_data = $this->_buildAddressData($d_val['customers_id'], 'default');
					
					if(is_array($_address_data))
					$data[$d_key] = array_merge($data[$d_key], $_address_data);
				}
			}
			
			$data_count = $table_data->_total_count;
		}elseif($ID){
			$data = $table_data->getData($ID);
			$data[0]['customers_password_old'] = $data[0]['customers_password'];
			$data[0]['customers_password'] = '';
		}else{
			$data = $table_data->getHeader();
			
			$__data = array(
				'customers_gender' => '',
				'customers_firstname' => '',
				'customers_lastname' => '',
				'customers_company' => '',
				'customers_city' => ''
			);

			if(is_array($data))
			$data = array_merge($data, $__data);							
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_get_bottom')) ? eval($plugin_code) : false;

		if($data_count!=0 || !$data_count)
		$count_data = $data_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_set_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($data['customers_password']){
			$data['customers_password'] = md5($data['customers_password']);
		}else{
			$data['customers_password'] = $data['customers_password_old'];
		}

		 $obj = new stdClass;
		 $oC = new adminDB_DataSave(TABLE_CUSTOMERS, $data, false, __CLASS__);
		 $obj = $oC->saveDataSet();

		return $obj;
	}

	function _unset($id = 0) {
	    global $db, $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.customer.php:_unset_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". TABLE_CUSTOMERS ." WHERE ".$this->master_id." = ?", array($id));
	    $db->Execute("DELETE FROM ". TABLE_CUSTOMERS_ADDRESSES ." WHERE ".$this->master_id." = ?", array($id));

	    ($plugin_code = $xtPlugin->PluginCode('class.customer.php:_unset_bottom')) ? eval($plugin_code) : false;
	}
}