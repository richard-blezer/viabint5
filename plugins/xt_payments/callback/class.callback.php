<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class callback_xt_payments extends callback {

    public $version = '2.2.1';
    private $_merchantId;
    private $_siteId;
    private $_secretKey;
    private $data;

    function callback_xt_payments(){
        $this->loadMerchantCredentials();
    }

    function loadMerchantCredentials(){

        if(XT_PAYMENTS_TEST_MODE){

            $this->_merchantId = XT_PAYMENTS_TEST_PPPMERCHANTID;
            $this->_siteId = XT_PAYMENTS_TEST_PPPWEBSITEID;
            $this->_secretKey = XT_PAYMENTS_TEST_PPPSECRETKEY;
        }
        else{
            $this->_merchantId = XT_PAYMENTS_LIVE_PPPMERCHANTID;
            $this->_siteId = XT_PAYMENTS_LIVE_PPPWEBSITEID;
            $this->_secretKey = XT_PAYMENTS_LIVE_PPPSECRETKEY;
        }
    }

    function process() {
        global $filter;
        //error_log('Entering callback process()');

        $this->data = array();
        foreach ($_GET as $key => $val) {
            $this->data[$key] = $filter->_filter($val);
        }

        foreach ($_POST as $key => $val) {
            $this->data[$key] = $filter->_filter($val);
        }

        // is it express ipn coming from sf proxy ?
        // if, it will have param SECRET_KEY
        if(array_key_exists('GWSecretKey', $this->data))
        {
            try{
                require_once _SRV_WEBROOT.'plugins/xt_payments/callback/class.callback_express.php';
                $cbExpress = new callback_xt_payments_express();
                $r = $cbExpress->process($this->data);
                $rCode = 200;
                $rMsg = 'OK';
                switch($r){
                    case ExpressCallbackResult::SUCCESS:
                		$rCode = 200;
                		$rMsg = 'OK';
                        header("HTTP/1.0 200 OK");
                        break;
                    case ExpressCallbackResult::ORDER_ID_MISSING:
                		$rCode = 500;
                		$rMsg = 'Missing order id';
                        header('HTTP/1.0 500 ');
                        break;
                    case ExpressCallbackResult::ORDER_NOT_FOUND:
                		$rCode = 500;
                		$rMsg = 'Order not found';
                        header('HTTP/1.0 500 ');
                        break;
                    case ExpressCallbackResult::CHECKSUM_ERROR:
                		$rCode = 500;
                		$rMsg = 'Checksum error';
                        header('HTTP/1.0 500 ');
                        break;
                    case ExpressCallbackResult::PARAM_MISSING:
                		$rCode = 500;
                		$rMsg = 'Parameter missing';
                        header('HTTP/1.0 500 ');
                        break;
                    default:
                		$rCode = 500;
                		$rMsg = 'Processed without error but return value missing';
                        header('HTTP/1.0 500 ');
                }
            }
            catch(Exception $e)
            {
            	$rCode = 500;
                $rMsg = 'Internal Server Error: '.$e->getMessage();
            }
            header("HTTP/1.0 $rCode $rMsg");
            echo("$rCode $rMsg");
            //error_log("express callback returned $rCode $rMsg");
            ob_end_flush();
            die();
        }
        else {
            $response = $this->_callbackProcess();

            if ($response->repost) {
                header('HTTP/1.0 404 Not Found');
            } else {
                header("HTTP/1.0 200 OK");
            }
        }
    }


    function _callbackProcess() {

        // show version
        if (isset($_GET['_showPluginVersion'])) {

            echo 'Version: '.$this->version;
            return;
        }

        // order ID already inserted ?
        $err = $this->_getOrderID();
        if (!$err)
            return false;

        // check if merchant ID matches
        $err = $this->_checkMerchantKey();

        if (!$err)
            return false;

        // validate md5signature
        $err = $this->_checkMD5Signature();
        if (!$err)
            return false;

        // validate Amount
        $err = $this->_checkAmount();
        if (!$err)
            return false;

        $this->_setStatus();
    }

    /**
     * compare orders total amount with ipn amount
     *
     * @return boolean
     */
    function _checkAmount() {
        global $db;

        $order = new order($this->orders_id,$this->customers_id);

        if ($order->order_total['total']['plain']==$this->data['totalAmount']) return true;

        $log_data = array();
        $log_data['module'] = 'xt_payments';
        $log_data['class'] = 'error';
        $log_data['error_msg'] = 'ERR 001: amount conflict';
        $log_data['error_data'] = serialize(array('detail'=>'Amount SEND:' . $this->data['totalAmount'] . ' Amount STORED:' . $order->order_total['total']['plain']));
        $this->_addLogEntry($log_data);
		$this->repost = true;
        return true;
    }

    /**
     * Find order ID for given MB transaction_id
     *
     * @return boolean
     */
    function _getOrderID() {
        global $db;

        $order_query = "SELECT orders_id,customers_id FROM ".TABLE_ORDERS." WHERE orders_id = ?";

        $rs = $db->Execute($order_query, array((int)$this->data['invoice_id']));

        if ($rs->RecordCount() == 1) {
            $this->orders_id = $rs->fields['orders_id'];
            $this->customers_id = $rs->fields['customers_id'];
            return true;
        }

        $log_data = array();
        $log_data['module'] = 'xt_payments';
        $log_data['class'] = 'error';
        $log_data['error_msg'] = 'ERR 002: order id not found';
        $log_data['error_data'] = serialize(array('transaction_id'=>$this->data['invoice_id']));
        $this->_addLogEntry($log_data);
        $this->repost = true;
        return false;

    }

    /**
     * check if merchant ID and mechant mail matches to DB
     *
     * @return boolean
     */
    function _checkMerchantKey() {

        // does merchant site id exists ?
        if (!isset ($this->data['merchant_site_id']) || $this->data['merchant_site_id'] != $this->_siteId) {
            $log_data = array();
            $log_data['module'] = 'xt_payments';
            $log_data['class'] = 'error';
            $log_data['error_msg'] = 'ERR 003: site id conflict';
            $log_data['error_data'] = serialize(array('detail'=>'Site ID SEND:' . $this->data['merchant_site_id'] . ' Site ID STORED:' . $this->_siteId));
            $this->_addLogEntry($log_data);
			$this->repost = true;
            return false;
        }
        // merchant id ?
        if (!isset ($this->data['merchant_id']) || $this->data['merchant_id'] != $this->_merchantId) {
            $log_data = array();
            $log_data['module'] = 'xt_payments';
            $log_data['class'] = 'error';
            $log_data['error_msg'] = 'ERR 004: merchant id conflict';
            $log_data['error_data'] = serialize(array('Merchant ID SEND:' . $this->data['merchant_id'] . ' Merchant ID STORED:' . $this->_merchantId));
            $this->_addLogEntry($log_data);
			$this->repost = true;
            return false;
        }

        return true;
    }

    /**
     * Calculate and check MD5 Signature of Callback
     *
     * @return boolean
     */
    function _checkMD5Signature() {

        if ($this->_secretKey == '')
            return true;

        $secret = $this->_secretKey;
        $md5sec = strtoupper(md5($secret));
        $hash = $secret.$this->data['ppp_status'] . $this->data['PPP_TransactionID'];
        $hash = md5($hash);

        if ($hash!=$this->data['responsechecksum']) {
            $log_data['module'] = 'xt_payments';
            $log_data['class'] = 'error';
            $log_data['error_msg'] = 'ERR 005: md5 check failed';
            $this->_addLogEntry($log_data);
			$this->repost = true;
            return false;

        }


        return true;

    }

    function _setStatus() {

		if(isset($this->data['transactionType']) && 
			($this->data['transactionType'] == "Credit" || 
			$this->data['transactionType'] == "Void" || 
			$this->data['transactionType'] == "Chargeback")
		) {
			
			$status = XT_PAYMENTS_HOLD;
			$log_data = array();
			$log_data['orders_id'] = $this->orders_id;
			$log_data['module'] = 'xt_payments';
			$log_data['class'] = 'hold';
			$log_data['transaction_id'] = $this->data['PPP_TransactionID'];
			$log_data['callback_data'] = array('message'=>'OK','error'=>'200','transaction_id'=>$this->data['PPP_TransactionID'], 'status'=>$this->data['Status'], 'transaction type'=>$this->data['transactionType']);
			$this->_addLogEntry($log_data);
			$txn_log_id = $this->data['PPP_TransactionID'];
			
		} else {
		
			switch ($this->data['Status']) {

				// processed
				case 'APPROVED' :
				case 'SUCCESS' :
					$status = XT_PAYMENTS_APPROVED;
					$log_data = array();
					$log_data['orders_id'] = $this->orders_id;
					$log_data['module'] = 'xt_payments';
					$log_data['class'] = 'success';
					$log_data['transaction_id'] = $this->data['PPP_TransactionID'];
					$log_data['callback_data'] = array('message'=>'OK','error'=>'200','transaction_id'=>$this->data['PPP_TransactionID'], 'status'=>$this->data['Status'], 'transaction type'=>$this->data['transactionType']);
					$this->_addLogEntry($log_data);
					$txn_log_id = $this->data['PPP_TransactionID'];
					break;

				// canceled
				case 'ERROR' :
					$status = XT_PAYMENTS_ERROR;
					$log_data = array();
					$log_data['orders_id'] = $this->orders_id;
					$log_data['module'] = 'xt_payments';
					$log_data['class'] = 'declined';
					$log_data['transaction_id'] = $this->data['PPP_TransactionID'];
					$log_data['callback_data'] = array('message'=>'FAILED','error'=>'999','transaction_id'=>$this->data['PPP_TransactionID'], 'status'=>$this->data['Status'], 'transaction type'=>$this->data['transactionType']);
					$txn_log_id = $this->_addLogEntry($log_data);
					break;
				// declined
				case 'DECLINED' :
					$status = XT_PAYMENTS_DECLINED;
					$log_data = array();
					$log_data['orders_id'] = $this->orders_id;
					$log_data['module'] = 'xt_payments';
					$log_data['class'] = 'declined';
					$log_data['transaction_id'] = $this->data['PPP_TransactionID'];
					$log_data['callback_data'] = array('message'=>'FAILED','error'=>'999','transaction_id'=>$this->data['PPP_TransactionID'], 'status'=>$this->data['Status'], 'transaction type'=>$this->data['transactionType']);
					$txn_log_id = $this->_addLogEntry($log_data);
					break;

				// pending
				case 'PENDING' :
					$status = XT_PAYMENTS_PENDING;
					$log_data = array();
					$log_data['orders_id'] = $this->orders_id;
					$log_data['module'] = 'xt_payments';
					$log_data['class'] = 'pending';
					$log_data['transaction_id'] = $this->data['PPP_TransactionID'];
					$log_data['callback_data'] = array('message'=>'PENDING','error'=>'200','transaction_id'=>$this->data['PPP_TransactionID'], 'status'=>$this->data['Status'], 'transaction type'=>$this->data['transactionType']);
					$txn_log_id = $this->_addLogEntry($log_data);
					break;
			}
		}
		
		$callback_message = "";
		if(isset($this->data['transactionType'])) {
			switch ($this->data['transactionType']) {

				case 'Credit' :
					$callback_message = TEXT_TRANSACTION_TYPE_CREDIT;
					break;
					
				case 'Void' :
					$callback_message = TEXT_TRANSACTION_TYPE_VOID;
					break;
					
				case 'Chargeback' :
					$callback_message = TEXT_TRANSACTION_TYPE_CHARGEBACK;
					break;
					
				case 'Retrieval' :
					$callback_message = TEXT_TRANSACTION_TYPE_RETRIEVAL;
					break;
					
				case 'Modification' :
					$callback_message = TEXT_TRANSACTION_TYPE_MODIFICATION;
					break;
			}
		}
		
        // update order status
        $this->_updateOrderStatus($status,'true',$this->data['PPP_TransactionID'], $callback_message);
    }
}