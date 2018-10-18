<?php
 
include_once dirname(__FILE__).'/../../../xtFramework/library/nusoap/nusoap.php';

class XTPaymentsConnector{

	public $wsdl;
	public $useWSDL;
	
	// PPP settings
	public $RETURN_URL;
	public $CANCEL_URL;
	public $BACK_URL;
	public $NOTIFY_URL;
	public $TARGET_URL;
	public $PPPMerchantID;
	public $PPPWebsiteID;
	public $PPPSecretKey;
	
	public $GWMerchantname;
	public $GWPassword;
	
	function __construct($wsdl = "", $useWSDL = true) {
		
		$this->setWSDL($wsdl, $useWSDL);
		$this->setURLS();
	}
	
	public function setWSDL($wsdl, $useWSDL) {
		
		$this->wsdl = $wsdl;
		$this->useWSDL = $useWSDL;
	}
	
	public function setURLS($RETURN_URL="", $CANCEL_URL="", $BACK_URL="", $NOTIFY_URL=""){
	
		global $xtLink;
		
		$this->RETURN_URL  = !empty($RETURN_URL) ? $RETURN_URL : $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment_process'));
		$this->CANCEL_URL  = !empty($CANCEL_URL) ? $CANCEL_URL : $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment','params'=>'error_payments=ERROR_PAYMENT'));
		$this->BACK_URL    = !empty($BACK_URL)   ? $BACK_URL   : $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment'));
		$this->NOTIFY_URL  = !empty($NOTIFY_URL) ? $NOTIFY_URL : _SYSTEM_BASE_URL._SRV_WEB.'index.php?page=callback&page_action=xt_payments';
		
		if(XT_PAYMENTS_TEST_MODE){
            $this->TARGET_URL    = XT_PAYMENTS_TEST_PPPURL;
            $this->PPPMerchantID = XT_PAYMENTS_TEST_PPPMERCHANTID;
			$this->PPPWebsiteID  = XT_PAYMENTS_TEST_PPPWEBSITEID;
			$this->PPPSecretKey  = XT_PAYMENTS_TEST_PPPSECRETKEY;
			$this->GWMerchantname  = XT_PAYMENTS_TEST_GWMERCHANTNAME;
			$this->GWPassword  = XT_PAYMENTS_TEST_GWPASSWORD;
		}
		else{
            $this->TARGET_URL    = XT_PAYMENTS_LIVE_PPPURL;
            $this->PPPMerchantID = XT_PAYMENTS_LIVE_PPPMERCHANTID;
            $this->PPPWebsiteID  = XT_PAYMENTS_LIVE_PPPWEBSITEID;
            $this->PPPSecretKey  = XT_PAYMENTS_LIVE_PPPSECRETKEY;
			$this->GWMerchantname  = XT_PAYMENTS_LIVE_GWMERCHANTNAME;
            $this->GWPassword  = XT_PAYMENTS_LIVE_GWPASSWORD;
        }
	}
	
	public function getPaymentMethodDetailsByMerchant($parameters){
	
		$client = new nusoap_client($this->wsdl, $this->useWSDL);
		$parameters["amount"] = "";
		try{
		
			$soap_response = $client->call('getMerchantSitePaymentOptions', $parameters); 
			
			return $soap_response;
			
		}catch(nusoap_fault $fault){
			//echo $fault->getMessage();
			die('error');
			return false;
		}
	}
	
	public function getPPPUrl($RETURN_URL, $CANCEL_URL, $BACK_URL, $NOTIFY_URL){
		
		$this->setURLS($RETURN_URL, $CANCEL_URL, $BACK_URL, $NOTIFY_URL);
	
		global $xtLink,$filter,$db,$countries,$language;

		$orders_id = (int)$_SESSION['last_order_id'];

		$rs = $db->Execute("SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id=?", array($orders_id));

		$order = new order($orders_id,$rs->fields['customers_id']);

		$random = md5(time());
        $random  = $random.$this->PPPMerchantID.md5($this->PPPWebsiteID);
        $random = md5($random);
        $trid = substr($random,0,16);
        $trid =  'XT4' . $trid;
		
		$this->transaction_id = $trid;
        $timestamp = date("Y-m-d.H:i:s");
        $total_amount = round($order->order_total['total']['plain'],2);
       
		$data = array();
		$data['merchant_id'] = $this->PPPMerchantID;
        $data['merchant_unique_id'] = $this->transaction_id;
        $data['merchant_site_id'] = $this->PPPWebsiteID;
        // items
        $products = $order->order_products;
        $data['numberofitems']=count($products); 
        $item_value = 0;
        foreach ($products as $key => $prod) {
            $id = $key+1;
            $data['item_name_'.$id]=($prod['products_name']);
            $data['item_amount_'.$id]=$prod['products_price']['plain'];
            
            $item_value+=$prod['products_final_price']['plain'];
            $data['item_number_'.$id]=$id;
            $data['quantity_'.$id]=(int)$prod['products_quantity'];
            $data['item_quantity_'.$id]=(int)$prod['products_quantity'];
        } 
        
        
        $data['total_amount'] = $total_amount;
        
        $handling=$total_amount-$item_value;
        $discount = 0;
        // discount
        $order_total = $order->order_total_data; 
        if (is_array($order_total)) {        	
        	foreach ($order_total as $key => $val) {        		
        		if ($val['orders_total_price']['plain']<0) {
        			$discount+=($val['orders_total_price']['plain']*-1);
 	      		}   		
        	}   	
        }
        
        $handling +=$discount;
        $data['handling'] = $handling;
        
        $data['handling']=round($data['handling'],2);
        if($data['handling'] == -0) $data['handling'] = 0;
        
        if ($discount>0) {
        	$data['discount']=round($discount,2);
        } 

        // customer details
        $data['first_name'] = $order->order_data['delivery_firstname'];
        $data['last_name'] = $order->order_data['delivery_lastname'];
        $data['email'] = $order->order_data['customers_email_address'];
        $data['address1'] = $order->order_data['delivery_street_address'];
        $data['zip'] =  $order->order_data['delivery_postcode'];
        $data['city'] = $order->order_data['delivery_city'];
        $data['phone1'] = $order->order_data['billing_phone'];
        $data['country'] = $order->order_data['billing_country_code'];
        
        /* shipping address */
        $data['shippingFirstName'] = $order->order_data['delivery_firstname'];
        $data['shippingLastName'] = $order->order_data['delivery_lastname'];
        $data['shippingMail'] = $order->order_data['customers_email_address'];
        $data['shippingAddress'] = $order->order_data['delivery_street_address'];
        $data['shippingZip'] =  $order->order_data['delivery_postcode'];
        $data['shippingCity'] = $order->order_data['delivery_city'];
        $data['shippingState'] = "";
        //$data['shippingPhone'] = "$order->order_data['delivery_phone']";
        $data['shippingPhone'] = " ";
        $data['shippingCell'] = "";
        $data['shippingCountry'] = $order->order_data['delivery_country_code'];
        /* shipping address end */
        
        $param ='/[^0-9]/';
		$var=preg_replace($param,'',$data['phone1']);
        $data['phone1'] = $var;
        
		// additional fields from 'select method' page
		if(isset($_SESSION["pppAdditionalParameters"]) && is_array($_SESSION["pppAdditionalParameters"])){
			
			foreach($_SESSION["pppAdditionalParameters"] as $additionalKey => $additionalValue){
			
				$data[$additionalKey] = $additionalValue;
			}
		}
		//dummy, based on merchant settings
		//$data['phone2'] = '1';
		//$data['phone3'] = '1';
		//$data['address2'] = '1';
		
        // urls
		$data['notify_url'] = $this->NOTIFY_URL;
		$data['success_url'] = $this->RETURN_URL;
		$data['error_url'] = $this->CANCEL_URL;
		$data['pending_url'] =  $this->RETURN_URL;
		$data['back_url'] =  $this->BACK_URL;
		

        if (!is_object($countries)) {
            $countries = new countries('true');
        }
        
        $subpayment = $_SESSION['selected_payment_sub'];
        if ($subpayment=='cc_card') $subpayment = 'cc_card';
        
        $data['payment_method'] = $subpayment;
        
		if($data['payment_method']!='cc_card' && $data['payment_method']!='dc_card'){

			$data['skip_billing_tab'] = 'true';
			$data['skip_review_tab'] = 'true';
		}
		
        // other parameters
        $data['version']='4.0.0';
        // remove spaces 
        $data['time_stamp']=$timestamp;   
        $data['currency'] = $order->order_data['currency_code'];
        $data['invoice_id'] = $orders_id;
        $data['customData'] = $orders_id;
        $data['userid'] = $rs->fields['customers_id'];
        $data['encoding'] = 'utf-8'; 

        include 'plugins/xt_payments/conf/localeconf.php'; 

        $locale = $_pp_locale['default'];
        if (isset($_pp_locale[$order->order_data['language_code']]))
        $locale = $_pp_locale[$order->order_data['language_code']];
        $data['merchantLocale'] = $locale;

		$params = '';
		$cnt = 0;
		$secret = $this->PPPSecretKey;
		foreach ($data as $key => $value) {
			
			$secret .=(stripslashes(($value)));
			
			($cnt == 0 ) ? $params .= '?'.$key.'='.urlencode(stripslashes(($value))) : $params .= '&amp;'.$key.'='.urlencode(stripslashes(($value)));
			$cnt++;
		}
		
		// calculate md5
		$secret_key=md5($secret);
		$params.='&checksum='.$secret_key; 

		// set trans ID 
		$db->Execute("UPDATE ".TABLE_ORDERS." SET orders_data=? WHERE orders_id=?", array($this->transaction_id, $orders_id));
		return $this->TARGET_URL.$params;
	}
	
	public function getUserdata($parameters){
	
		$client = new nusoap_client($this->wsdl, $this->useWSDL);
		
		try{
		
			$soap_response = $client->call('GetUserdata', 
				$parameters
			); 
			
			return $soap_response;
			
		}catch(nusoap_fault $fault){
			//echo $fault->getMessage();
			die('error');
			return false;
		}
	}
	
	public function registerXTpayments($parameters){
	
		$client = new nusoap_client($this->wsdl, $this->useWSDL);
		
		try{
			//$parameters["domain"] = $_SERVER['HTTP_HOST'];
			$soap_response = $client->call('RegisterXTpayments', 
				array('data'=>$parameters)
			); 
			
			return $soap_response;
			
		}catch(nusoap_fault $fault){
			//echo $fault->getMessage();
			die('error');
			return false;
		}
	}
}