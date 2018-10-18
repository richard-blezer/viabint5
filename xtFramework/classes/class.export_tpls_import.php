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

include_once _SRV_WEBROOT.'xtFramework/classes/class.CurlRequest.php';

class export_tpls_import {

	var $default_language = _STORE_LANGUAGE;

	protected $_table = TABLE_FEED;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'feed_id';


	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$header['export_tpls'] = array(
            'type' => 'dropdown',
            'url' => 'DropdownData.php?get=export_tpls'
        );

		$header['replace_existing_tpls'] = array('type' => 'hidden',);
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;

		return $params;
	}

	 function _get($ID = 0) {
         global $xtPlugin, $db, $language;

        if ($this->position != 'admin') return false;
        
        $data = array();
        $data['export_tpls'] = '';
        $data['replace_existing_tpls'] = '';

        $obj = new stdClass();
        $obj->totalCount = 0;
        $obj->data = Array($data);
        return $obj;
        
	}

	function _set($data, $set_type = 'edit') {
		global $db,$currency;
        
        $export_tpls_id = $data['export_tpls'];

        $export_tpls_path = 'http://addons.xt-commerce.com/psm/list.xml';
        $curl = new CurlRequest($export_tpls_path);

        $curl->get();
        $xml=$curl->result();
        $xml_data = XML_unserialize($xml);
        
        foreach($xml_data['templates']['template'] as $key=>$value){
        	if($value['id'] == $export_tpls_id){

                $export_tpls_path = 'http://addons.xt-commerce.com/psm/'.$value['file'];
                $curl = new CurlRequest($export_tpls_path);

                $curl->get();
                $xml=$curl->result();

	        	$tpls_data = XML_unserialize($xml);
	        	$tpls_data = $tpls_data['template'];
        	}
        }

        $data['feed_store_id'] = 1;
        $data['feed_type'] = 1;
        $data['feed_language_code'] = _STORE_LANGUAGE;
        $data['feed_p_currency_code'] = _STORE_CURRENCY;
        $data['feed_header'] = $tpls_data['export_tpls_h'];
        $data['feed_body'] = $tpls_data['export_tpls_b'];
        $data['feed_footer'] = $tpls_data['export_tpls_f'];
        $data['feed_title'] = $tpls_data['export_tpls_name'];
        $data['feed_filename'] = $tpls_data['export_tpls_fname'];
        $data['feed_filetype'] = $tpls_data['export_tpls_ftype'];
        $data['feed_save'] = 1;
        $data['feed_p_customers_status'] = 2;

        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();
        
        return $obj;
	}

	function _unset($id = 0) {
	    global $db;
	    return true;
	}
}