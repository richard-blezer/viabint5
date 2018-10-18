<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mario Zanier
 * Date: 27.06.13
 * Time: 11:57
 * (c) Mario Zanier, mzanier@xt-commerce.com
 */

include_once _SRV_WEBROOT.'plugins/paypal_qr_shopping/classes/class.paypal_qr.php';

class callback_paypal_qr_shopping extends callback {

    var $version = '1.0';
 //   var $API_USER = PAYPAL_QR_SHOPPING_API_USER;
 //   var $API_PASS= PAYPAL_QR_SHOPPING_API_KEY;
//    var $API_SIGNATURE = PAYPAL_QR_SHOPPING_API_SIGNATURE;
    var $test=0;

    function __construct() {

        $prq = new paypal_qr();

        $this->API_USER=$prq->api_user;
        $this->API_PASS=$prq->api_key;
        $this->API_SIGNATURE=$prq->api_signature;

    }


    function process() {
        global $filter,$db;

      //  if (isset($_GET['test'])) {
      //      $this->test=$_GET['test'];
      //      define('___DEBUG','true');
     //   } else {
            define('___DEBUG','false');
     //   }

        if (count($_POST)==0) die('empty post');


        $this->data = array();
        $request = '';

        if (isset($_POST['ws-request'])) {
            $request = $_POST['ws-request'];


       //     $this->_writeLogFile($_POST);

            $var = str_replace('<br>','',$request);
            $json = base64_decode($var);



            $log_data = array();
            $log_data['module'] = 'paypal_qr_shopping';
            $log_data['class'] = 'callback_data_json';
            $log_data['transaction_id'] = '0';
            $log_data['callback_data'] = $json;
            $this->_addLogEntry($log_data);

            $json = json_decode($json);
//            __debug($json);



            $this->data = $json;
     //       if (isset($_GET['pid'])) $this->data->attributes->productIdentifier=$_GET['pid'];
    //        $this->data->attributes->productIdentifier=3;

            // check for correct API credentials
            if (isset($this->data->attributes->apiUser)) {

                $return = $this->validate();
                if ($return==false) return;

                switch ($this->data->type) {

                    case '02': // Req_Product_Details
                        $this->req_product_details();
                        break;
                    case '06': // Req_FilledInPurchaseOrder
                        $this->req_FilledInPurchaseOrder();
                        break;
                    case '30':
                        $this->Submit_PurchaseOrder();
                        break;

                }


            } else {

                echo '-nothing to do';

                $log_data = array();
                $log_data['module'] = 'paypal_qr_shopping';
                $log_data['class'] = 'error';
                $log_data['transaction_id'] = '0';
                $log_data['error_msg'] = 'apiUser missing';
                $this->_addLogEntry($log_data);
            }



        } else {
            // IPN ?


            $raw_post_data = file_get_contents('php://input');
            $raw_post_array = explode('&', $raw_post_data);
            $myPost = array();

            foreach ($raw_post_array as $keyval) {
                $keyval = explode ('=', $keyval);
                if (count($keyval) == 2)
                    $myPost[$keyval[0]] = urldecode($keyval[1]);
            }

          //  $this->_writeLogFile($myPost);
            // read the post from PayPal system and add 'cmd'
            // verify data though curl call
            if(function_exists('get_magic_quotes_gpc')) {
                $get_magic_quotes_exists = true;
            }
            $this->pp_data = array();
            foreach ($myPost as $key => $value) {
                $this->pp_data[$key] = utf8_encode($value);
            }

            $oID = $this->orderExists($this->pp_data['txn_id']);
            if ($oID==false) {
                $log_data['module'] = 'paypal_qr_shipping';
                $log_data['orders_id'] = (int)$this->pp_data['txn_id'];
                $log_data['class'] = 'error';
                $log_data['error_msg'] = 'paymentReference not found';
                $log_data['error_data'] = serialize($this->pp_data);
                $this->_addLogEntry($log_data);
            } else {

                // set stati etc
                switch ($this->pp_data['payment_status']) {

                    case 'Completed':
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_COMPLETED;
                        break;
                    case 'Denied':
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_DENIED;
                        break;
                    case 'Failed':
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_FAILED;
                        break;
                    case 'Refunded':
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_REFUNDED;
                        break;
                    case 'Reversed':
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_REVERSED;
                        break;
                    case 'Pending':
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_PENDING;

                        // authorisierung ?
                        if ($this->data['pending_reason']=='authorization') {
                            $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_AUTHORIZATION;
                        }

                        break;

                    default:
                        $new_order_status = PAYPAL_QR_SHOPPING_ORDER_STATUS_FAILED;
                        break;

                }
                $send_info = 'false';
                $this->_updateOrderStatus($new_order_status,$send_info,$this->pp_data['txn_id'],'');

            }


        }

    }


    function orderExists($txn_id) {
        global $db;

        $rs = $db->Execute("SELECT * FROM ".TABLE_ORDERS." WHERE paymentReference=? ",array($txn_id));
        if ($rs->RecordCount()==1) {
            $this->orders_id = $rs->fields['orders_id'];
            $this->customers_id = $rs->fields['customers_id'];
            return $rs->fields['orders_id'];
        }
        return false;

    }

    function req_product_details() {

        $product_id = (int)$this->data->attributes->productIdentifier;
        $language_code = substr($this->data->attributes->language,0,2);

        $ppQR = new paypal_qr();
        $ppQR->product($product_id,$language_code);

    }

    function req_FilledInPurchaseOrder() {

        $ppQR = new paypal_qr();
        $ppQR->processBasket($this->data);

    }

    function Submit_PurchaseOrder() {

        $ppQR = new paypal_qr();
        $ppQR->processOrder($this->data);

    }

    function validate() {

        if (!isset($_GET['test'])) {
        if ($this->data->attributes->apiUser!=$this->API_USER || $this->data->attributes->apiUserPassword!=$this->API_PASS || $this->data->attributes->apiUserSignature!=$this->API_SIGNATURE) {
            $log_data = array();
            $log_data['module'] = 'paypal_qr_shopping';
            $log_data['class'] = 'error';
            $log_data['transaction_id'] = '0';
            $log_data['error_msg'] = 'api credentials not matching ('.$this->data->attributes->apiUser.' '.$this->data->attributes->apiUserPassword.' '.$this->data->attributes->apiUserSignature.')';
            $this->_addLogEntry($log_data);
            return false;
        }
        }
        return true;

    }





}
?>