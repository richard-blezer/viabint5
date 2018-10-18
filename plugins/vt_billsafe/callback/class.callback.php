<?php
	class callback_vt_billsafe extends callback {
	  	var $version = '1.0';
		var $log_callback = true;

		function debug($value, $text='') {
			return; // no debugging
			$f = fopen(_SRV_WEBROOT._SRV_WEB_PLUGINS.'vt_billsafe/callback/debug.txt', "a+");
			fprintf($f, "%s\n\n--------\n\n",$text."\n\n".var_export ($value, true));
			fclose($f);
		}

		function __construct() {
			preg_match('#vt_billsafe&([^&]+)=([^&]+)&#', $_SERVER['REQUEST_URI'], $m) ;
			$this->session = $m[1] . '=' . $m[2];
			session_name($m[1]);
			session_id($m[2]);
			session_start();
		}

		function _link($arr) {
			if($arr['conn'] == 'SSL' && _SYSTEM_SSL == true)
				$tmp_link = rtrim(_SYSTEM_BASE_HTTPS, '/');
			else
				$tmp_link = rtrim(_SYSTEM_BASE_URL, '/');

			$tmp_link .= _SRV_WEB;
			$tmp_link .= 'index.php?page=' . $arr['page'] . '&page_action=' . $arr['paction'];

			return $tmp_link;
		}

		function sendOrderMail($oid, $uid) {
			$order = new order($oid, $uid);
			$order->_sendOrderMail($oid);
		}

		function process() {
			global $filter, $db, $xtLink, $info;//, $store_handler;

			require_once _SRV_WEBROOT.'plugins/vt_billsafe/classes/class.SoapApi.php';
			$SoapApi = new SoapApi();

			$response = $SoapApi->transactionResult($_GET['token']);

			$oID = (int)$response->custom;

			if ($oID>0 && is_numeric($oID)) {
				$rs = $db->Execute("SELECT	* FROM " . TABLE_ORDERS . " WHERE orders_id =? ",array((int)$oID));

				if ($rs->RecordCount()==0) {
					$log_data['module'] = 'vt_billsafe';
					$log_data['orders_id'] = (int)$oID;
					$log_data['class'] = 'error';
					$log_data['error_msg'] = 'order '. $oID . ' in shop not found';
					$log_data['error_data'] = serialize($response);
					$this->_addLogEntry($log_data);

					$url = $this->_link(array('page'=>'checkout', 'paction'=>'payment'));
					$info->_addInfoSession(ERROR_PAYMENT);

					header( 'Location: ' . $url);
					exit (0);
				}

				$this->orders_id = $rs->fields['orders_id'];
				$this->customers_id = $rs->fields['customers_id'];
				$this->xt_po_module = $rs->fields['payment_code'];

				if($response->ack == 'OK') {
					if($response->status == 'ACCEPTED') {
						$log_data = array();
						$log_data['module'] = 'vt_billsafe';
						$log_data['orders_id'] = $oID;
						$log_data['transaction_id'] = $response->transactionId;
						$log_data['class'] = 'callback_data';
						$log_data['callback_data'] = serialize($response);
						$this->_addLogEntry($log_data);

						$new_order_status = VT_BILLSAFE_STATUS_SUCCESS;
						$this->sendOrderMail($oID, $this->customers_id);
						$send_mail = VT_BILLSAFE_STATUS_SUCCESS_MAIL;

						$this->_updateOrderStatus($new_order_status,$send_mail,$txn_log_id);
						$url =  $this->_link(array('page'=>'checkout', 'paction'=>'payment_process', 'conn'=>'SSL')) .  '&' . $this->session;

						header( 'Location: ' . $url);
						exit (0);
					} elseif($response->status == 'DECLINED') {
						$log_data['module'] = 'vt_billsafe';
						$log_data['orders_id'] = (int)$oID;
						$log_data['class'] = 'error';
						$log_data['error_msg'] = 'PAYMENT DECLINED';
						$log_data['error_data'] = serialize($response);
						$this->_addLogEntry($log_data);

						$new_order_status = VT_BILLSAFE_STATUS_FAILED;
						$send_mail = 'false';

						$this->_updateOrderStatus($new_order_status,$send_mail,$txn_log_id);

						$_SESSION['BILLSAFEDecline'] = true;

						$url = $this->_link(array('page'=>'checkout', 'paction'=>'payment'));
						$info->_addInfoSession(VT_BILLSAFE_PAYMENT_DECLINED);

						header( 'Location: ' . $url);
						exit (0);
					}
				} else {
					$log_data['module'] = 'vt_billsafe';
					$log_data['orders_id'] = '0';
					$log_data['class'] = 'error';
					$log_data['error_msg'] = 'No Order ID from Billsafe';
					$log_data['error_data'] = serialize($response);
					$this->_addLogEntry($log_data);

					$url = $this->_link(array('page'=>'checkout', 'paction'=>'payment'));
					$info->_addInfoSession(ERROR_PAYMENT);

					header( 'Location: ' . $url);
					exit (0);
				}
			} else {
				$log_data['module'] = 'vt_billsafe';
				$log_data['orders_id'] = '0';
				$log_data['class'] = 'error';
				$log_data['error_msg'] = 'No Order ID from Billsafe';
				$log_data['error_data'] = serialize($response);
				$this->_addLogEntry($log_data);

				$url = $this->_link(array('page'=>'checkout', 'paction'=>'payment'));
				$info->_addInfoSession(ERROR_PAYMENT);

				header( 'Location: ' . $url);
				exit (0);
			}
		} // function process()
	} // class callback_vt_masterpayment
?>