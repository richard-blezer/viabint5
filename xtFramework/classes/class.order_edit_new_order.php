<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK. 'classes/class.order_edit_tools.php';

define('XT_ORDER_EDIT_SECRET_KEY', 'O7JzSZdYEg3LEmiwXNL'); // XT_ORDER_EDIT_SECRET_KEY TODO in plg config

class order_edit_new_order {

    private $_master_key = 'id';

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        if (_LIC_TYPE=='free') die('not available in free license');

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
        $data = array();

        $obj = new stdClass;
        $obj->totalCount = 0;
        $obj->data = $data;
        return $obj;
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
        define("TEXT_ORDER_EDIT_ITEMS_"+$orderId, TEXT_ORDER_EDIT_ITEMS.' ('.TEXT_ORDER.' '.$orderId.')');

        $extF = new ExtFunctions();
        $extF->setCode('order_edit_items');
        $remoteWindow = $extF->_RemoteWindow("TEXT_ORDER_EDIT_ITEMS_"+$orderId,"TEXT_ORDER","adminHandler.php?plugin=order_edit&load_section=order_edit_products&pg=overview&orders_id='+edit_id+'", '', array(), 800, 600, 'window');
        $saveBtn = PhpExt_Button::createTextButton(__define("BUTTON_SAVE"),new PhpExt_Handler(PhpExt_Javascript::stm("contentTabs.getActiveTab().getUpdater().refresh();")));
        $remoteWindow->addButton($saveBtn);
        $remoteWindow->setModal(true);
        // 'schliessen' gegen 'fertig' ersetzen
        //$btns = &($remoteWindow->getButtons());
        $closeBtn = PhpExt_Button::createTextButton(__define("BUTTON_DONE"),new PhpExt_Handler(PhpExt_Javascript::stm("if (new_window) { new_window.destroy() } else{ this.destroy() } ")));
        $closeBtn->setIcon("images/icons/cancel.png")
            ->setIconCssClass("x-btn-text");
        $remoteWindow->addButton($closeBtn);

        $jsX = "var edit_id = ".$orderId.";";
        $jsX.= $remoteWindow->getJavascript(false, "new_window").' new_window.show();';

        $pnl = new PhpExt_Panel();
        $pnl->getTopToolbar()->addButton(1, __define('TEXT_ORDER_EDIT_ITEMS'), 'images/icons/basket_edit.png', new PhpExt_Handler(PhpExt_Javascript::stm($jsX)));
        $pnl->setRenderTo(PhpExt_Javascript::variable("Ext.get('oe-menubar'+".$orderId.")"));

        $js .= PhpExt_Ext::onReady(
            '$("#memoContainer"+'.$orderId.').parent().prepend("<div id=\'oe-menubar'.$orderId.'\'></div>");',
            $pnl->getJavascript(false, "oeMenubar")
        );
    }

    public function openNewOrderWindowFrontend($data)
    {
        global $xtLink;

        unset($_SESSION['registered_customer']);
        unset($_SESSION['customer']);
        unset($_SESSION['cart']);

        $adminUser = $_SESSION['admin_user'];
        if ($adminUser && $adminUser['user_id'] && $data['customers_id'] && $data['customers_email'])
        {
            global $language, $db;
            $lang = $_SESSION['selected_language'] ? $_SESSION['selected_language'] : $language->default_language;

            $cid = intval($data['customers_id']);
            $sql = "select s.* from ".TABLE_MANDANT_CONFIG." s, ".TABLE_CUSTOMERS." c
                    where c.customers_id = ? and c.shop_id = s.shop_id";
            $record = $db->Execute($sql, array($cid));
            if ($record->_numOfRows==1)
            {
                $url = $record->fields['shop_ssl'] != 'no_ssl' ? $record->fields['shop_https'] : $record->fields['shop_http'];
                if (strpos('_'.strrev($url),'/')!=1)
                {
                    $url .= '/';
                }
                $payload = array(
                  'adminUser' => $adminUser,
                  'userEmail' => $data['customers_email']
                );

                $lang = _SYSTEM_SEO_URL_LANG_BASED === true || _SYSTEM_SEO_URL_LANG_BASED == 'true' ? $lang.'/' : '';

                $sr = order_edit_tools::createSignedRequest($payload, XT_ORDER_EDIT_SECRET_KEY);
		        // Fix if the shop is in subfolder just add the path
		        $url = $url . ltrim(_SRV_WEB_UPLOAD, '/');
                $url = $url.$lang."customer/login?sr=".$sr;//.'&'.$sessName.'='.session_id();
                $xtLink->_redirect($url);
            }
            die();
        }
    }

    public function openNewOrderTabBackend($data)
    {
        global $xtLink, $db;

        unset($_SESSION['registered_customer']);
        unset($_SESSION['customer']);
        unset($_SESSION['cart']);

        $order = new order(0, $data['customers_id']);

        $customer = new customer($data['customers_id']);
        
        if (!$customer->customer_default_address || !$customer->customer_shipping_address || !$customer->customer_payment_address) {
        	die('Please add valid payment/shipping address for this customer.');
        }

        $sql = "SELECT `config_value` FROM ". TABLE_CONFIGURATION_MULTI.$customer->customer_info['shop_id'] . " WHERE `config_key`='_STORE_CURRENCY' ";
        $cur = $db->GetOne($sql);

        $cart = new cart();
        $_SESSION['cart'] = $cart;

        $orderData = array(
            'payment_code' => _SYSTEM_ORDER_EDIT_NEW_ORDER_PAYMENT, //$order->order_data['payment_code'],
            'subpayment_code' => '', //$order->order_data['subpayment_code'],
            'shipping_code' => _SYSTEM_ORDER_EDIT_NEW_ORDER_SHIPPING,  //$order->order_data['shipping_code'],
            'currency_code' => $cur,  //$order->order_data['currency_code'],
            'currency_value' => '1',  //$order->order_data['currency_value'],
            'orders_status' => '',  //$order->order_data['orders_status'],
            'account_type' => $customer->account_type,  //$order->order_data['account_type'],
            'allow_tax' => '',  //$order->order_data['allow_tax'],
            'comments' => '', //$order->order_data['comments'],
            'customers_id' => $data['customers_id'],
            'source_id' => _SYSTEM_ORDER_EDIT_NEW_ORDER_ORDER_SOURCE,
            'shop_id' => $customer->customer_info['shop_id'], //$order->order_data['shop_id'],
            'customers_ip' => '', //$order->order_data['customers_ip'],
            'delivery' => $customer->customer_shipping_address, //$this->_customer->customer_shipping_address,
            'billing' => $customer->customer_payment_address
        );
        $savedOrder = $order->_setOrder($orderData,'complete','insert', '');

        if (!$savedOrder['orders_id'])
        {
            $msg = '<div class="order_edit_error_box">Could not create order.<br />';
            if (!$customer->customer_default_address) $msg .= '<br/> Customers default address is empty.';
            if (!$customer->customer_shipping_address) $msg .= '<br/> Customers shipping address is empty.';
            if (!$customer->customer_payment_address) $msg .= '<br/> Customers payment address is empty.';
            $msg .= '</div>';
            die($msg);
        }

        $remoteWindow = '';
        if ($data['openRemoteWindow'])
        {
            $remoteWindow = '&openRemoteWindow='.$data['openRemoteWindow'];
        }
        // redirect zur order_edit.php
        $xtLink->_redirect('order_edit.php?pg=overview&parentNode=node_order&gridHandle=ordergridForm&edit_id='.$savedOrder['orders_id'].$remoteWindow);
    }
}