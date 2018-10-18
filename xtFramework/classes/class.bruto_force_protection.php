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

class bruto_force_protection{


	var $lock_time = '20';
	var $check_time = '15'; // minuten
	var $failed = 0;

	protected $_table = TABLE_FAILED_LOGIN;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'fail_id';

	/**
	 * log failed login
	 *
	 * @param string $lookup
	 * @param int $type
	 * @param string $fail_type
	 * @return boolean
	 */
	function escalateLoginFail($lookup,$type='1',$fail_type='customers_login') {
		global $db,$filter;

		// email address allready added ?
		$rs = "SELECT * FROM ".TABLE_FAILED_LOGIN." WHERE lookup=? and check_type=? and last_try >= NOW()-".$this->check_time*60;
		$rs = $db->Execute($rs, array($lookup, (int)$type));
		if ($rs->RecordCount()==1) {
			$this->failed = $rs->fields['fail_count'];
			$this->failed++;
			$db->Execute(
				"UPDATE ".TABLE_FAILED_LOGIN." SET fail_count=? WHERE fail_id=?",
				array($this->failed, $rs->fields['fail_id'])
			);
			if ($this->failed>=5) { 
				$this->_lockID($rs->fields['fail_id']);
				return false;
			}
			return true;

		} else {
			// insert
			$db->Execute(
				"DELETE FROM ".TABLE_FAILED_LOGIN." WHERE lookup=? and check_type=?",
				array($lookup, (int)$type)
			);
			$this->_delete($lookup);
			$this->failed = 1;
			$insert_array = array();
			$insert_array['check_type'] = $type;
			$insert_array['lookup'] = $filter->_filter($lookup);
			$insert_array['fail_count'] = '1';
			$insert_array['fail_type'] = $fail_type;
			$db->AutoExecute(TABLE_FAILED_LOGIN,$insert_array);
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
        $qry="UPDATE ".TABLE_FAILED_LOGIN." set lock_until=DATE_ADD(NOW(), INTERVAL ".$this->lock_time." MINUTE) WHERE fail_id=?";
		$db->Execute($qry, array($id));

		$query = "SELECT * FROM ".TABLE_FAILED_LOGIN." WHERE fail_id=?";
		$rs = $db->Execute($query, array($id));

		if($_SERVER["HTTP_X_FORWARDED_FOR"]){
			$customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}else{
			$customers_ip = $_SERVER["REMOTE_ADDR"];
		}

		$log_data = array();
		$log_data['locked_lookup'] = $rs->fields['lookup'];
		$log_data['intrudor_ip'] = $customers_ip;
		$logHandler->_addLog('intrusion','customers_login','0',$log_data);

	}

	/**
	 * Check if IP/Mail is locked
	 *
	 * @param string $lookup
	 * @param int $type
	 * @return boolean
	 */
	function _isLocked($lookup,$type='1') {
		global $db,$filter;

		$type = (int)$type;
		$rs = "SELECT * FROM ".TABLE_FAILED_LOGIN." WHERE lookup=? and check_type=? and lock_until >= NOW()";
		$rs = $db->Execute($rs, array($lookup, $type));
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

		 $db->Execute("DELETE FROM ".TABLE_FAILED_LOGIN." WHERE ".$this->_master_key." = ?", array($id));

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

	function _delete($lookup) {
		global $db,$filter;

		 $db->Execute("DELETE FROM ".TABLE_FAILED_LOGIN." WHERE lookup=?", array($lookup));
	}
}