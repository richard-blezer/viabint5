<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_clicks.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_clicks.php';

if(!isset($_SESSION['affiliate_id'])) {
	$xtLink->_redirect($xtLink->_link(array()));
}

$tpl_data = array();

$affiliate_clicks = new nd_affiliate_clicks();

$affiliate_clicks->period = $filter->_filter($_POST['a_period']);

$data = $affiliate_clicks->getClicks($_SESSION['affiliate_id']);
$tpl_data['affiliate_clicks'] = $data->split_data;

$tpl_data['periods'] = array('data' => $affiliate_clicks->buildPeriodSelector($_SESSION['affiliate_id']), 'default' => $affiliate_clicks->period);

$tpl = 'affiliate_clicks.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>