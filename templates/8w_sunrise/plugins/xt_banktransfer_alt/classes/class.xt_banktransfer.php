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

class xt_banktransfer{

  var $data=array();

  function xt_banktransfer(){

    if(is_data($_SESSION['xt_banktransfer_data'])){
      $this->data['payment_info'] = $this->build_payment_info($_SESSION['xt_banktransfer_data']);

      $tmp_data = $_SESSION['xt_banktransfer_data'];

      while (list ($key, $value) = each($tmp_data)) {
        $this->data[$key] = $value;
      }
    }

    $this->data['account_list'] = $this->getAccountList_data($_SESSION['registered_customer']);
  }

  function build_payment_info($data){

    $tmp_data = $data;

    // Keine Konstante im Checkout.
    unset($tmp_data['customer_id']);
    unset($tmp_data['banktransfer_save']);
    unset($tmp_data['banktransfer_country_code']);
    unset($tmp_data['banktransfer_amount']);
    unset($tmp_data['banktransfer_trxamount']);
    unset($tmp_data['banktransfer_currency']);

    // Keine Konstante Konto bearbeiten.
    unset($tmp_data['action']);
    unset($tmp_data['account_id']);
    unset($tmp_data['x']);
    unset($tmp_data['y']);
    $new_data='';
    while (list ($key, $value) = each($tmp_data)) {
      $text = constant('TEXT_'.strtoupper($key));
      $new_data .= $text.': '.$value.'<br />';
    }

    return $new_data;

  }

  function write_order_data($oID, $data){
    global $db, $xtPlugin;

    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:write_order_data_top')) ? eval($plugin_code) : false;
    if(isset($plugin_return_value))
    return $plugin_return_value;

    $payment_info_array = array('customer_id' => $_SESSION['customer']->customers_id,
                  'banktransfer_owner'=> $data['banktransfer_owner'],
                  'banktransfer_bank_name'=> $data['banktransfer_bank_name'],
                  'banktransfer_iban'=> $data['banktransfer_iban'],
                  'banktransfer_bic'=> $data['banktransfer_bic']);


    $order_record = $db->Execute("SELECT orders_data FROM " . TABLE_ORDERS . " WHERE orders_id = ? ", array((int) $oID));
    $old_order_data = unserialize($order_record->fields['orders_data']);

    if(is_array($old_order_data)){
      $payment_info = array_merge($old_order_data, $payment_info_array);
    }else{
      $payment_info = $payment_info_array;
    }

    $payment_info = serialize($payment_info);

    $data_array = array('orders_data' => $payment_info);
    $update_record = array('last_modified'=>$db->BindTimeStamp(time()));
    $record = array_merge($update_record, $data_array);

    $db->AutoExecute(TABLE_ORDERS, $record, 'UPDATE', "orders_id=". (int) $oID."");

    if($data['banktransfer_save']==true){

      $query = "SELECT * FROM " . TABLE_XT_BANKTRANSFER . " WHERE banktransfer_iban =? ";
      $check_record = $db->Execute($query,array($data['banktransfer_iban']));
      if($check_record->RecordCount() == 0){
        $db->AutoExecute(TABLE_XT_BANKTRANSFER, $payment_info_array, 'INSERT');
      }
    }

    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:write_order_data_bottom')) ? eval($plugin_code) : false;

    unset($_SESSION['xt_banktransfer_data']);

  }

    /**
     * generate PDF Mandat
     *
     * @param $oID
     * @return bool|string
     */
    public function getMandate($oID) {
      global $db;

      $orders_id=(int)$oID;


      $rs = $db->Execute("SELECT * FROM ".TABLE_ORDERS." WHERE payment_code='xt_banktransfer' and orders_id=? ",array((int)$orders_id));
      if ($rs->RecordCount()!=1) return false;

      $orders_data = unserialize($rs->fields['orders_data']);

      // check if there is allready a mandate for this bankaccount
      $ck = $db->Execute("SELECT sepa_mandat FROM ".TABLE_XT_BANKTRANSFER." 
                           WHERE banktransfer_iban LIKE ? and 
                           customer_id=? ",
                           array('%'.$orders_data['banktransfer_iban'].'%', (int) $rs->fields['customers_id']));
       if ($ck->fields['sepa_mandat']=='1') return false;


      $data=array();
      $data['iban']=$orders_data['banktransfer_iban'];
      $data['bic']=$orders_data['banktransfer_bic'];
      $data['bankname']=$orders_data['banktransfer_bank_name'];
      $data['orders_id']=$orders_id;
      $data['name']=$orders_data['banktransfer_owner'];
      $data['adresse']=$rs->fields['billing_street_address'].' ,'.$rs->fields['billing_postcode'].' '.$rs->fields['billing_city'];

      $this->customers_status = $rs->fields['customers_status'];
      $this->shop_id =$rs->fields['shop_id'];

      // shop related informations
      $data['merchant_id']=$db->GetOne("SELECT config_value FROM ".TABLE_CONFIGURATION_PAYMENT." WHERE shop_id=? and config_key='XT_BANKTRANSFER_SEPA_ID'", array((int)$rs->fields['shop_id']));
      $data['company_name']=$db->GetOne("SELECT config_value FROM ".TABLE_CONFIGURATION_PAYMENT." WHERE shop_id=? and config_key='XT_BANKTRANSFER_COMPANY_NAME'", array((int)$rs->fields['shop_id']));
      $data['company_address']=$db->GetOne("SELECT config_value FROM ".TABLE_CONFIGURATION_PAYMENT." WHERE shop_id=? and config_key='XT_BANKTRANSFER_COMPANY_ADDRESS'", array((int)$rs->fields['shop_id']));


      $smarty = new Smarty();
      $smarty->compile_dir = _SRV_WEBROOT . 'templates_c';
      $smarty->register_resource("db", array($this, 'db_get_template', 'db_get_timestamp', 'db_get_secure', 'db_get_trusted'));
      $smarty->assign('data', $data);


      $html = $smarty->fetch('db:' . $orders_id . '_' . $rs->fields['language_code']);

      require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/dompdf/dompdf_config.inc.php';
      $dompdf = new DOMPDF();
      $dompdf->load_html($html);
      $dompdf->render();

      return $dompdf->output();

  }

  function getAccountList($cID=0, $limit=5){
    global $xtPlugin, $db, $xtLink;

    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:getAccountList_top')) ? eval($plugin_code) : false;
    if(isset($plugin_return_value))
    return $plugin_return_value;

    if($cID == 0){
      $cID = $_SESSION['customer']->customer_id;
    }

    $query = "SELECT * FROM " . TABLE_XT_BANKTRANSFER . " WHERE customer_id = '" . (int)$cID . "'";

    $pages = new split_page($query, $limit, $xtLink->_getParams(array ('next_page', 'info')));

    $navigation_count = $pages->split_data['count'];
    $navigation_pages = $pages->split_data['pages'];

    $data_array = array('data'=>$pages->split_data['data'], 'count'=>$navigation_count, 'pages'=>$navigation_pages);
    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:getAccountList_bottom')) ? eval($plugin_code) : false;
    return $data_array;

  }

  function getAccountList_data($cID=0){
    global $xtPlugin, $db, $xtLink;

    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:getAccountList_top')) ? eval($plugin_code) : false;
    if(isset($plugin_return_value))
    return $plugin_return_value;

    if($cID == 0){
      $cID = $_SESSION['customer']->customer_id;
    }

    $data[] = array('id'=>0, 'text'=>TEXT_NONE);

    $query = "SELECT * FROM " . TABLE_XT_BANKTRANSFER . " WHERE customer_id =? ";

    $record = $db->Execute($query, array((int)$cID));
    if($record->RecordCount() > 0){
      while(!$record->EOF){

        $record->fields['id'] = $record->fields['account_id'];
        $record->fields['text'] = $record->fields['banktransfer_owner'].' '.$record->fields['banktransfer_bank_name'].' ('.$record->fields['banktransfer_blz'].' '.$record->fields['banktransfer_number'] .')';

        $data[] = $record->fields;
        $record->MoveNext();
      }$record->Close();
      return $data;
    }else{
      return false;
    }

  }

  /**
   * get single account data
   *
   * @param int $acID account id
   * @param int $cID customers id
   * @return array
   */
  function getAccountData($acID, $cID=0){
    global $db;

    if($cID == 0){
      $cID = $_SESSION['customer']->customer_id;
    }

    $query = "SELECT * FROM " . TABLE_XT_BANKTRANSFER . " WHERE account_id = ? and customer_id =? ";

    $record = $db->Execute($query, array((int) $acID,(int)$cID));
    if($record->RecordCount() > 0){
      return $record->fields;
    }else{
      return false;
    }

  }

  /**
   * validate input and save if valid input
   *
   * @param array $data
   * @return array
   */
  function setAccountData($data){
    global $db, $info;

    $error =  false;

    $banktransferValidationReturnValue = $this->_banktransferValidation($data);
    $data = $banktransferValidationReturnValue['data'];
    $error_data = $banktransferValidationReturnValue['error'];

    if (count($error_data) > 0)
    $error = true;

    if($error==true)
    return $data;

    $payment_info_array = array('customer_id' => $data['customer_id'],
                  'banktransfer_owner'=> $data['banktransfer_owner'],
                  'banktransfer_bank_name'=> $data['banktransfer_bank_name'],
                  'banktransfer_iban'=> $data['banktransfer_iban'],
                  'banktransfer_bic'=> $data['banktransfer_bic']);

    if($data['account_id']){

        // delete mandat
        $payment_info_array['sepa_mandat']='0';
        $db->AutoExecute(TABLE_XT_BANKTRANSFER, $payment_info_array, 'UPDATE', "account_id=". (int) $data['account_id']." and customer_id=".(int)$data['customer_id']."");
    }else{
      $db->AutoExecute(TABLE_XT_BANKTRANSFER, $payment_info_array, 'INSERT');
    }

    $data['success'] = true;
    return $data;

  }

  /**
   * delete bankaccount
   *
   * @param int $account_id
   * @param int $customer_id
   */
  function _deleteBankAccount($account_id, $customer_id){
    global $xtPlugin, $db;

    $db->Execute("DELETE FROM ".TABLE_XT_BANKTRANSFER." WHERE account_id =? and customer_id =? ",array((int) $account_id,(int) $customer_id));
  }

    /**
     * IBAN checksum validation
     *
     * @param $iban
     * @return bool
     */
    private function validateIBAN($iban) {

      $iban = str_replace(' ', '', strtoupper($iban));
      // check format
      if (preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
          $account = substr($iban, 4);
          $str = $account . substr($iban, 0, 4);

          $search = range('A','Z');
          foreach (range(10,35) as $tmp)
              $replace[]=strval($tmp);
          $checkstr=str_replace($search, $replace, $str);

          $checksum = intval(substr($checkstr, 0, 1));
          for ($pos = 1; $pos < strlen($checkstr); $pos++) {
              $checksum *= 10;
              $checksum += intval(substr($checkstr, $pos, 1));
              $checksum %= 97;
          }

          if ($checksum!='1') {
              return false;
          } else {
              return true;
          }

      } else {
          return false;
      }

      }

  /**
   * validation function for banktransfer data
   *
   * @param array $data
   */
  function _banktransferValidation($data) {
    global $xtPlugin, $info;

    $dd_values = $this->_get_dd_values();
    $data['banktransfer_country_code']  = $dd_values['customers_country_code'];
    $data['banktransfer_amount']        = $dd_values['cart_amount'];
    $data['banktransfer_trxamount']     = $dd_values['cart_trxamount'];
    $data['banktransfer_currency']      = $dd_values['cart_currency'];


    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:_banktransferValidation_top')) ? eval($plugin_code) : false;
    if(isset($plugin_return_value))
    return $plugin_return_value;

    $error = array();
    $banktransferValidationReturnValue = array();

    // use simple default validation
    if (strlen($data['banktransfer_iban']) < 10 or !isset($data['banktransfer_iban'])) {
      $error['banktransfer_iban'] = 'true';
      $info->_addInfo(ERROR_CHECK_IBAN);
    } else {
        //validate iban
        if (!$this->validateIBAN($data['banktransfer_iban'])) {
            $error['banktransfer_iban'] = 'true';
            $info->_addInfo(ERROR_CHECK_IBAN);
        }
    }

    if (strlen($data['banktransfer_bic']) < 8 or !isset($data['banktransfer_bic'])) {
      $error['banktransfer_bic'] = 'true';
      $info->_addInfo(ERROR_CHECK_BIC);
    }

    $check_bank = $this->_check_empty($data['banktransfer_bank_name']);
    if ($check_bank=='true') {
      $error['banktransfer_bank_name'] = 'true';
      $info->_addInfo(ERROR_CHECK_BANK);
    }

    $check_owner = $this->_check_empty($data['banktransfer_owner']);
    if ($check_owner == 'true') {
      $error['banktransfer_owner'] = 'true';
      $info->_addInfo(ERROR_CHECK_OWNER);
    }

    $banktransferValidationReturnValue['data']  = $data;
    $banktransferValidationReturnValue['error'] = $error;

    ($plugin_code = $xtPlugin->PluginCode('class.xt_banktransfer.php:_banktransferValidation_bottom')) ? eval($plugin_code) : false;
    if(isset($plugin_return_value))
    return $plugin_return_value;

    return $banktransferValidationReturnValue;
  }


  function _check_empty($data){
    global $xtPlugin, $info;

    if (strlen($data) < 1){
      return 'true';
    }
  }

  /**
   * get values for direct debit external processing
   */
  private function _get_dd_values(){
    global $xtPlugin, $info, $page, $currency ;

    $dd_values['customers_country_code']  = $_SESSION['customer']->customer_default_address['customers_country_code'];

    if ($page->page_name =='checkout') {
      $dd_values['cart_amount']     = round($_SESSION['cart']->content_total['plain'], 2);
      $dd_values['cart_trxamount']  = (intval($dd_values['cart_amount'] * 100));
      $dd_values['cart_currency']   = $currency->code;
    } else {
      $dd_values['cart_amount']     = 0;
      $dd_values['cart_trxamount']  = 0;
      $dd_values['cart_currency']   = 'EUR';
    }
    return $dd_values;
  }

    public function db_get_template($tpl_code, &$tpl_source, &$smarty_obj)
    {
        global $db;

        list($shopId, $langCode) = explode('_', $tpl_code);
        $tpl_source = $this->getTemplate($langCode);
        $tpl_trans = $db->GetAssoc("SELECT language_key, language_value FROM ".TABLE_LANGUAGE_CONTENT." WHERE language_code='".$langCode."' and (class='admin' or class='both')");
        $tpl_source = preg_replace('/({txt)\s(key=)([A-Z_]+)(\})/e','$tpl_trans["$3"]',$tpl_source);
        return true;
    }

    public function db_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj)
    {
        $tpl_timestamp = time();
        return true;
    }

    public function db_get_secure($tpl_name, &$smarty_obj)
    {
        return true;
    }

    public function db_get_trusted($tpl_name, &$smarty_obj)
    {

    }

    public function getTemplate($langCode)
    {
        // fake email to get tpl content
        $sepa = new xtMailer('sepa_mandat', $langCode, $this->customers_status, '-1', $this->shop_id);
        $tpl_content = $sepa->_getTPL();
        if (isset($tpl_content['mail_body_html'])) {
            return $tpl_content['mail_body_html'];
        } else {
            return 'Sepa Mandat Template missing';
        }


    }
}
?>