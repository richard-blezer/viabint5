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

include_once _SRV_WEBROOT . 'xtFramework/admin/classes/class.adminDB_DataSave.php';

class csv_api {

	var $limit = '1000';
	var $limit_lower = 0;
	var $limit_upper = 0;
	var $delimiter = '|';
	var $counter_new = 0;
	var $counter_update = 0;
	var $version = '1.0';
	var $_table_log = TABLE_EXPORTIMPORT_LOG;
    var $CAT_TREE = array();

	function csv_api() {}
	
	function clearLog() {
		global $db;
		$db->Execute("DELETE FROM ".$this->_table_log." WHERE ei_id='".$this->id."'");
	}
	
	function addLog($message) {
		global $db;
		$data = array();
		$data['ei_id']=$this->id;
		$data['error_message']=$message;
		$db->AutoExecute($this->_table_log,$data);
	}
	
	
	/**
	 * check if login credentials matching db
	 *
	 * @param unknown_type $data
	 */
	function _checkCredentials($data) {
		
		if ($this->_recordData['ei_username']!='') {
			if ($this->_recordData['ei_username']!=$data['user']) die ('- wrong login -');
		}
		
		if ($this->_recordData['ei_password']!='') {
			if ($this->_recordData['ei_password']!=$data['password']) die ('- wrong login -');
		}
		
	}

	/**
	 * get fieldnames of given table
	 *
	 * @param string $table
	 * @return array
	 */
	function readTabelFields ($table) {
		global $db;

		$query = "SHOW FIELDS FROM ".$table." ";
		$record = $db->Execute($query);
		$records = array();
		if ($record->RecordCount() > 0) {
			while(!$record->EOF){
				$records[$record->fields['Field']] = $record->fields;
				$record->MoveNext();
			} $record->Close();
		}
		return $records;
	}

	function _checkKeyImport($array,$type='primary') {
		if ($type=='primary') {
			if (!in_array($this->primary,$array)) die (' primary <b>'.$this->primary.'</b> not allowed');
		}
		if ($type=='secondary') {
			if (!in_array($this->secondary,$array)) die (' secondary <b>'.$this->secondary.'</b> not allowed');
		}
	}

	function _checkKeyExport($array,$type='primary') {
		if ($type=='primary') {
			if (!in_array($this->primary,$array)) die (' primary <b>'.$this->primary.'</b> not allowed');
		}
		if ($type=='secondary') {
			if (!in_array($this->secondary,$array)) die (' secondary <b>'.$this->secondary.'</b> not allowed');
		}
	}

	function _filterInput(&$data) {
		foreach ($data as $key => $val) {
				$data[$key] = addslashes(trim($val));
		}
	}

	function str_getcsv ($data, $delim, $encl) {
		$fp = fopen('php://temp/', 'r+');
		fputs ($fp, $data);
		rewind ($fp);
		$d = fgetcsv  ($fp, 0, $delim, $encl);
		fclose ($fp);
		return $d;
	} // str_getcsv()
	/**
	 * parse data of specific line, return trim array
	 *
	 * @param int $line
	 * @return array
	 */
	function _parseLineData($line) {
		// das muss zwangsla?fig zu problemen f?hren ....
		// $data = explode($this->delimiter, $this->filedata[$line]);
		/* php 5.3 and above use:
			$data = str_getcsv ($this->filedata[$line], $this->delimiter);
		 */
		/* workaround */
		$data = $this->str_getcsv ($this->filedata[$line], $this->delimiter, $this->enclosure);

		$count = count($data);
		$line_data = array();
		for ($i = 0; $i < $count; $i++) {
			$line_data[$this->mapping[$i]] = trim(str_replace(array('\n', '\r', '\n\r'), '', $data[$i]),'"');
		}
		return $line_data;
	}
	
	/**
	 * query for export/import dataset
	 *
	 * @param int $id
	 */
	function getDetails($id) {
		global $db,$filter;
		
		//$id = (int)$id;
		//if (!is_int($id)) return false;
		
		$sql = "SELECT * FROM ".TABLE_EXPORTIMPORT." WHERE ei_id = '".$filter->_charNum($id)."'";
		$rs = $db->Execute($sql);
		if ($rs->RecordCount()!=1) return false;
		
		if ($this->_recordData['ei_delimiter']!='') $this->delimiter=$this->_recordData['ei_delimiter'];
		
		$this->_recordData = $rs->fields;
		return true;

	}
	
	/**
	 * set starttime in log
	 *
	 * @param unknown_type $id
	 */
	function _startExport($id) {
		global $logHandler;
		
		$log_data= array();
		$log_data['message'] = 'start';
		$log_data['time'] = time();
		$logHandler->_addLog('success','xt_im_export',$id,$log_data);
		
	}
	
	/**
	 * set endtime in log
	 *
	 * @param unknown_type $id
	 */
	function _stopExport($id) {
		global $logHandler;
		$log_data= array();
		$log_data['message'] = 'stop';
		$log_data['time'] = time();
		$logHandler->_addLog('success','xt_im_export',$id,$log_data);
	}
	
	function showLog($id) {
		global $logHandler;
		$logHandler->showLog('xt_im_export', $id);
		
	}

    function _insertCategory($category_tree,$products_id) {
        global $db;

        // tree node allready known?
        if (isset($this->CAT_TREE[$category_tree])) {
            $this->_insertProd2Cat($this->CAT_TREE[$category_tree],$products_id);
        } else {
            // split tree
            $tree = explode($this->cat_tree_delimiter,$category_tree);

            $cat = $this->processTree($tree,'de');
            if (!isset($this->CAT_TREE[$category_tree]))$this->CAT_TREE[$category_tree]=$cat;
            $this->_insertProd2Cat($cat,$products_id);
        }



    }

    private function processTree($tree,$language='de') {
        global $db,$_language_list;

        $parent_id = 0;

        foreach ($tree as $id => $name) {

            // check if category exists
            $qry = "SELECT c.categories_id FROM ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd WHERE cd.categories_name='".addslashes($name)."' and cd.language_code='".$language."' and cd.categories_id=c.categories_id and parent_id='".$parent_id."' LIMIT 0,1";

            $rs = $db->Execute($qry);
            if ($rs->RecordCount()==0) {

                $categorie_data = array();
                $db->Execute("INSERT INTO " . TABLE_CATEGORIES . " (parent_id) VALUES ('" . $parent_id . "')");
                $categorie_data['categories_id'] = $db->Insert_ID();

                // add category
                $categorie_data['parent_id']=$parent_id;
                $categorie_data['categories_status']='1';
                foreach ($this->_language_list as $key => $val) {
                    $categorie_data['categories_name_'.$val['code']]=$name;
                }

                $category = new category;
                $obj = new stdClass;
                $obj=$category->_set($categorie_data);
                $parent_id=$categorie_data['categories_id'];

            } else {
                $parent_id=$rs->fields['categories_id'];
            }

        }
        return $parent_id;

    }

    private function SearchCategory($name,$parent_id) {



    }

    private function _insertProd2Cat($categories_id,$products_id) {
        global $db;

        $db->Execute("DELETE FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$products_id."'");
        $cat_data = array();
        $cat_data['categories_id']=$categories_id;
        $cat_data['products_id']=$products_id;
        $cat_data['master_link']='1';
        $db->AutoExecute(TABLE_PRODUCTS_TO_CATEGORIES,$cat_data);

    }
	
	function _clearLog($id) {
		global $logHandler;
		$logHandler->clearLogMessages('xt_im_export', $id);
	}

	function _displayHTML($next_target,$lower=1,$upper=0,$total=0) {

		$process = $lower / $total * 100;
		if ($process>100) $process=100;
		
		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="refresh" content="5; URL='.$next_target.'" />
<title>..import / export..</title>
<style type="text/css">
<!--
.process_rating_light .process_rating_dark {
background:#FF0000;
height:15px;
position:relative;
}

.process_rating_light {
height:15px;
margin-right:5px;
position:relative;
width:150px;
border:1px solid;
}

-->
</style>
</head>
<body>
<div class="process_rating_light"><div class="process_rating_dark" style="width:'.$process.'%">'.round($process,0).'%</div></div>
Processing '.$lower.' to '.$upper.' of total '.$total.'
</body>
</html>';
		return $html;

	}
	
	
	function _htmlHeader() {
		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>..import / export..</title>
<style type="text/css">
<!--
ul.stack {padding:5px}
ul.stack li {}
ul.stack li.success {list-style:none; padding:5px 0px 2px 20px; background-image:url(xtAdmin/images/icons/accept.png); background-repeat:no-repeat; background-position:0px 4px;}
ul.stack li.error {list-style:none; padding:5px 0px 2px 20px; background-image:url(xtAdmin/images/icons/cross.png); background-repeat:no-repeat; background-position:0px 4px;}
-->
</style>
</head>
<body>';
		return $html;
	}
	
	function _htmlFooter() {
		$html ='</body></html>';
		return $html;
	}
	

}

?>