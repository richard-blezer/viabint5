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

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.address.php';

class order_edit_edit_address {

	function _getParams() {
		global $language, $order_edit_controller;

        if (_LIC_TYPE=='free') die('not available in free license');

		$params = array();
        $header['orders_id'] = array('type'=>'hidden');
        $header['customers_dob'] = array('type' => 'date', 'width' => 300);
        $header['address_type_to_change'] = array('type'=>'dropdown', 'width' => 300, 'url'  => 'DropdownData.php?get=order_edit_address_type','text'=>TEXT_ORDER_EDIT_ADDRESS_TYPE);
        $header['address_book_id'] = array('type'=>'dropdown', 'width' => 500, 'url'  => 'DropdownData.php?get=order_edit_customer_addresses&plugin=order_edit&orders_id='.$this->url_data['orders_id'],'text'=>TEXT_ORDER_EDIT_ADDRESS_TO_APPLY);

        $header['customers_country_code'] = array(
            'type' => 'dropdown', 								// you can modyfy the auto type
            'url'  => 'DropdownData.php?get=countries'
        );

        $header['customers_gender'] = array(
            'renderer' => 'genderRenderer','type' => 'dropdown',
            'url'  => 'DropdownData.php?get=gender'
        );

        $params['header']         = $header;
        $params['master_key']     = 'orders_id';
        $params['display_resetBtn'] = false;
        $params['display_editBtn'] = false;

        $msgFieldsMissing = TEXT_ORDER_EDIT_FIELDS_REQUIRED . ': '.
            TEXT_CUSTOMERS_COMPANY.'/'.TEXT_CUSTOMERS_LASTNAME . ', '.
            TEXT_CUSTOMERS_STREET_ADDRESS . ', '.
            TEXT_CUSTOMERS_POSTCODE . ', '.
            TEXT_CUSTOMERS_CITY . ', '.
            TEXT_CUSTOMERS_COUNTRY_CODE;

        $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? ",sec:'".$_SESSION['admin_user']['admin_key']."'": '';
        $add_to_url_abs = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
        // neue adresse speichern und anwenden
        $js_new = "
            var cmpId = order_edit_edit_addressbd.id;
            var form = Ext.getCmp(cmpId).getForm();
            var orders_id = ". $this->url_data['orders_id'] . ";
            if (form.findField('address_type_to_change').getValue()=='')
            {
                form.findField('address_type_to_change').setValue('shipping');
            }
            if
            (
                (form.findField('customers_company').getValue()=='' && form.findField('customers_lastname').getValue()=='')
                ||
                form.findField('customers_street_address').getValue()==''
                ||
                form.findField('customers_city').getValue()==''
                ||
                form.findField('customers_postcode').getValue()==''
                ||
                form.findField('customers_country_code').getValue()==''

            )
            {
                var title = '".TEXT_ERROR_MSG."';
                var msg = '".$msgFieldsMissing."';
                Ext.MessageBox.alert(title,msg);
                return;
            }

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'applyNewAddress',
                    load_section:   'order_edit_edit_address',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    address_class: form.findField('address_type_to_change').getValue(),
                    customers_gender : form.findField('customers_gender').getValue() ,
                    customers_company : form.findField('customers_company').getValue() ,
                    customers_company_2 : form.findField('customers_company_2').getValue(),
                    customers_company_3 : form.findField('customers_company_3').getValue(),
                    customers_firstname : form.findField('customers_firstname').getValue() ,
                    customers_lastname : form.findField('customers_lastname').getValue() ,
                    customers_dob : form.findField('customers_dob').getValue() != '' ? form.findField('customers_dob').getValue().format('Y-m-d H:i:s') : '',
                    customers_street_address : form.findField('customers_street_address').getValue() ,
                    customers_suburb : form.findField('customers_suburb').getValue() ,
                    customers_postcode : form.findField('customers_postcode').getValue() ,
                    customers_city : form.findField('customers_city').getValue() ,
                    customers_country_code : form.findField('customers_country_code').getValue() ,
                    customers_federal_state_code : form.findField('customers_federal_state_code').getValue() ,
                    customers_phone : form.findField('customers_phone').getValue(),
                    customers_mobile_phone : form.findField('customers_mobile_phone').getValue(),
                    customers_fax : form.findField('customers_fax').getValue()".$add_to_url."
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
                    order_edit_edit_addressgridEditForm.getForm().load({url:'adminHandler.php?load_section=order_edit_edit_address&plugin=order_edit&pg=edit_address&edit_id=1&orders_id=". $this->url_data['orders_id'] . "&modal=true&parentNode=order_edit_edit_addressRemoteWindow&get_singledata=1".$add_to_url_abs."', waitMsg:'Loading',method: 'GET'});
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    console.log(responseObject)
                }
            });";
        $rowActionsFunctions['ORDER_EDIT_APPLY_NEW_ADDRESS'] = $js_new;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_APPLY_NEW_ADDRESS', 'qtipIndex' => 'qtip1', 'tooltip' => ORDER_EDIT_APPLY_NEW_ADDRESS);

        // geänderte adresse speichern und anwenden
        $js_edit = "
            var cmpId = order_edit_edit_addressbd.id;
            var form = Ext.getCmp(cmpId).getForm();
            var orders_id = ". $this->url_data['orders_id'] . ";
            if (form.findField('address_type_to_change').getValue()=='')
            {
                form.findField('address_type_to_change').setValue('shipping');
            }
            if
            (
                (form.findField('customers_company').getValue()=='' && form.findField('customers_lastname').getValue()=='')
                ||
                form.findField('customers_street_address').getValue()==''
                ||
                form.findField('customers_city').getValue()==''
                ||
                form.findField('customers_postcode').getValue()==''
                ||
                form.findField('customers_country_code').getValue()==''

            )
            {
                var title = '".TEXT_ERROR_MSG."';
                var msg = '".$msgFieldsMissing."';
                Ext.MessageBox.alert(title,msg);
                return;
            }

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'applyEditedAddress',
                    load_section:   'order_edit_edit_address',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    address_class: form.findField('address_type_to_change').getValue(),
                    address_book_id: form.findField('address_book_id').getValue(),
                    customers_gender : form.findField('customers_gender').getValue() ,
                    customers_company : form.findField('customers_company').getValue() ,
                    customers_company_2 : form.findField('customers_company_2').getValue(),
                    customers_company_3 : form.findField('customers_company_3').getValue(),
                    customers_firstname : form.findField('customers_firstname').getValue() ,
                    customers_lastname : form.findField('customers_lastname').getValue() ,
                    customers_dob : form.findField('customers_dob').getValue() != '' ? form.findField('customers_dob').getValue().format('Y-m-d H:i:s') : '',
                    customers_street_address : form.findField('customers_street_address').getValue() ,
                    customers_suburb : form.findField('customers_suburb').getValue() ,
                    customers_postcode : form.findField('customers_postcode').getValue() ,
                    customers_city : form.findField('customers_city').getValue() ,
                    customers_country_code : form.findField('customers_country_code').getValue() ,
                    customers_federal_state_code : form.findField('customers_federal_state_code').getValue() ,
                    customers_phone : form.findField('customers_phone').getValue() ,
                    customers_mobile_phone : form.findField('customers_mobile_phone').getValue(),
                    customers_fax : form.findField('customers_fax').getValue()".$add_to_url."
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
                    order_edit_edit_addressgridEditForm.getForm().load({url:'adminHandler.php?load_section=order_edit_edit_address&plugin=order_edit&pg=edit_address&edit_id=1&orders_id=". $this->url_data['orders_id'] . "&modal=true&parentNode=order_edit_edit_addressRemoteWindow&get_singledata=1".$add_to_url_abs."', waitMsg:'Loading',method: 'GET'});
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    console.log(responseObject)
                }
            });";
        $rowActionsFunctions['ORDER_EDIT_APPLY_EDITED_ADDRESS'] = $js_edit;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_APPLY_EDITED_ADDRESS', 'qtipIndex' => 'qtip1', 'tooltip' => ORDER_EDIT_APPLY_EDITED_ADDRESS);


        // vorhandene adresse anwenden
        $js_existing = "
            var cmpId = order_edit_edit_addressbd.id;
            var form = Ext.getCmp(cmpId).getForm();
            var orders_id = ". $this->url_data['orders_id'] . ";
            if (form.findField('address_type_to_change').getValue()=='')
            {
                form.findField('address_type_to_change').setValue('shipping');
            }

            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'applyExistingAddress',
                    load_section:   'order_edit_edit_address',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    address_class: form.findField('address_type_to_change').getValue(),
                    address_book_id: form.findField('address_book_id').getValue()".$add_to_url."
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
                    order_edit_edit_addressgridEditForm.getForm().load({url:'adminHandler.php?load_section=order_edit_edit_address&plugin=order_edit&pg=edit_address&edit_id=1&orders_id=". $this->url_data['orders_id'] . "&modal=true&parentNode=order_edit_edit_addressRemoteWindow&get_singledata=1".$add_to_url_abs."', waitMsg:'Loading',method: 'GET'});
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    console.log(responseObject)
                }
            });";
        $rowActionsFunctions['ORDER_EDIT_APPLY_EXISTING_ADDRESS'] = $js_existing;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_APPLY_EXISTING_ADDRESS', 'qtipIndex' => 'qtip1', 'tooltip' => ORDER_EDIT_APPLY_EXISTING_ADDRESS);

        if (count($rowActionsFunctions) > 0) {
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

        $customer = new customer();
        $addresses = $customer->_getAdressList($order_edit_controller->_customers_id);
        $js = PHP_EOL. "var customerAddresses_{$order_edit_controller->_orders_id} = new Array();".PHP_EOL;
        foreach($addresses as $address)
        {
            $js .= "customerAddresses_{$order_edit_controller->_orders_id}['abId_".$address['address_book_id']."'] = ".json_encode($address).";".PHP_EOL;
        }
        $js .= "
        Ext.onReady(function(){
            var cmpId = order_edit_edit_addressbd.id;
            if (cmpId)
            {
                var form = Ext.getCmp(cmpId).getForm();
                if (form)
                {
                    var abId = $('#address_book_id').val()
                    var abEntry = customerAddresses_{$order_edit_controller->_orders_id}['abId_'+abId];
                    form.findField('customers_dob').setValue(abEntry['customers_dob']);

                    form.findField('address_book_id').on('select', function(){
                        var abId = $('#address_book_id').val()
                        var abEntry = customerAddresses_{$order_edit_controller->_orders_id}['abId_'+abId];
                        if (abEntry)
                        {
                            // zuordnen
                            form.findField('customers_gender').setValue(abEntry['customers_gender']);
                            form.findField('customers_company').setValue(abEntry['customers_company']);
                            form.findField('customers_company_2').setValue(abEntry['customers_company_2']);
                            form.findField('customers_company_3').setValue(abEntry['customers_company_3']);
                            form.findField('customers_firstname').setValue(abEntry['customers_firstname']);
                            form.findField('customers_lastname').setValue(abEntry['customers_lastname']);
                            form.findField('customers_dob').setValue(abEntry['customers_dob']);
                            form.findField('customers_street_address').setValue(abEntry['customers_street_address']);
                            form.findField('customers_suburb').setValue(abEntry['customers_suburb']);
                            form.findField('customers_postcode').setValue(abEntry['customers_postcode']);
                            form.findField('customers_city').setValue(abEntry['customers_city']);
                            form.findField('customers_country_code').setValue(abEntry['customers_country_code']);
                            form.findField('customers_federal_state_code').setValue(abEntry['customers_federal_state_code']);
                            form.findField('customers_phone').setValue(abEntry['customers_phone']);
                            form.findField('customers_mobile_phone').setValue(abEntry['customers_mobile_phone']);
                            form.findField('customers_fax').setValue(abEntry['customers_fax']);
                        }
                    });
                }
            }
        });
        ";
        $params['rowActionsJavascript'] = $js;

		return $params;
	}

    function setPosition($position)
    {
        $this->position = $position;

    }

    function _get($ID = 0)
    {
        if ($this->position != 'admin') return false;

        global $order_edit_controller;

        $data = array();
        $data[] = array(
            'address_type_to_change' => 'shipping',
            'address_book_id' => $order_edit_controller->_orderFields['delivery_address_book_id'],
            'orders_id' => $this->url_data['orders_id'],
            'customers_gender' => $order_edit_controller->_orderFields['delivery_gender'],
            'customers_company' => $order_edit_controller->_orderFields['delivery_company'],
            'customers_company_2' => $order_edit_controller->_orderFields['delivery_company_2'],
            'customers_company_3' => $order_edit_controller->_orderFields['delivery_company_3'],
            'customers_firstname' => $order_edit_controller->_orderFields['delivery_firstname'],
            'customers_lastname' => $order_edit_controller->_orderFields['delivery_lastname'],
            'customers_dob' => $order_edit_controller->_orderFields['delivery_dob'],
            'customers_street_address' => $order_edit_controller->_orderFields['delivery_street_address'],
            'customers_suburb' => $order_edit_controller->_orderFields['delivery_suburb'],
            'customers_postcode' => $order_edit_controller->_orderFields['delivery_postcode'],
            'customers_city' => $order_edit_controller->_orderFields['delivery_city'],
            'customers_country_code' => $order_edit_controller->_orderFields['delivery_country_code'],
            'customers_federal_state_code' => $order_edit_controller->_orderFields['delivery_federal_state_code'],
            'customers_phone' => $order_edit_controller->_orderFields['delivery_phone'],
            'customers_mobile_phone' => $order_edit_controller->_orderFields['delivery_mobile_phone'],
            'customers_fax' => $order_edit_controller->_orderFields['delivery_fax']
        );

        $count_data = count($data);

        $obj = new stdClass();
        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function set($data, $set_type = 'edit') {
        return false;
    }

    function applyEditedAddress($data)
    {
        return $this->applyNewAddress($data, 'update');
    }

    function applyNewAddress($data, $type = 'insert')
    {
        $r = new stdClass();
        $r->success = false;

        $editCtrl = order_edit_controller::getInstance();

        $xt_customer = new customer();
        $data['customers_id'] =  $editCtrl->_customers_id;

        // check if we have a default address: if not create one
        // we should not hit that
        global $db;
        $defaultAddressExists = $db->GetOne(
            "SELECT 1 FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=? AND address_class='default'",
            array($editCtrl->_customers_id)
        );
        if (empty($defaultAddressExists))
        {
            $data['address_class'] = 'default';
            unset($data['address_book_id']);
            $type = 'insert';
        }
        // on update check if address class exists and change type-update/insert where necessary
        $shippingAddressExists = $db->GetOne(
            "SELECT 1 FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=? AND address_class='shipping'",
            array($editCtrl->_customers_id)
        );
        $paymentAddressExists = $db->GetOne(
            "SELECT 1 FROM " . TABLE_CUSTOMERS_ADDRESSES . " where customers_id=? AND address_class='payment'",
            array($editCtrl->_customers_id)
        );
        if (($data['address_class'] == 'shipping' && empty($shippingAddressExists))
            ||
            ($data['address_class'] == 'payment' && empty($paymentAddressExists))
        )
        {
            unset($data['address_book_id']);
            $type = 'insert';
        }


        $xt_customer->_writeAddressData($data, $type);
        $address_book_id = $xt_customer->address_book_id;

        global $db, $order_edit_controller;
        $order = $order_edit_controller->getOrder();

        $priceOverride = $_SESSION['order_edit_priceOverride'];
        if(!$priceOverride)
        {
        	$priceOverride = array();
        }
        else if (!$priceOverride[$order->oID])
        {
        	$priceOverride[$order->oID] = array();
        }
        if ($order->order_products)
        {
        	foreach($order->order_products as $p)
        	{
        		$priceOverride[$order->oID][$p['products_id']] = $p['products_price']['plain'];
        	}
        }
        
        $_SESSION['order_edit_priceOverride'] = $priceOverride;
        
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

        $editCtrl->_customer->_setAdress($address_book_id, $data['address_class']);
        $_SESSION['customer'] =  $editCtrl->_customer;

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

        // order speichern
        $order_edit_controller->setOrder($order);

        $r->success = true;

        $r->success = true;
        return json_encode($r);
    }

    function applyExistingAddress($data)
    {
        $r = new stdClass();
        $r->success = false;

        global $db, $order_edit_controller;
        $order = $order_edit_controller->getOrder();

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
                    'customer_id' => $order->customer
                ));
            }
        }

        $order_edit_controller->_customer->_setAdress($data['address_book_id'], $data['address_class']);
        $_SESSION['customer'] = $order_edit_controller->_customer;

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

        // order speichern
        $order_edit_controller->setOrder($order);

        $r->success = true;

        $r->success = true;
        return json_encode($r);
    }

    function getCustomerAddresses()
    {
        $data = array();
        $data[] = array('id' => '-1', 'name' => '---');

        $editCtrl = order_edit_controller::getInstance();

        $xt_customer = new customer();
        $addressData = $xt_customer->_getAdressList($editCtrl->_customers_id);
        foreach($addressData as $a)
        {
            $data[] = array('id' => $a['address_book_id'], 'name' => $a['text']);
        }

        return $data;
    }

	function _unset($id = 0) {
	    return false;
    }
}