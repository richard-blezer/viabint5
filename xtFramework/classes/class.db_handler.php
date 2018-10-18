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

class xDB  {

	function xDB($db) {
		$this->db_handler = $db;
		$this->count_insert = 0;
		$this->count_update = 0;
		$this->count_delete = 0;
	}

	function _setData($tablename, &$sql_array, $requirement = '') {
		if (!empty($requirement)) {
			$record = $this->db_handler->Execute("SELECT count(*) as total FROM ".$tablename." WHERE ".$requirement);
			if ($record->RecordCount() > 0) {
				$this->db_handler->AutoExecute($tablename,$sql_array,'UPDATE', $requirement);
				$check_data = true;
			}
		}

		if ($check_data!=true) {
			$this->db_handler->AutoExecute($tablename,$sql_array,'INSERT');
		}

		return $this->db_handler->Insert_ID();
	}

	function _getData ($tablename, $sql_colname_array, $requirement='', $single = true) {
		$colnames = '';

		foreach ($sql_colname_array as $colname){
  				$colnames .= $colname.", ";
		}

		if($requirement!=''){
			$requirement = " WHERE ".$requirement. "";
		}

		$record = $this->db_handler->Execute("SELECT ".substr($colnames,0,-2)." FROM ".$tablename." ".$requirement." ");

		while(!$record->EOF){
			$data[] = $record->fields;
			$record->MoveNext();
		}$record->Close();

		if (count($data) == 1 && $single)
			return $data[0];
		if (count($data) > 0)
			return $data;
	}

	function _deleteData ($tablename,  $requirement) {
		if (empty($requirement))  return '0';
			$this->db_handler->Execute("DELETE  FROM ".$tablename." WHERE ".$requirement);
	}
}