<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_summary.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_affiliate.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_clicks.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_inventory.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_payment.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_sales.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_leads.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_subaffiliatetree.php';

if(!isset($_SESSION['affiliate_id'])) {
	$xtLink->_redirect($xtLink->_link(array()));
}

$tpl_data = array();

$banner = new nd_affiliate_inventory();
$impressions_total = $banner->getImpressionsTotal($_SESSION['affiliate_id']);
$tpl_data['impressions_total'] = ($impressions_total>0)?$impressions_total:'n/a';

$clicks = new nd_affiliate_clicks();
$clicks_total = $clicks->getClicksTotal($_SESSION['affiliate_id']);
$tpl_data['clicks_total'] = ($clicks_total > 0)?$clicks_total:'n/a';

$sales = new nd_affiliate_sales();
$sales->billing_status = '';
$sales_total = $sales->getTotal($_SESSION['affiliate_id'], true);
$tpl_data['sales_total'] = array('total' => $price->_StyleFormat($sales_total['total']),
								 'total_numbers' => $sales_total['total_numbers'],
								 'provision' => $price->_StyleFormat($sales_total['provision']));

$affiliate_percent_tier = split(";", AFFILIATE_TIER_PERCENTAGE);
for($i=0; $i<sizeof($affiliate_percent_tier); $i++) {
	$sales->level = $i;
	$tier_sales = $sales->getTotal($_SESSION['affiliate_id'], true);
	$tpl_data['tier_sales_total'][] = array('level' => $i,
											'total' => $price->_StyleFormat($tier_sales['total']),
											'total_numbers' => $tier_sales['total_numbers'],
											'provision' => $price->_StyleFormat($tier_sales['provision']));
};

if(defined(AFFILIATE_LEAD_EXIST)) {
	$leads = new nd_affiliate_leads();
	$leads_total = $leads->getTotal($_SESSION['affiliate_id']);
	$tpl_data['leads_total'] = array('total_numbers' => $leads_total['total_numbers'],
									 'provision' => $price->_StyleFormat($leads_total['provision']));
	$tpl_data['show_leads'] = 'true';
} else {
	$tpl_data['show_leads'] = 'false';
}

if($clicks_total > 0) {
	$tpl_data['conversion'] = number_format(($sales_total['total_numbers'] / $clicks_total) * 100, 2, ',', '') . " %";
}
else {
    $tpl_data['conversion'] = "n/a";
}

$affiliate = new nd_affiliate_affiliate($_SESSION['affiliate_id']);

if($affiliate->affiliateData['affiliate_commission_percent'] < AFFILIATE_PERCENT) {
	$affiliate->affiliateData['affiliate_commission_percent'] = AFFILIATE_PERCENT;
}
$tpl_data['affiliate'] = $affiliate->affiliateData;

if($sales_total['total_numbers'] > 0) {
	$affiliate_average = $price->_StyleFormat(number_format($sales_total['total'] / $sales_total['total_numbers'], 2, ',', ''));
	$affiliate_average_percentage = number_format($sales_total['provision'] / $sales_total['total'] * 100, 2, ',', '') . ' %';
} else {
	$affiliate_average = "n/a";
	$affiliate_average_percentage = "n/a";
}
$tpl_data['average'] = $affiliate_average;
$tpl_data['average_percentage'] = $affiliate_average_percentage;

if(AFFILIATE_TIER_SHOW_SUBPARTNER == 'true' && AFFILATE_USE_TIER == 'true') {
	$subaffiliatree = new nd_affiliate_subaffiliatetree($affiliate->affiliateID);
	$tpl_data['show_subaffiliate_tree'] = 'true';
	$tpl_data['subaffiliate_tree'] = $subaffiliatree->getSubaffiliateTree();
}

$tpl = 'affiliate_summary.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>