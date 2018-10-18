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



class order_edit_edit_coupon {

	function _getParams() {
		global $language;

        if (_LIC_TYPE=='free') die('not available in free license');

		$params = array();
        $header['orders_id'] = array('type'=>'hidden');
        $header['removed_coupon_code'] = array('type'=>'textfield', 'width' => 500, 'readonly' => true, 'text'=>TEXT_ORDER_EDIT_REMOVED_COUPON_CODE);
		$header['current_coupon_code'] = array('type'=>'textfield', 'width' => 500, 'readonly' => true, 'text'=>TEXT_ORDER_EDIT_CURRENT_COUPON_CODE);
        $header['new_coupon_code_coupons'] = array('type'=>'dropdown', 'width' => 500, 'url'  => 'DropdownData.php?get=order_edit_coupons','text'=>TEXT_ORDER_EDIT_NEW_COUPON_CODE_TEMPLATE);
        $header['new_coupon_code_code'] = array('type'=>'textfield', 'width' => 500, 'text'=>TEXT_ORDER_EDIT_NEW_COUPON_CODE_CODE);


        $params['header']         = $header;
        $params['master_key']     = 'coupon_code';

        $params['display_resetBtn'] = false;
        $params['display_editBtn'] = false;

        // coupon code speichern
        $js = "
            var cmpId = order_edit_edit_couponbd.id;
            var new_coupon_code_coupons = Ext.getCmp(cmpId).getForm().findField('new_coupon_code_coupons').getValue();
            var new_coupon_code_code = Ext.getCmp(cmpId).getForm().findField('new_coupon_code_code').getValue();
            var orders_id = ". $this->url_data['orders_id'] . ";
            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'editCoupon',
                    load_section:   'order_edit_edit_coupon',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    new_coupon_code_code: new_coupon_code_code,
                    new_coupon_code_coupons: new_coupon_code_coupons
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
                    order_edit_edit_coupongridEditForm.getForm().load({url:'adminHandler.php?load_section=order_edit_edit_coupon&plugin=order_edit&pg=edit_coupon&edit_id=1&orders_id=123&modal=true&parentNode=order_edit_edit_couponRemoteWindow&get_singledata=1', waitMsg:'Loading',method: 'GET'});
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    console.log(responseObject)
                }
            });";
        $rowActionsFunctions['ORDER_EDIT_SAVE_COUPON'] = $js;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_SAVE_COUPON', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ORDER_EDIT_SAVE_COUPON);

        // coupon code löschen
        $js_rem = "
            var cmpId = order_edit_edit_couponbd.id;
            var cmp = Ext.getCmp(cmpId);
            var orders_id = ". $this->url_data['orders_id'] . ";
            var conn = new Ext.data.Connection();
            conn.request({
                url: 'adminHandler.php',
                method:'GET',
                params: {
                    pg:             'editCoupon',
                    load_section:   'order_edit_edit_coupon',
                    plugin:         'order_edit',
                    orders_id:      orders_id,
                    coupon_code: ''
                },
                success: function(responseObject)
                {
                    var r = Ext.decode(responseObject.responseText);
                    if (!r.success)
                    {
                        Ext.MessageBox.alert('Error', r.msg);
                    }
                    //order_edit_productsds.reload();
                    contentTabs.getActiveTab().getUpdater().refresh();
                    order_edit_edit_coupongridEditForm.getForm().load({url:'adminHandler.php?load_section=order_edit_edit_coupon&plugin=order_edit&pg=edit_coupon&edit_id=1&orders_id=123&modal=true&parentNode=order_edit_edit_couponRemoteWindow&get_singledata=1', waitMsg:'Loading',method: 'GET'});
                },
                failure: function(responseObject)
                {
                    var title = responseObject.statusText ? 'Error '+responseObject.status : 'Error ';
                    var msg = responseObject.statusText ? responseObject.statusText : 'No Details available';
                    Ext.MessageBox.alert(title,msg);
                    console.log(responseObject)
                }
            });";
        $rowActionsFunctions['ORDER_EDIT_REMOVE_COUPON'] = $js_rem;
        $rowActions[] = array('iconCls' => 'ORDER_EDIT_REMOVE_COUPON', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_ORDER_EDIT_REMOVE_COUPON);


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

    function _get($ID = 0) {
        global $db;

        if ($this->position != 'admin') return false;

        global $order_edit_controller;
        $order = $order_edit_controller->getOrder();

        $couponCode = '';
        $couponId = (int) $db->GetOne(
            "SELECT `coupon_id` FROM `".DB_PREFIX."_coupons_redeem` WHERE `order_id` = ?",
            array($order->oID)
        );
        if ($couponId)
        {
            $couponCode = $db->GetOne(
                "SELECT `coupon_code` FROM `".DB_PREFIX."_coupons` WHERE `coupon_id` = ?",
                array($couponId)
            );

            $oci = $order_edit_controller->getCouponForOrder($order->oID);
            if ($oci->isToken)
            {
                $couponCode = $oci->xt_coupon_token['coupon_token_code'];
            }
            $table_data = new adminDB_DataRead(TABLE_COUPONS, TABLE_COUPONS_DESCRIPTION, null, 'coupon_id', '', '1', '', '');
            $dbdata = $table_data->getData($couponId);
            if (is_array($dbdata))
            {
                $descData = $this->getCouponDescription($dbdata[0]);
                $couponCode .= '  ('. $descData['name']. ')';
            }
        }

        $removedCouponCode = '';
        $coupons = $_SESSION['order_edit_coupons'];
        if(!is_null($coupons) && is_array($coupons) && array_key_exists($order->oID, $coupons))
        {
            $oci = order_edit_controller::ociFromStdClass($coupons[$order->oID]);
            $removedCouponCode = $oci->xt_coupon['coupon_code'];
            if ($oci->isToken)
            {
                $removedCouponCode = $oci->xt_coupon_token['coupon_token_code'];
            }
            $table_data = new adminDB_DataRead(TABLE_COUPONS, TABLE_COUPONS_DESCRIPTION, null, 'coupon_id', '', '1', '', '');
            $dbdata = $table_data->getData($oci->xt_coupon['coupon_id']);
            if (is_array($dbdata))
            {
                $descData = $this->getCouponDescription($dbdata[0]);
                $removedCouponCode .= '  ('. $descData['name']. ')';
            }
        }

        $data = array();
        $data[] = array(
            'removed_coupon_code' => $removedCouponCode,
            'current_coupon_code' => $couponCode,
            'new_coupon_code_coupons' => '',
            'new_coupon_code_code' => '',
            'orders_id' => $this->url_data['orders_id']);

        $count_data = count($data);

        $obj = new stdClass();
        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set($data, $set_type = 'edit') {
        return false;
    }

	function editCoupon($data) {
		global $db,$language,$filter;

        $r = new stdClass();
        $r->success = false;

        $oId = (int) $data['orders_id'];
        $coupon_code = $data['new_coupon_code_coupons'];
        if (!empty($data['new_coupon_code_code']))
        {
            $coupon_code = $data['new_coupon_code_code'];
        }
        if ($oId)
        {
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

            // coupons, payment und shipping anfügen
            $ctrlResult = order_edit_controller::setCartSubContent($order, 'EDIT_COUPON', $coupon_code);
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

			order_edit_controller::updateProductOrderPercentageDiscount($order, $coupon_code);
            $r->success = true;
        }
        return json_encode($r);
	}

    function getCoupons()
    {
        $data = array();
        $table_data = new adminDB_DataRead(TABLE_COUPONS, TABLE_COUPONS_DESCRIPTION, null, 'coupon_id', 'coupon_status = 1', '', '', '');

        $db_data = $table_data->getData();

        if (is_array($db_data))
        {
            foreach ($db_data as $item)
            {
                $tmp_data = $this->getCouponDescription($item);
                $data[] = $tmp_data;
            }
        }
        return $data;
    }

    function getCouponDescription($dbItem)
    {
        global $language;

        $desc = ' - ';
        if (!$dbItem['coupon_percent'] && !$dbItem['coupon_free_shipping'])
        {
            $desc .= TEXT_COUPON_TYPE_FIX .' '. round($dbItem['coupon_amount'],2);
        }
        else if (!$dbItem['coupon_free_shipping'])
        {
            $desc .= TEXT_COUPON_TYPE_PERCENT .' '. round($dbItem['coupon_percent'],2) .'%';
        }
        else
        {
            $desc .= TEXT_COUPON_TYPE_FREESHIPPING;
        }
        if ($dbItem['coupon_free_on_100_status'])
        {
            $desc .= ', '.TEXT_COUPON_FREE_ON_100_STATUS;
        }
        if ($dbItem['coupon_minimum_order_value']!=0)
        {
            $desc .= ', '.TEXT_COUPON_MINIMUM_ORDER_VALUE .' '. round($dbItem['coupon_minimum_order_value'],2);
        }
        $name = $dbItem['coupon_code'].$desc;
        if ($dbItem['coupon_description_' . $language->code])
        {
            $desc .= $dbItem['coupon_description_' . $language->code];
        }

        $tmp_data = array('id' => $dbItem['coupon_code'], 'name' => $name, 'desc' => $desc);

        return $tmp_data;
    }
	
	function _unset($id = 0) {
	    return false;
    }
}