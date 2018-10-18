<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_sales.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_sales.php';

if(!isset($_SESSION['affiliate_id'])) {
	$xtLink->_redirect($xtLink->_link(array()));
}

$tpl_data = array();

$affiliate_sales = new nd_affiliate_sales();

$affiliate_sales->period = $filter->_filter($_POST['a_period']);
$affiliate_sales->level = $filter->_filter($_POST['a_level']);
$affiliate_sales->status = $filter->_filter($_POST['a_status']);
$affiliate_sales->billing_status = '';

$data = $affiliate_sales->getSales($_SESSION['affiliate_id']);
$tpl_data['affiliate_sales'] = $data->split_data;

$tpl_data['periods'] = array('data' => $affiliate_sales->buildPeriodSelector($_SESSION['affiliate_id']), 'default' => $affiliate_sales->period);
$tpl_data['status'] = array('data' => $affiliate_sales->buildStatusSelector(), 'default' => $affiliate_sales->status);
$check = $db->Execute("SELECT affiliate_id FROM " . TABLE_AFFILIATE . " WHERE affiliate_id = '" . $_SESSION['affiliate_id'] . "' AND affiliate_tiers_allowed = 1");
if($check->RecordCount() > 0) {
	$tpl_data['levels'] = array('data' => $affiliate_sales->buildLevelSelector(), 'default' => $affiliate_sales->level);
} else {
	$tpl_data['level_selector'] = array();
}

$total = $affiliate_sales->getTotal($_SESSION['affiliate_id']);
$tpl_data['total'] = sprintf(AFFILIATE_TEXT_TOTAL_SALES, $price->_StyleFormat($total['provision']), $total['total_numbers']);

$tpl = 'affiliate_sales.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>