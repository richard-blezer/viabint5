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


include 'xtCore/main.php';

if (isset($_GET['feed_id']) || isset($_GET['feed_key'])) {
	if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
		{
			echo TEXT_WRONG_SYSTEM_SECURITY_KEY; return false;
		}
			
	if(isset($_GET['feed_id']))
		$feed_id = (int)$_GET['feed_id'];
	elseif(isset($_GET['feed_key']))
		$feed_id = $_GET['feed_key'];

	if((int)strlen($feed_id) == 32) {
		$feed_id = (int)$db->GetOne("SELECT feed_id FROM " . TABLE_FEED . " WHERE feed_key='" . $feed_id . "'");
	}

	if (is_int($feed_id)) {

		include 'xtFramework/classes/class.export.php';

		$export = new export($feed_id);
		// check if user/pass is required
		if ($export->data['feed_pw_flag']=='1') {
			if ($export->data['feed_pw_user']!=$_GET['user'] || $export->data['feed_pw_pass']!=$_GET['pass']) die('- auth failed -');
		}

		// rewrite price class, rewrite currency class
		unset($customers_status);
		$customers_status = new customers_status($export->data['feed_p_customers_status']);

		if ($export->data['feed_type']=='1') {
			unset($price);
			$price = new price($customers_status->customers_status_id, $customers_status->customers_status_master,$export->data['feed_p_currency_code']);
		}
		$export->_run();
	} else {
		die ('- no id -');
	}
}

if (isset($_GET['imgProc'])) {
		if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
		{
			echo TEXT_WRONG_SYSTEM_SECURITY_KEY; return false;
		}
			
		include 'xtFramework/classes/class.ImageProcessing.php';

		$processing = new ImageProcessing();
		$processing->run_processing($_GET);

}

if (isset($_GET['ImportImages'])) {
		if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
		{
			echo TEXT_WRONG_SYSTEM_SECURITY_KEY; return false;
		}
			
		include 'xtFramework/classes/class.ImageImporting.php';

		$processing = new ImageImporting();
		$processing->run_importing($_GET);

}

if (isset($_GET['export_tlps'])) {
	include 'xtFramework/classes/class.export_tpls.php';
	include 'xtFramework/library/phpxml/xml.php';
	$exp  = new export_tpls();
	$exp->_export();
	
}

//Send order mail
if (isset($_GET['sendordermail']) && $_GET['sendordermail']==1) {
	
	$obj = new stdClass();
	if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
	{
	    $obj->success = false;
		echo json_encode($obj);
		die();
	}
	
    $sent_order_mail  = new order($_GET['order_id'],$_GET['customer_id']);
	$sent_order_mail->_sendOrderMail();
    $status = $_GET['status'];
    $comments = 'Send Order Mail';
    $customer_notified = 1;
    $show_comments = 1;
    $trigger = 'admin';
    $callback_id = 0;
	$data_array = array();
    $data_array['orders_id']=$_GET['order_id'];
    $data_array['orders_status_id']=$status;
    $data_array['customer_notified']=$customer_notified;
    $data_array['customer_show_comment']=$show_comments; 
    $data_array['comments']=$comments;
    $data_array['change_trigger']=$trigger;
    $data_array['callback_id']=$callback_id;
   
    $db->AutoExecute(TABLE_ORDERS_STATUS_HISTORY,$data_array,'INSERT');
	
	$obj->success = true;
	echo json_encode($obj);
}

// Reenable download
if (isset($_GET['reenable_download']) && $_GET['reenable_download']==1) {

	$obj = new stdClass();
	if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
	{
		$obj->success = false;
		echo json_encode($obj);
		die();
	}
	
	$orderId = $filter->_int($_GET['order_id']);
	$db->Execute(sprintf("UPDATE %s SET download_count=0 WHERE orders_id=%d", TABLE_ORDERS_PRODUCTS_MEDIA, $orderId));

	// Log action
	$insert_array = array(
			'download_action' => 2, // 2 - Downloads reenabled
			'download_count' => '',
			'orders_id' => $orderId,
			'media_id' => $mediaId,
			'attempts_left' => '',
			'file' => '',
	);
	$db->AutoExecute(TABLE_DOWNLOAD_LOG,$insert_array);
	
	$obj->success = true;
	echo json_encode($obj);
}

if (isset($_GET['seo_regenerate']) && $_GET['seo_regenerate']) {
    if (_SYSTEM_SECURITY_KEY!=$_GET['seckey'])
    {
        echo TEXT_WRONG_SYSTEM_SECURITY_KEY; return false;
    }
	$regenerate = new seo_regenerate();
	$regenerate->regenerateUrls($_GET['store_id'], $_GET['url_type'], $_GET['offset']);
}


if(defined('TABLE_CRON')) {
    $sql = 'select * from '.TABLE_CRON.' where  ((next_run_date < now()) or (next_run_date is null)) and active_status = 1 order by next_run_date limit 1';
    $arr_cron = $db->getAll($sql);
    if(count($arr_cron) > 0) {
        $xt_cron = new xt_cron();

        foreach($arr_cron as $item) {
            $xt_cron->cron_start_by_id($item['cron_id'], true);
        }
    }
}

($plugin_code = $xtPlugin->PluginCode('cronjob.php:main')) ? eval($plugin_code) : false;



?>