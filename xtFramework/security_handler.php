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

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.filter.php';
$filter = new filter();
$_REQUEST = $filter->_filterXSS($_REQUEST);
$_GET = $filter->_filterXSS($_GET);

if(USER_POSITION!='admin'){
	$post_filter = true;
	if (isset($_GET['page']) && isset($_GET['page_action'])) {
		if ($_GET['page']=='callback') {
			// check if plugin extists!
            $_pagename=$filter->_filter($_GET['page_action'],'pagename');
			$_file = _SRV_WEBROOT.'plugins/'.$_pagename.'/callback/class.ip_restriction.php'; 
			if (file_exists($_file)) {
				include $_file;
				$_class = $_pagename.'_ip'; 
				$_ip_restriction = new $_class;
				if ($_ip_restriction->_allowedIP()) {
					$post_filter=false;
				} else {
                    
                    $log_data = array();
                    $log_data = array();
                    $log_data['module'] = 'callback';
                    $log_data['class'] = 'error';
                    $log_data['error_msg'] = 'Callback IP Blocked';
                    $log_data['error_data'] = serialize(array('Unallowed IP'=>$_SERVER['REMOTE_ADDR'],'payment_class'=>$_pagename));
                    $log_data['transaction_id']=''; 
                    $db->AutoExecute(TABLE_CALLBACK_LOG,$log_data,'INSERT');
                    die('Access to the Service is not allowed from your IP Address');
                } 
			}
		}
		
	}
    // cao exception
    if (strstr($_SERVER['PHP_SELF'],'cronjob.php') && isset($_POST['action']) && isset($_GET['password']) && isset($_GET['user'])) $post_filter=false;
	if ($post_filter) $_POST = $filter->_filterXSS($_POST);
}

$_COOKIE = $filter->_filterXSS($_COOKIE);

?>