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
 # @version $Id: class.xt_billpay.php 4627 2011-03-30 21:23:46Z mzanier $
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

require_once _SRV_WEBROOT.'plugins/xt_billpay/classes/api/ipl_xml_api.php';
include_once _SRV_WEBROOT.'plugins/xt_billpay/classes/api/php5/ipl_module_config_request.php'; 
include_once _SRV_WEBROOT.'plugins/xt_billpay/classes/api/php5/ipl_preauthorize_request.php';
include_once _SRV_WEBROOT.'plugins/xt_billpay/classes/api/php5/ipl_capture_request.php';  
include_once _SRV_WEBROOT.'plugins/xt_billpay/classes/api/php5/ipl_cancel_request.php'; 
include_once _SRV_WEBROOT.'plugins/xt_billpay/classes/api/php5/ipl_invoice_created_request.php';  

class xt_billpay{

    var $data=array();
    var $external = false;
    var $version = '1.0';
    var $subpayments = true;
    var $iframe = false;
    var $logging = XT_BILLPAY_DEBUG;
    
    

    function __construct(){
        global $xtLink;
        $this->RETURN_URL  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment_process'));
        $this->CANCEL_URL  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment','params'=>'error=ERROR_PAYMENT'));
        $this->NOTIFY_URL  = $xtLink->_link(array('page'=>'callback', 'paction'=>'xt_billpay'));
        
        $this->log_dir = _SRV_WEBROOT.'plugins/xt_billpay/logs/';
        
        if (XT_BILLPAY_SANDBOX!='true') {
            $this->TARGET_URL = 'https://api.billpay.de/xml';
        } else {
            $this->TARGET_URL = 'https://test-api.billpay.de/xml/offline';
        }

        $this->allowed_subpayments = array('INVOICE','DID');
        
        $this->bp_merchant    = (int)XT_BILLPAY_MID;
        $this->bp_portal    = (int)XT_BILLPAY_PID;
        $this->bp_secure    = md5(XT_BILLPAY_BPSECURE);
    }
    

    function build_payment_info($data){

    }

    function pspRedirect($processed_data = array()) {
        global $xtLink,$filter,$db,$countries,$language;

    }
    
    
    /**
    * Anrede
    * 
    * @param mixed $gender
    * @return mixed
    */
    function _getCustomerSalutation($gender) {
        
        if ($salutation=='m') return 'Herr';
        if ($salutation=='f') return 'Frau';
        return 'Herr';
        
    }
    

    /**
    * check if customer is new or existing
    * 
    */
    function _getCustomerGroup() {
        global $db;
        $rs = $db->Execute("SELECT count(*) as count FROM ".TABLE_ORDERS." WHERE customers_id='".$_SESSION['customer']->customers_id."'");
        if ($rs->fields['count']>1) $group = 'e'; 
        $group = 'n';
        $rs = $db->Execute("SELECT account_type FROM ".TABLE_ORDERS." WHERE orders_id='".$_SESSION['last_order_id']."'");
        if ($rs->fields['account_type']=='1') $group='g';
        $this->customers_group = $group;
        return $group;

    }
    
    /**
    * get customers IP
    * 
    */
    function _getCustomerIp() {
        if($_SERVER["HTTP_X_FORWARDED_FOR"]){
            $customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }else{
            $customers_ip = $_SERVER["REMOTE_ADDR"];
        }
                
        return $customers_ip;
    }
    
    
    function _extractAddress($type) {
       global $order,$countries;
       $address = array();
       foreach ($order->order_data as $key => $val) {
           if (strstr($key,$type)) {
               $address[str_replace($type,'address',$key)]=$val;
           }
       }
       
       if (!is_object($countries)) {
            $countries = new countries('true');
        }
        
        $country  = $countries->_getCountryData($address['address_country_code']);
        $address['address_country_iso_3'] = $country['countries_iso_code_3'];
       
       return $address; 
    }
    
    
    /**
     * evaluate module config response
     *       
     */
    function showPayment() {
    	global $price;

    	$config = $this->getModuleConfig();
		
    	if (!$config) {
    		return false;
    	}
    	else {
			// Step 1: Is portal acitivated
	    	if (!$config['is_active']) {
				return false;
			}
			
			// Step 2: Is payment method allowed?
			if (!$config['is_allowed_invoice']) {
				$_SESSION['billpay_hide_invoice'] = true;
			}
	    	
			if (!$config['is_allowed_directdebit']) {
				$_SESSION['billpay_hide_directdebit'] = true;
			}
			
			// Step 3: Check min/max order values
			$cartTotal 	= round($_SESSION['cart']->content_total['plain']*100,0);
			$invoiceMin	= $config['minvalue_invoice'];
			$invoiceMax = $config['maxvalue_invoice'];
			$debitMin	= $config['minvalue_directdebit'];
			$debitMax 	= $config['maxvalue_directdebit'];
			
			if ($cartTotal < $invoiceMin || $cartTotal > $invoiceMax) {
				$this->_billpaylog('Order value out of range for invoice (Min: '.
					$invoiceMin.', max: '.$invoiceMax.', order: '.$cartTotal.')');
					
				$_SESSION['billpay_hide_invoice'] = true;
			}
			else {
				$_SESSION['billpay_hide_invoice'] = false;
			}
			
			if ($cartTotal < $debitMin || $cartTotal > $debitMax) {
				$this->_billpaylog('Order value out of range for direct debit (Min: '.
					$debitMin.', max: '.$debitMax.', order: '.$cartTotal.')');
					
				$_SESSION['billpay_hide_directdebit'] = true;
			}
			else {
				$_SESSION['billpay_hide_directdebit'] = false;
			}
			
			if ($_SESSION['billpay_hide_invoice'] && $_SESSION['billpay_hide_directdebit']) {
				return false;
			}
	        
	        return true;
    	}
    }

    /**
    * pre Auth request, is customer allwed to use this payment method ?
    * 
    */
    function preAuth() {
        global $language,$countries,$order,$db;
        
        
        $payment_method = $_SESSION['selected_payment'];
        $payment_method=explode(':',$payment_method);
        $payment_method=$payment_method[1];
        
        $orders_id = $_SESSION['last_order_id'];
        
        if (isset($_SESSION['selected_payment_sub'])) $payment_method =$_SESSION['selected_payment_sub'];
        
        switch ($payment_method) {
            case 'INVOICE':
                $billpay_paymenttype = 1;
                break;
            case 'DID':
                $billpay_paymenttype = 2;
                break;
            /*
                case 'RATE':
                $billpay_paymenttype = 3;
                break;
            */
            default:
                $billpay_paymenttype = 1; 
        }
        
        $req = new ipl_preauthorize_request($this->TARGET_URL, $billpay_paymenttype);
        $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
        
        
        $billing_address = $this->_extractAddress('billing');
        $delivery_address = $this->_extractAddress('delivery');

        // other shipping address ?
        $fields = count($billing_address);
        $addressCompare = (int)count(array_intersect_assoc($billing_address, $delivery_address));
        
        if ($order->order_customer['customers_dob']!='') {
            $dob = strtotime($order->order_customer['customers_dob']);  
            $dob = date("Ymd",$dob);
        }
        if (isset($_SESSION['billpay_gebdat'])) {
            $dob = strtotime($_SESSION['billpay_gebdat']);  
            $dob = date("Ymd",$dob);  
        }

        $req->set_customer_details(
                                    $_SESSION['customer']->customers_id,
                                    $this->_getCustomerGroup(),
                                    $this->_getCustomerSalutation($billing_address['address_gender']),
                                    '', // title
                                    $billing_address['address_firstname'],
                                    $billing_address['address_lastname'],
                                    $billing_address['address_street_address'],
                                    '', // streetno
                                    '', // address extra
                                    $billing_address['address_postcode'],
                                    $billing_address['address_city'],
                                    $billing_address['address_country_iso_3'], // iso 3!
                                    $order->order_customer['customers_email_address'],
                                    $billing_address['address_phone'],
                                    '', // cellphone
                                    $dob, // dob
                                    $order->order_data['language_code'],
                                    $this->_getCustomerIp(),
                                    'p');	// customer_group
        
         
        if ($addressCompare<$fields) {
           // if addresses don't match set shipping address
           $req->set_shipping_details(FALSE,
                                                $this->_getCustomerSalutation($delivery_address['address_gender']),
                                                '', // title
                                                $delivery_address['address_firstname'],
                                                $delivery_address['address_lastname'],
                                                $delivery_address['address_street_address'],
                                                '', // streetno
                                                '', // address extra
                                                $delivery_address['address_postcode'],
                                                $delivery_address['address_city'],
                                                $delivery_address['address_country_iso_3'], // iso 3!
                                                $order->order_data['customers_email_address'],
                                                $delivery_address['address_phone'],
                                                '' // cellphone
                                                ); 
        } else {
            $req->set_shipping_details(TRUE);
        }

        if ($payment_method=='DID') {   
          $req->set_bank_account($_SESSION['billpay_account_holder'], $_SESSION['billpay_account_number'], $_SESSION['billpay_account_blz']);  
        }
        
        // artikel
        foreach ($order->order_products as $id => $product) {
            
            $price_gross = $product['products_price']['plain'];
            $price_net = $product['products_price']['plain_otax'];
            $price_gross = round($price_gross,2);
            $price_net = round($price_net,2); 
            
            $price_gross=round($price_gross*100,0);
            $price_net=round($price_net*100,0);
            
            $req->add_article($product['products_id'],(int)$product['products_quantity'],$product['products_name'],'',$price_net,$price_gross);
            
        }
        
        // total, shipping etc
        $shipping_costs_net = 0;
        $shipping_costs_gross = 0;
        
        $other_costs_net = 0;
        $other_costs_gross = 0; 
        foreach ($order->order_total_data as $id => $total) {
            if ($total['orders_total_key']=='shipping' || $total['orders_total_key']=='payment') {
               $shipping_costs_net+=$total['orders_total_price']['plain_otax'];
               $shipping_costs_gross+=$total['orders_total_price']['plain'];  
            } else {
               $other_costs_net+=$total['orders_total_price']['plain_otax']; 
               $other_costs_gross+=$total['orders_total_price']['plain'];    
            }
        }
        
        $total_netto = $order->order_total['total_otax']['plain'];
        
        $total_netto = round($total_netto,2);
        $total_netto = round($total_netto*100,0);
        
        $total_brutto = $order->order_total['total']['plain'];
        
        $total_brutto = round($total_brutto,2);
        $total_brutto = round($total_brutto*100,0);
        
        $shipping_costs_net=round($shipping_costs_net,2);
        $shipping_costs_net=round($shipping_costs_net*100,0);
        
        $shipping_costs_gross=round($shipping_costs_gross,2);
        $shipping_costs_gross=round($shipping_costs_gross*100,0);
        
        $other_costs_net = round($other_costs_net,2);
        $other_costs_net = round($other_costs_net*100,0);
        if ($other_costs_net>0) $other_costs_net=$other_costs_net*(-1);
        
        $other_costs_gross = round($other_costs_gross,2);
        $other_costs_gross = round($other_costs_gross*100,0);
        if ($other_costs_gross>0) $other_costs_gross=$other_costs_gross*(-1);
        
        $req->set_total($other_costs_net, // rabatte Netto
                        $other_costs_gross, // rabatte Brutto
                        'Versandkosten', // Versandart
                        $shipping_costs_net, // versandkosten
                        $shipping_costs_gross, // versandkosten brutto
                        $total_netto, // total netto
                        $total_brutto, // total brutto
                        $order->order_data['currency_code'], // w#hrung
                        $orders_id);
        
        // history
        if ($this->customers_group=='e') {
            $_OrderHistory = $this->_getOrderHistory();
            
            if (is_array($_OrderHistory)) {
                
                foreach ($_OrderHistory as $historyPart)
                            {
                              $req->add_order_history($historyPart['hid'],
                                                        $historyPart['hdate'],
                                                      isset($historyPart['hamount']) ? $historyPart['hamount'] : 0,
                                                      isset($historyPart['hcurrency']) ? $historyPart['hcurrency'] : 'EUR' ,
                                                      $historyPart['hpaymenttype'],
                                                      $historyPart['hstatus']   
                                                      );
                            }    
            }
   
        }
        
                                    
        // agb
        $req->set_terms_accepted(true);
        
        $success = TRUE;
        $hidePayment = FALSE;
        $redirMessage = '';
        $logMessage = '';

       // __debug($order);
        
        $req->send();

        $error_msg = $req->get_merchant_error_message();
        if (!empty($error_msg) or $error_msg!='') {
       //    echo $error_msg;
           $log_data = array();
           $log_data['module'] = 'xt_billpay';
           $log_data['class'] = 'error';
           $log_data['orders_id'] = $orders_id;
           $log_data['error_msg'] = 'merchand error message';
           $log_data['error_data'] = $this->buildErrorMessage($req, true);
           $db->AutoExecute(TABLE_CALLBACK_LOG,$log_data,'INSERT');  
        }
        
        // customer message ?
        $customer_error_msg = $req->get_customer_error_message();
        if (!empty($customer_error_msg) or $customer_error_msg!='') {
            $this->display_error_message=$this->buildErrorMessage($req, XT_BILLPAY_SANDBOX == 'true' ? true : false);
        }        
      
		$xml = $req->get_request_xml();
		$this->_billpaylog($xml,'Preauthorize request XML'); 
		$xml = $req->get_response_xml();
		$this->_billpaylog($xml,'Preauthorize response XML');
       
       // status
       if ($req->get_status()=='APPROVED') {
           
           $this->transaction_id = (string)$req->get_bptid();
           $_SESSION['billpay_transaction_id']=$this->transaction_id;  
           $db->Execute("UPDATE ".TABLE_ORDERS." SET orders_data='".$this->transaction_id."' WHERE orders_id='".$orders_id."'"); 
           return true;   
       } else {
           if (XT_BILLPAY_ORDER_STATUS_FAILED>0 && XT_BILLPAY_ORDER_STATUS_FAILED !='') {
                    $order = new order($orders_id,$_SESSION['customer']->customers_id);
                    $callback_id = (string)$this->transaction_id;
                    if ($callback_id==null) $callback_id=''; 
                    $order->_updateOrderStatus(XT_BILLPAY_ORDER_STATUS_FAILED,'DENIED','false','true','IPN',$callback_id);
                    
                    $this->StatusLog($orders_id,$callback_id,'Denied',$req->get_merchant_error_message());        
            }
            
            if ($req->get_status()=='DENIED') {
            	$_SESSION['billpay_hide_payment'] = true;
            }
            
            return false;    
       }
        
    }
    
    private function _getOrderHistory() {
        global $db;
        $_return = array();
        $rs = $db->Execute("SELECT * FROM ".TABLE_ORDERS." WHERE orders_id!='".$_SESSION['last_order_id']."' and customers_id='".$_SESSION['customer']->customers_id."' ORDER by orders_id DESC LIMIT 0,20");
       // echo $rs->RecordCount();
        if ($rs->RecordCount()>0) {
           
            while (!$rs->EOF) {
           
                if (isset($tmp_order)) unset($tmp_order); 
                
                $tmp_order = new order($rs->fields['orders_id'],$_SESSION['customer']->customers_id);
          
                $total_brutto = $order->order_total['total']['plain'];
                $total_brutto = round($total_brutto,2);
                $total_brutto = round($total_brutto*100,0);
                
                // date
                $purchase_date = strtotime($rs->fields['date_purchased']);  
                $purchase_date = date("Ymd H:i:s",$purchase_date); 
           
                $_return[] = array(
                    'hid' => $rs->fields['orders_id'],
                    'hdate' => $purchase_date,
                    'hamount' => $total_brutto,
                    'hcurrency' => $tmp_order->order_data['currency_code'],
                    'hpaymenttype' => $this->_getPaymentMethod($tmp_order->order_data['payment_code']),
                    'hstatus' => $this->_getOrderStatus($rs->fields['orders_status'])
                    );     
                $rs->MoveNext();
            }
            
         //  die(); 
           return $_return; 
        } else {
            
            return false;
            
        }
        
    }
    
    private function _getOrderStatus($status_id) {
        $methods = explode(',',XT_BILLPAY_STATUS_PAID);
        if (in_array($status_id,$methods)) return 0;
        return 1;
    }
    
    /**
    * determine payment code
    * 
    * @param mixed $payment_code
    * @return mixed
    */
    private function _getPaymentMethod($payment_code) {
        
        switch ($payment_code) {
            
            case 'xt_invoice':
                return 6;
                break;
            case 'xt_billpay':
                return 7;
                break;
            case 'xt_saferpay':
            case 'pay_ogone':
            case 'pay_paymentpartner':
            case 'xt_qenta':
            case 'xt_moneybookers':
                return 1;
                break;
            case 'xt_paypal':
                return 4;
                break;
            case 'xt_sofortueberweisung':
                return 5;
                break;
            case 'xt_cashondelivery':
            case 'xt_cashpayment': 
                return 2;
                break;
            case 'xt_banktransfer':
                return 0;
            default:
                return 100;
                break;
              
        }
        
    }
    
    /**
    * capture order
    * 
    */
    function captureOrder() {
		global $order,$db;
        
		$orders_id = $_SESSION['last_order_id'];

		$req = new ipl_capture_request($this->TARGET_URL);
		$req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
         
		$total_brutto = $order->order_total['total']['plain'];
         
		$total_brutto = round($total_brutto,2);
		$total_brutto = round($total_brutto*100,0);
         
		$req->set_capture_params($_SESSION['billpay_transaction_id'],
			$total_brutto, $order->order_data['currency_code'],
			$orders_id, $_SESSION['customer']->customers_id);
                                         
		unset($_SESSION['billpay_transaction_id']);
         
		$req->send();
         
		$xml = $req->get_request_xml();
		$this->_billpaylog($xml,'Capture request XML');
		$xml = $req->get_response_xml();
		$this->_billpaylog($xml,'Capture respone XML');

		if($req->has_error()) {
            $this->display_error_message=$this->buildErrorMessage($req, XT_BILLPAY_SANDBOX == 'true' ? true : false);
    
            $log_data = array();
            $log_data['module'] = 'xt_billpay';
            $log_data['class'] = 'error';
            $log_data['orders_id'] = $orders_id;
            $log_data['error_msg'] = 'merchand error message';
            $log_data['error_data'] = $this->buildErrorMessage($req, true);
            $db->AutoExecute(TABLE_CALLBACK_LOG,$log_data,'INSERT');
            $db->Execute("UPDATE ".TABLE_ORDERS." SET orders_data='' WHERE orders_id='".$orders_id."'");
            
            if (XT_BILLPAY_ORDER_STATUS_FAILED>0 && XT_BILLPAY_ORDER_STATUS_FAILED !='') {
                    $order = new order($orders_id,$_SESSION['customer']->customers_id);
                   // $callback_id = (string)$req->get_bptid();
                    $callback_id=$_SESSION['billpay_transaction_id']; 
                    $order->_updateOrderStatus(XT_BILLPAY_ORDER_STATUS_FAILED,'Failed','false','true','IPN',$callback_id);                   
            }
            	// insert failed
            	$this->StatusLog($orders_id,$callback_id,'Failed',$req->get_merchant_error_message()); 
				return false;
            } 
            else {
                // accepted -> Set Status
                if (XT_BILLPAY_ORDER_STATUS_SUCCESS>0 && XT_BILLPAY_ORDER_STATUS_SUCCESS !='') {                  
                    $order = new order($orders_id,$_SESSION['customer']->customers_id);
                    $rs = $db->Execute("SELECT orders_data FROM ".TABLE_ORDERS." WHERE orders_id='".$orders_id."'");
                    $callback_id = $rs->fields['orders_data'];
                    if ($callback_id==null) $callback_id=''; 
                    $order->_updateOrderStatus(XT_BILLPAY_ORDER_STATUS_SUCCESS,'SUCCESS','false','true','IPN',$callback_id);
                }
                
                // insert success & bank account data
                $account=array();
                $account['account_holder']=$req->get_account_holder();
                $account['account_number']=$req->get_account_number();
                $account['bank_code']=$req->get_bank_code();
                $account['bank_name']=$req->get_bank_name();
                $account['invoice_reference']=$req->get_invoice_reference();
                $account['invoice_duedate']=$req->get_invoice_duedate();
                $this->StatusLog($orders_id,$callback_id,'Success','Captured',$account);
                return true;    
            }
            
    }
    
    /**
    * cancel Order
    * 
    * @param int $orders_id
    */
    function cancelOrder($orders_id) {
        global $db;

        $rs = $db->Execute("SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$orders_id."'");
        $order = new order($orders_id,$rs->fields['customers_id']);
        
        $req = new ipl_cancel_request($this->TARGET_URL);
        $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

        $total_brutto = $order->order_total['total']['plain'];
         
       /* $other_costs_gross = 0; 
        foreach ($order->order_total_data as $id => $total) {
            if ($total['orders_total_key']!='shipping') {
               $other_costs_gross+=$total['orders_total_price']['plain'];    
            }
        }
         if ($other_costs_gross>0) {
          $total_brutto-=$other_costs_gross;  
        }
        if ($other_costs_gross<0) {
          $total_brutto+=$other_costs_gross;   
        }*/
         $total_brutto = round($total_brutto,2);
         $total_brutto = round($total_brutto*100,0);
         
        $req->set_cancel_params($orders_id, $total_brutto, $order->order_data['currency_code']);
        

        $req->send();
        
		$xml = $req->get_request_xml();
		$this->_billpaylog($xml, 'Cancel request XML');
		$xml = $req->get_response_xml();
		$this->_billpaylog($xml, 'Cancel reponse XML');
        
        
        if ($req->has_error()) {
            $this->customer_error_msg = $req->get_customer_error_message();
    
            $log_data = array();
            $log_data['module'] = 'xt_billpay';
            $log_data['class'] = 'error';
            $log_data['orders_id'] = $orders_id;
            $log_data['error_msg'] = 'cancel error';
            $log_data['error_data'] = $this->buildErrorMessage($req, true);
            $db->AutoExecute(TABLE_CALLBACK_LOG,$log_data,'INSERT');
            
            $this->StatusLog($orders_id,'','Cancel Failed',$req->get_merchant_error_message());
            // insert cancel failure
        }
        else {
            // insert cancel success
            $this->StatusLog($orders_id,'','Cancel Success','');
        }
  
    }
    
    /**
    * invoiceCreated call
    * 
    * @param int $orders_id
    */
    function activateOrder($orders_id) {
        global $db;

        $rs = $db->Execute("SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$orders_id."'");
        $order = new order($orders_id,$rs->fields['customers_id']);
        
        $req = new ipl_invoice_created_request($this->TARGET_URL);
        $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

       
        $total_brutto = $order->order_total['total']['plain'];
         
       /* $other_costs_gross = 0; 
        foreach ($order->order_total_data as $id => $total) {
            if ($total['orders_total_key']!='shipping') {
               $other_costs_gross+=$total['orders_total_price']['plain'];    
            }
        }
         if ($other_costs_gross>0) {
          $total_brutto-=$other_costs_gross;  
        }
        if ($other_costs_gross<0) {
          $total_brutto+=$other_costs_gross;   
        }*/
         $total_brutto = round($total_brutto,2);
         $total_brutto = round($total_brutto*100,0);
       
       
        // XT_BILLPAY_SHIFTDAY       
        $req->set_invoice_params($total_brutto, $order->order_data['currency_code'], $orders_id, XT_BILLPAY_SHIFTDAY);

        
        $req->send();

		$xml = $req->get_request_xml();
		$this->_billpaylog($xml,'Invoice request XML');
		$xml = $req->get_response_xml();
        $this->_billpaylog($xml,'Invoice response XML');    
        
        if ($req->has_error()) {
            $this->customer_error_msg = $req->get_customer_error_message();
    
            $log_data = array();
            $log_data['module'] = 'xt_billpay';
            $log_data['class'] = 'error';
            $log_data['orders_id'] = $orders_id;
            $log_data['error_msg'] = 'activate error';
            $log_data['error_data'] = $this->buildErrorMessage($req, true);
            $db->AutoExecute(TABLE_CALLBACK_LOG,$log_data,'INSERT');
            
            $this->StatusLog($orders_id,'','invoiceCreated Failed',$req->get_merchant_error_message());
            // insert cancel failure
        }
        else {
            // insert cancel success
            $account=array();
            $account['account_holder']=$req->get_account_holder();
            $account['account_number']=$req->get_account_number();
            $account['bank_code']=$req->get_bank_code();
            $account['bank_name']=$req->get_bank_name();
            $account['invoice_reference']=$req->get_invoice_reference();
            $account['invoice_duedate']=$req->get_invoice_duedate();
            $this->StatusLog($orders_id,'','invoiceCreated Success','',$account);
        }
  
    }
    

    private function _billpaylog($content,$comment='',$filename='billpay.log') {
        if ($this->logging!='true') return;
         
        if ($content) {
            $fp = fopen($this->log_dir.$filename, "a+");
            if ($comment != '') {
            	fputs($fp,date("Y-m-d H:i:s").' - '.$comment.":\n");
            }
            fputs($fp,date("Y-m-d H:i:s").' - '.$content."\n\n");
            fclose($fp);
        }  
    }
    
    private function buildErrorMessage($req, $extended=false, $html=true) {
    	if ($extended) {
    		$lineBreak = $html ? '<br />' : ' ';
    		
    		return "HAENDLER: " . $req->get_merchant_error_message() . $lineBreak .
    			"KUNDE: " . $req->get_customer_error_message() . $lineBreak .
    			"ERROR CODE: " . $req->get_error_code();
    	}
    	else {
    		return $req->get_customer_error_message();
    	}
    }
    
    private function StatusLog($orders_id,$transaction_id='',$status,$note,$account_data='') {
         global $db;
        
        $data_array=array();
        $data_array['orders_id']=(int)$orders_id;
        $data_array['timestamp']=$db->BindTimeStamp(time());
        $data_array['transaction_id']=$transaction_id;
        $data_array['billpay_status']=$status;
        $data_array['billpay_note']=$note;
        
        if ($account_data!='') {
            $data_array=array_merge($data_array,$account_data);
        }
        
        $db->AutoExecute(TABLE_XT_BILLPAY,$data_array,'INSERT');
    }
    
    function pspSuccess() {
        return true;
    }

    
    function getBillpayAdmin($orders_id) {
        global $db;
        $template='';
        $rs = $db->Execute("SELECT * FROM ".TABLE_XT_BILLPAY." WHERE orders_id='".(int)$orders_id."' ORDER BY timestamp ASC");
        if ($rs->RecordCount()>0) {

        	// Get sub payment type
        	$rs2 = $db->Execute("SELECT subpayment_code, billpay_status FROM ".TABLE_ORDERS." LEFT OUTER JOIN ".TABLE_XT_BILLPAY." ON ".TABLE_ORDERS.".orders_id = ".TABLE_XT_BILLPAY.".orders_id AND billpay_status = 'invoiceCreated Success' WHERE ".TABLE_ORDERS.".orders_id='".(int)$orders_id."'");

        	$isInvoiceOrder 	= $rs2->fields['subpayment_code']=='INVOICE';
        	$isInvoiceCreated	= $rs2->fields['billpay_status']=='invoiceCreated Success';

            $template .='<img src="../plugins/xt_billpay/images/billpay_frontend.png" />';
            $template .= '&nbsp;&nbsp;<img src="../plugins/xt_billpay/images/';
            $template .= $isInvoiceOrder ? 'billpay-invoice.png' : 'billpay-debit.png';
			$template .= '"/>';
            $template .='<table width="100%" style="border:1px solid; border-collapse:collapse">'.
                '<tbody>'.
                '<tpl for="order_products" >';
            
            $activateText='Die Bestellung wurde noch nicht aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor dem Versand, in dem Sie den entsprechenden Status setzen.';

            while (!$rs->EOF) {
                $template.='<tr>'.
                    '<td style="text-align:left;border-bottom: 1px solid;">'.__define('TEXT_XT_BILLPAY_TIMESTAMP').': '.$rs->fields['timestamp'].'</td>'.
                   // '<td style="text-align:left;border-bottom: 1px solid;">'.__define('TEXT_XT_BILLPAY_TRANS_ID').': '.$rs->fields['transaction_id'].'</td>'.
                    '<td style="text-align:left;border-bottom: 1px solid;padding-right:5px">'.__define('TEXT_XT_BILLPAY_STATUS').': '.$rs->fields['billpay_status'].'</td>'.
                    '<td style="text-align:left;border-bottom: 1px solid;">'.__define('TEXT_XT_BILLPAY_NOTE').': '.$rs->fields['billpay_note'];

                   	// Invoice
                    if ($isInvoiceOrder) {
                    	$bankAccount = 'Kontoinhaber: '.$rs->fields['account_holder'].'<br />Geldinstitut: '.$rs->fields['bank_name'].'<br />BLZ: '.$rs->fields['bank_code'].'<br/>KTO: '.$rs->fields['account_number'];
                    
                       if (!$isInvoiceCreated) {
                       		if ($rs->fields['billpay_status'] == 'Success') {
                       			$template .= '<br />' . $activateText . '<br /><br />'.$bankAccount;
                       		}
                       }
                       else {
	                       	if ($rs->fields['billpay_status'] == 'invoiceCreated Success') {
	                       		$text='Die folgende Bankverbindung muss auf die Kundenrechnung &uuml;bernommen werden:';	
	                       		$dueDate = $rs->fields['invoice_duedate'];
	                       		$bankAccount.='<br/>F&auml;llig am: '.substr($dueDate,6,2).'.'.substr($dueDate,4,2).'.'.substr($dueDate,0,4);
	                       		$template .= '<br />' . $text . '<br /><br />'.$bankAccount;
	                       	}
                       }
                    }
                    else { // Direct debit
                    	if (!$isInvoiceCreated && $rs->fields['billpay_status'] == 'Success') {
                    		$template .= '<br />'.$activateText;
                    	}
                    }
                    
                    $template.'</td>'.
                '</tr>';
                $rs->MoveNext();
            }
            $template.='</tpl>'.
                '</tbody></table><br /><br />';
        }
        return $template;
    }
    
    /**
     * Get module coniguration parameters
     * 
     */
    function getModuleConfig() {
    	$config = $_SESSION['billpay_module_config'];
		if (!isset($config)) {
	    	$req = new ipl_module_config_request($this->TARGET_URL);
	        $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
	        
	        try {
	        	$req->send();
	        }
	        catch(Exception $e) {
	        	$this->_billpaylog($e->getMessage(), 'Error sending moduleConfig request');
	        	$_SESSION['billpay_module_config'] = false;
	        }
	        
	        $xml = $req->get_request_xml();
			$this->_billpaylog($xml,'ModuleConfig request XML'); 
			$xml = $req->get_response_xml();
			$this->_billpaylog($xml,'ModuleConfig response XML');
	        
	        if ($req->has_error()) {
	        	return false;
	        }
	        
	        $config = $req->get_config_data();
	        
	        $_SESSION['billpay_module_config'] = $config;
		}
		
		return $config;
    }
}
?>