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


class adminTask {
    var $active_id;
    var $class;
    var $status;
    var $action;


    function __construct() {
        $this->dbTable = TABLE_ADMIN_ACL_TASK;
        $this->setStatus('true');
        $this->LiveTime = '300'; // 300 sec = 5min
    }

    function setActiveID($value) {
        $this->active_id = $value;
    }

    function getActiveID() {
        return $this->active_id;
    }

    function setClass($value) {
        $this->class = $value;
    }

    function getClass() {
        return $this->class;
    }

    function setStatus($value) {
        $this->status = $value;
    }

    function getStatus() {
        return $this->status;
    }

    function setAction($value) {
        switch ($value) {
            case 'new':
            case 'edit':
            case 'delete':
                $this->setStatus('true');
                break;
            case 'view':
            case 'save':
            default:
                $this->setStatus('false');
                break;
        }

        $this->action = $value;
    }

    function getAction() {
        return $this->action;
    }

    function _cleanTask () {
        global $db;
        $record = $db->Execute("DELETE FROM ".$this->dbTable." WHERE action != 'new' and last_modified < ? ", array(date("Y-m-d H:i:s", (time()-$this->LiveTime))));
    }

    function _killTask () {
        global $db;
        $record = $db->Execute("DELETE FROM ".$this->dbTable."");
    }
    
    function isDisabled ($id = '') {
        global $db;
        if ($id === '')
        $id = $this->getActiveID();

        $record = $db->Execute(
            "SELECT * FROM ".$this->dbTable." WHERE active_id = ? and class = ? and closed = 'true'",
            array($id, $this->getClass())
        );
		if ($record->RecordCount() > 0)
		  return true;
		else return false;
    }

    function checkTask ($id) {
        global $db;

        $task_key = $this->buildKey();
        
        $record = $db->Execute(
            "SELECT active_id FROM ".$this->dbTable." WHERE class = ? and user_id =? and task_key = ? and action = 'new' and closed = 'true'",
            array($this->getClass(), $_SESSION['admin_user']['user_id'], $task_key)
        );
		if ($record->RecordCount() > 0){
			return $record->fields['active_id'];
		}else{
			return $id;
		}

    }

    function _isData() {
        global $db;
        $record = $db->Execute(
            "SELECT * FROM ".$this->dbTable." WHERE active_id = ? and class = ? ",
            array($this->getActiveID(), $this->getClass())
        );
		if ($record->RecordCount() > 0)
		  return true;
		else return false;
    }

    function _set() {
        global $db;

        $task_key = $this->buildKey();
        
        $active_id = $this->getActiveID();
        $action = $this->getAction();
        
        if($action=='new' && !$active_id){
        	$this->_cleanTask();
        	return false;
        }

        $content = array(
            'active_id' => $active_id,
            'user_id' => $_SESSION['admin_user']['user_id'],
            'class' => $this->getClass(),
            'action' => $action,
            'closed' => $this->getStatus(),
            'task_key' => $task_key,
            'last_modified' => date("Y-m-d H:i:s")
        );
        if ($this->_isData()) {
            $db->AutoExecute($this->dbTable, $content, 'UPDATE', "active_id='".(int)$this->getActiveID()."' and class = ".$db->Quote($this->getClass())." ");
        } else {
            $content = array_merge($content, array('date_added' => date("Y-m-d H:i:s"),));
            $db->AutoExecute($this->dbTable, $content, 'INSERT');
        }

        foreach ($content as $key => $val)
        $logdata[] = $key.':'.$val;
        $this->_cleanTask();
    }
    function _get() {
        global $db;

		$record = $db->Execute("SELECT * FROM ".$this->dbTable." ORDER BY last_modified DESC");
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$data[] = $record->fields;
				$record->MoveNext();
			} $record->Close();
        }
        return $data;
    }
    
    function buildKey($data=''){
    	global $_GET;

    	if(!is_array($data))
    	$data = $_GET;
        $new_key = '';

    	if(is_array($data) && count($data)!=0){
    		reset($data);
	    	foreach($data as $key=>$val){
	    		if($key!='_dc')
	    		$new_key .= $key.'='.$val.'&';
	    	}
    	}

    	$new_key = md5($new_key);
    	return $new_key;
    }
}