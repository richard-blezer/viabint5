<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_leads.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_leads.php';

if(!isset($_SESSION['affiliate_id'])) {
	$xtLink->_redirect($xtLink->_link(array()));
}

$tpl_data = array();

$affiliate_leads = new nd_affiliate_leads();

$affiliate_leads->period = $filter->_filter($_POST['a_period']);
$affiliate_leads->level = $filter->_filter($_POST['a_level']);
$affiliate_leads->billing_status = '';

$data = $affiliate_leads->getLeads($_SESSION['affiliate_id']);
$tpl_data['affiliate_leads'] = $data->split_data;

$tpl_data['periods'] = array('data' => $affiliate_leads->buildPeriodSelector($_SESSION['affiliate_id']), 'default' => $affiliate_leads->period);
$check = $db->Execute("SELECT affiliate_id FROM " . TABLE_AFFILIATE . " WHERE affiliate_id = '" . $_SESSION['affiliate_id'] . "' AND affiliate_tiers_allowed = 1");
if($check->RecordCount() > 0) {
	$tpl_data['levels'] = array('data' => $affiliate_leads->buildLevelSelector(), 'default' => $affiliate_leads->level);
} else {
	$tpl_data['level_selector'] = array();
}

$total = $affiliate_leads->getTotal($_SESSION['affiliate_id']);
$tpl_data['total'] = sprintf(AFFILIATE_TEXT_TOTAL_LEADS, $price->_StyleFormat($total['provision']), $total['total_numbers']);

$tpl = 'affiliate_leads.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>