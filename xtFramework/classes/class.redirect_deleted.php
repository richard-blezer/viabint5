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

class redirect_deleted {

	public    $_store_field = 'store_id';
	public    $store_field_exists = false;
    protected $_table = TABLE_SEO_URL_REDIRECT;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'master_key';

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
					$header['url_text_redirect_'.$add_to_f.$val['code']] = array('type'=>'hidden');
				}else{
					$header['url_text_'.$add_to_f.$val['code']] = array('width'=>400);
					$header['url_text_redirect_'.$add_to_f.$val['code']] = array('width'=>400);
				}
			}
		}
		
		
		$header['link_id'] = array('type'=>'text','readonly' => 1);
        $params['header']         = $header;
        $params['master_key']     = $this->_master_key;
        $params['default_sort']   = 'link_id';
        if (isset($this->sql_limit))
        {
            $exp= explode(",",$this->sql_limit);
            $params['PageSize'] = trim($exp[1]);
        }
		$params['display_newBtn']  = false;
		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'])
        	$params['include']        = array ('master_key','link_id', 'link_type','url_text','url_text_redirect');
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
		
        $store_field= '';
		if ($this->store_field_exists) 
		{
			$store_field= $this->_store_field;
		}
		$sql_where = ' is_deleted =1 ';
		
		if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,25";
        }
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $permissions,'','',$store_field);
		
        if ($this->url_data['get_data']){
			$sql_where .= " and language_code='".$language->code."'";
            $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, $this->sql_limit, $permissions,'','',$store_field);
			$data = $table_data->getData();
			$data_count = $table_data->_total_count;
        }elseif($ID){
        	
			$res = $db->Execute("SELECT DISTINCT t.* FROM ".$this->_table." t 
        							LEFT JOIN " . TABLE_SEO_URL . " p ON p.link_id = t.link_id and p.link_type = t.link_type 
        						WHERE t.is_deleted=1 and t.".$this->_master_key." = ? ", array($ID));
			if ($res->RecordCount() > 0) 
        	{					
				while(!$res->EOF)
				{
					$store_field='';
					if ($this->store_field_exists) {
						$store_field= 'store'.$res->fields['store_id'].'_';
					}
					$data[0]['url_text_'.$store_field.$res->fields['language_code']] = $res->fields['url_text'];
					$data[0]['url_text_redirect_'.$store_field.$res->fields['language_code']] = $res->fields['url_text_redirect'];
					$data[0]['link_type'] = $res->fields['link_type'];
					$data[0]['link_id'] = $res->fields['link_id'];
					$res->MoveNext();
				}$res->Close();	
				
				$data[0] = $this->checkAllLangs($data[0]);
			} 	
	
        }else{
            $data = $table_data->getHeader();
        }

        if($data_count!=0 || !$data_count)
            $count_data = $data_count;
        else
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
			}
		}
		return $data;
	}
	
    function _set($data, $set_type = 'edit') {
        global $db,$language, $seo;

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
	            $insert_data['link_type'] = $data['link_type'];
				$data['url_text'] = $data['url_text_'.$add_to_f.$val['code']]; 
				if ($data['url_text_redirect_'.$add_to_f.$val['code']]!='')
					$url_text = $data['url_text_redirect_'.$add_to_f.$val['code']];
				else $url_text = $data['url_text'];
				
				
				$exp = explode($val['code'].'/',$url_text);
				$insert_data['url_text_redirect'] = $url_text;
				$validate_data = $insert_data;
				$validate_data['url_text'] = $insert_data['url_text_redirect'];
				$url_text = $seo->validateDBKeyLink ($validate_data,'');
				
	    		$url_md5 = $seo->_UrlHash($url_text);
				$insert_data['url_md5_redirect'] = $url_md5;
		        $db->AutoExecute($this->_table,$insert_data,'UPDATE',"master_key=".$db->Quote($data['master_key'])." and link_type=".$db->Quote($data['link_type'])." and language_code=".$db->Quote($val['code'])." ".$add_to_where);
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
		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ? ", array($id));
    }
}