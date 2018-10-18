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

$show_index_boxes = false;

($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_first')) ? eval($plugin_code) : false;

if(!$_SESSION['registered_customer']) {
	$tmp_link  = $xtLink->_link(array('page'=>'checkout','paction'=>'shipping','conn'=>'SSL'));
	$brotkrumen->_setSnapshot($tmp_link);
	$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login','conn'=>'SSL')));
}

// redirect if customer is not allowed to see prices
if (_CUST_STATUS_SHOW_PRICE=='0')
$xtLink->_redirect($xtLink->_link(array('page'=>'cart')));

if(!empty($checkout_id)){
	$table_id = $checkout_id;
}else{
	$table_id = $store_handler->shop_id;
}

$checkout = new checkout();

// Add Payment and Shipping Classes:

if(isset($_POST['action'])){

	switch ($_POST['action']) {
		case 'shipping' :
			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_shipping_top')) ? eval($plugin_code) : false;

			$tmp_shipping_data = $checkout->_getShipping();

			$shipping_data = $tmp_shipping_data[$_POST['selected_shipping']];

			$shipping_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$shipping_data['shipping_dir'].'/classes/';
			$shipping_class_file = 'class.'.$shipping_data['shipping_code'].'.php';

			if (file_exists($shipping_class_path . $shipping_class_file)) {
				require_once($shipping_class_path.$shipping_class_file);
				$shipping_module_data = new $shipping_data['shipping_code']($shipping_data);
			}
				
			$shipping_data_array = array('customer_id' => $_SESSION['registered_customer'],
											 'qty' => $shipping_data['shipping_qty'],
											 'name' => $shipping_data['shipping_name'],
											 'model' => $shipping_data['shipping_code'],
											 'key_id' => $shipping_data['shipping_id'],
											 'price' => $shipping_data['shipping_price']['plain_otax'],
											 'tax_class' => $shipping_data['shipping_tax_class'],
											 'sort_order' => $shipping_data['shipping_sort_order'],
											 'type' => $shipping_data['shipping_type']
			);
			$_SESSION['cart']->_deleteSubContent($shipping_data_array['type']);
			$_SESSION['cart']->_addSubContent($shipping_data_array);

			$checkout->_setShipping($_POST['selected_shipping']);

			$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment', 'conn'=>'SSL'));
			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_shipping_bottom')) ? eval($plugin_code) : false;
			$xtLink->_redirect($tmp_link);
			break;

		case 'payment' :

			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_payment_top')) ? eval($plugin_code) : false;

			$tmp_payment_data = $checkout->_getPayment();

            $_payment = $_POST['selected_payment'];
            if (strpos($_payment,':')) {
                $_payments = explode(':',$_payment);
                $_payment = $_payments[0];
                $_payment_sub = $_payments[1];
            }
            
			$payment_data = $tmp_payment_data[$_payment];

			$payment_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$payment_data['payment_dir'].'/classes/';
			$payment_class_file = 'class.'.$payment_data['payment_code'].'.php';

			if (file_exists($payment_class_path . $payment_class_file)) {
				require_once($payment_class_path.$payment_class_file);
				$payment_module_data = new $payment_data['payment_code']($payment_data);
			}


			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_payment_check')) ? eval($plugin_code) : false;

			if($payment_data['payment_price']['plain_otax']){
				// payment discount ?
                
                if ($payment_data['payment_price']['discount']==1) {
                 
                } else {
                
                $payment_data_array = array('customer_id' => $_SESSION['registered_customer'],
											'qty' => $payment_data['payment_qty'],
											'name' => $payment_data['payment_name'],
											'model' => $payment_data['payment_code'],
											'key_id' => $payment_data['payment_id'],
											'price' => $payment_data['payment_price']['plain_otax'],
											'tax_class' => $payment_data['payment_tax_class'],
											'sort_order' => $payment_data['payment_sort_order'],
											'type' => $payment_data['payment_type']
				);
                }

			}
			
			$_SESSION['cart']->_deleteSubContent('payment');
			if(!empty($payment_data_array)){
				($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_payment_data')) ? eval($plugin_code) : false;
				$_SESSION['cart']->_addSubContent($payment_data_array);
			}

			$checkout->_setPayment($_POST['selected_payment']);

			if(is_data($_POST['conditions_accepted']) && $_POST['conditions_accepted'] == 'on'){
				$_SESSION['conditions_accepted'] = 'true';
			}
			unset($_SESSION['order_comments']);
			if (is_data($_POST['comments'])) {
				$_SESSION['order_comments']=$filter->_filter($_POST['comments']);
			}

			$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'confirmation', 'conn'=>'SSL'));
			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_payment_bottom')) ? eval($plugin_code) : false;
			$xtLink->_redirect($tmp_link);
			break;

		case 'process' :
			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_process_top')) ? eval($plugin_code) : false;

			
		//	__debug($_POST);
		//	die('-');
			// check if T&C accepted // required by ecommerce button law germany
			$_check_error = false;
	    	if (_STORE_TERMSCOND_CHECK == 'true') {
        		if($_POST['conditions_accepted'] != 'on'){
        			$_check_error=true;
            		$info->_addInfoSession(ERROR_CONDITIONS_ACCEPTED);
        		}
    		}

            if (_STORE_DIGITALCOND_CHECK=='true' && ($_SESSION['cart']->type=='virtual' || $_SESSION['cart']->type=='mixed')) {
                if($_POST['withdrawal_reject_accepted'] != 'on'){
                    $_check_error=true;
                    $info->_addInfoSession(TEXT_DIGITALCOND_CHECK_CHECK_ERROR);
                }
            }
    		
    		($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_process_check')) ? eval($plugin_code) : false;
    		
    		if ($_check_error==true) {
    			    $tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'confirmation', 'conn'=>'SSL'));
            		$xtLink->_redirect($tmp_link);
    		}
    		

			
			$shop_id = $store_handler->shop_id;

			$shipping_code = $_SESSION['selected_shipping'];
			$payment_code = $_SESSION['selected_payment'];
			$subpayment_code = '';
			if ($_SESSION['selected_payment_sub']!='') $subpayment_code=$_SESSION['selected_payment_sub'];

			// Shipping
			$tmp_shipping_data = $checkout->_getShipping();
			$shipping_data = $tmp_shipping_data[$shipping_code];
			$shipping_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$shipping_data['shipping_dir'].'/classes/';
			$shipping_class_file = 'class.'.$shipping_data['shipping_code'].'.php';

			if (file_exists($shipping_class_path . $shipping_class_file)) {
				require_once($shipping_class_path.$shipping_class_file);
				$shipping_module_data = new $shipping_data['shipping_code']();
			}


			// Payment
			$tmp_payment_data = $checkout->_getPayment();
			$payment_data = $tmp_payment_data[$payment_code];

			$payment_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$payment_data['payment_dir'].'/classes/';
			$payment_class_file = 'class.'.$payment_data['payment_code'].'.php';

			if (file_exists($payment_class_path . $payment_class_file)) {
				require_once($payment_class_path.$payment_class_file);
				$payment_module_data = new $payment_data['payment_code']();
			}
			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_process_payment_data')) ? eval($plugin_code) : false;
			
			$currency_code = $currency->code;
			$currency_value = $currency->value_multiplicator;

			$account_type = $_SESSION['customer']->customer_info['account_type'];

			$orders_status = $system_status->_getSingle('order_status', 'default', 'true', 'id');

			$allow_tax = $customers_status->customers_status_show_price_tax;


			if(_SYSTEM_SAVE_IP=='true'){
				if($_SERVER["HTTP_X_FORWARDED_FOR"]){
					$customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
				}else{
					$customers_ip = $_SERVER["REMOTE_ADDR"];
				}
			}
			
			$comments = '';
			if (isset($_SESSION['order_comments'])) $comments = $_SESSION['order_comments'];

			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_process_pre_data')) ? eval($plugin_code) : false;
			
			if ($shipping_data['shipping_code']!=$_SESSION['cart']->show_sub_content["shipping"]["products_model"])
			{
				$shipping_data3 = $checkout->_getShipping();
				$shipping_data2 = $shipping_data3[$_SESSION['selected_shipping']];
				$shipping_data_array = array('customer_id' => $_SESSION['registered_customer'],
													 'qty' => $shipping_data2['shipping_qty'],
													 'name' => $shipping_data2['shipping_name'],
													 'model' => $shipping_data2['shipping_code'],
													 'key_id' => $shipping_data2['shipping_id'],
													 'price' => $shipping_data2['shipping_price']['plain_otax'],
													 'tax_class' => $shipping_data2['shipping_tax_class'],
													 'sort_order' => $shipping_data2['shipping_sort_order'],
													 'type' => $shipping_data2['shipping_type']
					);
				$_SESSION['cart']->_deleteSubContent($shipping_data_array['type']);
				$_SESSION['cart']->_addSubContent($shipping_data_array);
				$_SESSION['cart']->_refresh();
			}
			
			$order_data = array('payment_code' => $payment_code,
								'subpayment_code'=>$subpayment_code,
									'shipping_code' => $shipping_code,
									'currency_code' => $currency_code,
									'currency_value' => $currency_value,
									'orders_status' => $orders_status,
									'account_type' => $account_type,
									'allow_tax' => $allow_tax,
									'comments' => $comments,
									'customers_id' => $_SESSION['registered_customer'],
									'shop_id' => $shop_id,
									'customers_ip' => $customers_ip,
                                    'delivery'=>$_SESSION['customer']->customer_shipping_address,
                                    'billing'=>$_SESSION['customer']->customer_payment_address
			);

			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_process_data')) ? eval($plugin_code) : false;

			$order = new order();

            if($_SESSION['cart']->content_count == 0){
                $url=$xtLink->_link(array('page'=>'cart'));
                $xtLink->_redirect($url);
            }

			if(empty($_SESSION['last_order_id'])){
				$processed_data = $order->_setOrder($order_data, 'complete', 'insert');
				$_SESSION['last_order_id'] = $processed_data['orders_id'];
			}else{
				$processed_data = $order->_setOrder($order_data, 'complete', 'update', $_SESSION['last_order_id']);
			}

			$order = new order($_SESSION['last_order_id'],$_SESSION['customer']->customers_id);

			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_proccess_order_processed')) ? eval($plugin_code) : false;
			
				
			/**
			 * If payment module is for external PSP redirect to PSP and return to payment_process
			 */
			if ($payment_module_data->external == true) {
				$_pspurl = $payment_module_data->pspRedirect($processed_data);
				$xtLink->_redirect($_pspurl);
				break;
			}

			/**
			 * if payment module is for external PSP with iframe solution
			 */
			if ($payment_module_data->iframe == true) {
				$_pspurl = $payment_module_data->IFRAME_URL;
				$xtLink->_redirect($_pspurl);
				break;
			}

			/**
			 * if payment module is for external PSP with POST form require
			 */
			if (isset($payment_module_data->post_form) && $payment_module_data->post_form == true) {
				$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'pay', 'conn'=>'SSL'));
				$xtLink->_redirect($tmp_link);
				break;
			}

			$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'success', 'conn'=>'SSL'));
			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_proccess_bottom')) ? eval($plugin_code) : false;
			$_SESSION['success_order_id'] = $_SESSION['last_order_id'];
			$order->_sendOrderMail($_SESSION['last_order_id']);

			unset($_SESSION['last_order_id']);
			unset($_SESSION['selected_shipping']);
			unset($_SESSION['selected_payment']);
			unset($_SESSION['conditions_accepted']);
			$_SESSION['cart']->_resetCart();
			$xtLink->_redirect($tmp_link);
			break;


			($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_process_switch')) ? eval($plugin_code) : false;

	}
}


/**
 * payment processing part (checkout -> redirect to payment -> return to payment_process)
 */
if($page->page_action=='payment_process'){
     include _SRV_WEBROOT._SRV_WEB_CORE.'pages/page_action/checkout.payment_process.php';   
}


/**
 *
 *
 * BUILD PAGE (DISPLAY)
 *
 *
 */

$shipping_address = $_SESSION['customer']->customer_shipping_address;
$payment_address = $_SESSION['customer']->customer_payment_address;

$shipping_data = array();
$payment_data = array();

$shipping_data = $checkout->_selectShipping();
$payment_data = $checkout->_selectPayment();
$address_data = $_SESSION['customer']->_getAdressList($_SESSION['registered_customer']);
$address_count = count($address_data);
$address_max_count = _STORE_ADDRESS_BOOK_ENTRIES;

if($address_count <= $address_max_count)
$add_address = 1;

$tmp_address_array[] = array('id'=>'', 'text'=>TEXT_SELECT);
$address_data =  array_merge($tmp_address_array,$address_data);

$page_data = $page->page_action;

if($_SESSION['cart']->type == 'virtual'){
	if($page_data=='shipping'){
		$xtLink->_redirect($xtLink->_link(array('page'=>'checkout', 'paction'=>'payment','conn'=>'SSL')));	
	}
}

($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_pages')) ? eval($plugin_code) : false;

/**
 * show shipping page
 */
if ($page_data=='shipping') {
    include _SRV_WEBROOT._SRV_WEB_CORE.'pages/page_action/checkout.shipping.php'; 
}


($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_selections')) ? eval($plugin_code) : false;

$checkout_data  = array('page_action'=>$page_data,
							'address_data' => $address_data,
							'add_new_address' => $add_address,
							'shipping_address' => $shipping_address,
							'payment_address' => $payment_address,
							'shipping_data' => $shipping_data,
							'payment_data' => $payment_data
);

/**
 * show payment page
 */
if($page->page_action=='payment'){
    include _SRV_WEBROOT._SRV_WEB_CORE.'/pages/page_action/checkout.payment.php'; 
}

/**
 * show confirmation page
 */
if($page->page_action=='confirmation'){
    include _SRV_WEBROOT._SRV_WEB_CORE.'/pages/page_action/checkout.confirmation.php';
}

/**
* 
* PAGE ACTION "PAY"
* 
*/
if ($page->page_action=='pay') {
    include _SRV_WEBROOT._SRV_WEB_CORE.'/pages/page_action/checkout.pay.php';
}

if ($page->page_action=='pay_frame') {
    include _SRV_WEBROOT._SRV_WEB_CORE.'pages/page_action/checkout.pay_frame.php';
}

/**
 * show success page
 */
if($page->page_action=='success'){
    include _SRV_WEBROOT._SRV_WEB_CORE.'pages/page_action/checkout.success.php';
    //reset $info on checkout sites
    $info->_showInfo('store');
}

($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_page_actions')) ? eval($plugin_code) : false;      

if($_GET['error']) {
	$param ='/[^a-zA-Z0-9_-]/';
	$field=preg_replace($param,'',$_GET['error']);
	if (defined($field))
	$info->_addInfo(constant($field));
}

$tpl_data = array('message'=>$info->info_content);
$tpl_data = array_merge($tpl_data, $checkout_data);
($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_data')) ? eval($plugin_code) : false;
if (isset($_SESSION['cart']->discount)) {
    if ($_SESSION['cart']->discount != 'false') {
        if (is_array($data)) {
        $data = array_merge($data,array('discount'=>$_SESSION['cart']->discount));
        } else {
        $data = array('discount'=>$_SESSION['cart']->discount);
        }
    }
}

$template = new Template();
$tpl = '/'._SRV_WEB_CORE.'pages/checkout.html';
($plugin_code = $xtPlugin->PluginCode('module_checkout.php:checkout_bottom')) ? eval($plugin_code) : false;
$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
?>