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
ini_set("display_errors", "1");
defined('_VALID_CALL') or die('Direct Access is not allowed.');

include_once _SRV_WEBROOT.'xtFramework/classes/class.MediaGallery.php';

class ImageProcessing extends MediaImages{

	public $limit = '5';
	public $limit_lower = 0;
	public $limit_upper = 0;
	public $counter_new = 0;
	public $counter_update = 0;
	public $version = '1.0';
	public $mgID;
	public $media_class;
	public $parent_id;
	public $start_id = 0;
	
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
	
	function run_processing($data) {

		try{
			if (isset($data['limit_lower'])) {
				$this->limit_lower = (int)$data['limit_lower'];
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

			$this->_process_images();
		}
		catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
	
	function _process_images() {
		global $db,$logHandler;

		if ($this->limit_upper==0) $this->limit_upper = $this->limit;

		if($this->parent_id != 0){
			$query = "SELECT m.* FROM ".TABLE_MEDIA." m left join ".TABLE_MEDIA_TO_MEDIA_GALLERY." m2g on m.id = m2g.m_id where m2g.mg_id=? and m.type='images' and m.class=? LIMIT ".(int)$this->limit_lower.",".(int)$this->limit;
			$count = $db->Execute(
				"SELECT COUNT(*) as count FROM ".TABLE_MEDIA." m left join ".TABLE_MEDIA_TO_MEDIA_GALLERY." m2g on m.id = m2g.m_id where m2g.mg_id=? and m.type='images' and m.class=? ",
				array($this->mgID, $this->media_class)
			);
			$rs = $db->Execute($query, array($this->mgID, $this->media_class));
		}else{
			$query = "SELECT * FROM ".TABLE_MEDIA." where type='images' and class=? LIMIT ".(int)$this->limit_lower.",".(int)$this->limit;
			$count = $db->Execute(
				"SELECT COUNT(*) as count FROM ".TABLE_MEDIA." where type='images' and class=? ",
				array($this->media_class)
			);
			$rs = $db->Execute($query, array($this->media_class));
		}
	
		$this->count = $count->fields['count'];

		if ($rs->RecordCount()>0) {
			while (!$rs->EOF) {
				$this->setClass($this->media_class);
				$this->processImage($rs->fields['file'], true);
				if (!$this->response) {
					$log_data= array();
					$log_data['message'] = 'error reading processing file: '.$rs->fields['file'];
					$log_data['time'] = time();
					$logHandler->_addLog('error',__CLASS__,$this->media_class,$log_data);
					$this->LogError($log_data);
				}

				$rs->MoveNext();
			}$rs->Close();
		}
		$this->_redirecting();		
	}	
	
	function LogError($data_string){
		$write_log = implode("  ",$data_string);
		$f=fopen(_SRV_WEBROOT.'xtLogs/imageprocessing.log', 'a+');
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
			$params = 'imgProc=images&mgID='.$this->mgID.
					  '&limit_lower='.$limit_lower.
					  '&limit_upper='.$limit_upper.
					  '&start_id='.$this->start_id.
					  '&timer_start='.$this->timer_start.
					  '&seckey='.$_GET['seckey'];
				
			if (isset($this->primary)) $params.='&primary='.$this->primary;
			if (isset($this->secondary)) $params.='&secondary='.$this->secondary;
			echo $this->_displayHTML($xtLink->_link(array('default_page'=>'cronjob.php', 'params'=>$params)),$limit_lower,$limit_upper,$this->count);
		} else {
			try{
				// insert into log
				$this->_stopProcessing();
				echo $this->_htmlHeader();	
				echo '- processing finished -<br />';
				echo '- processed images '.$this->count.'<br />';
				
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