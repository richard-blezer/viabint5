<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

include_once 'class.GetPaymentUrl.php';
require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_payments/classes/class.paypal.php';


class xt_payments extends paypal_express{

    var $data=array();
    var $external = true;
    var $version = '2.1.0';
    var $subpayments = true;
    var $iframe = false;


    function email_check() {}

    function secret_word_check() {}

    function __construct(){
        global $xtLink,$xtPlugin;

        $this->RETURN_URL  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment_process', 'conn'=>'SSL', 'params'=>session_name().'='.session_id()));
        $this->CANCEL_URL  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment','params'=>'error_payments=ERROR_PAYMENT'));
        $this->BACK_URL    = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment'));
        $this->NOTIFY_URL  = _SYSTEM_BASE_URL._SRV_WEB.'index.php?page=callback&page_action=xt_payments';

        $this->ppp_subpayments = array('cc_card', 'dc_card');
		/*
		Check if we have paypal as selected payment method from xt_payments. If so, call parent constuctor.
		*/
        if ($_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true
            ||
            (isset($_REQUEST['action']) && $_REQUEST['action']==XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT)
			||
			$_SESSION[XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT]==true
			||
			(isset($_REQUEST['action']) && $_REQUEST['action']==XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT)
			||
			(isset($_SESSION['selected_payment_sub']) && $_SESSION['selected_payment_sub'] == XT_PAYMENTS_PAYPAL_CODE))
		{
            parent::__construct();
        }

        // facebook shop
        $urlsToRewrite = array('Cancel' => $this->CANCEL_URL,'ReturnExpress' => $this->RETURN_EXPRESS_URL);

        ($plugin_code = $xtPlugin->PluginCode('createCancelReturnUrls')) ? eval($plugin_code) : false;

        $this->CANCEL_URL = $urlsToRewrite['Cancel'];
        $this->RETURN_EXPRESS_URL = $urlsToRewrite['ReturnExpress'];
		
		/*
		Check if current country/currency are BR/BRL/
		If they are, hide CC payment option and check if 
			the service will return Cielo. If it doesnt
			then display CC payment option.
		*/
		global $language, $currency, $smarty;
		
		if(isset($_SESSION['customer']->customer_payment_address['customers_country_code']) 
			&& $_SESSION['customer']->customer_payment_address['customers_country_code'] == "BR" 
			&& isset($currency->code) 
			&& $currency->code == "BRL"
			) 
		{
			
			$this->build_payment_info(array("hideCC"=>"true"));
		}
    }
	
    function build_payment_info($data){
		$this->data = $data;
	}

    function pspRedirect($processed_data = array())
    {
        if($_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true){
            $url = paypal_express::getExpressUrl('return');
            return $url;
        }

		/*
		Check if selected method is paypal
		If it is and the type of payment is 'order' (auth/capture), 
			skip the regular flow and proceed using paypal proxy server
		*/
		
		if($_SESSION['selected_payment_sub'] == XT_PAYMENTS_PAYPAL_CODE){
			$orders_id = (int)$processed_data['orders_id'];
			$this->_setOrderId($orders_id);
			$this->paypalAuthCall('checkout');
			
			$url = $this->payPalURL.'&useraction=commit';
			
			return $url;
		} else{
			$paymentMethodPPPUrl = new PaymentMethodPPPUrl();
			$url = $paymentMethodPPPUrl->getPPPUrl($this->RETURN_URL, $this->CANCEL_URL, $this->BACK_URL, $this->NOTIFY_URL);
			return $url;
		}
    }

    function pspSuccess() {

        global $xtLink;

        if ($_SESSION[XT_PAYMENTS_PARAM_PPEXPRESS_CHECKOUT]==true || $_SESSION[XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT]==true) {

			$type = $_SESSION[XT_PAYMENTS_PARAM_PPREGULAR_CHECKOUT]==true ? "checkout" : "";
            $this->_setOrderId($_SESSION['last_order_id']);

            if($_SESSION['reshash']['REDIRECTREQUIRED']==true){

                $this->completeStandardCheckout($type);
                $url = $this->GIROPAY_URL.$_SESSION['reshash']['TOKEN'];
                $xtLink->_redirect($url);
                //return $url;
            }else{

                return $this->completeStandardCheckout($type);
            }

            die;
        }


        return true;
    }

    // try to extract xt payment from session
    function getApm($apmCode) {
        if ($_SESSION['APMGWList']) {
            foreach($_SESSION['APMGWList'] as $apm){
                if ($apm->APMCode==$apmCode){
                    return $apm;
                }
            }
        }
        return false;
    }

}