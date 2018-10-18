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

include_once _SRV_WEBROOT.'xtFramework/classes/class.validation.vat_id.php';

class check_fields{


	/**
	 * check gender value, f m c allowed
	 *
	 * @param char $data
	 */
	function _checkGender($data){
		global $xtPlugin, $info;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkGender_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if (($data != 'm') && ($data != 'f') && ($data != 'c') ) {
			$this->error = true;
			$info->_addInfo(TEXT_GENDER_ERROR);
		}

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkGender_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * validate input by length
	 *
	 * @param string $data
	 * @param int $lenght
	 * @param string $error_message
	 */
	function _checkLenght($data, $lenght, $error_message){
		global $xtPlugin, $info;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkLenght_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if ((strlen($data) < $lenght) && $lenght!=0) {
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}
    
    /**
    * validate email address by regex expression
    * 
    * @param string $data email address
    * @param string $error_message
    */
    function _checkEmailAddress($data, $error_message){
        global $xtPlugin, $info;
        
        ($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkEmailAddress_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;
        
        $pattern = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' .
'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i';

        $result = preg_match ($pattern, $data);
        if (!$result) {
            // 2nd check (subdomain)
            $pattern = "/^[^0-9][A-z0-9_-]+([.][A-z0-9_-]+)*[@][A-z0-9_-]+([.][A-z0-9_-]+)*[.][A-z]{2,4}$/";
            $result = preg_match ($pattern, $data);
            if (!$result) {
                $this->error = true;
                $info->_addInfo($error_message);
            }
        }
        
    }
            
	function _checkDefaultAddress($cID, $error_message){
		global $db,$info;
		$record = $db->Execute(
			"SELECT address_book_id FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=? and address_class='default'",
			array($cID)
		);

		if($record->RecordCount() <2 ){
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}

	function _checkCurrentPassword($data,$cID,$error_message) {
		global $xtPlugin, $info,$db;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkCurrentPassword_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$password = md5($data);
		$cID = (int)$cID;
		if (!is_int($cID)) return false;

		$rs = $db->Execute(
			"SELECT * FROM ".TABLE_CUSTOMERS." WHERE customers_id=? and customers_password=?",
			array($cID, $password)
		);
		if ($rs->RecordCount()!=1) {
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}

	function _checkNum($data, $error_message){
		global $xtPlugin, $info;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkNum_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if (is_numeric($data) == false) {
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}

	/**
	 * validate by match
	 *
	 * @param string $data
	 * @param string $data_match
	 * @param string $error_message
	 */
	function _checkMatch($data, $data_match, $error_message){
		global $xtPlugin, $info;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkMatch_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if ($data != $data_match) {
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}

	function _checkExist($data, $field, $table, $params, $error_message){
		global $db, $xtPlugin, $info;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkExist_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute(
			"SELECT ".$field." FROM " . $table . " where ".$field."=? and ".$params."",
			array($data)
		);

		if($record->RecordCount() > 0){
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}

	function _checkState($data, $error_message){
		global $xtPlugin, $info;

		if(ACCOUNT_STATE == 'true' && empty($data)){
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}

	function _checkVatId($data,$error_message,$return_val = false) {
		global $xtPlugin, $info;
		
		if (!isset($data['cust_info']['customers_vat_id']) or $data['cust_info']['customers_vat_id']=='') return false;
		if (!(_STORE_VAT_CHECK_TYPE == 'simple' || _STORE_VAT_CHECK_TYPE == 'complex' || _STORE_VAT_CHECK_TYPE == 'live')) return false;;
		
		$vat_id = new vat_id();
		
		try {
			$check = $vat_id->_check($data['cust_info']['customers_vat_id'],$data['default_address']['customers_country_code']);

			if ($return_val==true) return $check;

			if ($check!=true) {
				$this->error = true;
				if ($check==-99) {
					$info->_addInfo(ERROR_VAT_ID_SERVICE);
				} else {
					$info->_addInfo($error_message);
				}

			}

		} catch (Exception $e) {
			$this->error = true;
			$info->_addInfo(ERROR_VAT_ID_SERVICE);
			if ($return_val==true) return -3;
			// TODO add service error to error log
		}
	}
    

	function _checkDefaultAddressClass($data, $old_data){
		global $xtPlugin, $db, $info;

		if($old_data == 'default' && $data != 'default'){
			return $old_data;
		}else{
			return $data;
		}
	}


	/**
	 * 
	 * checks if user input is a valid date (birthday)
	 * 
	 * @param string $data date to be checked
	 * @param string $format_str format date should have
	 * @param string $error_message message that is shown to the user in case of an error
	 */
	function _checkDate($data, $format_str, $error_message) {
		global $xtPlugin, $info;

		($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkDate_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		$isDate_bol = true;
		
		// check length
		if (strlen($data) != strlen($format_str)) $isDate_bol = false;
				
		// checking for delimiter
		if (strpos($format_str, '.') !== false) {
			$glue_str = '.';
		}
		elseif (strpos($format_str, '-') !== false) {
			$glue_str = '-';
		}
		elseif (strpos($format_str, '/') !== false) {
			$glue_str = '/';
		}
		elseif (strpos($format_str, ' ') !== false) {
			$glue_str = ' ';
		}
		else {
			$isDate_bol = false;
		}
		
		if ($isDate_bol) {
			// finding delimiter positions
			$firstPos_num = strpos($format_str, $glue_str);
			$lastPos_num = strrpos($format_str, $glue_str);
			
			$subLength1_num = $firstPos_num;
			$subLength2_num = $lastPos_num - $firstPos_num - 1;
			
			$startPos2_num = $firstPos_num + 1;
			$startPos3_num = $lastPos_num + 1;
			
			// checking date parts
			if (!is_numeric(substr($data,0,$subLength1_num))) $isDate_bol = false;
			if (!is_numeric(substr($data,$startPos2_num,$subLength2_num))) $isDate_bol = false;
			if (!is_numeric(substr($data,$startPos3_num))) $isDate_bol = false;
			if (substr($data,$firstPos_num,1) != $glue_str) $isDate_bol = false;
			if (substr($data,$lastPos_num,1) != $glue_str) $isDate_bol = false;
		}
		
		// checking validity
		if ($isDate_bol) {
			if ($format_str == 'dd.mm.yyyy') {
				$day_num = (int) substr($data,0,$subLength1_num);
				$month_num = (int) substr($data,$startPos2_num,$subLength2_num);
				$year_num = (int) substr($data,$startPos3_num);
				
				if (!checkdate($month_num, $day_num, $year_num)) $isDate_bol = false;
			}
			elseif ($format_str == 'yyyy-mm-dd') {
				$year_num = (int) substr($data,0,$subLength1_num);
				$month_num = (int) substr($data,$startPos2_num,$subLength2_num);
				$day_num = (int) substr($data,$startPos3_num);
				
				if (!checkdate($month_num, $day_num, $year_num)) $isDate_bol = false;
			}
			elseif ($format_str == 'mm/dd/yyyy') {
				$month_num = (int) substr($data,0,$subLength1_num);
				$day_num = (int) substr($data,$startPos2_num,$subLength2_num);
				$year_num = (int) substr($data,$startPos3_num);
				
				if (!checkdate($month_num, $day_num, $year_num)) $isDate_bol = false;
			}
			
			($plugin_code = $xtPlugin->PluginCode('class.customer_check.php:_checkDate_validity')) ? eval($plugin_code) : false;
		}
		
		if (!$isDate_bol) {
			$this->error = true;
			$info->_addInfo($error_message);
		}
	}
}