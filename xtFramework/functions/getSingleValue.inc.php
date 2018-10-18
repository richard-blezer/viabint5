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

function _getSingleValue($data){
		global $db;

		$qry = "select ". $data['value'] . " from ". $data['table'] ." where ".$data['key']." = '".$data['key_val'] ."' ".$data['key_where']."";
		$record = $db->Execute($qry);

		if($record->RecordCount() > 0){
			return $record->fields[$data['value']];
		}
}

/*Url data will be copied in order to be redirected later on*/
function saveDeletedUrl($link_id,$link_type,$store_id=''){
	global $db, $filter;
	$qry = "select master_key  from ". TABLE_SEO_URL_REDIRECT ." ORDER BY master_key desc";
	$master_key = 1;
	
	$record2 = $db->Execute("select DISTINCT master_key  from ". TABLE_SEO_URL_REDIRECT ." ORDER BY master_key desc LIMIT 0,1");
	if($record2->RecordCount() > 0){
		$master_key = $record2->fields['master_key'];
	}
	$master_key++;
	$add_to_url = ''; 
	if ($store_id!='') $add_to_url = " and store_id = '".$store_id."'";
	$qry = "select *  from ". TABLE_SEO_URL ." where link_id = '".$filter->_int($link_id)."' and link_type = '".$link_type."' ".$add_to_url;
	$record = $db->Execute($qry);

	if($record->RecordCount() > 0){
		while (!$record->EOF) {
			$insert_data = $record->fields;
			$insert_data['is_deleted'] = 1;
			$insert_data['master_key'] = $master_key;
			$db->AutoExecute(TABLE_SEO_URL_REDIRECT,$insert_data,'INSERT');
			$record->MoveNext();
			
		}
	}
	
}

/*Url data will be copied in order to be redirected later on*/
function save404Url($currnt_page,$store_id=''){
	global $db,$store_handler,$language,$filter;
	if ($currnt_page=='') return true;
	if ((strpos($currnt_page,'xtAdmin/') !== false) || (strpos($currnt_page,'xtCore/') !== false) || 
        (strpos($currnt_page,'xtFramework/') !== false) || (strpos($currnt_page,'plugins/') !== false) || 
        (strpos($currnt_page,'cronjob.php') !== false)){
        return true;
    }
	$currnt_page = $filter->_filter($currnt_page);
	$qry = "select master_key  from ". TABLE_SEO_URL_REDIRECT ." ORDER BY master_key desc";
	$master_key = 1;
	$total_count = 0;
	if ($store_id==''){
		 $store_id = $store_handler->shop_id;
	}
	
	$record2 = $db->Execute("select * from ". TABLE_SEO_URL_REDIRECT ." WHERE is_deleted = 0 and url_text = '".$currnt_page."' 
					and store_id = '".$store_id."' and language_code ='".$language->code."'  ");
	if($record2->RecordCount() > 0){
		$total_count = $record2->fields['total_count'];
		
		$insert_data['url_text'] = $currnt_page;
		$insert_data['url_md5'] = md5($currnt_page);
		$insert_data['total_count'] = $total_count+1;
		$db->AutoExecute(TABLE_SEO_URL_REDIRECT,$insert_data,'UPDATE', "url_text='".$currnt_page."' and store_id = '".$store_id."' 
		and is_deleted = 0 and language_code ='".$language->code."'");
	}
	else{
		$record2 = $db->Execute("select DISTINCT master_key  from ". TABLE_SEO_URL_REDIRECT ." ORDER BY master_key desc LIMIT 0,1");
		if($record2->RecordCount() > 0){
			$master_key = $record2->fields['master_key'];
		}
		$master_key++;
		$add_to_url = ''; 
		if ($store_id!='') $insert_data['store_id'] = $store_id;
		else $insert_data['store_id'] = $store_handler->shop_id;
		$insert_data['url_text'] = $currnt_page;
		$insert_data['url_md5'] = md5($currnt_page);
		$insert_data['is_deleted'] = 0;
		$insert_data['master_key'] = $master_key;
		$insert_data['total_count'] = $total_count+1;
		$insert_data['language_code'] = $language->code;
		$db->AutoExecute(TABLE_SEO_URL_REDIRECT,$insert_data,'INSERT');
	}	
}
?>