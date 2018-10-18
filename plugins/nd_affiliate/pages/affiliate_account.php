<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_account.php 66 2011-10-07 09:50:05Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_affiliate.php';

if(isset($_GET['page_action'])) {
	$page_action = $_GET['page_action'];
} elseif (isset($_POST['page_action'])) {
	$page_action = $_POST['page_action'];
}

$countries = new countries('true');

$tpl_data = array();

$tpl_data['affiliate_id'] = $_SESSION['affiliate_id'];

switch($page_action) {
	case 'logout':
		$affiliate = new nd_affiliate_affiliate();
		if($affiliate->logoffAffiliate()) {
			$xtLink->_redirect($xtLink->_link(array('page' => 'affiliate_account', 'paction' => 'logout_success')));
			break;
		}
	case 'logout_success':
		$info->_addInfo(AFFILIATE_TEXT_LOGOUT_SUCCESS, 'success');
		break;
	case 'login':
		$affiliate = new nd_affiliate_affiliate();
		$email = $filter->_filter($_POST['email']);
		$password = $filter->_filter($_POST['password']);
		if($affiliate->loginAffiliate($email, $password)) {
			$xtLink->_redirect($xtLink->_link(array('page'=>'affiliate_summary')));
			break;
		}
		$tpl_data['show_login_failed'] = 'true';
		break;
	case 'password_reset_action':
		$affiliate = new nd_affiliate_affiliate();
		$email = $filter->_filter($_POST['email']);
		if($affiliate->resetPassword($email)) {
			$info->_addInfo(AFFILIATE_TEXT_PASSWORD_RESET_SUCCESS, 'success');
			break;
		}
		$info->_addInfo(AFFILIATE_TEXT_PASSWORD_RESET_FAILED, 'error');
	case 'password_reset':
		$tpl_data['show_password_form'] = 'true';
		break;
	case 'register':
		$affiliate = new nd_affiliate_affiliate($_SESSION['affiliate_id']);
		$update = $affiliate->_existsAffiliate();
		if($affiliate->registerAffiliate($_POST)) {
			if($update) {
				$info->_addInfo(AFFILIATE_REGISTER_SUCCESS, 'success');
			} else {
				if(AFFILIATE_AUTO_ACTIVATE == 'true') {
					$xtLink->_redirect($xtLink->_link(array('page'=>'affiliate_summary')));
				} else {
					$info->_addInfo(AFFILIATE_REGISTER_PENDING, 'info');
					break;
				}
			}
		}
	default:
		$tpl_data['gender_data'] = array(array('id' => 'm', 'text' => TEXT_MALE), array('id' => 'f', 'text' => TEXT_FEMALE), array('id' => 'c', 'text' => TEXT_COMPANY_GENDER));
		$tpl_data['show_gender'] = _STORE_ACCOUNT_GENDER;
		$tpl_data['show_birthdate'] = _STORE_ACCOUNT_DOB;
		$tpl_data['show_suburb'] = _STORE_ACCOUNT_SUBURB;
		$tpl_data['show_company'] = _STORE_ACCOUNT_COMPANY;
		$tpl_data['show_scheck'] = AFFILIATE_USE_CHECK;
		$tpl_data['show_paypal'] = AFFILIATE_USE_PAYPAL;
		$tpl_data['show_bank'] = AFFILIATE_USE_BANK;
		$tpl_data['country_data'] = $countries->countries_list_sorted;
		$tpl_data['default_country'] = _STORE_COUNTRY;
		$record = $db->Execute("SELECT block_id FROM " . TABLE_CONTENT_BLOCK . " WHERE block_tag = 'nd_affiliate'");
		$block_id = $record->fields['block_id'];
		
		$record = $db->Execute("SELECT content_id FROM " . TABLE_CONTENT_TO_BLOCK . " WHERE block_id = '" . $block_id . "' ORDER BY content_id");
		$affiliateContent = array();
		
		while(!$record->EOF) {
			$affiliateContent[] = $record->fields['content_id'];
			$record->MoveNext();
		}
		
		if($_SESSION['affiliate_id'] > 0) {
			$tpl_data['affiliate_toc_accept'] = '';
		} else {
			$tpl_data['affiliate_toc_accept'] = sprintf(AFFILIATE_TOC_ACCEPT, '<a href="' . $xtLink->_link(array('page' => 'content', 'params' => 'coID=' . $affiliateContent[0])) . '" target="_blank">', '</a>');
		}
		
		$tpl_data['show_register_form'] = 'true';	
		$affiliate = new nd_affiliate_affiliate($_SESSION['affiliate_id']);
		if(!$affiliate->affiliateData['affiliate_country_code']) {
			$affiliate->affiliateData['affiliate_country_code'] = _STORE_COUNTRY;
		}
		$tpl_data['affiliateData'] = $affiliate->affiliateData;
		break;
}

$tpl_data['message'] = $info->info_content;

$tpl = 'affiliate_account.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>