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

class order_edit_edit_paymentShipping {

	function _getParams() {
		global $language, $xtPlugin;

        if (_LIC_TYPE=='free') die('not available in free license');

		$params = array();
        $header['orders_id'] = array('type'=>'hidden');
        $header['payment'] = array('type'=>'dropdown', 'width' => 300, 'url'  => 'DropdownData.php?get=order_edit_payment_methods','text'=>TEXT_PAYMENT);
        $header['shipping'] = array('type'=>'dropdown', 'width' => 300, 'url'  => 'DropdownData.php?get=shipping_methods','text'=>TEXT_SHIPPING);
        $header['source_id'] = array('type'=>'dropdown', 'width' => 300, 'url'  => 'DropdownData.php?get=order_sources','text'=>TEXT_ORDERS_SOURCE);

        if($xtPlugin->active_modules['xt_delivery_date'])
        {
            $header['desired_delivery_date'] = array('type' => 'date', 'width' => 300, 'text' => TEXT_DESIRED_DELIVERY_DATE);
        }

        ($plugin_code = $xtPlugin->PluginCode('class.order_edit_edit_paymentShipping.php:_getParams_top')) ? eval($plugin_code) : false;

        $params['header']         = $header;
        $params['master_key']     = 'orders_id';

        $params['display_resetBtn'] = false;
        $params['display_editBtn'] = false;

        $add_to_url_abs = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
        // apply
        $js_existing = "
            var cmpId = order_edit_edit_paymentShippingbd.id;
            var form = Ext.getCmp(cmpId).getForm();
            var orders_id = ". $this->url_data['orders_id'] . ";

            var conn = new Ext.data.Connection();
            var source_id = 0;

            if (form.findField('source_id'))
            {
                source_id = form.findField('source_id').getValue();
            }

            var dateS = '';
            if (form.findField('desired_delivery_date'))
            {
                var dateV = form.findField('desired_delivery_date').getValue();
                if (dateV instanceof Date)
                {
                     dateS = dateV.getDate() +'.'+ (dateV.getMonth()+1) +'.'+ dateV.getFullYear();
                }
            }


            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'apply',
                    load_section:   'order_edit_edit_paymentShipping',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    payment: form.findField('payment').getValue(),
                    shipping: form.findField('shipping').getValue(),
                    source_id: source_id,
                    desired_delivery_date: dateS
                },
                success: function(responseObject)
                {
                    var r = Ext.decode(responseObject.responseText);
                    if (r.success!=true)
                    {
                        Ext.MessageBox.alert('Error', r.msg);
                        return;
                    }
                    //order_edit_productsds.reload();
                    contentTabs.getActiveTab().getUpdater().refresh();
                    order_edit_edit_paymentShippinggridEditForm.getForm().load({url:'adminHandler.php?load_section=order_edit_edit_paymentShipping&plugin=order_edit&pg=edit_paymentShipping&edit_id=1&orders_id=". $this->url_data['orders_id'] . "&modal=true&parentNode=order_edit_edit_paymentShippingRemoteWindow&get_singledata=1".$add_to_url_abs."', waitMsg:'Loading',method: 'GET'});
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    //console.log(responseObject)
                }
            });";

        ($plugin_code = $xtPlugin->PluginCode('class.order_edit_edit_paymentShipping.php:_getParams_rowActions')) ? eval($plugin_code) : false;

        $rowActionsFunctions['ORDER_EDIT_APPLY_PAYMENT_SHIPPING'] = $js_existing;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_APPLY_PAYMENT_SHIPPING', 'qtipIndex' => 'qtip1', 'tooltip' => ORDER_EDIT_APPLY_PAYMENT_SHIPPING);

        if (count($rowActionsFunctions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }
		return $params;
	}

    function setPosition($position)
    {
        $this->position = $position;

    }

    function _get($ID = 0)
    {
        if ($this->position != 'admin') return false;

        global $order_edit_controller, $xtPlugin;
        $order = $order_edit_controller->getOrder();

        $data = array(
            array(
                'orders_id' => $this->url_data['orders_id'],
                'payment' => $order->order_data['payment_code'],
                'shipping' => $order->order_data['shipping_code']
            )
        );

        ($plugin_code = $xtPlugin->PluginCode('class.order_edit_edit_paymentShipping.php:_get_top')) ? eval($plugin_code) : false;

        if ($xtPlugin->active_modules['xt_delivery_date'])
        {
            $data[0]['desired_delivery_date'] = $order->order_data['desired_delivery_date'];
        }

        $sourceId = $order_edit_controller->_orderFields['source_id'];
        $data[0]['source_id'] = $sourceId ? $order_edit_controller->_orderFields['source_id'] : '';

        $count_data = count($data);

        $obj = new stdClass();
        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function apply($data)
    {
        $r = new stdClass();
        $r->success = false;

        global $order_edit_controller, $xtPlugin;
        $order = $order_edit_controller->getOrder();

        $shipping = $data['shipping'];
        if (empty($shipping))
        {
            $shipping = _SYSTEM_ORDER_EDIT_NEW_ORDER_SHIPPING;
        }
        $order->order_data['shipping_code'] = $shipping;

        $payment = $data['payment'];
        if (empty($payment))
        {
            $payment = _SYSTEM_ORDER_EDIT_NEW_ORDER_PAYMENT;
        }
        $order->order_data['payment_code'] = $payment;

        $cart = new cart();
        $_SESSION['cart'] = $cart;

        // vorhandene hinzufügen
        if ($order->order_products)
        {
            foreach($order->order_products as $p)
            {
                $_SESSION['cart']->_addCart( array(
                    'product' => $p['products_id'],
                    'qty' => $p['products_quantity'],
                    'customer_id' => $order->customer,
                	'products_info' => unserialize($p['products_data']),
                ));
            }
        }

        // coupons, payment und shipping anfügen
        $ctrlResult = order_edit_controller::setCartSubContent($order);
        if ($ctrlResult->errors)
        {
            $r->success = false;
            $r->msg = '';
            foreach($ctrlResult->errors as $error)
            {
                $r->msg .= $error;
            }
            return json_encode($r);
        }

        // order source ändern
        if (!empty($data['source_id']))
        {
            $order->order_data['source_id'] = $data['source_id'];
        }
        else {
            $order->order_data['source_id'] = 0;
        }

        // delivery date
        if (!empty($data['desired_delivery_date']))
        {
            $_SESSION['desired_delivery_date'] = $data['desired_delivery_date'];
        }
        else {
            $_SESSION['desired_delivery_date'] = null;
        }

        ($plugin_code = $xtPlugin->PluginCode('class.order_edit_edit_paymentShipping.php:_apply')) ? eval($plugin_code) : false;

        // order speichern
        $order_edit_controller->setOrder($order);

        $r->success = true;

        $r->success = true;
        return json_encode($r);
    }

    function set($data, $set_type = 'edit') {
        return false;
    }

	function _unset($id = 0) {
	    return false;
    }
}