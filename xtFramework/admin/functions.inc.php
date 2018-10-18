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

  function encodeMenuArray($a) {
  	  $erg = '[';
  	  $trenner1 = '';
  	  if (is_array($a))
  	  foreach($a as $item) {
  	  				$erg .= $trenner1.'{';
      	  $trenner2 = '';
        	  if (is_array($item))
  	  				  foreach($item as $key2 => $value2) {
															if ($key2 == 'handler') {
  	  	  									$erg .= $trenner2."$key2 : $value2";
															} else {
																		if (is_array($value2)) {
  	  	  									  $erg .= $trenner2."$key2 : ".encodeMenuArray($value2);
																		} else {
  	  	  									  $erg .= $trenner2."$key2 : '$value2'";
																		}
															}
               $trenner2 = ', ';
											}
  	      $erg .= '}';
  	      $trenner1 = ', ';
					}
					$erg .= ']';
					return $erg;
		}


function adminData_setLangCode ($data, $lang_code) {

	if (!is_array($data)) return $data;
	foreach ($data as $key => $val) {
		$_lang_data[$key.'_'.$lang_code] = $val;
	}
	return $_lang_data;
}

function adminData_unsetLangCode ($data, $lang_code, $use_array_keys) {
	global $filter;
	if (!is_array($data)) return $data;
	$new_data = array();
	foreach ($use_array_keys as $key) {
		if ($data[$key.'_'.$lang_code])
		$new_data[$key]=$data[$key.'_'.$lang_code];
	}
	return $new_data;
}

/* adminData_setStoreLangCode
 *
 * generating fields for each store and language by naming the filed with store and language key
 *
 * @param (object) ($data) - item data
 * @param (string) ($lang_code) - language
 * @param (string) ($store) - store id
 * @return object - created field
 */
function adminData_setStoreLangCode ($data, $lang_code,$store) {

	if (!is_array($data)) return $data;
	foreach ($data as $key => $val) {
		$_lang_data[$key.'_store'.$store.'_'.$lang_code] = $val;
	}
	return $_lang_data;
}

/* adminData_unsetStoreLangCode
 *
 * assigning field with language and store key to new array
 *
 * @param (object) ($data) - item data
 * @param (string) ($lang_code) - language
 * @param (array) ($use_array_keys) - array to look into
 * @param (string) ($store) - store id
 * @return array - created array
 */
function adminData_unsetStoreLangCode ($data, $lang_code, $use_array_keys,$store) {
	global $filter;
	if (!is_array($data)) return $data;
	$new_data = array();
	foreach ($use_array_keys as $key) {
		if ($data[$key.'_store'.$store.'_'.$lang_code])
		$new_data[$key]=$data[$key.'_store'.$store.'_'.$lang_code];
	}
	return $new_data;
}

function adminGetTabelFields ($table) {
    global $db;
	$obj = new stdClass();
    $query = "SHOW FIELDS FROM ".$table." ";
    $record = $db->Execute($query);
	if ($record->RecordCount() > 0) {
		while(!$record->EOF){
			$records = $record->fields;
			$obj->fields[] = $records['Field'];
			$obj->fieldData[$records['Field']] = $records;
			$record->MoveNext();
		} $record->Close();
	}
    $obj->totalCount = count($obj->fieldData);
    return $obj;
}

function __define($name) {
	$name = strtoupper($name);
		global $language, $store_handler, $customers_status,$language_list;
        $desc = '';
		$lang = false;
			$split_name = preg_split('/_/',$name);
			
			foreach ($language_list as $key => $val) {
				if ($split_name[(count($split_name)-1)] == strtoupper($val['code'])) {
						$name = substr($name, 0, strlen($name)-strlen('_'.$val['code']) );
						$desc = $val['code'].' ';
					break;
				}
			}

	if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
		$name_2 = TEXT_BLACKLIST_PERMISSIONS;
	}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
		$name_2 = TEXT_WHITELIST_PERMISSIONS;
	}

	if(strstr($name, TEXT_IMAGE_) && !strstr($name, _PREVIEW)){
		$tmp_name = preg_split('/_/',$name);
		$tmp_name = array_reverse($tmp_name);

		$tmp_id = (int) $tmp_name[0];

		$name = TEXT_IMAGE .'_'.$tmp_id;
	}

	if (!defined($name)) {
		$_SESSION['debug'][$name]['name'] = $name;
		$_SESSION['debug'][$name]['desc'] = $desc;
		return $name;
	} else {
		return constant($name);
	}
}

function _getPermissionInfo() {
    
    
     if (_SYSTEM_GROUP_PERMISSIONS=='blacklist') {
        return _filterText(html_entity_decode(TEXT_BLACKLIST_PERMISSIONS_INFO), $type='full');
     } else {
        return _filterText(html_entity_decode(TEXT_WHITELIST_PERMISSIONS_INFO), $type='full');
     }
 
}

function checkHTTPS() {
    if(!empty($_SERVER['HTTPS']))
        if($_SERVER['HTTPS'] !== 'off')
            return true; //https
        else
            return false; //http
    else
        if($_SERVER['SERVER_PORT'] == 443)
            return true; //https
        else
            return false; //http
}

 /* StoreIdExists
 *
 * Check if field exists in case CHECK_STORE_ID_EXISTS=true
 * IF CHECK_STORE_ID_EXISTS = false the check is skipped
 *
 * @param (string) ($table) - the table to check into
 * @param (string) ($column) - column to check in $table
 * @return boolean
 */
function StoreIdExists($table,$column){
	global $db;
	if (CHECK_STORE_ID_EXISTS==='true')
	{
		$rs=$db->Execute("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='"._SYSTEM_DATABASE_DATABASE."' AND table_name='".$table."' AND COLUMN_NAME = '".$column."' ");
		if ($rs->RecordCount()>0)
		{
			return true;
		}
		return false;
	}
	return true;
}
?>