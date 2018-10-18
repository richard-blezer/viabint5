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

class system_status{

	protected $_table = TABLE_SYSTEM_STATUS;
	protected $_table_lang = TABLE_SYSTEM_STATUS_DESCRIPTION;
	protected $_table_seo = null;
	protected $_master_key = 'status_id';

	function system_status($export_lang='') {
		global $db,$language;
		
		if ($export_lang!='') $lng = $export_lang;
		else $lng = $language->code;
		
		$this->values = array ();
		$result = $db->CacheExecute(
			"SELECT * FROM " . $this->_table . " st, ".$this->_table_lang." std where st.status_id=std.status_id and std.language_code = ?",
			array($lng)
		);

		$classes = array();

		while (!$result->EOF) {
			$arr = unserialize(stripslashes($result->fields['status_values']));

			$data = array (
				'id' => $result->fields['status_id'],
				'name' => $result->fields['status_name'],
				'image' => $result->fields['status_image']
			);

			if (is_array($arr)) $data = array_merge($data,$arr);
			if (!isset($classes[$result->fields['status_class']]) && isset($arr['sorting'])) $classes[$result->fields['status_class']] = $arr['sorting'];

			$this->values[$result->fields['status_class']][$result->fields['status_id']] = $data;
			$result->MoveNext();
		}

		// sort array
		foreach ($classes as $key => $val) {
			$this->values[$key] = $this->matrixSort($this->values[$key],$val);
		}
	}

	function matrixSort(&$matrix,$sortKey,$sort = 'ASC') {
		if (count($matrix) == 0) return false;

		foreach($matrix as $key => $subMatrix) {
			$tmpArray[$key]=$subMatrix['data'][$sortKey];
		}
		arsort($tmpArray);

		while(list($key, $value)=each($tmpArray)) {
		$ArrayNew[$key]=$matrix[$key];
		}

		if ($sort != 'ASC') {
			$ArrayNew = array_reverse($ArrayNew);
		}

		return $ArrayNew;
	}

	function _getSingle($seach_type, $serach_key, $serach_value, $return_value='all'){
		$content_data = $this->values[$seach_type];
		while (list ($key, $value) = each($content_data)) {
			if($value[$serach_key]==$serach_value){
				if($return_value!='all'){
				 	return	$value[$return_value];
				}else{
					return	$value;
				}
			}
		}
	}

	function _buildArray($data){
		global $db, $language;

		$t_fields = getTableFields($this->_table);
		$t_lang_fields = getTableFields($this->_table_lang);

		foreach ($t_fields as $key => $val) {
			$field_array[] = $key;
		}

		foreach ($t_lang_fields as $key => $val) {
			foreach ($language->_getLanguageList() as $lkey => $lval) {
				if($key !='status_id')
				$field_array[] = $key.'_'.$lval['code'];
			}
		}

		foreach ($field_array as $key => $val) {
			$new_data[$val] = $data[$val];
			unset($data[$val]);
		}

		$tmp_data['data'] = $data;

		$new_data['status_values'] = addslashes(serialize($tmp_data));

		return $new_data;
	}

	function _defaultValues(){
		global $xtPlugin;

		$data_array = array('stock_rule','shipping_status','base_price','order_status','campaign','zone');

		($plugin_code = $xtPlugin->PluginCode('class.system_status.php:_defaultValues_bottom')) ? eval($plugin_code) : false;

		foreach ($data_array as $key => $val) {
			$new_data_array[$val] = $val;
		}

		return $new_data_array;

	}

	function _completeData($data){

		if($data['status_values'] != ''){
			
			$tmp_arr_data = preg_replace('%/%','',$data['status_values']);
			$tmp_arr_data = str_replace('/','',$tmp_arr_data);
			
			$tmp_arr_data = stripslashes($tmp_arr_data);
			$arr = unserialize(stripslashes($tmp_arr_data));
	
			if(!is_array($arr))
			$arr = unserialize(stripslashes($this->status_fields_array));

				if(is_array($arr)){
					foreach ($arr as $key => $val) {

						if(is_array($val)){
							foreach ($val as $dkey => $dval) {
								$data[$dkey] = $dval;
							}
						}else{
								$data[$key] = $val;
						}

					}
				}

			unset($data['status_values']);
		}

		return $data;
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		$this->_defineFields();

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}

		$where = 'status_class = "'.$this->_master_status.'"';

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}elseif($ID){
			$data = $table_data->getData($ID);

			if($this->status_fields_array){
			$data[0] = $this->_completeData($data[0]);
			}else{
			unset($data[0]['status_values']);
			}
		}else{
			$data = $table_data->getHeader();
		}

		$obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;
	}

	function _set($data, $set_type = 'edit'){
		global $db,$language,$filter;

		 $obj = new stdClass;

		 $this->_defineFields();

		 $tmp_data = $data;
		 unset($data);

		foreach ($tmp_data as $key => $val) {

			if($val == 'on')
			   $val = 1;

			if($key!='')
			$data[$key] = $val;
		}

		 if ($set_type=='edit') {
		 	
             // take default
             if($this->status_fields_array) {
            $default_values = unserialize(stripslashes($this->status_fields_array));
            foreach ($default_values['data'] as $key => $val) { 
               $default_values['data'][$key]=0; 
            }    
            $data = array_merge($default_values['data'],$data);
             
			 $data = $this->_buildArray($data);
             }
		 }

		 if ($set_type=='new') {

		 	$record = $db->Execute("SELECT max(status_id) as id from " . $this->_table . "");
			$new_id = $record->fields['id']+1;

			$data['status_id'] = $new_id;

		 	if($this->status_fields_array)
			$data['status_values'] = $this->status_fields_array;

		 	$data['status_class'] = $this->_master_status;
		 }

		 $oCS = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		 $objCS = $oCS->saveDataSet();

		 if ($set_type=='new') {	// edit existing
			 $obj->new_id = $new_id;
			 $data = array_merge($data, array($this->master_key=>$obj->new_id));
		 }

		 $oCSD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
		 $objCSD = $oCSD->saveDataSet();

		 if ($objCS->success && $objCSD->success) {
		     $obj->success = true;
		 } else {
		     $obj->failed = true;
		 }

		return $obj;
	}

	function _unset($id = 0) {
	    global $db;
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
	    $db->Execute("DELETE FROM ". $this->_table_lang ." WHERE ".$this->_master_key." = ?", array($id));

	}
	
	function getSingleValue($id,$lang='')
	{
		global $db,$language;
		$data='';
		if ($lang=='') $lang = $language->code;
		
		$record =$db->Execute(
			"Select * FROM  ". $this->_table_lang ." WHERE status_id =? and language_code=?",
			array($id, $lang)
		);
		
		if($record->RecordCount() >0)
		{
			$data = $record->fields;
		}
		
		return $data;
	} 
}