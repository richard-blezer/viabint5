<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');


class order_edit_products extends product{

    public $position = null;
    public $_master_key = 'orders_products_id';

	protected $_table_xsell = TABLE_PRODUCTS_CROSS_SELL;

    function setPosition($position)
    {
        $this->position = $position;
    }

	function _getParams() {
        global $language;

        if (_LIC_TYPE=='free') die('not available in free license');

        $params = array();
        $header['products_id'] = array('type'=>'hidden', 'width' => 20);
        $header['products_name_'.$language->code] = array('readonly'=>true);
        $header['products_model'] = array('readonly'=>true);
        $header['products_quantity'] = array('readonly'=>true);
        $header['products_price'] = array('readonly'=>false);
        $header['products_preis_formated'] = array('readonly'=>true);
        $header['products_status'] = array('readonly'=>true);

        $params['display_checkCol']  = false;
        $params['display_editBtn']  = false;
        $params['display_deleteBtn']  = false;
        $params['display_newBtn']  = false;
        $params['display_GetSelectedBtn'] = false;

        $params['display_searchPanel']  = false;

        $params['gridType']  = 'EditGrid';

        $params['header']         = $header;
        $params['master_key']     = $this->_master_key;
        $params['default_sort']   = $this->_master_key;

        if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
            $params['include'] = array (
                'orders_products_id',
                'products_id',
                'products_name',
                'products_model',
                'products_quantity',
                'products_preis_formated',
                'order_products_quantity',
                'products_order_price',
                'products_status');
        }

        // update/save position
        $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? ",sec:'".$_SESSION['admin_user']['admin_key']."'": '';
        $js = "
                var conn = new Ext.data.Connection();
                conn.request({
                    url: 'adminHandler.php',
                    method:'GET',
                    params: {
                        pg:             'updateOrderItem',
                        load_section:   'order_edit_products',
                        plugin:         'order_edit',
                        orders_id:      orders_id,
                        orders_products_id: record.data.orders_products_id,
                        products_id: record.data.products_id,
                        order_products_quantity: record.data.order_products_quantity,
                        products_order_price: record.data.products_order_price".$add_to_url."
                    },
                    success: function(responseObject)
                    {
                        var r = Ext.decode(responseObject.responseText);
                        if (!r.success || null != r.msg)
                        {
                            Ext.MessageBox.alert('Error', r.msg);
                        }
                        order_edit_productsds.reload();
                        contentTabs.getActiveTab().getUpdater().refresh();
                    },
                    failure: function(responseObject)
                    {
                        var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                        var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                        Ext.MessageBox.alert(title,msg);
                        console.log(responseObject)
                    }
                });";

        $rowActionsFunctions['ORDER_EDIT_UPDATE_ITEM'] = $js;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_UPDATE_ITEM', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ORDER_EDIT_UPDATE_ITEM);

        // staffel berechnen
        $js = "
                var conn = new Ext.data.Connection();
                conn.request({
                    url: 'adminHandler.php',
                    method:'GET',
                    params: {
                        pg:             'calculateGraduatedPrice',
                        load_section:   'order_edit_add_products',
                        plugin:         'order_edit',
                        orders_id:      orders_id,
                        products_id: record.data.products_id,
                        order_products_quantity: record.data.order_products_quantity".$add_to_url."
                    },
                    success: function(responseObject)
                    {

                        var r = Ext.decode(responseObject.responseText);
                        if (!r.success || null != r.msg)
                        {
                            Ext.MessageBox.alert('Error', r.msg);
                            return;
                        }
                        console.log(r.price);
                        record.set('products_order_price', r.price);
                    },
                    failure: function(responseObject)
                    {
                        var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                        var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                        Ext.MessageBox.alert(title,msg);
                        console.log(responseObject)
                    }
                });";

        $rowActionsFunctions['ORDER_EDIT_CALCULATE_GRADUATED'] = $js;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_CALCULATE_GRADUATED', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ORDER_EDIT_CALCULATE_GRADUATED);


        // remove item
        $js = "
            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'removeOrderItem',
                    load_section:   'order_edit_products',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    orders_products_id: record.data.orders_products_id,
                    products_id: record.data.products_id,
                    products_quantity: record.data.products_quantity".$add_to_url."
                },
                success: function(responseObject)
                {
                    var r = Ext.decode(responseObject.responseText);
                    if (!r.success || null != r.msg)
                    {
                        Ext.MessageBox.alert('Error', r.msg);
                    }
                    contentTabs.getActiveTab().getUpdater().refresh();
                    order_edit_productsds.reload();
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    console.log(responseObject)
                }
            });";
        $rowActionsFunctions['ORDER_EDIT_REMOVE_ITEM'] = $js;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_REMOVE_ITEM', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ORDER_EDIT_REMOVE_ITEM);


        if (count($rowActionsFunctions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

		return $params;
	}

    public function updateOrderItem($data)
    {
        $result = new stdClass();
        $result->success = false;

        $oId = (int) $data['orders_id'];
        $opId = (int) $data['products_id'];
        if ($oId && $opId)
        {
            global $order_edit_controller;
            $order = $order_edit_controller->getOrder();

            $priceOverride = $_SESSION['order_edit_priceOverride'];
            if(!$priceOverride)
            {
                $priceOverride = array();
            }
            else if (!$priceOverride[$oId])
            {
                $priceOverride[$oId] = array();
            }
            $priceOverride[$oId][$opId] = $data['products_order_price'];
            $_SESSION['order_edit_priceOverride'] = $priceOverride;

            $cart = new cart();
            $_SESSION['cart'] = $cart;

            foreach($order->order_products as $p)
            {
                $qty = $p['products_id'] == $data['products_id'] ? $data['order_products_quantity'] : $p['products_quantity'];
                $_SESSION['cart']->_addCart( array(
                    'product' => $p['products_id'],
                    'qty' => $qty,
                    'customer_id' => $order->customer,
                	'products_info' => unserialize($p['products_data']),
                ));
            }

            // payment,shipping,coupon anfügen
            $r = order_edit_controller::setCartSubContent($order);

            if ($r->couponRemoved)
            {
                $result->msg = TEXT_ORDER_EDIT_WARNING_COUPON_REMOVED;
                if ($r->errors)
                {
                    foreach($r->errors as $e)
                    {
                        $result->msg .= $e;
                    }
                }
            }


            // cart berechnen
            //$_SESSION['cart']->_refresh();

            $data = array(
                'payment_code' => $order->order_data['payment_code'],
                'subpayment_code' => $order->order_data['subpayment_code'],
                'shipping_code' => $order->order_data['shipping_code'],
                'currency_code' => $order->order_data['currency_code'],
                'currency_value' => $order->order_data['currency_value'],
                'orders_status' => $order->order_data['orders_status_id'],
                'orders_status_id' => $order->order_data['orders_status_id'],
                'account_type' => $order->order_data['account_type'],
                'allow_tax' => $order->order_data['allow_tax'],
                'comments' => $order->order_data['comments'],
                'customers_id' => $order->order_data['customers_id'],
                'shop_id' => $order->order_data['shop_id'],
                'customers_ip' => $order->order_data['customers_ip'],
                'delivery' => $order_edit_controller->_customer->customer_shipping_address,
                'billing' => $order_edit_controller->_customer->customer_payment_address

            );

            // tweak des store_handler, es soll immer der aus der bestellung sein
            global $store_handler;
            $storeId = $store_handler->shop_id;
            $store_handler->shop_id = $order->order_data['shop_id'];

            $order->_setOrder($data,'complete','update', $order->oID);

            order_edit_controller::setStats($order);
            order_edit_controller::cleanSession();

            // store_handler tweak entfernen
            $store_handler->shop_id = $storeId;

            $result->success = true;
        }
        return json_encode($result);
    }

    public function removeOrderItem($data)
    {
        $result = new stdClass();
        $result->success = false;

        $oId = (int) $data['orders_id'];
        $pId = (int) $data['products_id'];
        if ($oId && $pId)
        {
            global $order_edit_controller;
            $order = $order_edit_controller->getOrder();

            $cart = new cart();
            $_SESSION['cart'] = $cart;

            foreach($order->order_products as $p)
            {
                if ($p['orders_products_id'] == $_REQUEST['orders_products_id'] ) continue;
                $_SESSION['cart']->_addCart( array(
                    'product' => $p['products_id'],
                    'qty' => $p['products_quantity'],
                    'customer_id' => $order->customer,
                	'products_info' => unserialize($p['products_data']),
                ));
            }

            // payment,shipping,coupon anfügen
            $r = order_edit_controller::setCartSubContent($order);

            // order speichern
            $order_edit_controller->setOrder($order);

            if ($r->couponRemoved)
            {
                $result->msg = TEXT_ORDER_EDIT_WARNING_COUPON_REMOVED;
                if ($r->errors)
                {
                    foreach($r->errors as $e)
                    {
                        $result->msg .= $e;
                    }
                }
                $result->success = false;
                return json_encode($result);
            }

            $result->success = true;

        }
        return json_encode($result);
    }



	function _getIDs($id) {
		global $xtPlugin, $db, $language, $seo;

		$query = "select products_id_cross_sell from ".$this->_table_xsell." where products_id = ? ";

		$record = $db->Execute($query, array((int)$id));
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id_cross_sell'];
				$record->MoveNext();
			} $record->Close();
		}

		return $data;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language, $system_status, $order_edit_controller;

		if ($this->position != 'admin') return false;

        $data = array();
		if(!$this->url_data['query']){

			if ($this->url_data['get_data'])
            {

                global $order_edit_controller;
                $order = $order_edit_controller->getOrder();

                if ($order->order_products)
                {
                    foreach($order->order_products as $op)
                    {
                        $product = new product($op['products_id'],'default', $op['products_quantity']);

                        // tweak des store_handler, es soll immer der aus der bestellung sein
                        global $store_handler;
                        $storeId = $store_handler->shop_id;
                        $store_handler->shop_id = $order->order_data['shop_id'];

                        $product->buildData('default');

                        // store_handler tweak entfernen
                        $store_handler->shop_id = $storeId;

                        $cStatus = $order_edit_controller->_customers_status;

                        $products_order_price = $op['products_price']['plain'] ;

                        $products_preis_formated = $product->data['products_price']['formated'];
                        if (is_array($product->data['group_price']) && is_array($product->data['group_price']['prices']))
                        {
                            foreach($product->data['group_price']['prices'] as $price )
                            {
                                $products_preis_formated .='<br />'.$price['qty'].' => '. round($price['price'],2);
                            }
                        }

                        $opData = array(
                            'orders_products_id' => $op['orders_products_id'],
                            'products_id' => $op['products_id'],
                            'products_name' => $op['products_name'],
                            'products_model' => $op['products_model'],
                            'products_quantity' => $product->data['products_quantity'],
                            'products_preis_formated' => $products_preis_formated,
                            'order_products_quantity' => $op['products_quantity'],
                            'products_order_price' => $products_order_price,
                            'products_status' => $product->data['products_status']
                        );
                        $data[] = $opData;
                    }
                }
            }
            else
            {
                $table_data = new adminDB_DataRead(TABLE_ORDERS_PRODUCTS, '', '', $this->_master_key, '', '', '');
				$data = $table_data->getHeader();
			}
		}else{

			$sql_where =
                " `orders_id` = ". $this->url_data['orders_id'] .
                " AND ( `products_model` LIKE '%" .$this->url_data['query']. "%' OR `products_name` LIKE '%" .$this->url_data['query']. "%' ) ";

			if (!isset($this->sql_limit)) {
				$this->sql_limit = "0,25";
			}

            $table_data = new adminDB_DataRead(TABLE_ORDERS_PRODUCTS, '', '', $this->_master_key, $sql_where, '', '');

			if ($this->url_data['get_data']){
				$data = $table_data->getData();
			}else{
				$data = $table_data->getHeader();
			}

		}

		if($table_data && ($table_data->_total_count!=0 || !$table_data->_total_count))
        {
		    $count_data = $table_data->_total_count;
        }
		else
        {
		    $count_data = count($data);
        }

        $obj = new stdClass();
		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($id, $set_type = 'edit') {
		global $db,$language,$filter;

		 $data = array();
		 $data['products_id'] = (int)$this->url_data['products_id'];
		 $data['products_id_cross_sell'] = (int)$id;

		 $obj = new stdClass;
		 $o = new adminDB_DataSave($this->_table_xsell, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		return $obj;
	}	
	
	function _unset($id = 0) {
	    global $db, $xtPlugin;
		$pID=(int)$this->url_data['products_id'];
		$id=(int)$id;
		
	    if (!$id || !$pID || $this->position != 'admin') return false;
	    $db->Execute(
            "DELETE FROM ". $this->_table_xsell ." WHERE products_id_cross_sell = ? and products_id = ?",
            array($id, $pID)
        );
	}

    public static function hook_adminHandler_bottom_edit_item($extjsAdminHandler)
    {
        // order item save function
        $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? ",sec:'".$_SESSION['admin_user']['admin_key']."'": '';
        $saveJs =
            "
            function saveOrderItem(orders_id)
            {
                var orders_products_id = ".$_GET['edit_id'].";".
                "var data = Ext.ComponentMgr.get('order_edit_edit_item".$extjsAdminHandler->SelectionItem."-grideditform').getForm().getValues()
                var conn = new Ext.data.Connection();
                //console.log(data);
                conn.request({
                    url: 'adminHandler.php',
                    method:'GET',
                    params: {
                        pg:             'updateOrderItem',
                        load_section:   'order_edit_edit_item',
                        plugin:         'order_edit',
                        orders_products_id: orders_products_id,
                        orders_id: orders_id,
                        products_additional_info: data.products_additional_info,
                        products_price:             data.products_price,
                        products_quantity:          data.products_quantity,
                        products_tax_class:               data.products_tax_class".$add_to_url."
                    },
                    success: function(responseObject)
                    {
                        var r = Ext.decode(responseObject.responseText);
                        if (!r.success || null != r.msg)
                        {
                            Ext.MessageBox.alert('Error', r.msg);
                        }

                        Ext.ComponentMgr.get('order_edit_productsgridForm').store.reload()
                        //Ext.ComponentMgr.get('order_edit_productsgridForm').view.refresh();
                        //order_edit_productsds.reload();
                        contentTabs.getActiveTab().getUpdater().refresh();
                        Ext.ComponentMgr.get('order_edit_edit_itemRemoteWindow').close();
                    },
                    failure: function(responseObject)
                    {
                        var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                        var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                        Ext.MessageBox.alert(title,msg);
                        console.log(responseObject)
                    }
                });
            }
        ";
        echo("<script type='text/javascript'>" .$saveJs. ";</script>");
    }
}