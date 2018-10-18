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

include _SRV_WEBROOT.'plugins/xt_paypal/classes/class.xt_paypal.php';

class callback_xt_paypal extends callback {

	var $log_callback = true;


	function process() {
		global $filter,$db;

        $send_info = XT_PAYPAL_SEND_STATUS_MAIL_ON_IPN == 1 ? 'true' : 'false';

		$this->data = array();
        
        // show version 
		if (isset($_GET['_showPluginVersion']) && $_GET['_showPluginVersion'] == 1) {		
			echo 'Paypal: '.$filter->_filter(paypal::$plugin_version);
            echo '<br />Shop: '._SYSTEM_VERSION;
			return;
		}
        
        // reading raw POST data from input stream instead. 
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        
        foreach ($raw_post_array as $keyval) {
          $keyval = explode ('=', $keyval);
          if (count($keyval) == 2)
             $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the post from PayPal system and add 'cmd'
        // verify data though curl call
        if(function_exists('get_magic_quotes_gpc')) {
           $get_magic_quotes_exists = true;
        } 
        
        $parameters = 'cmd=_notify-validate';

        foreach ($myPost as $key => $value) { 
            $this->data[$key] = utf8_encode($value);
            if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
                 $value = urlencode(stripslashes($value)); 
            } else {
                 $value = urlencode($value);
            }
            $parameters .= '&' . $key . '=' . $value;
        }

		if ($this->data['invoice']>0 && is_numeric($this->data['invoice'])) {

			// check if order exists
			$rs  = $db->Execute("SELECT	* FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$this->data['invoice'] . "'");
            //LOG: callback data if orders_id from Database not found 
			if ($rs->RecordCount()==0) {
				$log_data['module'] = $rs->fields['payment_code'];
				$log_data['orders_id'] = (int)$this->data['invoice'];
				$log_data['class'] = 'error';
				$log_data['error_msg'] = 'orders_id not found';
				$log_data['error_data'] = serialize($this->data);
				$this->_addLogEntry($log_data);
				return false;
			}

			$this->orders_id = $rs->fields['orders_id'];
			$this->customers_id = $rs->fields['customers_id'];
            
            //LOG: callback data
			$log_data = array();
			$log_data['module'] = $rs->fields['payment_code'];
			$log_data['orders_id'] = $this->data['invoice'];
			$log_data['transaction_id'] = $this->data['txn_id'];
			$log_data['class'] = 'callback_data';
			$log_data['callback_data'] = serialize($this->data);
            
            //refund memo
            if(isset($_POST['parent_txn_id']))
                $log_data['class'] = 'callback_data_paypal_refund';
			
            $this->_addLogEntry($log_data);

			$paypal = new paypal();
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $paypal->IPN_URL);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
            $config = include _SRV_WEBROOT . "plugins/xt_paypal/conf/curl_config.php";
            curl_setopt($ch, CURLOPT_SSLVERSION, $config["SSL_VERSION"] );
			if (!empty($config["CIPHER_LIST"]))
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, $config["CIPHER_LIST"]);
            
			if( !($result = curl_exec($ch)) ) {
                //LOG: Data of curl call
                $log_data = array();
				$log_data['module'] = $rs->fields['payment_code'];
				$log_data['class'] = 'error';
				$log_data['orders_id'] = $this->orders_id;
				$log_data['error_msg'] = curl_error($ch);
				$log_data['error_data'] = serialize($result);
				$txn_log_id = $this->_addLogEntry($log_data);
                curl_close($ch);
                exit;
            }
            
            curl_close($ch);
            
			if (strcmp ($result, "VERIFIED") == 0) {

				switch ($this->data['payment_status']) {

					case 'Completed':
						$new_order_status = XT_PAYPAL_ORDER_STATUS_COMPLETED;
                        $db->Execute("UPDATE ".TABLE_ORDERS." SET orders_data='".$this->data['txn_id']."' WHERE orders_id='".(int)$this->data['invoice']."'"); 
						break;
					case 'Denied':
						$new_order_status = XT_PAYPAL_ORDER_STATUS_DENIED;
						break;
					case 'Failed':
						$new_order_status = XT_PAYPAL_ORDER_STATUS_FAILED;
						break;
					case 'Refunded':
						$new_order_status = XT_PAYPAL_ORDER_STATUS_REFUNDED;
						break;
					case 'Reversed':
						$new_order_status = XT_PAYPAL_ORDER_STATUS_REVERSED;
						break;
						
					case 'Pending':
						$new_order_status = XT_PAYPAL_ORDER_STATUS_PENDING;

                        // write seperate auth transaction id into database
                        if ($this->data['pending_reason']=='authorization') {

                            $send_info = 'false';

                            $datum = $this->data['auth_exp'];
                            $datum = strtotime($datum);
                            $datetime = date("Y-m-d H:i:s", $datum);

                            $db->Execute("UPDATE ".TABLE_ORDERS." SET authorization_id='".$this->data['txn_id']."',authorization_amount='".$this->data['mc_gross']."',authorization_expire='".$datetime."' WHERE orders_id='".(int)$this->data['invoice']."'");
                        } else {
                            $db->Execute("UPDATE ".TABLE_ORDERS." SET orders_data='".$this->data['txn_id']."' WHERE orders_id='".(int)$this->data['invoice']."'");
                        }

						break;
					
					default:
						$new_order_status = XT_PAYPAL_ORDER_STATUS_FAILED;
						break;

				}

			} else if (strcmp ($result, "INVALID") == 0){
                //LOG: invalid data of Curl call
				$new_order_status = XT_PAYPAL_ORDER_STATUS_FAILED;
				$log_data['module'] = $rs->fields['payment_code'];
				$log_data['orders_id'] = $this->data['invoice'];
				$log_data['transaction_id'] = $this->data['txn_id'];
				$log_data['class'] = 'error';
				$log_data['error_msg'] = 'INVALID response but invoice and Customer matched';
				$log_data['error_data'] = serialize($result);
				$this->_addLogEntry($log_data);
			}

            $callback_message = '';
            if ($this->data['pending_reason']!='') $callback_message=$this->data['pending_reason'].' Amount:'.$this->data['mc_gross'];

            if ($this->data['auth_exp']!='' && $this->data['payment_status']=='Pending') {
                $datum = $this->data['auth_exp'];
                $datum = strtotime($datum);
                $datetime = date("Y-m-d H:i:s", $datum);
                $callback_message.=' Expires: '.$datetime;
            }
			// send status mail
			$this->_updateOrderStatus($new_order_status,$send_info,$this->data['txn_id'],$callback_message);
            //return true;


		} else {
            //LOG: callback data if orders_id (invoice in POST ) not found
			$log_data['module'] = 'xt_paypal';
			$log_data['orders_id'] = '0';
			$log_data['class'] = 'error';
			$log_data['error_msg'] = 'no orders_id';
			$log_data['error_data'] = serialize($this->data);
			$this->_addLogEntry($log_data);

		}
	header("HTTP/1.0 200 OK");   
    }
    
}
?>