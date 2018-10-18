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
 # @version $Id: class.ImageProcessing.php 6508 2013-10-09 10:51:20Z silviyap $
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
ini_set("display_errors", "1");
defined('_VALID_CALL') or die('Direct Access is not allowed.');

include _SRV_WEBROOT.'xtFramework/classes/class.MediaGallery.php';
include_once _SRV_WEBROOT.'xtFramework/admin/classes/class.adminDB_DataSave.php';

class ImageImporting extends MediaImages{

	public $limit = '10';
	public $limit_lower = 0;
	public $limit_upper = 0;
	public $counter_new = 0;
	public $counter_update = 0;
	public $version = '1.0';
	public $mgID;
	public $media_class;
	public $parent_id;
	public $start_id = 0;
	public $url_data= array();
	public $data_arr = array();
	public $imported=0;
	public $importer_type='';
	
    function __construct() {
		parent::__construct();
    }

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language;

		$params = array();

		return $params;
	}	
	
	function run_importing($data) {

		try{	
			if (isset($data['limit_lower'])) {
				$this->limit_lower = (int)$data['limit_lower'];
			} 
			if (isset($data['imported'])) {
				$this->imported = (int)$data['imported'];
			} 

			if (isset($data['limit_upper'])) {
				$this->limit_upper = (int)$data['limit_upper'];
			}
			
			if (isset($data['start_id'])) {
				$this->start_id = (int)$data['start_id'];
			}

			if (isset($data['primary'])) $this->primary = $data['primary'];
			if (isset($data['secondary'])) $this->secondary = $data['secondary'];

			$this->mgID = $data['mgID'];
			$mg = new MediaGallery();
			$this->media_class = $mg->_getParentClass($data['mgID']);
			$this->parent_id = $mg->_getParentID($data['mgID']);
			
			if (!$this->limit_lower) {
				$this->_startProcessing();
			}
			if (($_GET['currentType']=='files_free')||($_GET['currentType']=='files_order')) $this->importer_type='files';
			else $this->importer_type= $this->type;
			$this->readAllfiles2();
			if ($this->importer_type=='files') $this->_process_files();
			else $this->_process_images();	
		}
		catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
	
	function readAllfiles2()
	{
		$this->url_data['galType'] = $_GET['galType'];
		$this->url_data['mgID'] = $_GET['mgID'];
		$this->url_data['currentType'] = $_GET['currentType'];
		$cust_path ='';
		if ($this->importer_type=='files')
			$cust_path =_SRV_WEB_MEDIA_FILES;

		$this->setUrlData($this->url_data);
			
		$this->data_arr = $this->readDir($cust_path,$this->importer_type);
		$this->count = count($this->data_arr["images"]);
	}
	
	function _process_images() {
		global $db,$logHandler;
		
		if ($this->limit_upper==0) {
			$this->limit_upper = $this->limit;
		} else {
			$this->limit_upper = (int)$this->limit_upper + (int)$this->limit;
		}
		
		if ($this->limit_upper>$this->count)
			$this->limit_upper=$this->count;
		
		$i=0;
	    if (is_array($this->data_arr['images'])) {
			$im_arr = $this->data_arr['images'];
			$ind = $this->limit_lower;

			for($key=$this->limit_lower; $key<$this->limit_upper; $key++) {
				$imageData = $im_arr[$key];
					
				$record = $db->Execute(
					"SELECT id FROM ".$this->_table_media." where file=? and type = ? ",
					array($imageData['name'], $this->type)
				);
				if ($record->RecordCount() == 0) {
					$md = new MediaData;

					$this->processImage($imageData['name']);
					if (!$this->response) {
						$log_data= array();
							$log_data['message'] = 'error reading processing file: '.$imageData['name'];
							$log_data['time'] = time();
							$logHandler->_addLog('error',__CLASS__,$this->media_class,$log_data);
							$this->LogError($log_data);
						} else {
							$i++;
							$md->setMediaData(array('file' => $imageData['name'], 'type' => $this->type, 'class' => $this->url_data['currentType'], 'mgID'=>$this->url_data['mgID']));
						}
					}
				}
	    }
	   
		$this->imported =(int)$this->imported+$i;
		$this->_redirecting();	
	}	

	function _process_files() {
		global $db,$logHandler;
		$type='files';
		if ($this->limit_upper==0) $this->limit_upper = $this->limit;
		else $this->limit_upper = (int)$this->limit_upper + (int)$this->limit;
		
		if ($this->limit_upper>$this->count) $this->limit_upper=$this->count;
		
		$i=0;
	    if (is_array($this->data_arr['images'])) {
			$im_arr = $this->data_arr['images'];
			$ind = $this->limit_lower;

			for($key=$this->limit_lower; $key<$this->limit_upper; $key++) {
				$imageData = $im_arr[$key];
					
				$record = $db->Execute("SELECT id FROM ".$this->_table_media." where file='".$imageData['name']."' and type='".$type."'");

				if ($record->RecordCount() == 0) {
					$md = new MediaData;
					$i++;
					$md->setMediaData(array('file' => $imageData['name'], 'type' => $type, 'class' => $this->url_data['currentType'], 'mgID'=>$this->url_data['mgID']));
				}
			}
	    }
	   
		$this->imported =(int)$this->imported+$i;
		$this->_redirecting();	
	}	
	
	function LogError($data_string){
		$write_log = implode("  ",$data_string);
		$f=fopen(_SRV_WEBROOT.'xtLogs/imageimporting.log', 'a+');
		fwrite($f, $write_log.' '.date("Y-m-d H:i:s")."\n");
		fclose($f);
	}
	
	function _redirecting() {
		global $xtLink;
		
		if ($this->limit_upper<$this->count) {
			// redirect to next step
			global $xtLink;
			$limit_lower =$this->limit_upper;
			$limit_upper =$this->limit_upper+$this->limit;
			$params = 'ImportImages=1&currentType='.$_GET['currentType'].'&mgID='.$this->mgID.
					  '&limit_lower='.$limit_lower.
					  '&limit_upper='.$limit_upper.
					  '&start_id='.$this->start_id.
					  '&imported='.$this->imported.
					  '&timer_start='.$this->timer_start.
					  '&seckey='.$_GET['seckey'];
				
			if (isset($this->primary)) $params.='&primary='.$this->primary;
			if (isset($this->secondary)) $params.='&secondary='.$this->secondary;
			
			echo $this->_displayHTML($xtLink->_link(array('default_page'=>'cronjob.php', 'params'=>$params)),$limit_lower,$limit_upper,$this->count,$this->imported,$this->start_id);
		} 
		else {
				
			try{
				// insert into log
				$this->_stopProcessing();
				echo $this->_htmlHeader();	
				echo '- importing finished -<br />';
				if ($this->importer_type=='files') echo '- imported files '.$this->imported.'<br />';
				else echo '- imported images '.$this->imported.'<br />';
				
				$this->showLog($this->media_class);
				echo $this->_htmlFooter();
				$this->_clearLog($this->media_class);	
			}
			catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}
	}

	
	/**
	 * set starttime in log
	 *
	 * @param unknown_type $id
	 */
	function _startProcessing() {
		global $db,$logHandler;
		
		$log_data= array();
		$log_data['message'] = 'start';
		$log_data['time'] = time();
		$log_id = $logHandler->_addLog('success',__CLASS__,$this->media_class,$log_data);
		
		$this->start_id = $log_id;
	}
	
	/**
	 * set endtime in log
	 *
	 * @param unknown_type $id
	 */
	function _stopProcessing() {
		global $logHandler;
		$log_data= array();
		$log_data['message'] = 'stop';
		$log_data['time'] = time();
		$logHandler->_addLog('success',__CLASS__,$this->media_class,$log_data);
	}
	
	function showLog() {
		global $logHandler;
		$logHandler->showLog(__CLASS__, $this->media_class, " AND log_id >= '".(int)$this->start_id."'", '', 'ORDER BY log_id ASC');
	}
	
	function _clearLog() {
		global $db;
		// $db->Execute("DELETE FROM ".TABLE_SYSTEM_LOG." WHERE module='".__CLASS__."' and identification='".$this->class."' and class='success'"); // silvia - 09.10.2013
	}

	function _displayHTML($next_target,$lower=1,$upper=0,$total=0) {

		$process = $lower / $total * 100;
		if ($process>100) $process=100;
		
		$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="refresh" content="0; URL='.$next_target.'" />
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