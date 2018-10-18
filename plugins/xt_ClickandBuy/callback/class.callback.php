<?php
 /*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.callback.php 4611 2011-03-30 16:39:15Z mzanier $
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


define ('XT_CLICKANDBUY_WSDL', 'http://wsdl.eu.clickandbuy.com/TMI/1.4/TransactionManager_dotNET.wsdl');
define ('XT_CLICKANDBUY_TABLE', DB_PREFIX . '_plg_clickandbuy_ems');

class callback_xt_ClickandBuy extends callback {

  function debug ($s, $txt = null) {
    return; // no debugging
    $f = fopen (_SRV_WEBROOT.'plugins/xt_ClickandBuy/callback/debug.txt', "a+");
    $date = date('c');
    $dbg = print_r ($s, true);
    @fprintf ($f, ($txt ? "$txt: " : '') . "$date\n");
    fprintf ($f, $dbg . "\n\n");
    fclose ($f);
  } // debug()

  protected $header;

function __construct ($skip_actions = false) {
  if ($skip_actions) return;
    global $xtLink;
    $this->debug (session_id(), 'sessDI');
    //$this->debug ($_SESSION, 'session');
    $c = $_SERVER['REQUEST_URI'];
    $this->debug ("($c)", 'request');
    //$this->debug ($_GET, 'GET');
    $this->extractHeader();
    $this->debug ($this->header, 'HEADER');
    // handshake 1:
    if (! $_GET['result']) {
      $url = $this->recreateCallbackURL();
      $res = $this->checkHandshake1();
      if ($res == 'success') {
        $this->logandsetStatus (XT_CLICKANDBUY_ORDER_STATUS_PENDING);
      }
      $url .= '&result=' . $res;
      $this->redirect($url);
    }
    // handshake 2:
    // fall1: fail
    if ($_GET['result'] == 'fail') {
      unset ($_GET['result']);
      $this->createFailURL();
    }
    // fall2: success
    if (! ($bdrID = $this->checkIsExternalBDRIDcommitted())) {
      unset ($_GET['result']);
      $this->debug ('failing because of checkIsExternalBDRIDcommitted returns false');
      $this->logandsetStatus (XT_CLICKANDBUY_ORDER_STATUS_DENIED);
      $this->createFailURL();
    }
    $this->logandsetStatus (XT_CLICKANDBUY_ORDER_STATUS_COMPLETED, false, $bdrID);
    $this->createSuccessURL();

  } // __construct()

  protected function createBaseURL() {
    global $xtLink;
    $url =  $xtLink->_link(array());
    return str_replace ('?page=', '', $url);
  } // createBaseURL()

  protected function recreateCallbackURL() {
    $url = $this->createBaseURL();
    foreach ($_GET as $k => $v) {
      $get[] = "$k=$v";
    }
    $url .= '?' . join ('&', $get);
    return $url;
  } // recreateCallbackURL()

  protected function extractSessionVars() {
    if (preg_match ('/&(\w+)=(\w+)&fgkey=\w+(&result=\w+)?$/',$_SERVER['REQUEST_URI'], $m)) {
      return $m[1] . '=' . $m[2];
    }
    return '';
  } // extractSessionVars()

  protected function createFailURL() {
    global $info, $xtLink;
    $url = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment'));
    $url .= '&' . $this->extractSessionVars();
    $info->_addInfoSession(ERROR_PAYMENT);
    $this->redirect($url);
  } // createFailURL()

  protected function createSuccessURL() {
    global $xtLink;
    $url = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment_process'));
    $url .= '&' . $this->extractSessionVars();
    $this->redirect($url);
  } // createSuccessURL()

  protected function redirect($url) {
    $this->debug ("redirecting to $url");
    header ('Location: ' . $url);
    exit (0);
  } // redirect()


  protected function checkIsExternalBDRIDcommitted() {
    require_once _SRV_WEBROOT.'plugins/xt_ClickandBuy/lib/nusoap.php';

    $wsdl = XT_CLICKANDBUY_WSDL;
    $client =  new nusoapclient($wsdl, true);
    $secondconfirmation = array(
    	'sellerID' => XT_CLICKANDBUY_SELLER_ID,
    	'tmPassword' => XT_CLICKANDBUY_WSDL_PASSWD,
    	'slaveMerchantID' => '0',
    	'externalBDRID' => $this->header['external_bdr_id']
    );
    $f = $client->call ('isExternalBDRIDCommitted', $secondconfirmation);
    $this->debug ($f, 'isExternalBDRIDCommitted returns');
    if (is_array($f) && $f['isCommitted'] === true) {
      $this->header['bdr_id'] = $f['BDRID'];
      return $f['BDRID'];
    }
    return false;
  } // checkIsExternalBDRIDcommitted()

  protected $headerparams = array ('userid', 'price', 'currency', 'transaction', 'contentid', 'userip');
  protected function extractHeader() {
    $this->header['remote_addr'] = $_SERVER['REMOTE_ADDR'];
    $this->header['external_bdr_id'] = $_GET['externalBDRID'];
    foreach ($this->headerparams as $p) {
      $key1 = 'HTTP_X_' . strtoupper($p);
      $key2 = 'X-' . strtoupper($p);
      if (isset ($_SERVER[$key1])) {
        $this->header[$p] = $_SERVER[$key1];
      }
      elseif (isset($_SERVER[$key2])) {
        $this->header[$p] = $_SERVER[$key2];
      }

    }
  } // extractHeader()

  protected function checkHandshake1() {
    foreach ($this->headerparams as $p) {
      if (! isset ($this->header[$p])) {
        $this->debug ('failing because of noexisting header elem ' . $p);
        return 'fail';
      }
    }
    if (! $this->header['external_bdr_id']) {
      $this->debug ('failing because of missing external_bdr_id');
      return 'fail';
    }
    // check 'remote_addr' 217.22.128.*
    if (! preg_match('/^217\.22\.128\.\d+$/', $this->header['remote_addr'])) {
      $this->debug ('failing because of illegal IP ' . $this->header['remote_addr']);
      return 'fail';

    }
    // check transaction != 0
    if ($this->header['transaction'] == 0) {
      $this->debug ('failing because of transaction == 0');
      return 'fail';
    }
    // check external_bdr_id
    $oid = $this->retreiveOrderId();
    if (! $oid) {
      $log_data['module'] = 'xt_ClickandBuy';
      $log_data['orders_id'] = (int)$oid;
      $log_data['class'] = 'error';
      $log_data['error_msg'] = 'orders_id not found';
      $log_data['error_data'] = $call_back_data;
      $this->_addLogEntry($log_data);
      $this->debug ('failing because of no order id');
      return 'fail';
    }
    $sql = 'select * from ' . TABLE_ORDERS . ' where orders_id=' . $oid;
    global $db;
    if (! ($row = $db->GetRow ($sql))) {
      $log_data['module'] = 'xt_ClickandBuy';
      $log_data['orders_id'] = (int)$oid;
      $log_data['class'] = 'error';
      $log_data['error_msg'] = 'orders_id not found';
      $log_data['error_data'] = serialize ($this->header);
      $this->_addLogEntry($log_data);

      $this->debug ("failing because of orders_id $oid not found");
      return 'fail';
    }
    $this->debug ($row, 'GetRow');
    $order = new order($oid, $row['customers_id']);
    $this->debug ($order->order_data, 'OD');
    $this->debug ($order->order_total, 'OT');

    // chick $this->header['price'] and 'currency
    if ($this->header['currency'] != $row['currency_code']) {
      $this->debug ("failing because of currency_code is unequal");
      return 'fail';
    }
    require_once _SRV_WEBROOT.'plugins/xt_ClickandBuy/classes/class.ClickandBuy.php';
    if ($this->header['price'] != (1000 * ClickandBuy::_buildPrice($order->order_total['total']['plain']))) {
      $this->debug ("failing because of price is unequal " . (1000 * ClickandBuy::_buildPrice($order->order_total['total']['plain'])));
      return 'fail';
    }
    return 'success';

  } // checkHandshake1()

  function retreiveOrderId() {
    $tmp = explode('0x0', $this->header['external_bdr_id']);
    return (int) $tmp[0];
  } // retreiveOrderId()

  protected function logandsetStatus ($status, $do_sendmail = false, $bdrID = null) {
    global $db;
    $oid = $this->retreiveOrderId();
    $call_back_data = serialize ($this->header);

    $this->debug('in process oid=' . $oid . ' status == ' . $status);
    $rs  = $db->Execute("SELECT	* FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $oid . "'");
    $this->debug('nach execute');

    if ($rs->RecordCount()==0) {
      $log_data['module'] = 'xt_ClickandBuy';
      $log_data['orders_id'] = (int)$oid;
      $log_data['class'] = 'error';
      $log_data['error_msg'] = 'orders_id not found';
      $log_data['error_data'] = $call_back_data;
      $this->_addLogEntry($log_data);
      return false;
    }
    if ($bdrID) {
      $sql = 'update ' . TABLE_ORDERS . ' set clickandbuy_bdrid=' . $bdrID . " where orders_id=$oid";
      $db->Execute ($sql);
    }

    $this->orders_id = $rs->fields['orders_id'];
    $this->customers_id = $rs->fields['customers_id'];

    $log_data = array();
    $log_data['module'] = 'xt_ClickandBuy';
    $log_data['orders_id'] = $oid;
    $log_data['transaction_id'] = $this->header['bdr_id'];
    $log_data['class'] = 'callback_data';
    $log_data['callback_data'] = $call_back_data;
    $this->debug('vor _addLogEntry');
    $this->_addLogEntry($log_data);
    $this->debug('nach _addLogEntry');

    $this->debug('vor _updateOrderStatus');
    $this->_updateOrderStatus($status, ($do_sendmail ? 'true' : 'false'),$txn_log_id);
    $this->debug('nach _updateOrderStatus');

  } // logandsetStatus()

  function process () {
    // all in __construct
  } // process()

  private function xpath (SimpleXMLElement $node, $path, & $res_array) {
    $res = $node->xpath ($path);
    if ($res) {
      $res = $res[0];
      foreach ($res->children() as $c) {
        $res_array[$c->getName()] = (string) $c;
      }
    }
    return $res;
  }
  public function handleEMSPush (SimpleXMLElement $xml) {

    // auf true:
    // bei den oben aufgelisteten Status können Sie dann die Transaktion als "Pending Payment" interpretieren.
    $states = array (
      'created' => false,
      'payment_successful' => false,
      'cancelled' => false,
      'charge back' => true,
      'charge back lifted' => false,
      'booked-out' => true,
      'BDR to collection agency' => false,
      'booked-in' => false,
      'BDR successfully collected from collection agency' => true,
      'BDR not collected from collection agency' => true
    );

    if (! $global = $this->xpath ($xml, '/EVENT-DATA/GLOBAL', $global_array)) {
      $this->debug ('no global found');
      return false;
    }
    if ($event_switch = $this->xpath ($xml, '/EVENT-DATA/EVENT-SWITCH', $switch_array)) {
      if ($switch_array['action'] == 'push') {
        $this->debug ('push switch');
        return true;
      }
    }
    if (! $bdr = $this->xpath ($xml, '/EVENT-DATA/BDR/bdr-data', $bdr_array)) {
      $this->debug ('no bdr found');
      return false;
    }

    $this->header['external_bdr_id'] = $bdr_array['externalBDRID'];
    $oid = $this->retreiveOrderId();
    $crn = $global_array['crn'];
    $action = $bdr_array['action'];

    $this->debug($global_array, 'global');
    $this->debug($bdr_array, 'bdr');

    if (! array_key_exists($action, $states)) {
      $this->debug ('unknown action ' . $action);
      return false;
    }
    global $db;
    $sql = 'insert into ' . XT_CLICKANDBUY_TABLE . '(created,order_id,crn,event) values (NOW(),' . "$oid,$crn,'$action')";
    $db->Execute ($sql);
    $this->debug ('handleEMSPush returns true');

    if ($states[$action] === true) {
      $this->logandsetStatus(XT_CLICKANDBUY_ORDER_STATUS_PENDING);
    }
    return true;
  } // handleEMSPush()

} // class
?>
