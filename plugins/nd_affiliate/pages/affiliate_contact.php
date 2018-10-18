<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_contact.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_affiliate.php';

if(!isset($_SESSION['affiliate_id'])) {
	$xtLink->_redirect($xtLink->_link(array()));
}

$affiliate = new nd_affiliate_affiliate($_SESSION['affiliate_id']);

$tpl_data = array();

if($_POST['page_action'] == 'send') {
	$email_message = $filter->_filter($_POST['email_message']);
	
	$mail = new xtMailer('nd_affiliate_contact');
	$mail->_addReceiver($affiliate->affiliateData['affiliate_email_address'], $affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname']);
	$mail->_addReceiver(AFFILIATE_EMAIL_ADDRESS);
	$mail->_assign('name', $affiliate->affiliateData['affiliate_firstname'] . ' ' . $affiliate->affiliateData['affiliate_lastname']);
	$mail->_assign('message', $email_message);
	$mail->_sendMail();
	
	$tpl_data['email_success'] = 'true';
} else {
	$tpl_data['show_form'] = 'true';
}

$tpl = 'affiliate_contact.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>