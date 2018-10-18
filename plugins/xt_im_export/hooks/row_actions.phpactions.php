<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($_GET['type']=='api_csv_export') {
		if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
		{
			die(TEXT_WRONG_SYSTEM_SECURITY_KEY);

		}
		$id = $_GET['id'];
		require_once _SRV_WEBROOT.'plugins/xt_im_export/classes/class.csvapi.php';
		require_once _SRV_WEBROOT.'plugins/xt_im_export/classes/class.export.php';
		$csv_export = new csv_export();
		$csv_export->getDetails($id);
		if ($csv_export->_recordData['ei_type']=='export') {
			$params = 'api=csv_export&id='.$id.'&seckey='.$_GET['seckey'];
		} else {
			$params = 'api=csv_import&id='.$id.'&seckey='.$_GET['seckey'];
		}

		$iframe_target = $xtLink->_adminlink(array('default_page'=>'cronjob.php','conn'=>'SSL', 'params'=>$params));
		echo '<iframe src="'.$iframe_target.'" frameborder="0" width="100%" height="500"></iframe>';
	}

?>