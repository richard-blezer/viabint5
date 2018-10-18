<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// TOD entweder alles in controller oder hierher, besser zum controller
class order_edit {

    private $_master_key = 'id';

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        $header = array();
        $header['id'] = array('type' => 'hidden', 'readonly'=>true);
        $header['products_id'] = array('type' => 'hidden', 'readonly'=>true);
        $header['products_key'] = array('type' => 'textfield');
        $header['stock'] = array('type' => 'textfield');

        $params = array();
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['display_deleteBtn'] = false;
        $params['display_resetBtn'] = false;
        $params['display_editBtn'] = false;
        $params['display_newBtn'] = false;
        $params['display_searchPanel']  = false;

        return $params;
    }

    function _get($ID = 0)
    {
        return false;
    }

    public static function get($id)
    {
        return false;
    }

    function _set($data, $set_type = 'edit')
    {
        return false;
    }

    function _unset($id = 0)
    {
        return false;
    }

    public static function hook_order_edit_display_tpl($orderId, &$js)
    {
        global $db;

        $sql = "SELECT `customers_status` FROM ". TABLE_ORDERS . " WHERE `orders_id`=?";
        $cStatusId = $db->GetOne($sql, array($orderId));
        $sql = "SELECT `customers_status_name` FROM ". TABLE_CUSTOMERS_STATUS_DESCRIPTION . " WHERE `customers_status_id`=? AND `language_code`=?";
        $cGroupName = $db->GetOne($sql, array($cStatusId, _STORE_LANGUAGE));
        $sql = "SELECT `customers_status_show_price_tax` FROM ". TABLE_CUSTOMERS_STATUS . " WHERE `customers_status_id`=?";
        $showGrossPrice = $db->GetOne($sql, array($cStatusId));
        $netGross = $showGrossPrice ? TEXT_TAX_INC : TEXT_TAX_EXC;
        $infoText = ' ('.TEXT_ORDER.' '.$orderId.', '.$cGroupName.' - '.TEXT_PRODUCT_PRICE.': '.$netGross.')';

        define("TEXT_ORDER_EDIT_ITEMS_"+$orderId , TEXT_ORDER_EDIT_ITEMS.$infoText);
        define("TEXT_ORDER_EDIT_ADD_ITEM_".$orderId , TEXT_ORDER_EDIT_ADD_ITEM.$infoText);

        // edit window
        $extF_edit = new ExtFunctions();
        $extF_edit->setCode('order_edit_items');
        $remoteWindow = $extF_edit->_RemoteWindow("TEXT_ORDER_EDIT_ITEMS_"+$orderId,"TEXT_ORDER","adminHandler.php?plugin=order_edit&load_section=order_edit_products&pg=overview&orders_id='+edit_id+'", '', array(), 900, 600, 'window');

        $remoteWindow->setModal(true);
        $js_edit = "var edit_id = ".$orderId.";";
        $js_edit.= $remoteWindow->getJavascript(false, "new_window").' new_window.show();';

        // add window
        $extF_add = new ExtFunctions();
        $code = 'order_edit_add_items';
        $extF_add->setCode($code);
        $remoteWindow = $extF_add->_RemoteWindow("TEXT_ORDER_EDIT_ADD_ITEM_".$orderId,"TEXT_PRODUCTS","adminHandler.php?plugin=order_edit&load_section=order_edit_add_products&pg=overview&orders_id='+edit_id+'", '', array('modal'=>true), 900, 600, 'window');
        $remoteWindow->setModal(true);
        // open add order items window
        $js_add = "var edit_id = ".$orderId.";";
        $js_add.= $remoteWindow->getJavascript(false, "new_window").' new_window.show();';
        $UserButtons[$code] = array('text'=>'TEXT_ORDER_EDIT_ADD_ITEMS'.$infoText, 'style'=>$code, 'icon'=>'basket_add.png', 'acl'=>'edit', 'stm' => $js_add);
        $params['display_'.$code.'Btn'] = true;

        // coupon window
        $couponsActive = order_edit_controller::isCouponPluginActive();
        if ($couponsActive)
        {
            $extF_coupon = new ExtFunctions();
            $code = 'order_edit_edit_coupon';
            $extF_coupon->setCode($code);
            $remoteWindow = $extF_coupon->_RemoteWindow("TEXT_ORDER_EDIT_EDIT_COUPON","TEXT_PRODUCTS","adminHandler.php?plugin=order_edit&load_section=order_edit_edit_coupon&pg=edit_coupon&edit_id=1&orders_id='+orders_id+'", '', array('modal'=>true), 900, 325, 'window');
            $remoteWindow->setModal(true);
            // open add order items window
            $js_coupon = "var orders_id = ".$orderId.";";
            $js_coupon.= $remoteWindow->getJavascript(false, "new_window").' new_window.show();';
            $UserButtons[$code] = array('text'=>'TEXT_ORDER_EDIT_EDIT_COUPON', 'style'=>$code, 'icon'=>'money_euro.png', 'acl'=>'edit_coupon', 'stm' => $js_coupon);
            $params['display_'.$code.'Btn'] = true;
        }

        // address window
        $extF_editAddress = new ExtFunctions();
        $code = 'order_edit_edit_address';
        $extF_editAddress->setCode($code);
        $remoteWindow = $extF_editAddress->_RemoteWindow("TEXT_ORDER_EDIT_EDIT_ADDRESS","TEXT_ADDRESS","adminHandler.php?plugin=order_edit&load_section=order_edit_edit_address&pg=edit_address&edit_id=1&orders_id='+orders_id+'", '', array('modal'=>true), 720, 725, 'window');
        $remoteWindow->setModal(true);
        // open add order items window
        $js_address = "var orders_id = ".$orderId.";";
        $js_address.= $remoteWindow->getJavascript(false, "new_window").' new_window.show();';
        $UserButtons[$code] = array('text'=>'TEXT_ORDER_EDIT_EDIT_ADDRESS', 'style'=>$code, 'icon'=>'vcard_edit.png', 'acl'=>'edit_address', 'stm' => $js_address);
        $params['display_'.$code.'Btn'] = true;


        // payment/shipping window
        $extF_editPaymentShipping = new ExtFunctions();
        $code = 'order_edit_edit_paymentShipping';
        $extF_editPaymentShipping->setCode($code);
        $remoteWindow = $extF_editPaymentShipping->_RemoteWindow("TEXT_ORDER_EDIT_EDIT_PAYMENT_SHIPPING","TEXT_EDIT","adminHandler.php?plugin=order_edit&load_section=order_edit_edit_paymentShipping&pg=edit_address&edit_id=1&orders_id='+orders_id+'", '', array('modal'=>true), 700, 305, 'window');
        $remoteWindow->setModal(true);
        // open add order items window
        $js_paymentShipping = "var orders_id = ".$orderId.";";
        $js_paymentShipping.= $remoteWindow->getJavascript(false, "new_window").' new_window.show();';
        $UserButtons[$code] = array('text'=>'TEXT_SAVE', 'style'=>$code, 'icon'=>'folder_wrench.png', 'acl'=>'edit_address', 'stm' => $js_paymentShipping);
        $params['display_'.$code.'Btn'] = true;

        // overwrite for ! professional+,merchant,ultimate
        if (_LIC_TYPE=='free') {
            $js_edit = 'Ext.Msg.alert(\''.__define('TEXT_ADMIN_PAID_ONLY').'\',\''.__define('TEXT_ADMIN_PAID_ONLY_INFO').'<a href=\"http://addons.xt-commerce.com/index.php?page=product&info=4324&campaign=ADMIN\" target=\"_blank\">[-> Lizenzupgrade]</a>\');';
            $js_add=$js_coupon=$js_address=$js_paymentShipping=$js_edit;
        }


        // panel and buttons
        $pnl = new PhpExt_Panel();
        $pnl->getTopToolbar()->addButton(1, __define('TEXT_ORDER_EDIT_ITEMS'), 'images/icons/basket_edit.png', new PhpExt_Handler(PhpExt_Javascript::stm($js_edit)));
        $pnl->getTopToolbar()->addButton(2, __define('TEXT_ORDER_EDIT_ADD_ITEM'), 'images/icons/basket_add.png', new PhpExt_Handler(PhpExt_Javascript::stm($js_add)));
        if ($couponsActive)
        {
            $pnl->getTopToolbar()->addButton(3, __define('TEXT_ORDER_EDIT_EDIT_COUPON'), 'images/icons/money_euro.png', new PhpExt_Handler(PhpExt_Javascript::stm($js_coupon)));
        }
        $pnl->getTopToolbar()->addButton(4, __define('TEXT_ORDER_EDIT_EDIT_ADDRESS'), 'images/icons/vcard_edit.png', new PhpExt_Handler(PhpExt_Javascript::stm($js_address)));
        $pnl->getTopToolbar()->addButton(5, __define('TEXT_ORDER_EDIT_EDIT_PAYMENT_SHIPPING'), 'images/icons/lorry_flatbed_money.png', new PhpExt_Handler(PhpExt_Javascript::stm($js_paymentShipping)));
        $pnl->setRenderTo(PhpExt_Javascript::variable("Ext.get('oe-menubar'+".$orderId.")"));
        $pnl->setCssStyle('position:fixed; width:100%');

        $js .= PhpExt_Ext::onReady(
            '$("#memoContainer"+'.$orderId.').parent().prepend("<div id=\'oe-menubar'.$orderId.'\'></div>");',
            $pnl->getJavascript(false, "oeMenubar")
        );

        switch ($_REQUEST['openRemoteWindow']) {
            case 'addProducts':
                $js .= PhpExt_Ext::onReady(
                    $js_add
                );
                break;
            default:
        }
    }
}
