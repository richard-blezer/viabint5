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
$no_index_tag = true;

if(isset($page->page_action) && $page->page_action != ''){

	switch ($page->page_action) {

		case 'edit_customer' :
			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

			unset($_SESSION['customer']->error);

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_customer_top')) ? eval($plugin_code) : false;

			$customer_data =  $_SESSION['customer']->customer_info;

			$customer_data['customers_email_address_confirm'] = $customer_data['customers_email_address'];


			if(!empty($customer_data['customers_default_language'])){
				$selected_lang = $customer_data['customers_default_language'];
			}else{
				$selected_lang = $language->code;
			}

			if(!empty($customer_data['customers_default_currency'])){
				$selected_curr = $customer_data['customers_default_currency'];
			}else{
				$selected_curr = $currency->code;
			}


			$customer_tpl_data = array('show_company'=> _STORE_ACCOUNT_COMPANY == 'true' ? 1:0,
									   'show_vat'=> _STORE_ACCOUNT_COMPANY_VAT_CHECK == 'true' ? 1:0,
									   'lang_data' => $language->_getLanguageList('store'),
									   'selected_lang' => $selected_lang,
									   'currency_data' => $currency->_getCurrencyList('store'),
									   'selected_curr' => $selected_curr
			);

			if (isset ($_POST['action']) && $_POST['action']=='edit_account'){

				$data_array = $_POST;
				$data_array['customers_id'] = $_SESSION['registered_customer'];
				($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_customer_update_data')) ? eval($plugin_code) : false;

				$_SESSION['customer']->_checkCustomerData($data_array, 'update');
				if(!$_SESSION['customer']->error){
					$_SESSION['customer']->_buildCustomerData($data_array, 'update', true, false);
					$customer_data = $_SESSION['customer']->customerData;
					$_SESSION['customer']->_customer($_SESSION['registered_customer']);
				}

			}

			if($customer_data['success']==true){
				$tmp_link  = $xtLink->_link(array('page'=>$customer_data['link_target']));
				$info->_addInfoSession(SUCCESS_ACCOUNT_UPDATED,'success');
				($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_customer_link_target')) ? eval($plugin_code) : false;
				$xtLink->_redirect($tmp_link);
			}

			$tpl_data = array('message'=>$info->info_content);
			$tpl_data = array_merge($tpl_data, $customer_tpl_data, $customer_data);
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:create_account_data')) ? eval($plugin_code) : false;

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'edit_customer')),TEXT_EDIT_ACCOUNT);

			$template = new Template();
			$tpl = '/'._SRV_WEB_CORE.'pages/edit_account.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:create_account_bottom')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);

			break;

		case 'address_overview' :
			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

			$countries = new countries('true','store');

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:address_overview_top')) ? eval($plugin_code) : false;

			$address_data = $_SESSION['customer']->_getAdressList($_SESSION['registered_customer']);

			$address_count = count($address_data);
			$address_max_count = _STORE_ADDRESS_BOOK_ENTRIES;

			if($address_count < $address_max_count)
			$add_address = 1;

			$customer_tpl_data = array('addresses_data'=> $address_data,
									   'address_count' => $address_count,
									   'address_max_count' => $address_max_count,
									   'add_new_address' => $add_address
			);

			$tpl_data = array('message'=>$info->info_content);
			$tpl_data = array_merge($tpl_data, $customer_tpl_data);

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:address_overview_data')) ? eval($plugin_code) : false;

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'address_overview')),TEXT_PAGE_TITLE_ADDRESS_BOOK);

			$template = new Template();
			$tpl = '/'._SRV_WEB_CORE.'pages/address_book.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:address_overview_bottom')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
			break;

		case 'edit_address' :
			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));


			unset($_SESSION['customer']->error);

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_address_top')) ? eval($plugin_code) : false;

			$countries = new countries('true','store');
			$gender_data = array(array('id'=>'m', 'text'=>TEXT_MALE), array('id'=>'f', 'text'=>TEXT_FEMALE));
			if (_STORE_ACCOUNT_COMPANY=='true') $gender_data = array_merge($gender_data,array(array('id'=>'c','text'=>TEXT_COMPANY_GENDER)));

			$address_type_array = array (array ('id' => '0', 'text' => TEXT_NONE),
			array ('id' => 'default', 'text' => TEXT_DEFAULT_ADDRESS),
			array ('id' => 'payment', 'text' => TEXT_PAYMENT_ADDRESS),
			array ('id' => 'shipping', 'text' => TEXT_SHIPPING_ADDRESS)
			);

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_address_types')) ? eval($plugin_code) : false;

			$customer_data = array();

			if(!empty($_GET['abID'])){
				$customer_data = $_SESSION['customer']->_buildAddressData($_SESSION['registered_customer'], '', (int)$_GET['abID']);
			}elseif($_POST['address_book_id']){
				$customer_data = $_SESSION['customer']->_buildAddressData($_SESSION['registered_customer'], '', (int)$_POST['address_book_id']);
			}

			$customer_data['old_address_class'] = $customer_data['address_class'];

			if (isset ($_POST['action']) && $_POST['action']=='edit_address'){

				$_POST['old_address_class'] = $customer_data['old_address_class'];

				$data_array = $_POST;
				$data_array['customers_id'] = $_SESSION['registered_customer'];
				
				($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_address_update_data')) ? eval($plugin_code) : false;

				$_SESSION['customer']->_checkCustomerAddressData($data_array);
                
                //delete slashes in $_POST to save it into DB
                foreach($data_array as $key=>$val){
                    $data_array[$key] = stripslashes($val);
                }
                
				if(!$_SESSION['customer']->error){
					if(!empty($data_array['address_book_id'])){
						$_SESSION['customer']->_buildCustomerAddressData($data_array, 'update', true, false);
					}else{
						$_SESSION['customer']->_buildCustomerAddressData($data_array, 'insert', true, false);
					}

					$_SESSION['customer']->_customer($data_array['customers_id']);

					$customer_data = array_merge($_SESSION['customer']->{'customer'.'_'.$_POST['address_class'].'_address'}, $customer_data);
					$customer_data['success'] = true;
				}
				//if not success
				else {
					$customer_data = $data_array;
				}
			}

			if($customer_data['success']==true){

				if(empty($_POST['adType'])){
					$tmp_link  = $xtLink->_link(array('page'=>'customer', 'paction'=>'address_overview', 'conn'=>'SSL'));
					//$_SESSION['customer']->_setAdress($customer_data['address_book_id'],$_POST['adType']);
					$info->_addInfoSession(SUCCESS_ADDRESS_UPDATED,'success');
				}

				if($_POST['adType']=='shipping'){
					$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'shipping', 'conn'=>'SSL'));
					$_SESSION['customer']->_setAdress($customer_data['address_book_id'],$_POST['adType']);
					unset($_SESSION['selected_shipping']);
				}

				if($_POST['adType']=='payment'){
					$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment', 'conn'=>'SSL'));
					$_SESSION['customer']->_setAdress($customer_data['address_book_id'],$_POST['adType']);
					unset($_SESSION['selected_payment']);
				}

				if($_POST['adType']=='default'){
					$tmp_link  = $xtLink->_link(array('page'=>'checkout', 'paction'=>'payment', 'conn'=>'SSL'));
					$_SESSION['customer']->_setAdress($customer_data['address_book_id'],$_POST['adType']);
					unset($_SESSION['selected_payment']);
				}

				($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_address_link_target')) ? eval($plugin_code) : false;
				$xtLink->_redirect($tmp_link);
			}

			// in case selected country is empty, use default country instead
			$selected_country = $customer_data['customers_country_code'];
			if ($selected_country == '') {
				$selected_country = _STORE_COUNTRY;
			}
			
			$customers_federal_state_code = $customer_data['customers_federal_state_code'];
			
			$customer_tpl_data = array('show_gender'=> _STORE_ACCOUNT_GENDER == 'true' ? 1:0,
									   'show_birthdate' => _STORE_ACCOUNT_DOB == 'true' ? 1:0,
									   'show_company'=> _STORE_ACCOUNT_COMPANY == 'true' ? 1:0,
									   'show_vat'=> _STORE_ACCOUNT_COMPANY_VAT_CHECK == 'true' ? 1:0,
									   'show_suburb'=> _STORE_ACCOUNT_SUBURB == 'true' ? 1:0,
									   'show_federal_states'=> _STORE_ACCOUNT_FEDERAL_STATES == 'true' ? 1:0,
									   'country_data' => $countries->countries_list_sorted,
									   'gender_data' => $gender_data,
									   'selected_country' => $selected_country,
									   'default_country' => _STORE_COUNTRY,
									   'address_type' => $address_type_array,
									   'customers_federal_state_code' => $customers_federal_state_code,
									   'adType' =>$_GET['adType']
			);
			//to display on input text
            foreach($customer_data as $key=>$val){
                $customer_data[$key] = htmlspecialchars($val, ENT_COMPAT);
            }
            
			$tpl_data = array('message'=>$info->info_content);
			$tpl_data = array_merge($tpl_data, $customer_tpl_data, $customer_data);
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_address_data')) ? eval($plugin_code) : false;

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'edit_address','params'=>'abID='.(int)$_GET['abID'])),TEXT_EDIT_ADDRESS);

			$template = new Template();
			$tpl = '/'._SRV_WEB_CORE.'pages/edit_address.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:edit_address_bottom')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);

			break;

		case 'delete_address' :

			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

			$del_data = $_SESSION['customer']->_deleteAddressData((int)$_GET['abID'], $_SESSION['registered_customer']);

				$tmp_link  = $xtLink->_link(array('page'=>'customer', 'paction'=>'address_overview'));
				$info->_addInfoSession($del_data['message'],$del_data['message_type']);
				($plugin_code = $xtPlugin->PluginCode('module_customer.php:delete_address_link_target')) ? eval($plugin_code) : false;
				$xtLink->_redirect($tmp_link);

			break;

		case 'order_info' :
			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:order_info_top')) ? eval($plugin_code) : false;
			$_GET['oid'] = (int)$_GET['oid'];
			$order = new order($_GET['oid']);
	
			$check_order = $order->_checkOrderId($_GET['oid']);

			if($check_order != true){
				$xtLink->_redirect($xtLink->_link(array('page'=>'customer')));
			}

			$customer_tpl_data = array('registered_customer'=>true, 'order_data' => $order->order_data, 'order_products'=>$order->order_products, 'order_total_data'=>$order->order_total_data, 'total'=>$order->order_total, 'order_history'=>$order->order_history);

			$tpl_data = array('message'=>$info->info_content);

			$tpl_data = array_merge($tpl_data, $customer_tpl_data);
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:order_info_data')) ? eval($plugin_code) : false;

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'order_info','params'=>'oid='.(int)$_GET['oid'])),TEXT_PAGE_TITLE_ACCOUNT_HISTORY_INFO);

			$template = new Template();
			$tpl = '/'._SRV_WEB_CORE.'pages/account_history_info.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:order_info_bottom')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
			break;

		case 'download_overview':
			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:download_overview_top')) ? eval($plugin_code) : false;

			// download ?
			if (isset($_GET['order']) && isset($_GET['media']) && isset($_GET['opid'])) {
				include_once(_SRV_WEBROOT.'xtFramework/classes/class.download.php');
				$download = new download();
				$download->_deleteOutOfDateLinks();
				$download->serveFile($_GET['order'],$_GET['media'], $_GET['opid']);
			}

			$order = new order();
			$download_list = $order->_getDownloadList();
			//	__debug($download_list);
			$customer_tpl_data = array('registered_customer'=>true, 'download_data' => $download_list);

			$tpl_data = array('message'=>$info->info_content);

			$tpl_data = array_merge($tpl_data, $customer_tpl_data);

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'download_overview')),TEXT_ACCOUNT_DOWNLOADS);

			$template = new Template();
			$tpl = '/'._SRV_WEB_CORE.'pages/download_history.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:download_overview_data')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
			break;

		case 'order_overview' :
			if(!$_SESSION['registered_customer'])
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'login')));

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:order_overview_top')) ? eval($plugin_code) : false;
			$order = new order();
			$order_list = $order->_getOrderList();
			$customer_tpl_data = array('registered_customer'=>true, 'order_data' => $order_list['data'], 'count'=>$order_list['count'], 'pages'=>$order_list['pages']);

			$tpl_data = array('message'=>$info->info_content);

			$tpl_data = array_merge($tpl_data, $customer_tpl_data);

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'order_overview')),TEXT_ACCOUNT_ORDERS);

			$template = new Template();
			$tpl = '/'._SRV_WEB_CORE.'pages/account_history.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:order_overview_data')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
			break;

		case 'login' :
			require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/check_pw.inc.php';
			require _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.bruto_force_protection.php';
			$bruto_force = new bruto_force_protection();
            
            adodb_sess_gc(2880);

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:login_top')) ? eval($plugin_code) : false;

			$countries = new countries('true','store');

			$gender_data = array(array('id'=>'m', 'text'=>TEXT_MALE), array('id'=>'f', 'text'=>TEXT_FEMALE));
			if (_STORE_ACCOUNT_COMPANY=='true') $gender_data = array_merge($gender_data,array(array('id'=>'c','text'=>TEXT_COMPANY_GENDER)));

			$customer_data =  array();
			if (isset ($_POST['action']) && $_POST['action']=='add_customer'){
				$customer_data = $_SESSION['customer']->_registerCustomer($_POST);
			}

			if($customer_data['success']==true){
				$_SESSION['cart']->_restore();
				($plugin_code = $xtPlugin->PluginCode('module_customer.php:register_success')) ? eval($plugin_code) : false;

				$link_array=array('page'=>$filter->_filter($_POST['page']), 'conn'=>'SSL');
				if ($_POST['paction']!='') $link_array=array_merge($link_array,array('paction'=>$filter->_filter($_POST['paction'])));
				$tmp_link  = $xtLink->_link($link_array);
				$snap_link = $brotkrumen->_getSnapshot();
				if ($snap_link != false) $tmp_link = $snap_link;
				($plugin_code = $xtPlugin->PluginCode('module_customer.php:login_link_target')) ? eval($plugin_code) : false;
				$xtLink->_redirect($tmp_link);
			}

			$phone_refix = '';
            if (_STORE_SHOW_PHONE_PREFIX=='true')
            {
                $phone_prefix = $countries->_buildCountriesPhonePrefix(true,'store');
            }
			
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:pre_data')) ? eval($plugin_code) : false;

			$customer_tpl_data = array('show_gender'=> _STORE_ACCOUNT_GENDER == 'true' ? 1:0,
									   'show_birthdate' => _STORE_ACCOUNT_DOB == 'true' ? 1:0,
									   'show_company'=> _STORE_ACCOUNT_COMPANY == 'true' ? 1:0,
									   'show_vat'=> _STORE_ACCOUNT_COMPANY_VAT_CHECK == 'true' ? 1:0,
									   'show_suburb'=> _STORE_ACCOUNT_SUBURB == 'true' ? 1:0,
									   'show_privacy'=> XT_PRIVACYCHECK_SHOW == 'true' ? 1:0,
									   'show_federal_states'=> _STORE_ACCOUNT_FEDERAL_STATES == 'true' ? 1:0,
									   'country_data' => $countries->countries_list_sorted,
									   'phone_prefix' => $phone_prefix,
									   'gender_data' => $gender_data,
									   'selected_country' => $selected_country,
									   'default_country' => _STORE_COUNTRY,
                                        'currency' => $currency->code,
			);

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:post_data')) ? eval($plugin_code) : false;
			// login
			if (isset ($_POST['action']) && $_POST['action']=='login'){
				$email = $filter->_filter($_POST['email']);
				$password = $filter->_filter($_POST['password']);

				$store_sql = "and shop_id = ".$store_handler->shop_id."";
				$status_sql = "and account_type != 1";

				($plugin_code = $xtPlugin->PluginCode('module_customer.php:pre_login')) ? eval($plugin_code) : false;

				// bruto force check
				if (!$bruto_force->_isLocked($email) || $email=='') {

					$record = $db->Execute("SELECT customers_id, customers_password, customers_status, customers_default_currency, customers_default_language FROM ".TABLE_CUSTOMERS." WHERE customers_email_address = '".$email."' ".$login_sql." ".$store_sql." ".$status_sql."");
					if ($record->RecordCount()==1) {

						$pw_check = _checkPW($password, $record->fields['customers_password']);

						if($pw_check == true){
                            
                            // switch to other language if needed
                            $language->_getLanguage($record->fields['customers_default_language']);
                            $_SESSION['selected_language'] = $record->fields['customers_default_language'];
                
							$_SESSION['registered_customer'] = $record->fields['customers_id'];
							$_SESSION['customer']->_customer($record->fields['customers_id']);
							$customers_status->_getStatus($record->fields['customers_status']);				

							$_SESSION['cart']->_restore();
							
							if ($_SESSION['cart']->content_count > 0) {
								$info->_addInfoSession(TEXT_LOGIN_CART_MERGED, 'warning');
								$tmp_link = $xtLink->_link(array('page'=>'cart'));
							}
							else $tmp_link = $xtLink->_link(array('page'=>'customer','conn'=>'SSL'));	

							($plugin_code = $xtPlugin->PluginCode('module_customer.php:success_login')) ? eval($plugin_code) : false;

							// saved snapshot ?
							$snap_link = $brotkrumen->_getSnapshot();
							if ($snap_link != false) $tmp_link = $snap_link;
							
							($plugin_code = $xtPlugin->PluginCode('module_customer.php:success_login_snap')) ? eval($plugin_code) : false;
							
							$xtLink->_redirect($tmp_link);

						}else{
							if ($email!='') {
								$bruto_force->escalateLoginFail($email);
								$info->_addInfo(sprintf(ERROR_LOGIN_COUNT,$bruto_force->failed,$bruto_force->lock_time));
							} else {
								$info->_addInfo(ERROR_LOGIN);
							}
							($plugin_code = $xtPlugin->PluginCode('module_customer.php:failed_login')) ? eval($plugin_code) : false;
						}
					}else{
						($plugin_code = $xtPlugin->PluginCode('module_customer.php:failed_login')) ? eval($plugin_code) : false;
						if ($email!='') {
							$bruto_force->escalateLoginFail($email);
							$info->_addInfo(sprintf(ERROR_LOGIN_COUNT,$bruto_force->failed,$bruto_force->lock_time));
						} else {
							$info->_addInfo(ERROR_LOGIN);
						}
					}
				} else {
					($plugin_code = $xtPlugin->PluginCode('module_customer.php:locked_login')) ? eval($plugin_code) : false;
					$info->_addInfo(sprintf(ERROR_LOGIN_LOCKED,$bruto_force->lock_time));
				}

			}

			// validate verification code
			if (isset ($_GET['action']) && $_GET['action']=='check_code'){

				$key = $filter->_filter($_GET['remember']);
				$key_data = explode(':',$key);
				$id = (int)$key_data[0];
				$key = $key_data[1];

				if ($id=='' or $key=='') {
					$info->_addInfo(ERROR_REMEMBER_KEY_ERROR);
				} else {

					$store_sql = "and shop_id = ".$store_handler->shop_id."";

                    ($plugin_code = $xtPlugin->PluginCode('module_customer.php:check_code_sql')) ? eval($plugin_code) : false;

					$record = $db->Execute("SELECT customers_id FROM ".TABLE_CUSTOMERS." WHERE password_request_key = '".$key."' and customers_id='".$id."' ".$store_sql."");
					if ($record->RecordCount()==1) {
						$info->_addInfo(SUCCESS_PASSWORD_SEND,'success');
						$remember_customer = new customer;
						$remember_customer->_customer($id);
						$remember_customer->_sendNewPassword();
					} else {
						$info->_addInfo(ERROR_REMEMBER_KEY_ERROR);
					}
				}
				//	$add_data = array('password_mode'=>'send_success');
			}

			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'login')),TEXT_LOGIN);

			$template = new Template();
			$tpl_data = array('message'=>$info->info_content);
			$tpl_data = array_merge($tpl_data, $customer_tpl_data, $customer_data);
			$tpl = '/'._SRV_WEB_CORE.'pages/login.html';

			if ($_REQUEST['sr'])
			{
				require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.order_edit_tools.php';
				$payLoad = order_edit_tools::parseSignedRequest($_REQUEST['sr'], 'O7JzSZdYEg3LEmiwXNL'); // XT_ORDER_EDIT_SECRET_KEY TODO in plg config
				if ( ! $payLoad || ! $payLoad['adminUser'] || ! is_array($payLoad['adminUser']) || ! $payLoad['adminUser']['user_id'] || ! $payLoad['userEmail'])
				{
					die('Please login');
				}

				$adminUser = $payLoad['adminUser'];
				$userEmail = $payLoad['userEmail'];

				$_SESSION['orderEditAsUser'] = $userEmail;
				$_SESSION['orderEditAdminUser'] = $adminUser;

				$a = array('asUser' => $userEmail);
				$tpl_data = array_merge($tpl_data, $a);
				$tpl = _SRV_WEBROOT._SRV_WEB_TEMPLATES.'xt_grid/xtCore/pages/login_order_edit.html';
			}

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:login_tpl_data')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
			break;

        case 'logoff' :
            $customers_language = $_SESSION['customer']->customer_info['customers_default_language'];
            if (array_key_exists('selected_language', $_SESSION) && !empty($_SESSION['selected_language']))
                $customers_language = $_SESSION['selected_language'];
            
            $params = session_get_cookie_params();
            
            if ( (empty($params['domain'])) && (empty($params['secure'])) ) {
            	setcookie(session_name(), '', time()-3600, $params['path']);
            } elseif (empty($params['secure'])) {
            	setcookie(session_name(), '', time()-3600, $params['path'], $params['domain']);
            } else {
            	setcookie(session_name(), '', time()-3600, $params['path'], $params['domain'], $params['secure']);
            }
            
			session_destroy();
			$xtLink->_redirect($xtLink->_link(array('page'=>'customer', 'paction'=>'logged_off', 'conn'=>'SSL', 'params'=>'language='.$customers_language)));
			break;

		case 'logged_off' :
			$template = new Template();
			$tpl_data = array('message'=>$info->info_content);
			$tpl = '/'._SRV_WEB_CORE.'pages/logoff.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:logged_off_tpl_data')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);
			break;

		case 'password_reset' :
          
			// validate captcha code
			if (isset ($_POST['action']) && $_POST['action']=='check_captcha'){
				
				// check for email
                $email = $filter->_filter($_POST['email']);
				$store_sql = "and shop_id = ".$store_handler->shop_id."";
				$status_sql = "and account_type != 1";

                ($plugin_code = $xtPlugin->PluginCode('module_customer.php:password_reset_sql')) ? eval($plugin_code) : false;
				
				$record = $db->Execute("SELECT customers_id FROM ".TABLE_CUSTOMERS." WHERE customers_email_address = '".$email."' ".$store_sql." ".$status_sql."");
				if ($record->RecordCount()==1) {
					$captcha_plugin = true;
                    ($plugin_code = $xtPlugin->PluginCode('module_customer.php:page_password_reset_captcha_top')) ? eval($plugin_code) : false;
                    if($captcha_plugin){
                        include _SRV_WEBROOT.'/xtFramework/library/captcha/php-captcha.inc.php';
                        if (PhpCaptcha::Validate($_POST['captcha'])) {
                            $info->_addInfo(SUCCESS_CAPTCHA_VALID,'success');
                            $remember_customer = new customer;
                            $remember_customer->_customer($record->fields['customers_id']);
                            $remember_customer->_sendPasswordOptIn();
							$captcha_show = 'false';
                        } else {
                            $info->_addInfo(ERROR_CAPTCHA_INVALID);
                        }
                    }else{
                        ($plugin_code = $xtPlugin->PluginCode('module_customer.php:page_password_reset_captcha_validator')) ? eval($plugin_code) : false;
                    }    
				} else {
					$info->_addInfo(ERROR_MAIL_NOT_FOUND);
				}
               //without captcha 
			}elseif(isset ($_POST['email'])){

                $email = $filter->_filter($_POST['email']);
				$store_sql = "and shop_id = ".$store_handler->shop_id."";
				$status_sql = "and account_type != 1";
                $record = $db->Execute("SELECT customers_id FROM ".TABLE_CUSTOMERS." WHERE customers_email_address = '".$email."' ".$store_sql." ".$status_sql."");
				if ($record->RecordCount()==1) {
                    $info->_addInfo(SUCCESS_CAPTCHA_VALID,'success');
                    $remember_customer = new customer;
                    $remember_customer->_customer($record->fields['customers_id']);
                    $remember_customer->_sendPasswordOptIn();
                } else {
					$info->_addInfo(ERROR_MAIL_NOT_FOUND);
				}
            }
            
            $captcha = 'true';
            if(_STORE_CAPTCHA != 'Standard' && _STORE_CAPTCHA != 'ReCaptcha'){
                $captcha = 'false';
            }elseif( _STORE_CAPTCHA == 'ReCaptcha' && $xtPlugin->active_modules['xt_recaptcha']!=true){
            	$captcha = 'false';
            }
           
			$add_data = array('captcha_link'=>$xtLink->_link(array('default_page'=>'captcha.php','conn'=>'SSL')));

            ($plugin_code = $xtPlugin->PluginCode('module_customer.php:page_password_reset_captcha_show')) ? eval($plugin_code) : false;
            
			$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer','paction'=>'password_reset')),TEXT_PASSWORD_RESET_PAGE);

			$template = new Template();
			$tpl_data = array('message'=>$info->info_content,'captcha'=>$captcha,'captcha_show'=>$captcha_show);
			if (is_array($add_data)) $tpl_data = array_merge($tpl_data,$add_data);
			$tpl = '/'._SRV_WEB_CORE.'pages/password_reset.html';
			($plugin_code = $xtPlugin->PluginCode('module_customer.php:logged_off_tpl_data')) ? eval($plugin_code) : false;
			$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);

			break;

			default:

			($plugin_code = $xtPlugin->PluginCode('module_customer.php:page')) ? eval($plugin_code) : false;
			if(isset($plugin_return_value))
			return $plugin_return_value;
	}

}else{
	($plugin_code = $xtPlugin->PluginCode('module_customer.php:default_top')) ? eval($plugin_code) : false;

	if($_SESSION['registered_customer']){

		$order = new order();
		$order_list = $order->_getOrderList();

		$download_flag = $order->_hasDownloads();

		$customer_tpl_data = array('registered_customer'=>true, 'order_data' => $order_list['data'],'download_flag'=>$download_flag);
		($plugin_code = $xtPlugin->PluginCode('module_customer.php:default_registered_bottom')) ? eval($plugin_code) : false;

	}else{

		$customer_tpl_data = array('registered_customer'=>false);
		($plugin_code = $xtPlugin->PluginCode('module_customer.php:default_unregistered_bottom')) ? eval($plugin_code) : false;

	}

	$brotkrumen->_addItem($xtLink->_link(array('page'=>'customer')),TEXT_ACCOUNT);
	$tpl_data = array('message'=>$info->info_content);

	$tpl_data = array_merge($tpl_data, $customer_tpl_data);

	$template = new Template();
	$tpl = '/'._SRV_WEB_CORE.'pages/account.html';
	($plugin_code = $xtPlugin->PluginCode('module_customer.php:default_tpl_data')) ? eval($plugin_code) : false;
	$page_data = $template->getTemplate('smarty', $tpl, $tpl_data);

}
?>