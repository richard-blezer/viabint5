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

require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.recursive.php');
class product_to_media {

	public $_table = TABLE_MEDIA_LINK;
	public $_table_lang = null;
	protected $_table_seo = null;
	public $_master_key = 'ml_id';
    protected $_icons_path = "images/icons/";
    protected $type = 'media';
    protected $class = 'product';

    function setPosition ($position) {
    	$this->position = $position;
    }
    
    function _getParams() {
    	$params = array();
    	
    	$add_to_url = (isset($_SESSION['admin_user']['admin_key']))? 'sec='.$_SESSION['admin_user']['admin_key'].'&': '';
    	$params['master_key'] 		= $this->_master_key;
    	$params['default_sort']   	= 'sort_order';
    	$params['SortField']      	= 'sort_order';
    	$params['SortDir']       	= "ASC";
    	$params['display_editBtn'] 	= false;
    	$params['display_newBtn'] 	= false;
    	$params['display_checkCol'] = true;
    	$extF = new ExtFunctions();
    	
    	$rowActionsFunctions['sort_up'] = $extF->_MultiButton_stm(TEXT_SORT_UP, 'sort_up');
    	$rowActionsFunctions['sort_down'] = $extF->_MultiButton_stm(TEXT_SORT_DOWN, 'sort_down');
    	$rowActions[] = array('iconCls' => 'sort_up', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_SORT_UP);
    	$rowActions[] = array('iconCls' => 'sort_down', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_SORT_DOWN);
    	
    	$params['rowActions'] = $rowActions;
    	$params['rowActionsFunctions'] = $rowActionsFunctions;
    	
    	return $params;
    }
    
    function _get($ID = 0) {
    	global $xtPlugin, $db, $language;
    
    	if ($this->position != 'admin') return false;
    
    	$whereQuery = 'link_id=' . $db->Quote($this->url_data['link_id']) . ' AND ' . $this->_table . '.class=' . $db->Quote($this->class) . ' AND ' . $this->_table . '.type='.$db->Quote($this->type).'';
    	
    	$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $whereQuery);
    	$condition = sprintf(' LEFT JOIN %s ON(%s.id=%s.m_id)', TABLE_MEDIA, TABLE_MEDIA, $this->_table);
    	$table_data->setJoinCondtion($condition);
    	
    	if ($this->url_data['get_data'])
    		$data = $table_data->getData();
    	elseif($ID) {
    		$data = $table_data->getData($ID);
    	}
    	else {
    		$data = $table_data->getHeader();
    		$data[0] = array('file' => '') + $data[0]; 
    	}
    	
    	$obj = new stdClass;
    	$obj->totalCount = count($data);
    	$obj->data = $data;
    
    	return $obj;
    }
    
    function _set($data, $set_type='edit'){
    	global $db,$language,$filter;
    
    	$obj = new stdClass;
    	$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
    	$obj = $o->saveDataSet();
    		
    	return $obj;
    }
    
    
    function _unset($id = 0) {
    	global $db;
    	if ($id == 0) return false;
    	if ($this->position != 'admin') return false;
    	$id=(int)$id;
    	if (!is_int($id)) return false;
    
    	$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
    }
    
    function sort () {
    	$mIds = $this->getCurrentIds();
    	$currentElement = $this->url_data['m_ids'];
    	
    	$sortPos = array_search($currentElement, $mIds->currentIds);
    	$count = count($mIds->currentIds);
    	
    	if ($this->url_data['pos'] == 'up' && $sortPos > -1) {
    		$newPos = $sortPos - 1;
    	}
    	
    	if ($this->url_data['pos'] == 'down' && $sortPos < $count) {
    			$newPos = $sortPos + 1;
    	}
    	
    	if ($newPos !== false) {
    		$swapElement = $mIds->currentIds[$newPos];
    		$obj = new stdClass();
    		
    		if (($currentElement && $newPos !== false) && ($swapElement && $sortPos !== false)) {
    			$currentData = array('ml_id' => $currentElement, 'sort_order' => $newPos);
    			$swapData = array('ml_id' => $swapElement, 'sort_order' => $sortPos);
    			$this->_setSortOrder($currentData);
    			$this->_setSortOrder($swapData);
    	
    			$obj->success = true;
    		}
    	}
    	
    	if (!$obj->success)
    		$obj->failed = true;
    }
    
    public function getCurrentIds() {
    	global $db;
    	
    	$qry = " type = '".$this->type."' and class = '".$this->class."'";
    	$record = $db->Execute(
			"SELECT * FROM ".$this->_table." ml WHERE link_id = ? and ".$qry." order by sort_order ",
			array($this->url_data['link_id'])
		);
    	if ($record->RecordCount() > 0) {
    		while(!$record->EOF){
    			$currentIds[] = $record->fields['ml_id'];
    			if (!$record->fields['sort_order'] > 0 || count($currentIds)-1 != $record->fields['sort_order']) {
    				$sortData = $record->fields;
    				$sortData['sort_order'] = count($currentIds)-1;
    				$this->_setSortOrder($sortData);
    				$sortCount = $sortData['sort_order'];
    			} else {
    				$sortCount = $record->fields['sort_order'];
    			}
    			 
    			$sortedIds[$record->fields['ml_id']] = $sortCount;
    			 
    			$record->MoveNext();
    		} $record->Close();
    	}
    	$obj = new stdClass();
    	$obj->sortedIds = $sortedIds;
    	$obj->currentIds = $currentIds;
    	return $obj;
    }
    
    function _setSortOrder ($data)
    {
    	global $db;
		
    	$where = ' link_id = ? and class = ? and ml_id = ?';
    	$qryCheck = "SELECT * FROM " . $this->_table . " m WHERE " . $where;
    	$record = $db->Execute($qryCheck, array($this->url_data['link_id'], $this->class, $data['ml_id']));
    	if ($record->RecordCount() == 0) {
    		$default = array(
    			'link_id' => $this->url_data['link_id'],
    			'class' => $this->class,
    			'type' => $this->type
			);
    		$data = array_merge($default, $data);
    		$db->AutoExecute($this->_table, $data, 'INSERT');
    	} else {
    		$newData['sort_order'] = $data['sort_order'];
    		$db->AutoExecute($this->_table, $newData, 'UPDATE', $where);
    	}
    
    	$obj = new stdClass();
    	$obj->success = true;
    	return $obj;
    }
}