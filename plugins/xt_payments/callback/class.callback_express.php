<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class callback_xt_payments_express extends callback {

    public  $version = '1.0';
    protected $orders_id;
    protected $customers_id;
    private $log_callback = true;
    private $data;
    private $GWSecretKey; // PP proxy key

    function process($data) {
        global $db;

        $expectedParams = array('invoice', 'txn_id', 'payment_status', 'GWSecretKey' );
        foreach($expectedParams as $param)
        {
            if (!array_key_exists($param, $data) || empty($data[$param]))
            {
                return ExpressCallbackResult::PARAM_MISSING;
            }
        }

        $this->data = $data;
		if(XT_PAYMENTS_TEST_MODE){
			$this->_orderPrefix  = $_SERVER['SERVER_NAME']."_test";
		}
		else{
            $this->_orderPrefix  = $_SERVER['SERVER_NAME'];
        }
		if(isset($this->data['invoice'])){
			$this->data['invoice'] = str_replace($this->_orderPrefix."_xtc_orderid_", "", $this->data['invoice']);
		}

        // xt_payments
        if(XT_PAYMENTS_TEST_MODE){

            $this->GWSecretKey  = XT_PAYMENTS_TEST_GWSECRETKEY;
        }
        else{
            $this->GWSecretKey  = XT_PAYMENTS_LIVE_GWSECRETKEY;
        }

        if ($this->data['invoice']>0 && is_numeric($this->data['invoice'])) {
            // check if order exists
            $rs  = $db->Execute("SELECT	* FROM " . TABLE_ORDERS . " WHERE orders_id = ?", array((int)$this->data['invoice']));

            if ($rs->RecordCount()==0) {
                $log_data['module'] = 'xt_payments_express';
                $log_data['orders_id'] = (int)$this->data['invoice'];
                $log_data['class'] = 'error';
                $log_data['error_msg'] = 'orders_id not found';
                $log_data['error_data'] = serialize($this->data);
                $this->_addLogEntry($log_data);
                return ExpressCallbackResult::ORDER_NOT_FOUND;
            }

            // validate request
            $query = $_SERVER['QUERY_STRING'];
            $start = strpos($query, '&mc_gross=') + 1;
            if ($start == false){
                return ExpressCallbackResult::PARAM_MISSING;
            }
            $s = substr($query, $start);

            $key = mb_strtoupper(md5($s . mb_strtoupper(md5($this->GWSecretKey))));

            if ($key!=$data['GWSecretKey'])
            {
                $log_data['module'] = 'xt_payments_express';
                $log_data['orders_id'] = (int)$this->data['invoice'];
                $log_data['class'] = 'error';
                $log_data['error_msg'] = 'checksum error';
                $log_data['error_data'] = serialize($this->data);
                $this->_addLogEntry($log_data);
                return ExpressCallbackResult::CHECKSUM_ERROR;
            }

            $this->orders_id = $rs->fields['orders_id'];
            $this->customers_id = $rs->fields['customers_id'];

            //refund memo
            if(isset($this->data['parent_txn_id']))
                $log_data['class'] = 'callback_data_paypal_refund';

            $send_info = 'true';

            $log_data = array();
            $log_data['orders_id'] = $this->orders_id;
            $log_data['module'] = 'xt_payments_express';
            $log_data['transaction_id'] = $this->data['txn_id'];
            $log_data['class'] = $data['payment_status'];

            switch ($this->data['payment_status']) {

                case 'Completed':
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_COMPLETED;
                    $db->Execute("UPDATE ".TABLE_ORDERS." SET orders_data='".$this->data['txn_id']."' WHERE orders_id='".(int)$this->data['invoice']."'");
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_COMPLETED,'error'=>'200','transaction_id'=>$this->data['txn_id']);
                    break;
                case 'Denied':
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_DENIED;
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_DENIED,'error'=>'200','transaction_id'=>$this->data['txn_id']);
                    break;
                case 'Failed':
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_FAILED;
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_FAILED,'error'=>'200','transaction_id'=>$this->data['txn_id']);
                    break;
                case 'Refunded':
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_REFUNDED;
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_REFUNDED,'error'=>'200','transaction_id'=>$this->data['txn_id']);
                    break;
                case 'Reversed':
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_REVERSED;
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_REVERSED,'error'=>'200','transaction_id'=>$this->data['txn_id']);
                    break;

                case 'Pending':
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_PENDING;
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_PENDING,'error'=>'200','transaction_id'=>$this->data['txn_id']);

                    // write seperate auth transaction id into database
                    if ($this->data['pending_reason']=='order') {

                        $send_info = 'false';

                        $datum = $this->data['auth_exp'];
                        $datum = strtotime($datum);
                        $datetime = date("Y-m-d H:i:s", $datum);

                        $db->Execute(
                            "UPDATE ".TABLE_ORDERS." SET xt_payments_authorization_id=?,xt_payments_authorization_amount=?,xt_payments_authorization_expire=? WHERE orders_id=?",
                            array($this->data['txn_id'], $this->data['mc_gross'], $datetime, (int)$this->data['invoice'])
                        );
                    } else {
                        $db->Execute(
                            "UPDATE ".TABLE_ORDERS." SET orders_data=? WHERE orders_id=?",
                            array($this->data['txn_id'], (int)$this->data['invoice'])
                        );
                    }

                    break;

                default:
                    $status = XT_PAYMENTS_PAYPAL_ORDER_STATUS_FAILED;
                    $log_data['callback_data'] = array('message'=>XT_PAYMENTS_PAYPAL_ORDER_STATUS_FAILED,'error'=>'200','transaction_id'=>$this->data['txn_id']);
                    break;

            }
            $this->_addLogEntry($log_data);

            // send status mail
            $this->_updateOrderStatus($status,$send_info,$this->data['txn_id']);
        } else {

            $log_data['module'] = 'xt_payments_express';
            $log_data['orders_id'] = '0';
            $log_data['class'] = 'error';
            $log_data['error_msg'] = 'no orders_id';
            $log_data['error_data'] = serialize($this->data);
            $this->_addLogEntry($log_data);
            return ExpressCallbackResult::ORDER_ID_MISSING;

        }
        return ExpressCallbackResult::SUCCESS;
    }

}

class ExpressCallbackResult {
    const SUCCESS = 1;
    const ORDER_NOT_FOUND = 2;
    const CHECKSUM_ERROR = 3;
    const ORDER_ID_MISSING = 4;
    const PARAM_MISSING = 5;
}