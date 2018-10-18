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

class order {

	public $customer;
	public $oID;

	function order($oID=0, $cID=0) {
		global $db, $xtPlugin;
		if($cID > 0){
			$this->customer = $cID;
		}elseif($cID==-1) {
			$rs = $db->Execute(
				"SELECT customers_id FROM ".TABLE_ORDERS." WHERE orders_id=?",
				array((int)$oID)
			);
			if ($rs->RecordCount()==1) $this->customer = $rs->fields['customers_id'];

		}else{
			$this->customer = $_SESSION['registered_customer'];
		}

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_order_id')) ? eval($plugin_code) : false;

		if($oID != 0){
			$this->oID = (int) $oID;
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
		$this->oID = (int) $oID;
	}
	
	function _setOrder($data, $type='complete', $add_type = 'insert', $update_orders_id='',$date_purchased=''){
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
			if ($date_purchased!='') $order_data['date_purchased'] =$date_purchased;
		}
        // fix shop_id losses in order-edit updates
        if($type=='complete' && $add_type=='update' && isset($data['shop_id']))
        {
            $order_data['shop_id'] = $data['shop_id'];
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

			// loginAs modus: hinzufügen des speichernden admins zu order_data
			if ($_SESSION['orderEditAdminUser'] || $_SESSION['admin_user']['user_id'])
			{
				if ($_SESSION['orderEditAdminUser'])
				{
					$order_data['ORDER_EDIT_ACL_USER'] = $_SESSION['orderEditAdminUser']['user_id'];
					unset($_SESSION['orderEditAdminUser']);
				}
				else
				{
					$order_data['ORDER_EDIT_ACL_USER'] = $_SESSION['admin_user']['user_id'];
				}
			}
			//error_log(print_r($_REQUEST,true));
			if ($_REQUEST['plugin'] === 'order_edit')
			{
				if ($_REQUEST['load_section'] === 'order_edit_edit_paymentShipping' && $_REQUEST['pg'] === 'apply')
				{
					$order_data['source_id'] = $this->order_data['source_id'];
				}
				elseif ($_REQUEST['load_section'] === 'order_edit_new_order' && $_REQUEST['pg'] === 'openNewOrderTabBackend')
				{
					$order_data['source_id'] = $data['source_id'];
				}
			}

			($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_data_bottom')) ? eval($plugin_code) : false;

			$order_data = array_merge($order_data, $customer_data, $delivery_data, $billing_data);

			if($add_type=='update' && $update_orders_id){
				$order_data['orders_id'] = $update_orders_id;
			}

			$this->_saveCustomerData($order_data, $add_type);

			$data['orders_id'] = $this->data_orders_id;
		}

		if($data['orders_id'])
			$this->oID = (int)$data['orders_id'];

		// Products
		if($type=='complete' || $type=='product'){

			if($add_type=='update'){
				$this->_deleteOrderProduct($data['orders_id'],'',true);
		                $this->_deleteOrderProductMedia($data['orders_id']);
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
				$this->_deleteOrderTotal($data['orders_id'], $data['shipping_code']);
			}

			$total_data =  $this->_buildTotalData($data['orders_id'], $data['total']);
			if(is_array($total_data)){
				while (list ($key, $value) = each($total_data)) {
					$this->oID = $data['orders_id'];
					$this->_saveTotalData($value, 'insert');
				}
			}

		}

		if ($add_type=='update') {
			// clean history
		}

		if (($add_type=='insert' && empty($data['orders_status']))) {
			// set default order status
			$orders_status_new = $this->_getPaymentOrderStatusNew($order_data['payment_code'], (int)$order_data['shop_id']);
			$this->_updateOrderStatus($orders_status_new, $order_data['comments'], 'false', 'false');
		}

		$data['success'] = true;
		//if ($add_type=='insert') $this->_sendOrderMail($data['orders_id']);
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_setOrder_product_return')) ? eval($plugin_code) : false;
		$this->_setStats($data['orders_id']);

		return  $data;

	}

	function _setStats($oID){
		global $db;

		$tmp_order = new order($oID, -1);
		if (!isset($tmp_order->order_customer))
			return;
		
		$data_array = array('products_count'=>$tmp_order->order_count,
							'orders_stats_price'=>$tmp_order->order_total['total']['plain']
							);

		$check_sql = "SELECT orders_id from ".TABLE_ORDERS_STATS." where orders_id = ?";
		$rs = $db->Execute($check_sql, array((int)$tmp_order->oID));
		if ($rs->RecordCount()>0) {
			$db->AutoExecute(TABLE_ORDERS_STATS, $data_array, 'UPDATE', "orders_id=".(int)$tmp_order->oID."");
		}else{
			$insert_array = array('orders_id'=>$tmp_order->oID);
			$data_array = array_merge($data_array, $insert_array);
			$db->AutoExecute(TABLE_ORDERS_STATS, $data_array, 'INSERT');
		}
		
		$data = array();
		$data['sales_stat_type'] = 1; // Checkouted
		$data['shop_id'] = $tmp_order->order_customer['shop_id'];
		$data['customers_status'] = $tmp_order->order_customer['customers_status'];
		$data['customers_id'] = $tmp_order->order_customer['customers_id'];
		$data['products_count'] = $tmp_order->order_count;
		$data['date_added'] = date('Y-m-d h:i:s', strtotime('now'));
		$db->AutoExecute(TABLE_SALES_STATS, $data, 'INSERT');
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
            $customer_array['customers_gender'] = $default_adress['customers_gender'];
		}

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

		$customer_array = array(
			'billing_gender'=>$b_data['customers_gender'],
			'billing_phone'=>$b_data['customers_phone'],
			'billing_mobile_phone'=>$b_data['customers_mobile_phone'],
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
			'billing_federal_state_code'=>$b_data['customers_federal_state_code'],
			'billing_federal_state_code_iso'=>$b_data['customers_federal_state_code_iso'],
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

		$customer_array = array(
			'delivery_gender'=>$d_data['customers_gender'],
			'delivery_phone'=>$d_data['customers_phone'],
			'delivery_mobile_phone'=>$d_data['customers_mobile_phone'],
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
			'delivery_federal_state_code'=>$d_data['customers_federal_state_code'],
			'delivery_federal_state_code_iso'=>$d_data['customers_federal_state_code_iso'],
			'delivery_address_book_id'=>$d_data['address_book_id']
		);

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
            // do we have discount on position ?
            if ($value['_original_products_price']['plain_otax']>$value['products_price']['plain_otax']) {
                $product_discount = 100-$value['products_price']['plain_otax']/$value['_original_products_price']['plain_otax']*100;
                $product_discount = round($product_discount,2);
                $value['products_discount']=$product_discount;
            }
			$product_array[$i] = array(
				'orders_id'=>$orders_id,
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

		require_once _SRV_WEBROOT.'/xtFramework/classes/class.order_edit_edit_item.php';
		if ($_REQUEST['pg'] === 'updateOrderItem')
		{
			order_edit_edit_item::hook_order_buildProductData_bottom($form_grid, $_REQUEST['orders_id']);
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
				 WHERE ml.link_id=? and m.download_status='order' and m.status='true'";
		$rs = $db->Execute($query, array((int)$data['products_id']));
		if ($rs->RecordCount()>0) {
			while (!$rs->EOF) {
				$product_media_array[] = array(
					'orders_id'=>$data['orders_id'],
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

				$total_array[$i] = array(
					'orders_id'=>$orders_id,
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

				$total_array[$i] = array(
					'orders_id'=>$orders_id,
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

		if ($add_type === 'insert')
		{
			$insert_record = array('date_purchased' => $db->BindTimeStamp(time()), 'last_modified' => $db->BindTimeStamp(time()));
			$record = array_merge($insert_record, $data);
			$db->AutoExecute(TABLE_ORDERS, $record, 'INSERT');
			$this->data_orders_id = $db->Insert_ID();
		}
		elseif ($add_type === 'update')
		{
			$update_record = array('last_modified' => $db->BindTimeStamp(time()));
			$record = array_merge($update_record, $data);
			$db->AutoExecute(TABLE_ORDERS, $record, 'UPDATE', 'orders_id='.(int)$data['orders_id']);
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
			$db->Execute(
				"UPDATE ".TABLE_PRODUCTS." SET products_ordered=products_ordered+".(int)$data['products_quantity']." WHERE products_id=?",
				array((int)$data['products_id'])
			);
            $db->Execute(
				"UPDATE ".TABLE_PRODUCTS." SET products_transactions=products_transactions+1 WHERE products_id=?",
				array((int)$data['products_id'])
			);

			$db->AutoExecute(TABLE_ORDERS_PRODUCTS, $data, 'INSERT');
			$this->data_orders_products_id = $db->Insert_ID();
			$this->_saveDownloadData($data, $add_type,$this->data_orders_products_id);
		}elseif($add_type=='update'){
			$db->AutoExecute(TABLE_ORDERS_PRODUCTS, $data, 'UPDATE', "orders_products_id=".(int)$data['orders_products_id']."");
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
			$db->AutoExecute(TABLE_ORDERS_PRODUCTS_MEDIA, $data, 'UPDATE', "orders_products_id=".(int)$data['orders_products_id']."");
			$this->data_orders_products_id = $data['orders_products_id'];
		}
	}

	function _saveTotalData($data, $add_type = 'insert'){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_saveTotalData_top')) ? eval($plugin_code) : false;
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
		$record = $db->Execute(
			"SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = ? and customers_id = ?",
			array((int)$oID, (int)$this->customer)
		);
		if($record->RecordCount() > 0){
			return true;
		}else{
			return false;
		}
	}

    function _getStoreByOrder($oID){
        global $db;
        $sql="SELECT `shop_id` FROM ".TABLE_ORDERS." WHERE `orders_id` = ? LIMIT 1;";
        $record=$db->Execute($sql, array((int)$oID));
        if($record->RecordCount()>0){
            return (int)$record->fields['shop_id'];
        }else{
            return false;
        }
    }

	function _buildData($oID){
		global $xtPlugin, $db, $store_handler;
		$oID = (int)$oID;
        $this->_setStore($this->_getStoreByOrder($oID));
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$order_data = $this->_getOrderData($oID);
		$order_products = $this->_getOrderProductData($oID, $order_data);
		$order_total_data = $this->_getOrderTotalData($oID, $order_data);
		$total = $this->_getTotal($order_products, $order_total_data, $order_data);
        $order_history = $this->_getOrderHistory($oID);
        $order_download_log = $this->_getOrderDownloadsLog($oID);

		$order_data = array(
			'order_customer' => $this->_buildCustomerData($order_data['customers_id']),
			'order_data'=>$order_data,
			'order_products' => $order_products,
			'order_total_data' => $order_total_data,
			'order_total' => $total,
			'order_history'=>$order_history,
			'order_count' => count($order_products),
			'order_download_log' => $order_download_log,
		);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_buildData_bottom')) ? eval($plugin_code) : false;
        $this->_resetStore();
		return $order_data;
	}
    
    /**
    * query infos to payment class
    * 
    * @param mixed $class
    */
    function _getPaymentInfo($class,$lng) {
        global $db;
        $rs = $db->Execute(
			"SELECT * FROM ".TABLE_PAYMENT_DESCRIPTION." pd, ".TABLE_PAYMENT." p WHERE pd.language_code=? and p.payment_id=pd.payment_id and p.payment_code=?",
			array($lng, $class)
		);
        if ($rs->RecordCount()==1) {
            return $rs->fields;
        }
        return false;
    }

    /**
     * query infos to shipping class
     *
     * @param mixed $class
     */
    function _getShippingInfo($class,$lng) {
        global $db;
        $rs = $db->Execute(
			"SELECT * FROM ".TABLE_SHIPPING_DESCRIPTION." shd, ".TABLE_SHIPPING." sh WHERE shd.language_code=? and sh.shipping_id=shd.shipping_id and sh.shipping_code=?",
			array($lng, $class)
		);
        if ($rs->RecordCount()==1) {
            return $rs->fields;
        }
        return false;
    }

	/**
	 * query infos to payment class
	 *
	 * @param mixed $class
	 */
	function _getPaymentOrderStatusNew($class, $shop_id)
	{
		global $db;

		$query = 'SELECT config_value FROM '.TABLE_CONFIGURATION_PAYMENT.' cp, '.TABLE_PAYMENT
			." p WHERE cp.shop_id = ? AND p.payment_id = cp.payment_id AND cp.config_key = ? AND p.payment_code = ?";
		$rs = $db->Execute($query, array($shop_id, strtoupper($class).'_ORDER_STATUS_NEW', $class));
		if ($rs->RecordCount() == 1)
		{
			return $rs->fields['config_value'];
		}

		return _STORE_DEFAULT_ORDER_STATUS;
	}

    function _getOrderHistory($oID) {
        global $xtPlugin, $db, $system_status, $price;
        ($plugin_code = $xtPlugin->PluginCode('class.order.php:__getOrderHistory_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        $record = $db->Execute(
			"SELECT * FROM ".TABLE_ORDERS_STATUS_HISTORY." WHERE `orders_id` = ? ORDER BY date_added ASC",
			array((int)$oID)
		);
        if($record->RecordCount() > 0){
           while(!$record->EOF){
               $record->fields['status_name']=$system_status->values['order_status'][$record->fields['orders_status_id']]['name'];
               $record->fields['status_image']=$system_status->values['order_status'][$record->fields['orders_status_id']]['image'];
               $data[]=$record->fields;
               $record->MoveNext();
           } 
           return $data;    
        }
    }
    
    function _getOrderDownloadsLog($oID) {
    	global $xtPlugin, $db, $system_status, $price;
    	($plugin_code = $xtPlugin->PluginCode('class.order.php:__getOrderDownloadsLog_top')) ? eval($plugin_code) : false;
    	if(isset($plugin_return_value))
    		return $plugin_return_value;
    
    	$record = $db->Execute(
			"SELECT * FROM ".TABLE_DOWNLOAD_LOG." WHERE `orders_id` = ? ORDER BY log_datetime ASC",
			array((int)$oID)
		);
    	$actions = array(1 => TEXT_DOWNLOAD_ACTION_CLIENT_DOWNLOAD, 2 => TEXT_DOWNLOAD_REENABLED);
    	if($record->RecordCount() > 0){
    		while(!$record->EOF){
    			$record->fields['download_action']=$actions[$record->fields['download_action']];
    			$data[]=$record->fields;
    			$record->MoveNext();
    		}
    		return $data;
    	}
    }
    
	function _getOrderData($oID){
		global $xtPlugin, $db, $system_status, $price, $language;
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM ".TABLE_ORDERS." WHERE `orders_id` = ?", array((int)$oID));
		if($record->RecordCount() > 0){
			while(!$record->EOF){

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderData')) ? eval($plugin_code) : false;
				// fix
				$record->fields['orders_status_id'] = $record->fields['orders_status'];

				$record->fields['orders_status'] = $system_status->values['order_status'][$record->fields['orders_status']]['name'];
				$record->fields['orders_status_image'] = $system_status->values['order_status'][$record->fields['orders_status']]['image'];
				$record->fields['date_purchased_plain'] = $record->fields['date_purchased'];
				$record->fields['date_purchased'] = date_short($record->fields['date_purchased']);

				$_payment = $this->_getPaymentInfo($record->fields['payment_code'], $language->code);
 				if (is_array($_payment))
					$record->fields['payment_name'] = $_payment['payment_name'];

				$_shipping = $this->_getShippingInfo($record->fields['shipping_code'], $language->code);
				if (is_array($_shipping))
					$record->fields['shipping_name'] = $_shipping['shipping_name'];

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
		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderProductData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		$record = $db->Execute(
			"SELECT * FROM ".TABLE_ORDERS_PRODUCTS." WHERE `orders_id` = ?",
			array((int)$oID)
		);
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
				$_price = $_price_otax;
				
				if($record->fields['allow_tax'] == 1){
					$_final_price_otax = $price->_roundPrice($_price) * $record->fields['products_quantity'];
					$_add_single_price_otax = $_add_single_price;
					$_price = $price->_AddTax($_price, $record->fields['products_tax']);
					$_price = $price->_roundPrice($_price);
					$_add_single_price = $price->_AddTax($_add_single_price, $record->fields['products_tax']);
					$_final_price  = $_price * $record->fields['products_quantity'];
					$_final_price = $_final_price + $_add_single_price;
				}
				else {
					$_price = $price->_roundPrice($_price);
					// bugfix 4.0.14 round and display correct price brutto and netto
					$_final_price_otax = $_price * $record->fields['products_quantity'];
					$_add_single_price_otax = $_add_single_price;
					$_final_price = $_final_price_otax + $_add_single_price;
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
				
				$record2 = $db->Execute(
					"SELECT * FROM " . TABLE_PRODUCTS . " WHERE products_id = ?",
					array($record->fields['products_id'])
				);
				
				if($record2->RecordCount() > 0){
					$record->fields['products_weight'] =$record2->fields['products_weight'];
					$record->fields['total_products_weight'] =$record->fields['products_quantity']*$record2->fields['products_weight'];
					$record->fields['total_order_weight'] = $record->fields['total_order_weight'] + $record->fields['total_products_weight'];
					$record2->Close();
				}
				
				if($record->fields['products_data']){
					$arr = unserialize($record->fields['products_data']);
					$record->fields['products_info_data'] = $arr;
	
					if (is_array($arr))
					foreach ($arr as $tkey => $tval) {
						$record->fields['products_info_options'][] = array('text'=>'TEXT_'.strtoupper($tkey), 'data'=>$tkey,  'value'=>$tval);
					}
	
               		($plugin_code = $xtPlugin->PluginCode('class.order.php:_order_products_info_options')) ? eval($plugin_code) : false;
				}
                //add stock rule
                if (_SYSTEM_STOCK_RULES == 'true'){
                    $stock_image = $this->getProductStockTrafficRule($record->fields['products_id']);
                    if ($stock_image != false) $record->fields['stock_image'] = $stock_image;
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

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderTotalData_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$record = $db->Execute("SELECT * FROM ".TABLE_ORDERS_TOTAL." WHERE `orders_id` = ?", array((int)$oID));
		if($record->RecordCount() > 0){
			while(!$record->EOF){
				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getOrderTotalData')) ? eval($plugin_code) : false;

				// Calc Tax
				$_tax = $price->_calcTax($price->_roundPrice($record->fields['orders_total_price']), $record->fields['orders_total_tax']);

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

	function _getTotal($products, $sub_total, $order_data)
	{
		global $xtPlugin, $price, $currency;

		// fix für nicht initialisierte variable $total_tax
		if (empty($total_tax)) //  nur wenn nicht schon durch andere hook-nutzer initialisiert
		{
			$total_tax = array();
		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		$product_data = $products;
		$order_total_data = $sub_total;
		$product_data_tax = array();
		$product_data_total = 0;
		$product_data_total_otax = 0;
		$total_otax = 0;

		if(is_data($product_data)){
			while (list ($key, $value) = each($product_data)) {

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_content_top')) ? eval($plugin_code) : false;
		
				$product_data_tax[$value['products_tax_class']] += $value['products_final_tax']['plain'];
				$product_data_tax_rate[$value['products_tax_class']] = $value['products_tax_rate'];
				$product_data_total += $value['products_final_price']['plain'];
				$product_data_total_otax += $value['products_final_price']['plain_otax'];

				$total_tax[$value['products_tax_class']] += $value['products_final_tax']['plain'];
				$total_tax_rate[$value['products_tax_class']] = $value['products_tax_rate'];
				
				$total_otax += $value['products_final_price']['plain_otax'];
				$total_otax += $value['products_add_price']['plain_otax'];
				
			}
		}

		$order_total_data_tax = array();
		$order_total_data_total = 0;
		$order_total_data_total_otax = 0;

		if(is_data($order_total_data)){
			while (list ($key, $value) = each($order_total_data)) {

				($plugin_code = $xtPlugin->PluginCode('class.order.php:_getTotal_sub_content_top')) ? eval($plugin_code) : false;

				$order_total_data_tax[$value['orders_total_tax_class']] += $value['orders_total_final_tax']['plain'];
				$order_total_data_tax_rate[$value['orders_total_tax_class']] = $value['orders_total_tax_rate'];
				$order_total_data_total += $value['orders_total_final_price']['plain'];
				$order_total_data_total_otax += $value['orders_total_final_price']['plain_otax'];

				$total_tax[$value['orders_total_tax_class']] += $value['orders_total_final_tax']['plain'];
				$total_tax_rate[$value['orders_total_tax_class']] = $value['orders_total_tax_rate'];
								
				$total_otax += $value['orders_total_final_price']['plain_otax'];
			}
		}
		
		$tax_sum = 0;
		foreach($total_tax as $k=>$v){
			$tax_sum += $v;
		}
		
		$c_status = new customers_status();
		$c_status->_getStatus($order_data['customers_status']);
		
		$total = $product_data_total + $order_total_data_total;
		if($c_status->customers_status_add_tax_ot =='1' && $c_status->customers_status_show_price_tax == '0'){
			$total += $tax_sum;
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

			if($order_total_data_tax_rate[$key] != 0 && empty($product_data_tax_rate[$key]))
			$new_total_tax[$key] =  array('tax_value' => $price->_Format(array('price'=>$value, 'format'=>true, 'format_type'=>'default')), 'tax_key'=>round($total_tax_rate[$key], $currency->decimals));
						
		}
			
		$data = array(
			'product_tax' => $new_product_data_tax,
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

		$query = "SELECT o.orders_id FROM ".TABLE_ORDERS." o INNER JOIN ".TABLE_ORDERS_PRODUCTS." op ON o.orders_id = op.orders_id INNER JOIN ".TABLE_ORDERS_PRODUCTS_MEDIA." opm ON op.orders_id = opm.orders_id WHERE o.customers_id = ?";

		$rs = $db->Execute($query, array($cID));
		if ($rs->RecordCount()>0)
			return true;
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

		$query = "SELECT o.date_purchased,o.orders_status,o.orders_id,opm.media_id, md.*, opm.download_count, opm.orders_products_id, m.* FROM ".TABLE_MEDIA." m INNER JOIN ".TABLE_ORDERS." o ON o.orders_id = ? INNER JOIN ".TABLE_ORDERS_PRODUCTS_MEDIA." opm ON m.id = opm.media_id LEFT JOIN ".TABLE_MEDIA_DESCRIPTION." md ON (m.id = md.id  and md.language_code='de') WHERE opm.orders_id = ?";

		$rs = $db->Execute($query, array($orders_id, $orders_id));
		$download_data = array();

		include_once(_SRV_WEBROOT.'xtFramework/classes/class.download.php');
		$download = new download();

		while (!$rs->EOF) {

			$file = $rs->fields['file'];
			$file = _SRV_WEBROOT.'media/files/'.$file;
			if (file_exists($file)) {
				$_data = $rs->fields;
				if ($_data['media_name']=='') $_data['media_name'] = $rs->fields['file'];
				$historyDate = $db->Execute(
					"SELECT date_added FROM ".TABLE_ORDERS_STATUS_HISTORY." WHERE orders_id = ? and orders_status_id = ? ORDER BY date_added DESC LIMIT 1 ",
					array($_data['orders_id'], $_data['orders_status'])
				);
				if ($historyDate->RecordCount()>0) {
					$_data['date_purchased'] = $historyDate->fields['date_added'];
				}
				$_data['media_size'] = filesize($file);

				// count left
				$count_left = $_data['max_dl_count']-$_data['download_count'];

				// valid until
				if ($_data['max_dl_days']>0) {
					$valid_until = vtn_date_add(datetime_to_timestamp($_data['date_purchased']),$_data['max_dl_days']);
					$_data['allowed_until'] = format_timestamp($valid_until);
				}


				// set allowed flag
				$_data['download_allowed'] = '1';
				if ($_data['max_dl_count']>0)
					$_data['allowed_count'] = $count_left;

				// download link
				$link = $xtLink->_link(array('page'=>'customer','paction'=>'download_overview','params'=>'order='.$_data['orders_id'].'&media='.$_data['media_id'].'&opid='.$_data['orders_products_id']));
				$_data['download_url'] = $link;

				if ($count_left<=0 && $_data['max_dl_count']>0) $_data['download_allowed'] = 0;

				// check if allowed
				if (!$download->_checkDowloadAllowed($_data['date_purchased'],$_data['max_dl_days'],$_data['max_dl_count'],$_data['download_count'])) {
					$_data['download_allowed'] = 0;
				}

				// check permission
				if (!$download->checkDownloadPermission($_data['media_id'], 'order')) {
					$rs->MoveNext();
					continue;
				}

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
		global $xtPlugin, $price, $db, $xtLink, $system_status;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_getDownloadList_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($cID == 0){
			$cID = $this->customer;
		}

		$cID=(int)$cID;

		$query = "SELECT o.orders_id, o.orders_status FROM ".TABLE_ORDERS." o INNER JOIN ".TABLE_ORDERS_PRODUCTS." op ON o.orders_id = op.orders_id INNER JOIN ".TABLE_ORDERS_PRODUCTS_MEDIA." opm ON op.orders_id = opm.orders_id WHERE o.customers_id = ?";

		$rs = $db->Execute($query, array((int)$cID));
		$data = array();
		if ($rs->RecordCount()>0) {

			while (!$rs->EOF) {
				$_data = array();
				$_data['order_data']['orders_id'] = $rs->fields['orders_id'];
				
				$_data['order_data']['orders_status'] = $system_status->values['order_status'][$rs->fields['orders_status']]['name'];
				
				$d_data = array('download_data'=>$this->_getDownloads($rs->fields['orders_id']));
				if (count($d_data['download_data'])==0)
				{
					$d_data['download_data'][0]['media_description'] = TEXT_NO_DOWNLOAD_IN_LANG;
				}
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
		$query = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE customers_id = '" . (int)$cID . "' order by orders_id desc";

		$pages = new split_page($query, $limit, $xtLink->_getParams(array ('next_page', 'info'), array('page_action')));

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

		$db->Execute("DELETE FROM ".TABLE_ORDERS." WHERE orders_id = ?", array((int) $orders_id));

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
					$rs = $db->Execute("SELECT products_id,products_quantity FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = ?", array((int) $orders_id ));
					while (!$rs->EOF) {
						$stock->addStock($rs->fields['products_id'],$rs->fields['products_quantity']);
						$rs->MoveNext();
					}
				}

				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = ?", array((int) $orders_id));
			}elseif(empty($orders_id) && !empty($orders_products_id)){

				if ($refill_stock) {
					$stock = new stock();
					$rs = $db->Execute("SELECT products_id,products_quantity FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id = ?", array((int) $orders_products_id));
					while (!$rs->EOF) {
						$stock->addStock($rs->fields['products_id'],$rs->fields['products_quantity']);
						$rs->MoveNext();
					}
				}
				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id = ?", array((int) $orders_products_id));
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
				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS_MEDIA." WHERE orders_id = ?", array((int) $orders_id));
			}elseif(empty($orders_id) && !empty($media_id)){
				$db->Execute("DELETE FROM ".TABLE_ORDERS_PRODUCTS_MEDIA." WHERE media_id = ?", array((int) $media_id));
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
	function _deleteOrderTotal($orders_id, $shipping_code = ''){
		global $xtPlugin, $db;

		if (!empty($shipping_code)) {
			$rs = $db->Execute('SELECT shipping_id FROM ' . TABLE_SHIPPING . ' WHERE shipping_code=?', array($shipping_code));
			
			// Do not delete already deleted shipping totals because they can't be recalculated.
			if ($rs->RecordCount() == 0) {
				return;
			}
		}
		$db->Execute("DELETE FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = ?", array((int) $orders_id));
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

		$db->Execute("DELETE FROM ".TABLE_ORDERS_STATUS_HISTORY." WHERE orders_id = ?", array((int) $orders_id));

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderHistory_bottom')) ? eval($plugin_code) : false;
	}

	function _deleteOrderStats($orders_id){
		global $xtPlugin, $db;

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderStats_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$db->Execute("DELETE FROM ".TABLE_ORDERS_STATS." WHERE orders_id = ?", array((int) $orders_id));

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_deleteOrderStats_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * update order status, send status update to customer
	 *
	 * @param int $status
	 * @param string $comments
	 */
	function _updateOrderStatus($status, $comments = '', $send_email = 'true', $send_comments = 'true', $trigger = 'user', $callback_id = 0, $callback_message = '')
	{
		global $xtPlugin, $db, $system_status, $order_edit_controller;

		$status = (int)$status;
		$extra_assign = array();

		// kein order status update bei backend edit
		if ($order_edit_controller->isActive())
		{
			$db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$status.', last_modified = now() WHERE orders_id = ?', array((int)$this->oID));
			return true;
		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_updateOrderStatus_top')) ? eval($plugin_code) : false;

		$db->Execute(
			'UPDATE '.TABLE_ORDERS.' SET orders_status = '.$status.', last_modified = now() WHERE `orders_id` = ?',
			array((int)$this->oID)
		);

		$customer_notified = (int) ($send_email === 'true');
		if ($send_comments === 'true') {
			$comments_txt = $comments;
			$show_comments = 1;
		} else {
			$comments_txt = '';
			$show_comments = 0;
		}

		$data_array = array(
			'orders_id' => (int)$this->oID,
			'orders_status_id' => $status,
			'customer_notified' => $customer_notified,
			'customer_show_comment' => $show_comments,
			'comments' => $comments,
			'change_trigger' => $trigger,
			'callback_id' => $callback_id,
			'callback_message' => $callback_message,
		);

		// OE-3 erzeugung von order history eintrag verhindern bei add/edit
		if ($_REQUEST['plugin'] === 'order_edit' && $_REQUEST['load_section'] !== 'order_edit_new_order') {
			$data_array = array();
		}

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_updateOrderStatusHistory_data')) ? eval($plugin_code) : false;

		$db->AutoExecute(TABLE_ORDERS_STATUS_HISTORY,$data_array,'INSERT');

		$statusName = $system_status->values['order_status'][$status]['name'];

		if ($system_status->values['order_status'][$data_array['orders_status_id']]['enable_download'])
		{
			$this->resetDownloadCount();
		}
		
		$order = $db->Execute(
			"SELECT customers_status FROM " . TABLE_ORDERS . " WHERE orders_id=?",
			array((int)$this->oID)
		);
		
		if ($send_email === 'true')
			$this->_sendStatusMail($statusName, $comments_txt,$extra_assign,$data_array['orders_status_id'], $order->fields['customers_status']);

		($plugin_code = $xtPlugin->PluginCode('class.order.php:_updateOrderStatus_bottom')) ? eval($plugin_code) : false;
	}

	/**
	 * send order mail to customer
	 *
	 * @return boolean
	 */
	function _sendOrderMail(){
		global $xtPlugin, $db, $store_handler,$language,$customers_status;

        $special=-1;

		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendOrderMail_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value)) return $plugin_return_value;
			
        $old_lng_code = $language->code;
        $old_status = $customers_status->customers_status_id;
        $customers_status->customers_status_id = $this->order_customer['customers_status'];
        $language->_getLanguage($this->order_data['language_code']);
        $ordermail = new xtMailer('send_order', $language->code, $this->order_customer['customers_status'], $special, $this->order_data['shop_id']);
        $ordermail->_setStore($this->order_data['shop_id']);
        $ordermail->getPermission();
        $ordermail->_addReceiver($this->order_data['customers_email_address'], $this->order_data['billing_lastname'].' '.$this->order_data['billing_firstname']);
		$ordermail->_assign('order_customer',$this->order_customer);
		$ordermail->_assign('order_data',$this->order_data);
		
		$ordermail->_assign('order_products',$this->order_products);
		$ordermail->_assign('order_total_data',$this->order_total_data);
		$ordermail->_assign('total',$this->order_total);
		$ordermail->_assign('order_count',$this->order_count);

		// get text for payment method
		$rs = $db->Execute(
			"SELECT pd.payment_email_desc_txt,pd.payment_email_desc_html FROM ".TABLE_PAYMENT_DESCRIPTION." pd, ".TABLE_PAYMENT." p WHERE pd.language_code=? and p.payment_id=pd.payment_id and p.payment_code=?",
			array($language->code, $this->order_data['payment_code'])
		);
		if ($rs->RecordCount()==1) {
			// old payment info
			$ordermail->_assign('payment_info',$rs->fields['payment_email_desc_txt']);

			// new payment info
			$ordermail->_assign('payment_info_html',$rs->fields['payment_email_desc_html']);
			$ordermail->_assign('payment_info_txt',$rs->fields['payment_email_desc_txt']);
		}
		$shipping_d = $this->_getShippingInfo($this->order_data['shipping_code'],$language->code); 
        if ( $shipping_d){
            $ordermail->_assign('shipping_info_html',$shipping_d['shipping_email_desc_html']);
            $ordermail->_assign('shipping_info_txt',$shipping_d['shipping_email_desc_txt']); 
        }
		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendOrderMail_bottom')) ? eval($plugin_code) : false;
		$ordermail->_sendMail();
        $ordermail->_resetStore();
        $language->_getLanguage($old_lng_code);
        $customers_status->customers_status_id = $old_status;
		return true;

	}

	/**
	 * send order status mail to customer
	 *
	 * @param int $status
	 * @param string $comments
	 */
	function _sendStatusMail($status,$comments,$extra_assign = array(),$status_id, $customers_status) {
		global $xtPlugin, $db,$store_handler;

		($plugin_code = $xtPlugin->PluginCode('class.orders.php:_sendStatusMail_top')) ? eval($plugin_code) : false;

		$statusmail = new xtMailer('update_order-admin', $this->order_data['language_code'], $customers_status, $status_id, $this->order_data['shop_id']);
		$statusmail->_addReceiver($this->order_data['customers_email_address'],$this->order_data['billing_lastname'].' '.$this->order_data['billing_firstname']);
		$statusmail->_assign('order_data',$this->order_data);
		$statusmail->_assign('order_products',$this->order_products);
		$statusmail->_assign('order_total_data',$this->order_total_data);
		$statusmail->_assign('total',$this->order_total);
		$statusmail->_assign('order_count',$this->order_count);
		$statusmail->_assign('comments',$comments);
		
        $sql="SELECT status_name FROM " . TABLE_SYSTEM_STATUS_DESCRIPTION . " WHERE status_id=? AND language_code=?;";
        $status_name_res=$db->Execute($sql, array($status_id, $this->order_data['language_code']));
		$statusmail->_assign('status',$status_name_res->fields['status_name']);
		
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
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol']  = true;
		if (isset($this->sql_limit)) {
			$exp= explode(",",$this->sql_limit);
			$params['PageSize'] = trim($exp[1]);
		}
		
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
		global $xtPlugin;
		
		$tmp_search_array = explode(' ', $search_data);

		$ts = '';
		foreach ($tmp_search_array as $ts) {
			if ( strstr($ts, '@')===FALSE) {
				if(strstr($ts, '-') ||	strstr($ts, '.') ||	strstr($ts, ':')){
					$date_array[] = $ts;	
					unset($ts);
				}
			}
			
			if($ts)
				$search_array[] = trim($ts);
		}

		$sql_tablecols = array(
			'orders_id',
			'customers_email_address',
			'delivery_phone',
			'delivery_mobile_phone',
			'delivery_firstname',
			'delivery_lastname',
			'delivery_company',
			'delivery_street_address',
			'delivery_city',
			'delivery_postcode',
			'billing_phone',
			'billing_mobile_phone',
			'billing_firstname',
			'billing_lastname',
			'billing_company',
			'billing_street_address',
			'billing_city',
			'billing_postcode',
		);
        
        ($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_getSearchIDs')) ? eval($plugin_code) : false;

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

	function _get($oID = 0)
	{
		global $xtPlugin, $db, $language, $store_handler;

		if ($this->position !== 'admin') return false;

		if ($oID === 'new')
		{
			$obj = $this->_set(array(), 'new');
			$oID = (int)$obj->new_id;
		}
		else $oID = (int)$oID;

		$default_array = array(
			'orders_id' => 0,
			'order_purchased' => '',
			'orders_status' => '',
			'billing_firstname' => '',
			'billing_lastname' => '',
			'order_total' => '',
			'payment' => '',
			'store_data' => '',
			'orders_source' => '',
			'orders_source_external' => '',
		);
		
		if (_SYSTEM_ORDER_EDIT_SHOW_ORDER_EDITOR_COLUMN === 'true')
		{
			$default_array['order_edit_acl_user'] = '';
		}

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_top')) ? eval($plugin_code) : false;

		if ($this->url_data['get_data'])
		{
			if($oID)
			{
				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_data')) ? eval($plugin_code) : false;

				$data[] = $this->_buildData($oID);
			}
			else
			{
				$where = '';
				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_list_top')) ? eval($plugin_code) : false;

				if ( ! empty($this->url_data['c_oID']))
					$where .= ' AND customers_id = '.(int) $this->url_data['c_oID'];

				if ( ! empty($this->url_data['query']))
				{
					$where .= $this->_getSearchIDs($this->url_data['query']);
				}

				if ( ! empty($this->url_data['sort']))
				{
					if ($this->url_data['sort'] === 'order_purchased')
					{
						$this->url_data['sort'] = 'date_purchased';
					}

					$data_read = new adminDB_DataRead(TABLE_ORDERS, '', '', 'orders_id');
					$fields = $data_read->getTableFields(TABLE_ORDERS);

					$sort_by = $this->url_data['sort'];
					if (isset($fields[$sort_by]))
					{
						$sort_dir = ($this->url_data['dir'] === 'ASC') ? 'ASC': 'DESC';
						$where .= ' ORDER BY '.$sort_by.' '.$sort_dir;
					}

				}
				else
				{
					$where .=' ORDER BY '.TABLE_ORDERS.'.orders_id DESC';
				}

				($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_list_qry')) ? eval($plugin_code) : false;

				// andreya: what the hell is vvv?!
				//vvv
				require(_SRV_WEBROOT."xtFramework/admin/filter/class.ordersPost.php");
				if (isset($where_ar) && count($where_ar) > 0)
				{
					$where = ' AND '.implode(' AND ', $where_ar).$where;
				}
				// end vvv

				if ($this->sql_limit)
				{
					$count_query = "SELECT ".TABLE_ORDERS.".orders_id FROM ".TABLE_ORDERS." ". (isset($ad_table) ? $ad_table : '') ." WHERE ".TABLE_ORDERS.".orders_id != 0 ".$where;

					$count_record = $db->Execute($count_query);
					$tmp_total_count = $count_record->RecordCount();

					$where .= ' LIMIT '.$this->sql_limit.'';
				}

				$record = $db->Execute("SELECT ".TABLE_ORDERS.".orders_id FROM ".TABLE_ORDERS." ".(isset($ad_table) ? $ad_table : '')." WHERE ".TABLE_ORDERS.".orders_id != 0 ".$where);
				if ($record->RecordCount() > 0)
				{
					while( ! $record->EOF)
					{
						$_data = $this->_buildData($record->fields['orders_id']);

						$sql = "SELECT o.orders_source_external_id, os.source_name, CONCAT(acl.firstname, ' ', acl.lastname) AS order_edit_acl_user FROM ".TABLE_ORDERS
							.' o LEFT OUTER JOIN '.TABLE_ORDERS_SOURCE.' os ON o.source_id = os.source_id LEFT OUTER JOIN '
							.TABLE_ADMIN_ACL_AREA_USER.' acl ON o.order_edit_acl_user = acl.user_id WHERE o.orders_id = ?';
						$sr = $db->Execute($sql, array($_data['order_data']['orders_id']));
						if ( ! $sr->RecordCount()) {
							$sName = '';
							$eId = '';
							$acl_user = '';
						} else {
							$sName = (isset($sr->fields['source_name']) && defined('TEXT_'.$fields['source_name']))
								? constant('TEXT_'.$fields['source_name'])
								: $sr->fields['source_name'];
							$eId = $sr->fields['orders_source_external_id'];
							$acl_user = trim($sr->fields['order_edit_acl_user']);
						}

						$tmp_data = array(
							'orders_id' => $_data['order_data']['orders_id'],
							'order_purchased' => $_data['order_data']['date_purchased_plain'],
							'orders_status' => $_data['order_data']['orders_status'],
							'billing_firstname' => $_data['order_data']['billing_firstname'],
							'billing_lastname' => $_data['order_data']['billing_lastname'],
							'store_data' => $store_handler->getStoreName($_data['order_data']['shop_id']),
							'order_total' => $_data['order_total']['total']['formated'],
							'payment' => $_data['order_data']['payment_code'],
							'orders_source' => $sName,
							'orders_source_external' => $eId,
						);
						
						if (_SYSTEM_ORDER_EDIT_SHOW_ORDER_EDITOR_COLUMN === 'true')
						{
							$tmp_data['order_edit_acl_user'] = $acl_user;
						}
						
						$data[] = $tmp_data;

						($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_order_list_while')) ? eval($plugin_code) : false;

						$record->MoveNext();
					}

					$record->Close();
				} elseif ( ! $this->url_data['get_data']) {
					$data[] = $default_array;
				}
			}
		} else {
			$data[] = $default_array;
		}

		$count = ($tmp_total_count) ? $tmp_total_count : count($data);

		// loginAs modus: anzeige des admins der die order bearbeitet hat 1/2
		if (_SYSTEM_ORDER_EDIT_SHOW_ORDER_EDITOR_COLUMN === 'true') {
			$default_array['ORDER_EDIT_ACL_USER'] = '';
		}

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_get_bottom')) ? eval($plugin_code) : false;

		$obj = new stdClass;
		$obj->totalCount = ($oID) ? 0 : $count;
		$obj->data = $data;

		return $obj;
	}

	function _unset($id = 0)
	{
		global $db, $xtPlugin;
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		if (!is_int($id)) return false;

		($plugin_code = $xtPlugin->PluginCode(__CLASS__.':_unset')) ? eval($plugin_code) : false;

		$this->_deleteOrder($id);
	}

	
	function resetDownloadCount() {
		global $db;
		
		if (!$this->oID) return false;
		$data = array('download_count' => 0);
		$db->AutoExecute(TABLE_ORDERS_PRODUCTS_MEDIA, $data, 'UPDATE', "orders_products_id=".(int)$this->oID);
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

    function getProductStockTrafficRule($pid) {
        global $db, $system_status;

        if (count($system_status->values['stock_rule'])==0) return false;

        reset($system_status->values['stock_rule']);
        //get products_quantity, products_average_quantity
        $rs = $db->Execute("SELECT products_average_quantity, products_quantity FROM ".TABLE_PRODUCTS. " WHERE products_id=? LIMIT 1", array($pid));
        if ($rs->RecordCount()>0) {
            $products_quantity = $rs->fields['products_quantity'];
            $products_average_quantity = $rs->fields['products_average_quantity'];
        }

        if ($products_quantity > 0) {
            if ($products_average_quantity <= 0) {
                $percentage = 100;
            }else{
                $percentage = $products_quantity / $products_average_quantity * 100;
            }
            if ($percentage > 100)
                $percentage = 100;
        } else {
            $percentage = 0;
        }

        while (current($system_status->values['stock_rule'])) {
            $current = current($system_status->values['stock_rule']);

            if ($percentage < $current['data']['percentage']) {
                $next = next($system_status->values['stock_rule']);
                if ($percentage > $next['data']['percentage']) {
                    return array ('name' => $next['name'],'image' => $next['image']);
                } else {
                    prev($system_status->values['stock_rule']);
                }
            } elseif ($percentage == $current['data']['percentage']) {
                return array ('name' => $current['name'],'image' => $current['image']);
            }
            next($system_status->values['stock_rule']);
        }
    }
}