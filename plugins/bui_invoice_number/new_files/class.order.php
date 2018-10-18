<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2008 xt:Commerce GmbH. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~~~~ xt:Commerce VEYTON 4.0 Enterprise IS NOT FREE SOFTWARE ~~~~~~~~~~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.order.php 931 2013-05-07 07:31:27Z m.hinsche $
 # @copyright xt:Commerce GmbH, www.xt-commerce.com
 #
 # @author Mario Zanier, xt:Commerce GmbH	mzanier@xt-commerce.com
 #
 # @author Matthias Hinsche					mh@xt-commerce.com
 # @author Matthias Benkwitz				mb@xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce GmbH, Bachweg 1, A-6091 Goetzens (AUSTRIA)
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class order {

	var $customer;
	var $oID;

	function order($oID=0, $cID=0) {
		global $db, $xtPlugin;
		if($cID > 0){
			$this->customer = $cID;
		}elseif($cID==-1) {
			$rs = $db->Execute("SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$oID."'");
			if ($rs->RecordCount()==1) $this->customer = $rs->fields['customers_id'];

		}else{
			$this->customer = $_SESSION['registered_customer'];
		}

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_order_id')) ? eval($plugin_code) : false;
		
		if($oID != 0){
			$this->oID = $oID;
			$check_order = $this->_checkOrderId($this->oID);
			if($check_order == true){
				$data = $this->_buildData($this->oID);
				while (list ($key, $value) = each($data)) {
					$this->$key = $value;
				}
			}else{
				return false;
			}
		}

	}

	function setOrderID($oID){
		$this->oID = $oID;
	}
	
	function _setOrder($data, $type='complete', $add_type = 'insert', $update_orders_id=''){
		global $xtPlugin, $price, $db, $language, $customers_status;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$order_data = array();

		if($type=='complete' && $add_type=='insert'){
			$order_data['customers_id'] = $data['customers_id'];
			$order_data['shop_id'] = $data['shop_id'];
			$order_data['customers_ip'] = $data['customers_ip'];
			$order_data['date_purchased'] = $db->BindTimeStamp(time());
		}

		if($type=='complete' || $type=='customer'){

			// Customer Data
			$customer_data = array();
			if(!empty($data['customer'])){
				$customer_data = $data['customer'];
			}else{
				$customer_data = '';
			}

			$customer_data = $this->_buildCustomerData($data['customers_id'], $customer_data);

			($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_customer_data')) ? eval($plugin_code) : false;

			// Shipping Data
			$delivery_data = array();
			if(!empty($data['delivery'])){
				$delivery_data = $data['delivery'];
			}else{
				$delivery_data = '';
			}

			$delivery_data = $this->_buildCustomerDeliveryAddress($data['customers_id'], $delivery_data);

			($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_delivery_data')) ? eval($plugin_code) : false;

			// Billing Data
			$billing_data = array();
			if(!empty($data['billing'])){
				$billing_data = $data['billing'];
			}else{
				$billing_data = '';
			}

			$billing_data = $this->_buildCustomerBillingAddress($data['customers_id'], $billing_data);

			($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_billing_data')) ? eval($plugin_code) : false;

			if(!empty($data['payment_code']))
			$order_data['payment_code'] = $data['payment_code'];

			if(!empty($data['subpayment_code']))
			$order_data['subpayment_code'] = $data['subpayment_code'];

			if(!empty($data['shipping_code']))
			$order_data['shipping_code'] = $data['shipping_code'];

			if(!empty($data['currency_code']))
			$order_data['currency_code'] = $data['currency_code'];

			if(!empty($data['currency_value']))
			$order_data['currency_value'] = $data['currency_value'];

			if(empty($data['language_code']))
			$data['language_code'] = $language->environment_language;

			$order_data['language_code'] = $data['language_code'];

			if(!empty($data['orders_status']))
			$order_data['orders_status'] = $data['orders_status'];

			if(!empty($data['account_type']))
			$order_data['account_type'] = $data['account_type'];

			if(!empty($data['allow_tax'])){
			$order_data['allow_tax'] = $data['allow_tax'];
			}else{
				if($customers_status->customers_status_show_price_tax == 1 || $customers_status->customers_status_add_tax_ot==1){
					$order_data['allow_tax'] = 1;
				}else{
					$order_data['allow_tax'] = 0;	
				}			
			}
			
			$order_data['comments'] = '';
			if(!empty($data['comments']))
			$order_data['comments'] = $data['comments'];

			($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_data_bottom')) ? eval($plugin_code) : false;

			$order_data = array_merge($order_data, $customer_data, $delivery_data, $billing_data);

			if($add_type=='update' && $update_orders_id){
				$order_data['orders_id'] = $update_orders_id;
			}

			$this->_saveCustomerData($order_data, $add_type);

			$data['orders_id'] = $this->data_orders_id;

		}

		if($data['orders_id'])
		$this->oID = $data['orders_id'];

		// Products
		if($type=='complete' || $type=='product'){

			if($add_type=='update'){
				$this->_deleteOrderProduct($data['orders_id'],'',true);
			}

			$p_data = $this->_buildProductData($data['orders_id'], $data['products']);
            

			($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_product_bottom')) ? eval($plugin_code) : false;
			while (list ($key, $value) = each($p_data)) {
				$this->_saveProductData($value, 'insert' ,true);
			}

		}

		// Total
		if($type=='complete' || $type=='total'){

			if($add_type=='update'){
				$this->_deleteOrderTotal($data['orders_id']);
			}

			$total_data =  $this->_buildTotalData($data['orders_id'], $data['total']);
			if(is_array($total_data)){
				while (list ($key, $value) = each($total_data)) {
					$this->oID = $data['orders_id'];
					$this->_saveTotalData($value, 'insert');
				}
			}else{
				$this->oID = $data['orders_id'];
				$total_array = array('orders_id'=>$data['orders_id'],
								   'orders_total_key'=>'None',
								   'orders_total_model'=>'',
								   'orders_total_name'=>'None',
								   'orders_total_price'=>'0',
								   'orders_total_tax'=>'0',
								   'orders_total_tax_class'=>'0',
								   'orders_total_quantity'=>'1');

				$this->_saveTotalData($total_array, 'insert');
			}

		}

		if ($add_type=='insert') {
			// set default order status
			$this->_updateOrderStatus(_STORE_DEFAULT_ORDER_STATUS, $order_data['comments'], 'false', 'false');
		}

		$data['success'] = true;
		//if ($add_type=='insert') $this->_sendOrderMail($data['orders_id']);
        ($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_product_return')) ? eval($plugin_code) : false;           
		$this->_setStats($data['orders_id']);

		return  $data;

	}

	function _setStats($oID){
		global $db;

		$tmp_order = new order($oID);

		$data_array = array('products_count'=>$tmp_order->order_count,
							'orders_stats_price'=>$tmp_order->order_total['total']['plain']
							);

		$check_sql = "SELECT orders_id from ".TABLE_ORDERS_STATS." where orders_id = ".(int)$tmp_order->oID."";
		$rs = $db->Execute($check_sql);
		if ($rs->RecordCount()>0) {

			$db->AutoExecute(TABLE_ORDERS_STATS, $data_array, 'UPDATE', "orders_id=".(int)$tmp_order->oID."");

		}else{

			$insert_array = array('orders_id'=>$tmp_order->oID);
			$data_array = array_merge($data_array, $insert_array);
			$db->AutoExecute(TABLE_ORDERS_STATS, $data_array, 'INSERT');

		}
	}

	function _buildCustomerData($customer_id='', $data=''){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildCustomerData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($customer_id) && empty($data)){
			$tmp_data = new customer($customer_id);
			$c_data = $tmp_data->customer_info;
		}elseif(!empty($customer_id) && !empty($data)){
			$c_data = $data;
		}else{
			$tmp_data = $_SESSION['customer'];
			$c_data = $tmp_data->customer_info;
		}
		
		$customer_array = $c_data;
		if ($tmp_data->customer_info['customers_id']) {
			$default_adress = $tmp_data->_buildAddressData($tmp_data->customer_info['customers_id'],'default');
			$customer_array['customers_age'] = $default_adress['customers_age'];
			$customer_array['customers_dob'] = $default_adress['customers_dob'];
		}
		/*
		$customer_array = array('customers_cid'=>$c_data['customers_cid'],
								'customers_vat_id'=>$c_data['customers_vat_id'],
								'customers_status'=>$c_data['customers_status'],
								'customers_email_address'=>$c_data['customers_email_address']); */

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildCustomerData_bottom')) ? eval($plugin_code) : false;
		return $customer_array;
	}

	function _buildCustomerBillingAddress($customer_id='', $data=''){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildCustomerAddress_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($customer_id) && empty($data)){
			$tmp_data = new customer($customer_id);
			$b_data = $tmp_data->customer_default_address;
		}elseif(!empty($customer_id) && !empty($data)){
			$b_data = $data;
		}else{
			$tmp_data = $_SESSION['customer'];
			$b_data = $tmp_data->customer_default_address;
		}

		$customer_array = array('billing_gender'=>$b_data['customers_gender'],
								'billing_phone'=>$b_data['customers_phone'],
								'billing_fax'=>$b_data['customers_fax'],
								'billing_firstname'=>$b_data['customers_firstname'],
								'billing_lastname'=>$b_data['customers_lastname'],
								'billing_company'=>$b_data['customers_company'],
								'billing_company_2'=>$b_data['customers_company_2'],
								'billing_company_3'=>$b_data['customers_company_3'],
								'billing_street_address'=>$b_data['customers_street_address'],
								'billing_suburb'=>$b_data['customers_suburb'],
								'billing_city'=>$b_data['customers_city'],
								'billing_postcode'=>$b_data['customers_postcode'],
								'billing_zone'=>$b_data['customers_zone'],
								'billing_zone_code'=>$b_data['customers_zone_code'],
								'billing_country'=>$b_data['customers_country'],
								'billing_country_code'=>$b_data['customers_country_code'],
								'billing_address_book_id'=>$b_data['address_book_id']
								);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildCustomerAddress_bottom')) ? eval($plugin_code) : false;
		return $customer_array;
	}

	function _buildCustomerDeliveryAddress($customer_id='', $data=''){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildCustomerDeliveryAddress_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($customer_id) && empty($data)){
			$tmp_data = new customer($customer_id);
			$d_data = $tmp_data->customer_shipping_address;
		}elseif(!empty($customer_id) && !empty($data)){
			$d_data = $data;
		}else{
			$tmp_data = $_SESSION['customer'];
			$d_data = $tmp_data->customer_shipping_address;
		}

		$customer_array = array('delivery_gender'=>$d_data['customers_gender'],
								'delivery_phone'=>$d_data['customers_phone'],
								'delivery_fax'=>$d_data['customers_fax'],
								'delivery_firstname'=>$d_data['customers_firstname'],
								'delivery_lastname'=>$d_data['customers_lastname'],
								'delivery_company'=>$d_data['customers_company'],
								'delivery_company_2'=>$d_data['customers_company_2'],
								'delivery_company_3'=>$d_data['customers_company_3'],
								'delivery_street_address'=>$d_data['customers_street_address'],
								'delivery_suburb'=>$d_data['customers_suburb'],
								'delivery_city'=>$d_data['customers_city'],
								'delivery_postcode'=>$d_data['customers_postcode'],
								'delivery_zone'=>$d_data['customers_zone'],
								'delivery_zone_code'=>$d_data['customers_zone_code'],
								'delivery_country'=>$d_data['customers_country'],
								'delivery_country_code'=>$d_data['customers_country_code'],
								'delivery_address_book_id'=>$d_data['address_book_id']);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildCustomerDeliveryAddress_bottom')) ? eval($plugin_code) : false;
		return $customer_array;
	}

	function _buildProductData($orders_id, $data=''){
		global $xtPlugin, $db, $tax, $product, $customers_status;
        $product_array = array();
		if(empty($orders_id)) return false;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildProductData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($data)){
			$data = $_SESSION['cart']->show_content;
		}

		$tmp_data = $data;
		$i = 0;
		while (list ($key, $value) = each($tmp_data)) {
			
			($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildProductData_product')) ? eval($plugin_code) : false;
//            debugbreak();
            // do we have discount on position ?
            if ($value['_original_products_price']['plain_otax']>$value['products_price']['plain_otax']) {
                $product_discount = 100-$value['products_price']['plain_otax']/$value['_original_products_price']['plain_otax']*100;
                $product_discount = round($product_discount,2);
                $value['products_discount']=$product_discount;
            }
			$product_array[$i] = array('orders_id'=>$orders_id,
							     'products_id'=>$value['products_id'],
							     'products_model'=>$value['products_model'],
							     'products_name'=>$value['products_name'],
							     'products_price'=>$value['products_price']['plain_otax'],
							     'products_discount'=>$value['products_discount'],
							     'products_shipping_time'=>$value['products_shipping_time'],
							     'products_tax'=>$value['products_tax_value'],
							     'products_tax_class'=>$value['products_tax_class_id'],
							     'products_quantity'=>$value['products_quantity'],
							     'products_data'=>$value['products_info']
			);

			if(!empty($value['orders_products_id']))
			$product_array[$i]['orders_products_id'] = $value['orders_products_id'];

			if($customers_status->customers_status_show_price_tax == 1)
			$product_array[$i]['allow_tax'] = 1;
			else
			$product_array[$i]['allow_tax'] = 0;


			($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildProductData_data')) ? eval($plugin_code) : false;
			$i++;
		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildProductData_bottom')) ? eval($plugin_code) : false;
		return $product_array;

	}

	function _buildDownloadData($data){
		global $xtPlugin, $db, $tax, $product;

		$tmp_data = $data;

		$i = 0;
		$product_media_array = array();

		$query = "SELECT ml.m_id FROM
				 ".TABLE_MEDIA_LINK." ml left join
				 ".TABLE_MEDIA." m
				 on m.id=ml.m_id
				 WHERE ml.link_id='".(int)$data['products_id']."' and m.download_status='order' and m.status='true'";
		//echo $query;
		$rs = $db->Execute($query);
		if ($rs->RecordCount()>0) {
			while (!$rs->EOF) {
				$product_media_array[] = array('orders_id'=>$data['orders_id'],
												'orders_products_id'=>$data['orders_products_id'],
												'media_id'=>$rs->fields['m_id'],
												'download_count'=>'0'
				);
				$rs->MoveNext();
			}$rs->Close();
		}

		return $product_media_array;

	}

	function _buildTotalData($orders_id, $data=''){
		global $xtPlugin, $db, $tax, $customers_status;

		if(empty($orders_id)) return false;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildTotalData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($data)){
			$data = $_SESSION['cart']->show_sub_content;

			$tmp_data = $data;

			$i = 0;
			while (list ($key, $value) = each($tmp_data)) {

				$total_array[$i] = array('orders_id'=>$orders_id,
								   'orders_total_key'=>$value['products_key'],
								   'orders_total_key_id'=>$value['products_key_id'],
								   'orders_total_model'=>$value['products_model'],
								   'orders_total_name'=>$value['products_name'],
								   'orders_total_price'=>$value['products_price']['plain_otax'],
								   'orders_total_tax'=>$value['products_tax_value'],
								   'orders_total_tax_class'=>$value['products_tax_class'],
								   'orders_total_quantity'=>$value['products_quantity']
				);

				if(!empty($value['orders_total_id']))
				$total_array[$i]['orders_total_id'] = $value['orders_total_id'];

				if($customers_status->customers_status_show_price_tax == 1)
				$total_array[$i]['allow_tax'] = 1;
				else
				$total_array[$i]['allow_tax'] = 0;

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildTotalData_data_cart')) ? eval($plugin_code) : false;
				$i++;
			}

		}else{
			$i = 0;
			while (list ($key, $value) = each($data)) {

				$total_array[$i] = array('orders_id'=>$orders_id,
								   'orders_total_key'=>$value['orders_total_key'],
								   'orders_total_model'=>$value['orders_total_model'],
								   'orders_total_name'=>$value['orders_total_name'],
								   'orders_total_price'=>$value['orders_total_price'],
								   'orders_total_tax'=>$value['orders_total_tax'],
								   'orders_total_tax_class'=>$value['orders_total_tax_class'],
								   'orders_total_quantity'=>$value['orders_total_quantity']
				);
				if(!empty($data['orders_total_id']))
				$total_array[$i]['orders_total_id'] = $value['orders_total_id'];

				if($customers_status->customers_status_show_price_tax == 1 || $customers_status->customers_status_add_tax_ot==1)
				$total_array[$i]['allow_tax'] = 1;
				else
				$total_array[$i]['allow_tax'] = 0;

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildTotalData_data_post')) ? eval($plugin_code) : false;
				$i++;
			}

		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildTotalData_bottom')) ? eval($plugin_code) : false;
		return $total_array;

	}

	function _saveCustomerData($data, $add_type = 'insert'){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_saveCustomerData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($add_type=='insert'){
			$insert_record = array('date_purchased'=>$db->BindDate(time()), 'last_modified'=>$db->BindDate(time()));
			$record = array_merge($insert_record, $data);
			$db->AutoExecute(TABLE_ORDERS, $record, 'INSERT');
			$this->data_orders_id = $db->Insert_ID();
		}elseif($add_type=='update'){
			$update_record = array('last_modified'=>$db->BindDate(time()));
			$record = array_merge($update_record, $data);
			$db->AutoExecute(TABLE_ORDERS, $record, 'UPDATE', "orders_id=".$data['orders_id']."");
			$this->data_orders_id = $data['orders_id'];
		}

	}

	function _saveProductData($data, $add_type = 'insert',$reduce_stock=false){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_saveProductData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($add_type=='insert'){

			if ($reduce_stock) {
				$stock = new stock();
				$stock->removeStock($data['products_id'],$data['products_quantity']);
			}
			// update products_ordered
			$db->Execute("UPDATE ".TABLE_PRODUCTS." SET products_ordered=products_ordered+".$data['products_quantity']." WHERE products_id='".$data['products_id']."'");

			$db->AutoExecute(TABLE_ORDERS_PRODUCTS, $data, 'INSERT');
			$this->data_orders_products_id = $db->Insert_ID();
			$this->_saveDownloadData($data, $add_type,$this->data_orders_products_id);
		}elseif($add_type=='update'){
			$db->AutoExecute(TABLE_ORDERS_PRODUCTS, $data, 'UPDATE', "orders_products_id=".$data['orders_products_id']."");
			$this->data_orders_products_id = $data['orders_products_id'];
		}
        

        ($plugin_code = $xtPlugin->PluginCode('class.order.php:_saveProductData_bottom')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

	}

	function _saveDownloadData($data, $add_type = 'insert',$orders_products_id){
		global $xtPlugin, $db;
		$data = array_merge($data,array('orders_products_id'=>$orders_products_id));


		$insert_data = $this->_buildDownloadData($data);


		($plugin_code = $xtPlugin->PluginCode('class.order.php:_saveDownloadData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($add_type=='insert'){
			foreach ($insert_data as $key => $arr)
			$db->AutoExecute(TABLE_ORDERS_PRODUCTS_MEDIA, $arr, 'INSERT');
		}elseif($add_type=='update'){
				$db->AutoExecute(TABLE_ORDERS_PRODUCTS_MEDIA, $data, 'UPDATE', "orders_products_id=".$data['orders_products_id']."");
				$this->data_orders_products_id = $data['orders_products_id'];
		}


	}

	function _saveTotalData($data, $add_type = 'insert'){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_saveProductData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($add_type=='insert'){
			$db->AutoExecute(TABLE_ORDERS_TOTAL, $data, 'INSERT');
			$this->data_orders_total_id = $db->Insert_ID();
		}elseif($add_type=='update'){
			$db->AutoExecute(TABLE_ORDERS_TOTAL, $data, 'UPDATE', "orders_total_id=".(int)$data['orders_total_id']."");
			$this->data_orders_total_id = $data['orders_total_id'];
		}
	}

	function _checkOrderId($oID){
		global $db;
		$oID = (int)$oID;
		$record = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = ".$oID." and customers_id = '" . (int)$this->customer . "'");
		if($record->RecordCount() > 0){
			return true;
		}else{
			return false;
		}
	}

	function _buildData($oID){
		global $xtPlugin, $db;
		$oID = (int)$oID;
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$order_data = $this->_getOrderData($oID);
		$order_products = $this->_getOrderProductData($oID, $order_data);
		$order_total_data = $this->_getOrderTotalData($oID, $order_data);
		$total = $this->_getTotal($order_products, $order_total_data, $order_data);


		$order_data = array('order_customer' => $this->_buildCustomerData($order_data['customers_id']),
							'order_data'=>$order_data,
						    'order_products' => $order_products,
						    'order_total_data' => $order_total_data,
						    'order_total' => $total,
							'order_count' => count($order_products)
		);


		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildData_bottom')) ? eval($plugin_code) : false;
		return $order_data;
	}

	function _getOrderData($oID){
		global $xtPlugin, $db, $system_status, $price;
		$oID = (int)$oID;
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $oID . "'");
		if($record->RecordCount() > 0){
			while(!$record->EOF){

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderData')) ? eval($plugin_code) : false;

				$record->fields['orders_status'] = $system_status->values['order_status'][$record->fields['orders_status']]['name'];
				$record->fields['date_purchased_plain'] = $record->fields['date_purchased'];
				$record->fields['date_purchased'] = date_short($record->fields['date_purchased']);
				
				if($record->fields['orders_data']){
					$arr = unserialize($record->fields['orders_data']);
					$record->fields['order_info_data'] = $arr;
	
					if (is_array($arr))
					foreach ($arr as $tkey => $tval) {
						$record->fields['order_info_options'][] = array('text'=>constant('TEXT_'.strtoupper($tkey)), 'data'=>$tkey,  'value'=>$tval);
					}
               		($plugin_code = $xtPlugin->PluginCode('class.order.php:_order_info_options')) ? eval($plugin_code) : false;
				}				
				
				$price->_setCurrency($record->fields['currency_code']);

				$data = $record->fields;
				$record->MoveNext();
			}$record->Close();
			return $data;
		}else{
			return false;
		}
	}

	function _getOrderProductData($oID, $order_data){
		global $xtPlugin, $db, $price, $currency;
		$oID = (int)$oID;
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderProductData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		$record = $db->Execute("SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = '" . $oID . "'");
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				$_add_single_price = '';
				$_add_single_price_otax = '';
				
				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderProductData')) ? eval($plugin_code) : false;
									
				// Calc Tax
				$_tax = $price->_calcTax($record->fields['products_price'], $record->fields['products_tax']);
				$_tax_single = $price->_calcTax($_add_single_price, $record->fields['products_tax']);
				
				// Calc Price
				$_price_otax = $record->fields['products_price'];
				$_add_single_price_otax = $_add_single_price;
				$_final_price_otax = $_price_otax * $record->fields['products_quantity'];
				
				$_price = $_price_otax;
				$_final_price = $_final_price_otax + $_add_single_price;				
				
				if($record->fields['allow_tax'] == 1){
					$_price = $price->_AddTax($_price, $record->fields['products_tax']);
					$_add_single_price = $price->_AddTax($_add_single_price, $record->fields['products_tax']);
					$_price = $price->_roundPrice($_price);
					$_add_single_price = $price->_roundPrice($_add_single_price);
					$_final_price  = $_price * $record->fields['products_quantity'];
					$_final_price = $_final_price + $_add_single_price;
				}
				
				$_final_tax = $_tax * $record->fields['products_quantity'];
				$_final_tax = $_tax_single + $_final_tax;
				
				$record->fields['products_price'] = $price->_Format(array('price'=>$_price, 'price_otax'=>$_price_otax, 'format'=>true, 'format_type'=>'default'));
				$record->fields['products_add_price'] = $price->_Format(array('price'=>$_add_single_price, 'price_otax'=>$_add_single_price_otax, 'format'=>true, 'format_type'=>'default'));
				
				$record->fields['products_final_price'] = $price->_Format(array('price'=>$_final_price, 'price_otax'=>$_final_price_otax, 'format'=>true, 'format_type'=>'default'));
				$record->fields['products_tax_rate'] = round($record->fields['products_tax'],  $currency->decimals);

				$record->fields['products_tax'] = $price->_Format(array('price'=>$_tax, 'format'=>true, 'format_type'=>'default'));
				$record->fields['products_final_tax'] = $price->_Format(array('price'=>$_final_tax, 'format'=>true, 'format_type'=>'default'));
				
				$record->fields['add_single_tax'] = $price->_Format(array('price'=>$_tax_single, 'format'=>true, 'format_type'=>'default'));

				if($record->fields['products_data']){
					$arr = unserialize($record->fields['products_data']);
					$record->fields['products_info_data'] = $arr;
	
					if (is_array($arr))
					foreach ($arr as $tkey => $tval) {
						$record->fields['products_info_options'][] = array('text'=>'TEXT_'.strtoupper($tkey), 'data'=>$tkey,  'value'=>$tval);
					}
	
               		($plugin_code = $xtPlugin->PluginCode('class.order.php:_order_products_info_options')) ? eval($plugin_code) : false;
				}					
				
				
				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderProductData_bottom')) ? eval($plugin_code) : false;
				$data[] = $record->fields;

				$record->MoveNext();
			}$record->Close();
			
			return $data;
		}else{
			return false;
		}
	}

	function _getOrderTotalData($oID){
		global $xtPlugin, $db, $price, $currency;
		
		$oID = (int)$oID;
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderTotalData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = " . $oID . "");
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderTotalData')) ? eval($plugin_code) : false;
				
				// Calc Tax
				$_tax = $price->_calcTax($record->fields['orders_total_price'], $record->fields['orders_total_tax']);
				
				// Calc Price
				$_price_otax = $record->fields['orders_total_price'];
				$_final_price_otax = $_price_otax * $record->fields['orders_total_quantity'];
				
				$_price = $_price_otax;
				$_final_price = $_final_price_otax;				
				
				if($record->fields['allow_tax'] == 1){
					$_price = $price->_AddTax($_price, $record->fields['orders_total_tax']);
					$_price = $price->_roundPrice($_price);
					$_final_price  = $_price * $record->fields['orders_total_quantity'];
				}
				
				$_final_tax = $_tax * $record->fields['orders_total_quantity'];				
				
				$record->fields['orders_total_price'] = $price->_Format(array('price'=>$_price, 'price_otax'=>$_price_otax, 'format'=>true, 'format_type'=>'default'));
				$record->fields['orders_total_final_price'] = $price->_Format(array('price'=>$_final_price, 'price_otax'=>$_final_price_otax, 'format'=>true, 'format_type'=>'default'));
				$record->fields['orders_total_tax_rate'] = round($record->fields['orders_total_tax'], $currency->decimals);

				$record->fields['orders_total_tax'] = $price->_Format(array('price'=>$_tax, 'format'=>true, 'format_type'=>'default'));
				$record->fields['orders_total_final_tax'] = $price->_Format(array('price'=>$_final_tax, 'format'=>true, 'format_type'=>'default'));

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderTotalData_bottom')) ? eval($plugin_code) : false;
				$data[] = $record->fields;

				$record->MoveNext();
			}$record->Close();
			return $data;
		}else{
			return false;
		}
	}

	function _getTotal($products, $sub_total){
		global $xtPlugin, $price, $currency;
		
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$product_data = $products;
		$order_total_data = $sub_total;
			
		if(is_data($product_data)){
			while (list ($key, $value) = each($product_data)) {

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_content_top')) ? eval($plugin_code) : false;
		
				$product_data_tax[$value['products_tax_class']] += $value['products_final_tax']['plain'];
				$product_data_tax_rate[$value['products_tax_class']] = $value['products_tax_rate'];
				$product_data_total += $value['products_final_price']['plain'];
				$product_data_total_otax += $value['products_final_price']['plain_otax'];

				$total_tax[$value['products_tax_class']] += $value['products_final_tax']['plain'];
				$total_tax_rate[$value['products_tax_class']] = $value['products_tax_rate'];

				$total += ($price->_roundPrice($value['products_price']['plain_otax'] + $value['products_tax']['plain']) * $value['products_quantity']);
				$total += ($price->_roundPrice($value['products_add_price']['plain_otax'] + $value['add_single_tax']['plain']));
				//$total += ($value['products_final_price']['plain_otax'] + $value['products_final_tax']['plain']);
				
				$total_otax += $value['products_final_price']['plain_otax'];
				$total_otax += $value['products_add_price']['plain_otax'];
			}
		}
   
		
		if(is_data($order_total_data)){
			while (list ($key, $value) = each($order_total_data)) {

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_sub_content_top')) ? eval($plugin_code) : false;

				$order_total_data_tax[$value['orders_total_tax_class']] += $value['orders_total_final_tax']['plain'];
				$order_total_data_tax_rate[$value['orders_total_tax_class']] = $value['orders_total_tax_rate'];
				$order_total_data_total += $value['orders_total_final_price']['plain'];
				$order_total_data_total_otax += $value['orders_total_final_price']['plain_otax'];

				$total_tax[$value['orders_total_tax_class']] += $value['orders_total_final_tax']['plain'];
				$total_tax_rate[$value['orders_total_tax_class']] = $value['orders_total_tax_rate'];
				//$total += ($value['orders_total_final_price']['plain_otax'] + $value['orders_total_final_tax']['plain']);
				$total += ($price->_roundPrice($value['orders_total_price']['plain_otax'] + $value['orders_total_tax']['plain']) * $value['orders_total_quantity']);
				$total_otax += $value['orders_total_final_price']['plain_otax'];

			}
		}

		$product_data_total = $price->_Format(array('price'=>$product_data_total, 'format'=>true, 'format_type'=>'default'));
		$product_data_total_otax = $price->_Format(array('price'=>$product_data_total_otax, 'format'=>true, 'format_type'=>'default'));

		$order_total_data_total = $price->_Format(array('price'=>$order_total_data_total, 'format'=>true, 'format_type'=>'default'));
		$order_total_data_total_otax = $price->_Format(array('price'=>$order_total_data_total_otax, 'format'=>true, 'format_type'=>'default'));
		
		$total = $price->_Format(array('price'=>$total, 'format'=>true, 'format_type'=>'default'));
		$total_otax = $price->_Format(array('price'=>$total_otax, 'format'=>true, 'format_type'=>'default'));

		$ptax = $product_data_tax;
		$ottax = $order_total_data_tax;
		$ttax = $total_tax;

		if (is_array($ptax))
		while (list ($key, $value) = each($ptax)) {
			if ($product_data_tax_rate[$key] != 0)
			$new_product_data_tax[$key] =  array('tax_value' => $price->_Format(array('price'=>$value, 'format'=>true, 'format_type'=>'default')), 'tax_key'=>round($product_data_tax_rate[$key], $currency->decimals));
		}

		if (is_array($ottax))
		while (list ($key, $value) = each($ottax)) {
			if ($product_data_tax_rate[$key] != 0)
			$new_order_total_data_tax[$key] =  array('tax_value' => $price->_Format(array('price'=>$value, 'format'=>true, 'format_type'=>'default')), 'tax_key'=>round($order_total_data_tax_rate[$key], $currency->decimals));
		}

		if (is_array($ttax))
		while (list ($key, $value) = each($ttax)) {
			if ($product_data_tax_rate[$key] != 0)
			$new_total_tax[$key] =  array('tax_value' => $price->_Format(array('price'=>$value, 'format'=>true, 'format_type'=>'default')), 'tax_key'=>round($total_tax_rate[$key], $currency->decimals));
		}
			
		$data = array('product_tax' => $new_product_data_tax,
						  'product_total' => $product_data_total,
						  'product_total_otax' => $product_data_total_otax,
						  'product_tax_rate' => $product_data_tax_rate,
						  'data_tax' => $new_order_total_data_tax,
						  'data_total' => $order_total_data_total,
						  'data_total_otax' => $order_total_data_total_otax,
						  'data_tax_rate' => $order_total_data_tax_rate,
						  'total_tax' => $new_total_tax,
						  'total' => $total,
						  'total_otax' => $total_otax,
						  'total_tax_rate' => $total_tax_rate
		);
		
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_bottom')) ? eval($plugin_code) : false;

		return $data;
	}

	/**
	 * check if customer has orders with download files
	 *
	 * @param int $cID
	 */
	function _hasDownloads($cID=0) {
		global $db;

		if($cID == 0){
			$cID = $this->customer;
		}

		$cID = (int)$cID;
		if (!is_int($cID)) return false;

		$query = "SELECT o.orders_id FROM ".TABLE_ORDERS." o INNER JOIN ".TABLE_ORDERS_PRODUCTS." op ON o.orders_id = op.orders_id INNER JOIN ".TABLE_ORDERS_PRODUCTS_MEDIA." opm ON op.orders_id = opm.orders_id WHERE o.customers_id = '".$cID."'";

		$rs = $db->Execute($query);
		if ($rs->RecordCount()>0) return true;
		return false;

	}

	/**
	 * query downloads for orders id
	 *
	 * @param int $orders_id
	 * @return array
	 */
	function _getDownloads($orders_id) {
		global $db,$xtPlugin,$system_status,$xtLink;

		$orders_id = (int)$orders_id;

		$query = "SELECT md.media_name,o.date_purchased,o.orders_status,o.orders_id,opm.media_id, md.media_description, opm.download_count, m.* FROM ".TABLE_MEDIA." m INNER JOIN ".TABLE_ORDERS." o ON o.orders_id = '".$orders_id."' INNER JOIN ".TABLE_ORDERS_PRODUCTS_MEDIA." opm ON m.id = opm.media_id INNER JOIN ".TABLE_MEDIA_DESCRIPTION." md ON m.id = md.id WHERE opm.orders_id = '".$orders_id."' and md.language_code='de'";
		$rs = $db->Execute($query);
		$download_data = array();

		include_once(_SRV_WEBROOT.'xtFramework/classes/class.download.php');
		$download = new download();

		while (!$rs->EOF) {

			$file = $rs->fields['file'];
			$file = _SRV_WEBROOT.'media/files/'.$file;
			if (file_exists($file)) {
				$_data = $rs->fields;
				$_data['media_size'] = filesize($file);

				// count left
				$count_left = $_data['max_dl_count']-$_data['download_count'];

				// valid until
				if ($_data['max_dl_days']>0) {
					$valid_until = date_add(datetime_to_timestamp($_data['date_purchased']),$_data['max_dl_days']);
					$_data['allowed_until'] = format_timestamp($valid_until);
				}


				// set allowed flag
				$_data['download_allowed'] = '1';
				if ($_data['max_dl_count']>0)
					$_data['allowed_count'] = $count_left;

				// download link
				$link = $xtLink->_link(array('page'=>'customer','paction'=>'download_overview','params'=>'order='.$_data['orders_id'].'&media='.$_data['media_id']));
				$_data['download_url'] = $link;

				if ($count_left<=0 && $_data['max_dl_count']>0) $_data['download_allowed'] = 0;

				// check if allowed
				if (!$download->_checkDowloadAllowed($_data['date_purchased'],$_data['max_dl_days'],$_data['max_dl_count'],$_data['download_count']))
					$_data['download_allowed'] = 0;

				// order status allowed ?
				if (isset($system_status->values['order_status'][$rs->fields['orders_status']]))
				if ($system_status->values['order_status'][$rs->fields['orders_status']]['data']['enable_download']!='1') $_data['download_allowed'] = 0;


				$download_data[] = $_data;
			}


			$rs->MoveNext();
		}$rs->Close();
		return $download_data;
	}

	function _getDownloadList($cID=0, $limit=10){
		global $xtPlugin, $price, $db, $xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getDownloadList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($cID == 0){
			$cID = $this->customer;
		}

		$cID=(int)$cID;

		$query = "SELECT o.orders_id FROM ".TABLE_ORDERS." o INNER JOIN ".TABLE_ORDERS_PRODUCTS." op ON o.orders_id = op.orders_id INNER JOIN ".TABLE_ORDERS_PRODUCTS_MEDIA." opm ON op.orders_id = opm.orders_id WHERE o.customers_id = '".(int)$cID."'";

		$rs = $db->Execute($query);
		$data = array();
		if ($rs->RecordCount()>0) {

			while (!$rs->EOF) {

				$_data = $this->_buildData($rs->fields['orders_id']);

				$d_data = array('download_data'=>$this->_getDownloads($rs->fields['orders_id']));
				$data[$rs->fields['orders_id']] =  array_merge($_data,$d_data);

				$rs->MoveNext();
			}

			return $data;


		} $rs->Close();
		return false;
	}

	function _getOrderList($cID=0, $limit=10){
		global $xtPlugin, $price, $db, $xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($cID == 0){
			$cID = $this->customer;
		}
		$cID=(int)$cID;
		$query = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE customers_id = '" . $cID . "' order by orders_id desc";

		$pages = new split_page($query, $limit, $xtLink->_getParams(array ('next_page', 'info')));

		$navigation_count = $pages->split_data['count'];
		$navigation_pages = $pages->split_data['pages'];

		for ($i = 0; $i < count($pages->split_data['data']);$i++) {
			($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderListData')) ? eval($plugin_code) : false;
			$data[] =  $this->_buildData($pages->split_data['data'][$i]['orders_id']);
		}

		$data_array = array('data'=>$data, 'count'=>$navigation_count, 'pages'=>$navigation_pages);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderList_bottom')) ? eval($plugin_code) : false;
		return $data_array;

	}

	/**
	 * delete order with connected tables
	 *
	 * @param int $orders_id
	 * @param boolean $refill_stock
	 * @return unknown
	 */
	function _deleteOrder($orders_id,$refill_stock=false){
		global $xtPlugin, $db;
		$orders_id=(int)$orders_id;
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrder_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$this->_deleteOrderCustomer($orders_id);
		$this->_deleteOrderProduct($orders_id,'',$refill_stock);
		$this->_deleteOrderTotal($orders_id);
		$this->_deleteOrderHistory($orders_id);
		$this->_deleteOrderProductMedia($orders_id);
		$this->_deleteOrderStats($orders_id);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrder_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * delete order from table orders
	 *
	 * @param int $orders_id
	 */
	function _deleteOrderCustomer($orders_id){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderCustomer_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$db->Execute("DELETE FROM ".TABLE_ORDERS." WHERE orders_id = '" . (int) $orders_id . "'");

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderCustomer_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * delete order from table orders products
	 *
	 * @param int $orders_id
	 * @param int $orders_products_id
	 */
	function _deleteOrderProduct($orders_id = '', $orders_products_id = '',$refill_stock = false){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderProduct_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($orders_id) || !empty($orders_products_id)){

			if(!empty($orders_id) && empty($orders_products_id)){

				if ($refill_stock) {
					$stock = new stock();
					$rs = $db->Execute("SELECT products_id,products_quantity FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = '" . (int) $orders_id . "'");
					while (!$rs->EOF) {
						$stock->addStock($rs->fields['products_id'],$rs->fields['products_quantity']);
						$rs->MoveNext();
					}
				}

				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = '" . (int) $orders_id . "'");
			}elseif(empty($orders_id) && !empty($orders_products_id)){

				if ($refill_stock) {
					$stock = new stock();
					$rs = $db->Execute("SELECT products_id,products_quantity FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id = '" . (int) $orders_products_id . "'");
					while (!$rs->EOF) {
						$stock->addStock($rs->fields['products_id'],$rs->fields['products_quantity']);
						$rs->MoveNext();
					}
				}

				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id = '" . (int) $orders_products_id . "'");
			}

		}else{
			return false;
		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderProduct_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * delete order from table orders products
	 *
	 * @param int $orders_id
	 * @param int $orders_products_id
	 */
	function _deleteOrderProductMedia($orders_id = '', $media_id = ''){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderProductMedia_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(!empty($orders_id) || !empty($media_id)){

			if(!empty($orders_id) && empty($media_id)){
				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS_MEDIA." WHERE orders_id = '" . (int) $orders_id . "'");
			}elseif(empty($orders_id) && !empty($media_id)){
				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS_MEDIA." WHERE media_id = '" . (int) $media_id . "'");
			}

		}else{
			return false;
		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderProductMedia_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * delete order from table orders total
	 *
	 * @param int $orders_id
	 * @param int $orders_total_id
	 */
	function _deleteOrderTotal($orders_id){
		global $xtPlugin, $db;

		$db->Execute("DELETE FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = '" . (int) $orders_id . "'");

	}

	/**
	 * delete order from table orders_history
	 *
	 * @param int $orders_id
	 */
	function _deleteOrderHistory($orders_id){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderHistory_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$db->Execute("DELETE FROM ".TABLE_ORDERS_STATUS_HISTORY." WHERE orders_id = '" . (int) $orders_id . "'");

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderHistory_bottom')) ? eval($plugin_code) : false;
	}

	function _deleteOrderStats($orders_id){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderStats_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$db->Execute("DELETE FROM ".TABLE_ORDERS_STATS." WHERE orders_id = '" . (int) $orders_id . "'");

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderStats_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * update order status, send status update to customer
	 *
	 * @param int $status
	 * @param string $comments
	 */
	function _updateOrderStatus($status, $comments='', $send_email='true', $send_comments='true',$trigger = 'user',$callback_id = 0) {
		global $xtPlugin, $db, $system_status;

		$status = (int)$status;

		$extra_assign = array();


		($plugin_code = $xtPlugin->PluginCode('class.order.php:_updateOrderStatus_top')) ? eval($plugin_code) : false;

		$db->Execute("update " . TABLE_ORDERS . " set orders_status = '" . (int)$status . "', last_modified = now() where orders_id = '" . (int)$this->oID . "'");

		if($send_email=='true'){
			$customer_notified = '1';
		}else{
			$customer_notified = '0';
		}

		$data_array = array();
		$data_array['orders_id']=$this->oID;
		$data_array['orders_status_id']=$status;
		$data_array['customer_notified']=$customer_notified;
		$data_array['comments']=$comments;
		$data_array['change_trigger']=$trigger;
		$data_array['callback_id']=$callback_id;
		//$data_array['date_added']=$db->BindDate(time());
		$db->AutoExecute(TABLE_ORDERS_STATUS_HISTORY,$data_array,'INSERT');

		if($send_comments=='true'){
			$comments = $comments;
		}else{
			$comments = '';
		}

		$status = $system_status->values['order_status'][$status]['name'];

		if ($system_status->values['order_status'][$status]['enable_download']) {
			$this->resetDownloadCount();
		}
	
		
		
		if($send_email=='true')
		$this->_sendStatusMail($status, $comments,$extra_assign);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_updateOrderStatus_bottom')) ? eval($plugin_code) : false;

	}

	/**
	 * send order mail to customer
	 *
	 * @return boolean
	 */
	function _sendOrderMail( $type = ''){
		global $xtPlugin, $db,$store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendOrderMail_top')) ? eval($plugin_code) : false;
		if($type != 'send_invoice'){
			$type = 'send_order';
		}
		$ordermail = new xtMailer($type);
		$ordermail->_addReceiver($this->order_data['customers_email_address'], $this->order_data['billing_lastname'].' '.$this->order_data['billing_firstname']);
		$ordermail->_assign('order_data',$this->order_data);
		$ordermail->_assign('order_products',$this->order_products);
		$ordermail->_assign('order_total_data',$this->order_total_data);
		$ordermail->_assign('total',$this->order_total);
		$ordermail->_assign('order_count',$this->order_count);

		// get text for payment method
		$rs = $db->Execute("SELECT pd.payment_email_desc FROM ".TABLE_PAYMENT_DESCRIPTION." pd, ".TABLE_PAYMENT." p WHERE pd.language_code='".$this->order_data['language_code']."' and p.payment_id=pd.payment_id and p.payment_code='".$this->order_data['payment_code']."'");
		if ($rs->RecordCount()==1) {
			$ordermail->_assign('payment_info',$rs->fields['payment_email_desc']);
		}
		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendOrderMail_bottom')) ? eval($plugin_code) : false;
		$ordermail->_sendMail();

		return true;

	}

	/**
	 * send order status mail to customer
	 *
	 * @param int $status
	 * @param string $comments
	 */
	function _sendStatusMail($status,$comments,$extra_assign = array()) {
		global $xtPlugin, $db,$store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendStatusMail_top')) ? eval($plugin_code) : false;

		$statusmail = new xtMailer('update_order-admin', -1, -1, -1, $this->order_data['shop_id']);
		$statusmail->_addReceiver($this->order_data['customers_email_address'],$this->order_data['billing_lastname'].' '.$this->order_data['billing_firstname']);
		$statusmail->_assign('order_data',$this->order_data);
		$statusmail->_assign('order_products',$this->order_products);
		$statusmail->_assign('order_total_data',$this->order_total_data);
		$statusmail->_assign('total',$this->order_total);
		$statusmail->_assign('order_count',$this->order_count);
		$statusmail->_assign('comments',$comments);
		$statusmail->_assign('status',$status);
		if (count($extra_assign)>0) {
			foreach ($extra_assign as $key => $val) {
				$statusmail->_assign($key,$val);
			}
		}

		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendStatusMail_bottom')) ? eval($plugin_code) : false;
		$statusmail->_sendMail();

	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language, $xtPlugin, $customers_status;
		
		$params = array();

		$header['orders_status'] = array('type'=>'');

		$params['header']         = $header;
		$params['master_key']     = 'orders_id';
		$params['default_sort']   = 'orders_id';
		$params['RemoteSort']   = true;

		$params['languageTab']    = 0;
		$params['edit_masterkey'] = false;
		$params['edit_url']       = 'order_edit.php?';

		$params['display_searchPanel']  = true;
		$params['display_newBtn'] = false;
//		$params['display_deleteBtn'] = false;

		$rowActions[] = array('iconCls' => 'delete_order', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_DELETE_ORDER);

		$js = "var edit_id = record.id;";
		$js .= "Ext.Msg.show({title:'".TEXT_DELETE_ORDER."',
   							  msg: '".TEXT_DELETE_ORDER_ASK."',
   							  buttons: Ext.Msg.YESNOCANCEL,
   							  animEl: 'elId',
   							  fn: function(btn){deleteOrder(edit_id,btn);},
   							  icon: Ext.MessageBox.QUESTION
							  });";

		$rowActionsFunctions['delete_order'] = $js;


		$js = " function deleteOrder(edit_id,btn){
	  		var edit_id = edit_id;
	  		if (btn == 'yes') {

	  		var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'row_actions.php',
                 method:'GET',
                 params: {'orders_id': edit_id,'type': 'delete_order','fillup_stock':'1'},
                 success: function(responseObject) {
                 		   orderds.reload();
                           Ext.MessageBox.alert('Message', '".TEXT_ORDER_DELETE_SUCCESS."');
                          }
                 });

			}
			if (btn == 'no') {
				var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'row_actions.php',
                 method:'GET',
                 params: {'orders_id': edit_id,'type': 'delete_order','fillup_stock':'0'},
                 success: function(responseObject) {
                 		   orderds.reload();
                           Ext.MessageBox.alert('Message', '".TEXT_ORDER_DELETE_SUCCESS."');
                          }
                 });
			}
		};";

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getParams_row_actions')) ? eval($plugin_code) : false;
		
		$params['rowActionsJavascript'] = $js;


		$params['display_deleteBtn'] = false;

		$params['rowActions']             = $rowActions;
		$params['rowActionsFunctions']    = $rowActionsFunctions;

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getParams_bottom')) ? eval($plugin_code) : false;
		
		return $params;
	}

	function _getSearchIDs($search_data) {

		$tmp_search_array = explode(' ', $search_data);

		$ts = '';
		foreach ($tmp_search_array as $ts) {

			if(strstr($ts, '-') ||	strstr($ts, '.') ||	strstr($ts, ':')){

				$date_array[] = $ts;

				unset($ts);
			}

			if($ts)
			$search_array[] = $ts;
		}

		$sql_tablecols = array('orders_id',
							   'customers_email_address',
	    					   'delivery_phone',
	    					   'delivery_firstname',
	    					   'delivery_lastname',
	    					   'delivery_company',
	    					   'delivery_street_address',
	    					   'delivery_city',
	    					   'delivery_postcode',
	    					   'billing_phone',
	    					   'billing_firstname',
	    					   'billing_lastname',
	    					   'billing_company',
	    					   'billing_street_address',
	    					   'billing_city',
	    					   'billing_postcode',
		);

		if(is_array($search_array)){
			foreach ($search_array as $search_key) {

				foreach ($sql_tablecols as $tablecol) {
					$sql_where[]= "(".$tablecol." LIKE '%".$search_key."%')";
				}

			}
		}

		if(is_array($sql_where)){
			$sql_data_array = " and (".implode(' or ', $sql_where).")";
		}

		if(is_array($date_array)){

			if(count($date_array)==2){

				if($date_array[0]){
					$date_from = strtotime($date_array[0]);
					$search_date_from = " and date_purchased >= '" . date("Y-m-d",$date_from) . "'";
				}

				if($date_array[1]){
					$date_to = strtotime($date_array[1]);
					$search_date_to = " and date_purchased <= '" . date("Y-m-d",$date_to) . "'";
				}

			}elseif(count($date_array)==1){

				$date = strtotime($date_array[0]);
				$search_date = " and date_purchased = '" . date("Y-m-d",$date) . "'";

			}

			$sql_data_array = $sql_data_array . $search_date . $search_date_from . $search_date_to;

		}
		return $sql_data_array;
	}

	function _get($oID = 0) {
		global $xtPlugin, $db, $language, $store_handler;

		if ($this->position != 'admin') return false;

		if ($oID === 'new') {
			$obj = $this->_set(array(), 'new');
			$oID = $obj->new_id;
		}

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_bottom')) ? eval($plugin_code) : false;
		
		
		$default_array=array('orders_id'=>'',
			'order_purchased' => '',
			'orders_status' =>'',
			'billing_firstname' => '',
								'billing_lastname' => '',
								'order_total' => '',
								'payment' => '',
								'store_data' => ''
								);

		if ($this->url_data['get_data']){
			if($oID){
	
				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_data')) ? eval($plugin_code) : false;
				
				$data[] = $this->_buildData($oID);
				
			}else{

				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_list_top')) ? eval($plugin_code) : false;
					
				if($this->url_data['c_oID'])
				$where .= ' and customers_id = "' . (int)$this->url_data['c_oID'] . '"';

				if($this->url_data['query']){
					$sql_where = $this->_getSearchIDs($this->url_data['query']);
					$where .= $sql_where;
				}

				if($this->url_data['sort']) {
					$data_read = new adminDB_DataRead(TABLE_ORDERS,'','','orders_id');
					$fields = $data_read->getTableFields(TABLE_ORDERS);

					$sort_by = $this->url_data['sort'];
					if (isset($fields[$sort_by])) {
						($this->url_data['dir']=='ASC') ? $sort_dir='ASC':$sort_dir='DESC';
						$where .=' ORDER BY '.$sort_by.' '.$sort_dir;
					}

				} else {
					$where.=' ORDER BY orders_id DESC';
				}

				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_list_qry')) ? eval($plugin_code) : false;
				
				
				if($this->sql_limit){

					$count_query = "SELECT orders_id FROM " . TABLE_ORDERS . " where orders_id != '0' ".$where."";
					$count_record = $db->Execute($count_query);
					$tmp_total_count = $count_record->RecordCount();

					$where .= ' LIMIT '.$this->sql_limit.'';
				}

				$query = "SELECT orders_id FROM " . TABLE_ORDERS . " where orders_id != '0' ".$where." ";

				$record = $db->Execute($query);

				if($record->RecordCount() > 0){

					while(!$record->EOF){

						$_data = array();
						$_data = $this->_buildData($record->fields['orders_id']);
						
						$data[] = array('orders_id'=>$_data['order_data']['orders_id'],
										'order_purchased' => $_data['order_data']['date_purchased'],
										'orders_status' => $_data['order_data']['orders_status'],
										'billing_firstname' => $_data['order_data']['billing_firstname'],
										'billing_lastname' => $_data['order_data']['billing_lastname'],
										'store_data' => $store_handler->getStoreName($_data['order_data']['shop_id']),
										'order_total' => $_data['order_total']['total']['formated'],
										'payment' => $_data['order_data']['payment_code']

							);

						$record->MoveNext();
					}$record->Close();
					
				}else{

					if (!$this->url_data['get_data']){
						$data[] = $default_array;
						}

				}
			}

		}else{

			$data[] = $default_array;

		}
		
		if($tmp_total_count){
			$count = $tmp_total_count;
		}else{
			$count = count($data);
		}
		
		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_bottom')) ? eval($plugin_code) : false;
		
		//echo $count;
		$obj = new stdClass;
		if (!$oID) {
			$obj->totalCount = $count;
			$obj->data = $data;
		} else {
			$obj->totalCount = 0;
			$obj->data = $data;
		}
		return $obj;
	}

	function _unset($id = 0) {
		global $db, $xtPlugin;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		if (!is_int($id)) return false;

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_unset')) ? eval($plugin_code) : false;
				
		$this->_deleteOrder($id);

		//$obj = new stdClass;
		//$obj->success = true;
		//return $obj;
	}

	
	function resetDownloadCount() {
		global $db;
		
		if (!$this->oID) return false;
		$data = array('download_count' => 0);
		$db->AutoExecute(TABLE_ORDERS_PRODUCTS_MEDIA, $data, 'UPDATE', "orders_products_id=".$this->oID."");
	} 
	
	function _setGroup($id){
		global $customers_status;

		$this->old_c_status = $customers_status->customers_status_id;
		$customers_status = new customers_status($id);

	}

	function _resetGroup(){
		global $customers_status;

		$customers_status = new customers_status($this->old_c_status);
		unset($this->old_c_status);

	}
	
	function _setPrice(){
		global $price, $customers_status;

		$price = new price($customers_status->customers_status_id, $customers_status->customers_status_master);
		
	}
	
	function _setStore($id){
		global $store_handler;

		$this->old_store = $store_handler->shop_id;
		$store_handler->shop_id = $id;

	}

	function _resetStore(){
		global $store_handler;

		$store_handler->shop_id = $this->old_store;
		unset($this->old_store);

	}		
}
?>