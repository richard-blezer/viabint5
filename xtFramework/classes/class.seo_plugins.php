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
 # @version $Id: class.stop_words.php 6060 2013-03-14 13:10:33Z mario $
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

class seo_plugins {

	public    $_store_field = 'store_id';
	public    $store_field_exists = false;
    protected $_table = TABLE_SEO_URL;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'link_id';

    function setPosition ($position) {
        $this->position = $position;
    }

    function _getParams() {
    	global $language;
        $params = array();
		
		if (StoreIdExists($this->_table,$this->_store_field)) 
		{
			$this->store_field_exists=true;
		}
		
		if ($this->store_field_exists)
			$params['languageStoreTab'] = true;
		
		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {
	        foreach ($language->_getLanguageList() as $key => $val) {
				$add_to_f='';
				if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
				if(_SYSTEM_HIDE_SUMAURL=='true'){
					$header['url_text_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				}else{
					$header['url_text_'.$add_to_f.$val['code']] = array('width'=>400);
				}
	
				$header['meta_keywords_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_title_'.$add_to_f.$val['code']] = array('width'=>400);
				$header['meta_description_'.$add_to_f.$val['code']] = array('type' => 'textarea','width'=>400,'height'=>60);
			}
		}
		
		$header['link_type'] = array('type'=>'hidden');
		$header['link_id'] = array('type'=>'text','readonly' => 1);
		$header['plugin_code'] = array('type'=>'text','readonly' => 1);
		
        $params['header']         = $header;
        $params['master_key']     = $this->_master_key;
        $params['default_sort']   = 'link_id';
        $params['PageSize']       = 50;
		$params['display_newBtn']  = false;
		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'])
        	$params['include']        = array ('link_id', 'link_type','plugin_code','url_text');
		else  $params['exclude']        = array ('');
        $params['display_searchPanel']  = false;

        return $params;
    }

    function _get($ID = 0) {
        global $xtPlugin, $db, $language;
        $where='';
        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }

        $ID = (int)$ID;
        if ($this->url_data['get_data']){
        	
        	$res = $db->Execute(
				"SELECT DISTINCT t.*,p.plugin_code FROM ".$this->_table." t
        		LEFT JOIN " . TABLE_PLUGIN_CODE . " p ON p.plugin_id = t.link_id
        		WHERE t.link_type = '1000' and t.language_code = ? ", array($language->code));
        	if ($res->RecordCount() > 0) 
        	{
        		$i=0;
				while(!$res->EOF)
				{
					$data[$i]= $res->fields; 
					$i++;
					$res->MoveNext();
				}$res->Close();	 
			}
        }elseif($ID){
        	
			$res = $db->Execute("SELECT DISTINCT t.*, p.plugin_code FROM ".$this->_table." t 
        							LEFT JOIN " . TABLE_PLUGIN_CODE . " p ON p.plugin_id = t.link_id 
        						WHERE t.link_type = '1000' and t.link_id = ? ", array($ID));
			if ($res->RecordCount() > 0) 
        	{					
				while(!$res->EOF)
				{
					$store_field='';
					if ($this->store_field_exists) {
						$store_field= 'store'.$res->fields['store_id'].'_';
					}
					$data[0]['url_text_'.$store_field.$res->fields['language_code']] = $res->fields['url_text'];
					$data[0]['meta_keywords_'.$store_field.$res->fields['language_code']] = $res->fields['meta_keywords'];
					$data[0]['meta_title_'.$store_field.$res->fields['language_code']] = $res->fields['meta_title'];
					$data[0]['meta_description_'.$store_field.$res->fields['language_code']] = $res->fields['meta_description'];
					$data[0]['link_type'] = $res->fields['link_type'];
					$data[0]['link_id'] = $res->fields['link_id'];
					$data[0]['plugin_code'] = $res->fields['plugin_code'];
					$res->MoveNext();
				}$res->Close();	
				
				$data[0] = $this->checkAllLangs($data[0]);
			} 			
        }else{
        	$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where,'','','','',$store_field);
            $data = $table_data->getHeader();
			$data[0]['plugin_code'] = 'plugin_code'; 
        }

        $count_data = count($data);
		$obj = new stdClass;
        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }
	
	function checkAllLangs($data)
	{
		global $db,$language;
		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) 
			{
				$add_to_f='';
				if ($this->store_field_exists) $add_to_f = 'store'.$store['id'].'_';
				$data['url_text_'.$add_to_f.$val['code']] = ($data['url_text_'.$add_to_f.$val['code']]=='')? '' : $data['url_text_'.$add_to_f.$val['code']];
				$data['meta_keywords_'.$add_to_f.$val['code']] = ($data['meta_keywords_'.$add_to_f.$val['code']]=='')? '' :$data['meta_keywords_'.$add_to_f.$val['code']];
				$data['meta_title_'.$add_to_f.$val['code']] = ($data['meta_title_'.$add_to_f.$val['code']]=='')? '' :$data['meta_title_'.$add_to_f.$val['code']];
				$data['meta_description_'.$add_to_f.$val['code']] = ($data['meta_description_'.$add_to_f.$val['code']]=='')? '' :$data['meta_description_'.$add_to_f.$val['code']];
	
			}
		}
		return $data;
	}
	
    function _set($data, $set_type = 'edit') {
        global $db,$language, $seo;

		
		$rec = $db->Execute("SELECT * FROM " . TABLE_PLUGIN_PRODUCTS . " WHERE  plugin_id=? LIMIT 0,1", array($data['link_id']));
	    if ($rec->RecordCount() >0) 
	    	$data['plugin_code'] = $rec->fields['code'];
		$st = new multistore();
		$stores = $st->getStores();
		
		foreach ($stores as $store) {
			foreach ($language->_getLanguageList() as $key => $val) 
			{
				$add_to_f='';
				$add_to_where='';
				$rs=$db->Execute(
					"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema=? AND table_name=? AND COLUMN_NAME = ? ",
					array(_SYSTEM_DATABASE_DATABASE, $this->_table, $this->_store_field)
				);
				if ($rs->RecordCount()>0) {
					$add_to_f = 'store'.$store['id'].'_';
					$insert_data['store_id'] = $store['id'];
					$add_to_where = " and store_id='".$store['id']."' ";
				}
				$insert_data['language_code'] = $val['code'];
	            $insert_data['link_type'] = '1000';
	            $insert_data['link_id'] =$data['link_id'];
				if ($data['url_text_'.$add_to_f.$val['code']]!='')
					$url_text = $data['url_text_'.$add_to_f.$val['code']];
				else $url_text = $val['code'].'/'.$data['plugin_code'];
				$exp = explode($val['code'].'/',$url_text);
				$insert_data['url_text'] = $val['code'].'/'.$seo->filterAutoUrlText($exp[count($exp)-1],$val['code'] );
		
				$url_text = $seo->validateDBKeyLink ($insert_data,'');
	    		$url_md5 = $seo->_UrlHash($url_text);
				$insert_data['url_md5'] = $url_md5;
				$insert_data['meta_keywords'] = $data['meta_keywords_'.$add_to_f.$val['code']];
				$insert_data['meta_title'] = $data['meta_title_'.$add_to_f.$val['code']];
				$insert_data['meta_description'] = $data['meta_description_'.$add_to_f.$val['code']];
				
				$record = $db->Execute(
					"SELECT * FROM " . TABLE_SEO_URL . " WHERE link_type='1000' and link_id=? and language_code=? ".$add_to_where,
					array($data['link_id'], $val['code'])
				);
		        if ($record->RecordCount() == 0) {
		            $db->AutoExecute(TABLE_SEO_URL,$insert_data,'INSERT');
		        }else{
		        	
		            $db->AutoExecute(TABLE_SEO_URL,$insert_data,'UPDATE',"link_type=".$db->Quote($data['link_type'])." and link_id=".$db->Quote($data['link_id'])." and language_code=".$db->Quote($val['code'])." ".$add_to_where);
		        }
			}
		}
		$obj = new stdClass;
       	$obj->success = true;
		return $obj;	
    }

    function _unset($id = 0) {
        global $db;

        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id=(int)$id;
        if (!is_int($id)) return false;
		saveDeletedUrl($id,1000);
		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ? and link_type=1000", array($id));

    }

	function getPluginData($id,$all=0)
	{
		global $xtPlugin, $db,$language,$filter;

		($plugin_code = $xtPlugin->PluginCode('class.seo_plugin.php:getPluginData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		if ($all==0) $add_to_sql=" and language_code = '".$language->code."'";
		else $add_to_sql = '';
		$query = "SELECT * FROM ".$this->_table." WHERE link_type='1000' and link_id = ? ".$add_to_sql;
		
		$record = $db->Execute($query, array($id));
		if($record->RecordCount() > 0){
			if($all==0) 
				$data = $record->fields;
			else
			{
				$i=0;
				while(!$record->EOF)
				{
					$data[$i]= $record->fields; 
					$i++;
					$record->MoveNext();
				}$record->Close();	 
			}
			($plugin_code = $xtPlugin->PluginCode('class.seo_plugin.php:getPluginData_bottom')) ? eval($plugin_code) : false;
			return $data;
		}else{
			return false;
		}
	}
	
	function getPluginByPluginCode($plugin_code)
	{
		global $db,$language;

		$query = "SELECT * FROM ".TABLE_PLUGIN_PRODUCTS." WHERE code =? LIMIT 0,1 ";
		
		$record = $db->Execute($query, array($plugin_code));
		if($record->RecordCount() > 0){
			$data = $record->fields;
			return $data;
		}else{
			return false;
		}
	}
	
	function getPluginByID($id)
	{
		global $db,$language;

		$query = "SELECT * FROM ".TABLE_PLUGIN_PRODUCTS." WHERE plugin_id =? LIMIT 0,1 ";
		$record = $db->Execute($query, array($id));
		if($record->RecordCount() > 0){
			$data = $record->fields;
			return $data;
		}else{
			return false;
		}
	}
	
	public function setPluginSEO($code)
	{
		$plugin_data = $this->getPluginByPluginCode($code);
		$insert_data = array('plugin_code' => $code, 'link_id'=> $plugin_data['plugin_id']);
		$this->_set($insert_data);
		
	} 

	public function unsetPluginSEO($code)
	{ global $db;
		$plugin_data = $this->getPluginByPluginCode($code);
		$db->Execute("DELETE FROM ". TABLE_SEO_URL ." WHERE link_id = ? and link_type=1000", array($plugin_data['plugin_id']));
	}
}