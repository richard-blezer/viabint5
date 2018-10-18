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

require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/classes/class.adminDB_DataRead.php');
require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'admin/functions.inc.php');
class recursive {
	protected $_table          = null;
	protected $_masterKey      = null;
	protected $_masterLangKey  = null;
	protected $_parentKey      = 'parent_id';
	protected $_sortKey        = 'sort_order';
	protected $_sortDir        = 'ASC';
	protected $_currentItemId  = null;
	protected $_parentItemId   = 0; // root by 0
    protected $_async          = true; // default true: load data by ajax calls | false: load data recursive
    protected $_level          = 0;
    protected $_lastIds        = array();
    protected $_lastLangIds    = array();
    protected $_displayKey     = null;
    protected $_displayLang    = false;
    protected $_displayLangCode= null;
    protected $_touchedIds     = array();
    protected $_touchedLangIds = array();
    protected $_rootIds        = array();
	protected $_store_id	   =null;

	function __construct($table, $masterKey, $parentKey = null, $sortKey = null, $async = null) {
	    global $language;
        $this->_displayLangCode = $language->code;
	    // set required settings
        $this->setTable($table);
        $this->setMasterKey($masterKey);

        // set optional settings
        if ($parentKey != null)
            $this->setParentKey($parentKey);
        if ($sortKey != null)
            $this->setSortKey($sortKey);
        if ($async != null)
            $this->setAsync($async);
	}

    ////////////////////////////////////////////////////////
    // required

    	////////////////////////////////////////////////////
    	//
    	function setTable($table) {
            $this->_table = $table;
    	}
    	function getTable() {
            return $this->_table;
    	}
    	////////////////////////////////////////////////////
    	//

    	function setMasterLangKey($key) {
            $this->_masterLangKey = $key;
    	}
    	function getMasterLangKey() {
    	    if ($this->_masterLangKey !== null)
            return $this->_masterLangKey;
            return $this->getMasterKey();
    	}

    	function setLangTable($table) {
            $this->_tableLang = $table;
    	}
    	function getLangTable() {
            return $this->_tableLang;
    	}
    	////////////////////////////////////////////////////
    	//
    	function setMasterKey($key) {
            $this->_masterKey = $key;
    	}
    	function getMasterKey() {
            return $this->_masterKey;
    	}
    	function setDisplayLangCode($code) {
            $this->_displayLangCode = $code;
    	}
    	function getDisplayLangCode() {
            return $this->_displayLangCode;
    	}
		   	
    	////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////
    // optional

        ////////////////////////////////////////////////////
    	//
    	function setParentKey($key) {
            $this->_parentKey = $key;
    	}
    	function getParentKey() {
            return $this->_parentKey;
    	}

    	////////////////////////////////////////////////////
    	//
    	function setSortKey($key) {
            $this->_sortKey = $key;
    	}
    	function getSortKey() {
            return $this->_sortKey;
    	}

    	////////////////////////////////////////////////////
    	//
    	function setDisplayKey($key) {
            $this->_displayKey = $key;
    	}
    	function getDisplayKey() {
    	    $key = $this->_displayKey;
    	    if ($this->isDisplayLang())
    	       $key.= '_'.$this->_displayLangCode;
            return $key;
    	}

    	////////////////////////////////////////////////////
    	//
    	function setSortDir($dir) {
            $this->_sortDir = $dir;
    	}
    	function getSortDir() {
            return $this->_sortDir;
    	}
    	////////////////////////////////////////////////////
    	//
    	function setCurrentItemId($itemId) {
            $this->_currentItemId = $itemId;
    	}
    	function getCurrentItemId() {
            return $this->_currentItemId;
    	}

    	////////////////////////////////////////////////////
    	//
    	function setParentItemId($itemId) {
            $this->_parentItemId = $itemId;
    	}
    	function getParentItemId() {
            return $this->_parentItemId;
    	}

    	////////////////////////////////////////////////////
    	//
    	function setAsync($boolean) {
            $this->_async = $boolean;
    	}
    	function isAsync() {
            return $this->_async;
    	}

    	////////////////////////////////////////////////////
    	//
    	function setDisplayLang($boolean) {
            $this->_displayLang = $boolean;
    	}
    	function isDisplayLang() {
            return $this->_displayLang;
    	}
    // end settings
    ////////////////////////////////////////////////////////

    function setLastId($id) {
        $this->_lastIds[] = $id;
    }

    function getLastIds() {
        return $this->_lastIds;
    }
    function setLastLangId($id) {
        $this->_lastILangds[] = $id;
        $this->_lastILangds = array_unique($this->_lastILangds);
    }

    function getLastLangIds() {
        return $this->_lastILangds;
    }
    function setTouchedId($id) {
        $this->_touchedIds[] = $id;
        $this->_touchedIds = array_unique($this->_touchedIds);
    }

    function getTouchedIds() {
        return $this->_touchedIds;
    }
    function setTouchedLangId($id) {
        $this->_touchedLangIds[] = $id;
        $this->_touchedLangIds = array_unique($this->_touchedLangIds);
    }

    function getTouchedLangIds() {
        return $this->_touchedLangIds;
    }
    function setRootId($id) {
        $this->_rootIds[] = $id;
        $this->_rootIds = array_unique($this->_rootIds);
    }

    function setWhereQuery($qry){
    	$this->WhereQuery =  $qry;
    }
	
	function setJoinedTable($qry){
    	$this->JoinedTable =  $qry;
    }
	
	function getJoinedTable(){
    	return $this->JoinedTable;
    }
    
	function setStoreID($store_id){
			$this->_store_id = $store_id;
		} 
		function getStoreID(){
			return $this->_store_id;
		} 
    function getRootIds() {
        return $this->_rootIds;
    }
    function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {

	}

    ////////////////////////////////////////////////////////
    // protected helpers

        protected function _getSortQuery() {
            if ($this->getSortKey() && $this->getSortDir())
            return ' ORDER BY '. $this->getSortKey() . ' ' . $this->getSortDir();
        }

        protected function _getWhereQuery() {
            return $this->WhereQuery;
        }     
        
        
	// protected helpers
    ////////////////////////////////////////////////////////

    function _getItemDataByLang ($id) {
        global $db;
        //if (is_array($this->_itemCachData[$id])) return $this->_itemCachData[$id];
		$record = $db->Execute("SELECT * FROM ".$this->getTable()." WHERE ".$this->getMasterLangKey(). " = '".$id."' LIMIT 1");
		if ($record->RecordCount() == 1) {
		    $item    = $record->fields;
		}
        return $item;
    }

    function _getItemLangData($id) {
        $item = $this->_getItemData($id);
        return $this->_getLangData($id, $item);
    }

    function _getItemData ($id) {
        global $db;
        if (is_array($this->_itemCachData[$id])) return $this->_itemCachData[$id];
		$record = $db->Execute("SELECT * FROM ".$this->getTable()." WHERE ".$this->getMasterKey(). " = '".$id."' LIMIT 1");
		if ($record->RecordCount() == 1) {
		    $item    = $record->fields;
		    $this->_setItemCache($item);
		}
        return $item;
    }

    function _setItemCache($item) {
        $this->_itemCachData[$item[$this->getMasterKey()]] = $item;

    }


    function _getLevelItems($parent_id = '') {
        global $db;

        $parentItemId = $this->getParentItemId();
        if ($parent_id)
            $parentItemId = $parent_id;
		
		$record = $db->Execute("SELECT * FROM ".$this->getTable().' '.$this->getJoinedTable()." WHERE ".$this->getParentKey(). " = '".$parentItemId."' ". $this->_getWhereQuery() . $this->_getSortQuery());
		
		if ($record->RecordCount() > 0) {
			while(!$record->EOF){
                $item = $record->fields;

                $item = $this->_getLangData($item[$this->getMasterKey()], $item);
                $data = $this->_getLevelNoteItemData($item);


                if ($parentItemId == 0)
                $this->setRootId($item[$this->getMasterKey()]);
                $this->setTouchedId($item[$this->getMasterKey()]);
                $this->setTouchedLangId($item[$this->getMasterLangKey()]);

                $this->_setItemCache($item);

                $items[$item[$this->getMasterKey()]] = $data;
                if (!$this->isAsync()) {
                    $sub = $this->_getLevelItems($item[$this->getMasterKey()]);
                    if (count($sub)) {
                        $items[$item[$this->getMasterKey()]]['sub'] = $sub;
                    } else {
                        $this->setLastId($item[$this->getMasterKey()]);
                        $this->setLastLangId($item[$this->getMasterLangKey()]);
                    }
                }
                $record->MoveNext();
			}
			$record->Close();
		}

        return $items;
    }

    function _getLangData($id, $item) {
        if ($this->getLangTable()) {
        	
            $data = new adminDB_DataRead($this->getTable(), $this->getLangTable(), '', $this->getMasterLangKey(), '', '', '','','',$this->getStoreID());
    		$itemL = $data->getData($id);
			
            if (count($itemL[0]) > 0) {
                return $itemL[0];
            }
        }
		return $item;

    }

    function _moveNode($dropId, $targetId) {
        global $db;
        if (!$dropId && !$targetId) return false;

        $db->AutoExecute($this->getTable(), array($this->getParentKey() => $targetId),'UPDATE',$this->getMasterKey()."='".(int)$dropId."'");
        return true;
    }

    // override helpers
    function _getLevelNoteItemData($item) {

        return $item;
    }


/////////////////////////////////////////////////////////////////////
	/**
	 * get level of categories id
	 *
	 * @param int $catID
	 */
	function getLevel ($id) {
        return $this->getNavigationPath($id);
	    //return array_reverse(array_merge($this->getPath($id), array (0)));
	}


	function getPath ($id, $path = array()) {
		$path[]= $id ;
		$parentID = $this->getParentID($id);

		if ($parentID != 0)
		$path = $this->getPath($parentID, $path);
		return $path;
	}

	function getNavigationPath ($id, $path = array()) {
		$parent = $this->getParentData($id);
		$path[]= $parent ;
		if ($parent[$this->getParentKey()] != 0)
		$path = $this->getNavigationPath($parent[$this->getParentKey()], $path);
		return $path;
	}


	/**
	 * get parent id of category
	 *
	 * @param int $catID
	 * @return int
	 */
	function getParentID ($id) {

		$data = $this->_getItemData($id);

		if (is_data($data))
		return $data[$this->getParentKey()];
		else
		return 0;
	}

	/**
	 * get parent data of category
	 *
	 * @param int $catID
	 * @return array
	 */
	function getParentData ($id) {
		$data = $this->_getItemData($id);
		if (is_array($data))
		return $data;
		else
		return false;
	}


	/**
	 * Check if category has sub categories
	 *
	 * @param int $id
	 * @return boolean
	 */
	function hasSubcategories($parent_id) {
		global $db, $xtPlugin;

		$record = $db->Execute("select count(*) as count from " . $this->getTable() . " where ".$this->getParentKey()." = '" . (int) $parent_id . "'");
		if($record->fields['count']>0){
			return true;
		} else {
			return false;
		}
	}


	function getAllPaths ($parent_id = 0) {
        $data = $this->_getLevelItems($parent_id);
        return $data;
	}
	function getAllChilds($parentId = 0) {
        $paths = $this->getAllPaths($parentId);

        $ids = $this->getLastIds();
        foreach ($ids as $id) {
            $path = array_reverse($this->getNavigationPath($id));
            $data[$id] = $path;
        }
        return $data;
	}

	function getAllChildsRecursive($parentId = 0) {
        $paths = $this->getAllPaths($parentId);

        $ids = $this->getLastIds();
        foreach ($ids as $id) {
            $path = array_reverse($this->getNavigationPath($id));
            $name = $this->getDisplayPath($path);

            $data[] = array($id, $name);
        }
        return $data;
	}

	function getDisplayPath (&$path, $separator = '-') {
            $name = '';

            foreach ($path as $item) {
                $items = $this->_getLangData($item[$this->getMasterLangKey()], $item);
                $name.= $separator.$items[$this->getDisplayKey()];
            }
            return $name;
	}
}

?>