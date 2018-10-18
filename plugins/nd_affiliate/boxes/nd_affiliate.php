<?php
/*------------------------------------------------------------------------------
	$Id: nd_affiliate.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db;
	
if (ACTIVATE_ND_AFFILIATE_BOX == 'true' && isset($xtPlugin->active_modules['nd_affiliate'])){
	$record = $db->Execute("SELECT block_id FROM " . TABLE_CONTENT_BLOCK . " WHERE block_tag = 'nd_affiliate'");
	$block_id = $record->fields['block_id'];
		
	$record = $db->Execute("SELECT content_id FROM " . TABLE_CONTENT_TO_BLOCK . " WHERE block_id = '" . $block_id . "' ORDER BY content_id");
	$affiliateContent = array();
	
	while(!$record->EOF) {
		$affiliateContent[] = $record->fields['content_id'];
		$record->MoveNext();
	}
	
	$box_content = array();
	
	if (isset($_SESSION['affiliate_id'])) 
	{	
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_summary')), 'text' => AFFILIATE_SUMMARY);
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_account')), 'text' => AFFILIATE_ACCOUNT);
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_payment')), 'text' => AFFILIATE_PAYMENT);
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_clicks')), 'text' => AFFILIATE_CLICKS);
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_sales')), 'text' => AFFILIATE_SALES);
		
		if(defined(AFFILIATE_LEAD_EXIST)) {
			$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_leads')), 'text' => AFFILIATE_LEADS);
		}
		
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_inventory', 'params' => 'type=i')), 'text' => AFFILIATE_INVENTORY);
		//$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_contact')), 'text' => AFFILIATE_CONTACT);
		//$box_content[] = array('link' => $xtLink->_link(array('page' => 'content', 'params' => 'coID=' . $affiliateContent[2])), 'text' => AFFILIATE_FAQ);
		$box_content[] = array('link' => $xtLink->_link(array('page' => 'affiliate_account', 'paction' => 'logout')), 'text' => AFFILIATE_LOGOUT);
	} 
	else 
	{
		//$tpl_data['loggedin'] = 'false';
		//$box_content[] = array('link' => $xtLink->_link(array('page' => 'content', 'params' => 'coID=' . $affiliateContent[1])), 'text' => AFFILIATE_INFO);
		//$box_content[] = array('link' => $xtLink->_link(array('page' => 'content', 'params' => 'coID=' . $affiliateContent[0])), 'text' => AFFILIATE_TOC);
	}
	
	$tpl_data['_links'] = $box_content;
	$show_box = true;
} else {
	$show_box = false;
}
?>