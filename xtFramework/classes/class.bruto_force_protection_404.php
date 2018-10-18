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

// type 1 = email
// type 2 = IP

class bruto_force_protection_404{


	var $lock_time = '20';
	var $check_time = '15'; // minuten
	var $failed = 0;

	protected $_table = TABLE_FAILED_PAGES;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'fail_id';
	protected $_failed_attempts = 50;

	/**
	 * log failed login
	 *
	 * @param string $lookup
	 * @param int $type
	 * @param string $fail_type
	 * @return boolean
	 */
	function escalateFailedPageLoad($fail_type='customers_404') {
		global $db,$filter;
		
		$ip = $this->getCustomerIP();
		
		// email address allready added ?
		$rs = "SELECT * FROM ".$this->_table." WHERE ip=? and fail_type=? and last_try >= NOW()-".$this->check_time*60;
		$rs = $db->Execute($rs, array($ip, $fail_type));
		if ($rs->RecordCount()==1) {
			$this->failed = $rs->fields['fail_count'];
			$this->failed++;
			
			if ($this->failed>=$this->_failed_attempts) {
			    if (!$this->_isLocked()){
			        $this->_lockID($rs->fields['fail_id']);
			    }
				return false;
			}else{
			    $db->Execute(
					"UPDATE ".$this->_table." SET fail_count=? WHERE fail_id=?",
					array($this->failed, $rs->fields['fail_id'])
				);
			}
			return true;

		} else {
			// insert
            $db->Execute(
                "INSERT INTO xt_failed_pages ( IP, FAIL_COUNT, FAIL_TYPE ) VALUES ( ?, ?, ? ) ON DUPLICATE KEY UPDATE `fail_count`=`fail_count`+1",
                array($filter->_filter($ip), 1, $fail_type)
            );
			return true;
		}
	}

	/**
	 * lock IP/Mail for given timespan
	 *
	 * @param int $id
	 */
	function _lockID($id) {
		global $db,$logHandler;
		$id = (int)$id;
		
        $qry="UPDATE ".$this->_table." set lock_until=DATE_ADD(NOW(), INTERVAL ".$this->lock_time." MINUTE) WHERE fail_id=?";
		$db->Execute($qry, array($id));
	}

	/**
	 * Check if IP/Mail is locked
	 *
	 * @param string $lookup
	 * @param int $type
	 * @return boolean
	 */
	function _isLocked() {
		global $db,$filter;
		
		$ip = $this->getCustomerIP();

		$rs = "SELECT * FROM ".$this->_table." WHERE ip=? and lock_until >= NOW()";
		$rs = $db->Execute($rs, array($ip));
		if ($rs->RecordCount()==1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * clear IP/Mail from table
	 *
	 * @param string $lookup
	 */
	function _unset($id=0) {
		global $db;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

		$db->Execute("DELETE FROM ".$this->_table." WHERE ".$this->_master_key." = ?", array($id));
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();

		$params['header']         = array();
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;

		$params['display_newBtn'] = false;
		$params['display_editBtn'] = false;
		$params['display_checkCol']  = true;
        
        $params['SortField'] = 'last_try';
        $params['SortDir'] = "DESC";

		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}elseif($ID){
			$data = $table_data->getData($ID);
			$data[0]['passwd'] = '';
		}else{
			$data = $table_data->getHeader();
		}

		$obj = new stdClass;
		$obj->totalCount = count($data);
		$obj->data = $data;

		return $obj;
	}

	function _delete($ip,$fail_type) {
		global $db,$filter;
		$db->Execute(
			"DELETE FROM ".$this->_table." WHERE ip=? and fail_type=?",
			array($ip, $fail_type)
		);

	}
	
	function getCustomerIP(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}