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

$payment = new payment();
$payment->_payment();
$data_array = $payment->_getPossiblePayment();
$show_page = false;
foreach($data_array as $k=>$v){
    if($v['payment_code']=='xt_banktransfer' && $v['status'] = 1){
        $show_page = true;
    }
}

if(!$show_page){
    $tmp_link  = $xtLink->_link(array('page'=>'customer', 'conn'=>'SSL'));
    $xtLink->_redirect($tmp_link);
}

require_once(_SRV_WEBROOT._SRV_WEB_PLUGINS.'/xt_banktransfer/classes/class.xt_banktransfer.php');
$xt_banktransfer = new xt_banktransfer();


$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer', 'conn'=>'SSL')),TEXT_PAGE_TITLE_ACCOUNT);

if(isset($page->page_action) && $page->page_action != ''){

  switch ($page->page_action) {

    case 'overview' :

    if(!$_SESSION['registered_customer'])
      $xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

      $account_data = $xt_banktransfer->getAccountList($_SESSION['registered_customer']);

      if (count($account_data['data'])==0) {
        $info->_addInfo(TEXT_BANKTRANSFER_NO_ACCOUNTS,'warning');
      }

      $account_tpl_data =  array();
      $account_tpl_data = array('account_data'=> $account_data,'account_count'=>count($account_data['data']));

      $tpl_data = array('message'=>$info->info_content);
      $tpl_data = array_merge($tpl_data, $account_tpl_data);
      $tpl = 'bank_account_overview.html';

      $brotkrumen->_addItem($xtLink->_link(array('page'=>'bank_account', 'paction'=>'overview','conn'=>'SSL')),TEXT_XT_BANKTRANSFER_ACCOUNTS);

      $template = new Template();
      $template->getTemplatePath($tpl, 'xt_banktransfer', '', 'plugin');
      $page_data = $template->getTemplate('xt_banktransfer_account_smarty', $tpl, $tpl_data);
    break;

    case 'edit_account' :

      $account_tpl_data = array();

      if($_GET['acID']){
        $account_tpl_data = $xt_banktransfer->getAccountData((int)$_GET['acID'], $_SESSION['registered_customer']);
        $account_tpl_data['account_id'] = (int) $_GET['acID'];
      }

      if (isset ($_POST['action']) && $_POST['action']=='edit_bank_account'){
        $data_array = $_POST;
        $data_array['customer_id'] = $_SESSION['registered_customer'];

        $account_tpl_data = $xt_banktransfer->setAccountData($data_array);
      }

      if($account_tpl_data['success']==true){
        $tmp_link  = $xtLink->_link(array('page'=>'bank_account', 'paction'=>'overview'));
        $xtLink->_redirect($tmp_link);
      }

      $tpl_data = array();
      $tpl_data = array('message'=>$info->info_content);
      $tpl_data = array_merge($tpl_data, $account_tpl_data);
      $tpl = 'bank_account_edit.html';

      $brotkrumen->_addItem($xtLink->_link(array('page'=>'bank_account', 'paction'=>'edit_account','conn'=>'SSL')),TEXT_XT_BANKTRANSFER_ACCOUNTS);

      $template = new Template();
      $template->getTemplatePath($tpl, 'xt_banktransfer', '', 'plugin');
      $page_data = $template->getTemplate('xt_banktransfer_account_edit_smarty', $tpl, $tpl_data);
    break;

     case 'delete_account' :

      if($_GET['acID']){
        $xt_banktransfer->_deleteBankAccount((int)$_GET['acID'], $_SESSION['registered_customer']);
      }
      $tmp_link  = $xtLink->_link(array('page'=>'bank_account', 'paction'=>'overview'));
      $xtLink->_redirect($tmp_link);
    break;
  }
}
?>