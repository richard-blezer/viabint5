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

class language_import {

	public $default_language = _STORE_LANGUAGE;

	protected $_table = TABLE_LANGUAGE_CONTENT;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'language_content_id';


	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['language_file'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=language_xml');
		$header['content_language'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=language_codes');
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = 'language_key';
 		$params['languageTab']    = 0;
 		$params['PageSize']       = 50;

		return $params;
	}

	 function _get($ID = 0) {
         global $xtPlugin, $db, $language;

        if ($this->position != 'admin') return false;
        
        $data = array();
        $data['language_file'] = '';
        $data['content_language'] = '';
        
        $obj = new stdClass();
        $obj->totalCount = 0;
        $obj->data = Array($data);
        return $obj;
	}

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter,$currency;
        
        $file = $data['language_file'];
        $lang = str_replace('.xml','',$file);
        $downloadsFolder = _SRV_WEBROOT . "media" . DIRECTORY_SEPARATOR . "lang_downloads";
        $cnt_file = $downloadsFolder . DIRECTORY_SEPARATOR . "{$lang}_content.yml";
        $path = 'media/lang/';
        $file = _SRV_WEBROOT.$path.$file;
        
        if (!file_exists($cnt_file)) {
	        $cnt_file = _SRV_WEBROOT.$path.$lang.'_content.yml';
        }
        
        if (file_exists($file) && file_exists($cnt_file)) {
            
            // add language
            $xml = file_get_contents($file);
            $xml_data = XML_unserialize($xml);    

           // check if language allready existing
            $code = $filter->_filter($xml_data['xtcommerce_language']['code'],'lng');
            $lng = $language->_getLanguageList('admin','code');
            $curr = $filter->_filter($xml_data['xtcommerce_language']['default_currency'],'cur');
            
            $_data = array();     
            if (is_array($lng[$code]))  $_data['languages_id']=$lng[$code]['languages_id'];
                
            $_data['name'] = $filter->_filter($xml_data['xtcommerce_language']['name']);  
            $_data['code'] = $code;
            $_data['content_language']=$code; 
            if (isset($data['content_language'])) {
                if ($data['content_language']!='') {
                $_data['content_language']=$filter->_filter($data['content_language'],'lng');
                }
            }
            
            $_data['default_currency'] = $curr;
            $_data['language_status'] = 1;
            $_data['font'] = $filter->_filter($xml_data['xtcommerce_language']['font']);
            $_data['font_position'] = $filter->_filter($xml_data['xtcommerce_language']['font_position']);
            $_data['font_size'] = $filter->_filter($xml_data['xtcommerce_language']['font_size']);
            $_data['image'] = $filter->_filter($xml_data['xtcommerce_language']['image']);
            $_data['language_charset'] = 'utf-8';
            $_data['setlocale'] = $filter->_filter($xml_data['xtcommerce_language']['setlocale']);

            $obj = $language->_set($_data);
            
            $language->_importYML($cnt_file,$code);
            
            // check for currencies
            $cur = $currency->_getCurrencyList('admin','code');
            if (!is_array($cur[$curr])) {
                // add currency
                $curr_data = array();
                $curr_data['code']=$curr;
                $curr_data['dec_point']=',';
                $curr_data['decimals']='2'; 
                $curr_data['prefix']=$curr; 
                $curr_data['suffix']=''; 
                $curr_data['thousands_sep']='.'; 
                $curr_data['title']=$curr; 
                $curr_data['value_multiplicator']='1';
                $currency->_set($curr_data);
            }
            // duplicate country definition
            // check if lng country list exists
            $country_file = _SRV_WEBROOT.$path.'en_countries.csv';
            if (file_exists($country_file)) {
               $this->_importCountries($country_file,$code); 
            }
        }

		return $obj;
	}
    
    /**
    * import csv file with country names
    * 
    * @param mixed $file
    * @param mixed $code
    */
    private function _importCountries($file,$code) {
        global $db;
        
        $handle = fopen ($file,"r");
        $db->Execute(
            "DELETE FROM ".TABLE_COUNTRIES_DESCRIPTION." WHERE language_code=?",
            array($code)
        );
        while ( ($data = fgetcsv ($handle, 1000, ";",'"')) !== FALSE ) {
            $insert_array = array();
            $insert_array['language_code']=$code;
            $insert_array['countries_name']=$data[1];
            $insert_array['countries_iso_code_2']=$data[2];

            $db->AutoExecute(TABLE_COUNTRIES_DESCRIPTION,$insert_array);
        }

        fclose ($handle);
    }

	function _unset($id = 0) {
	    global $db;
	    return true;
	}
}