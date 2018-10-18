<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.permissions.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.item_permissions.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.language_content.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.language.php';

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.order.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.product.php';

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.orderCouponInfo.php';

global $order_edit_controller;
$order_edit_controller = order_edit_controller::getInstance();

class order_edit_controller
{
    private static $_inst = null;

    private static $_active = false;

    public $_orders_id = 0;
    public $_orderFields = null;
    public $_order = null;

    public $_products_id = 0;
    public $_product = null;

    public $_customers_id = 0;
    public $_customer = null;

    public $_customers_status_id = 0;
    public $_customers_status = null;

    public $_coupons = array();

    public static function getInstance()
    {
        if (self::$_inst==null)
        {
            self::$_inst = new order_edit_controller();
        }
        return self::$_inst;
    }

    private static function init()
    {
        self::getInstance();
        return self::$_active;
    }

    private function __construct()
    {
        if (
            ($_REQUEST['plugin'] == 'order_edit' && $_REQUEST['orders_id'])
            ||
            ($_REQUEST['parentNode'] == 'node_order' && $_REQUEST['gridHandle']=='ordergridForm' && $_REQUEST['edit_id'])
        )
        {
            if ($_REQUEST['edit_id'] && empty($_REQUEST['orders_id']))
            {
                $_REQUEST['orders_id'] = $_REQUEST['edit_id'];
            }

            self::cleanSession();
            $this->initFrameworkClasses();

            global $db;
            $this->_orders_id = (int) $_REQUEST['orders_id'];
            $orderQuery = $db->Query("SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id = ".(int)$this->_orders_id);
            $this->_orderFields = $orderQuery->fields;

            $this->_customers_id = (int) $this->_orderFields['customers_id'];
            $this->_customer = new customer($this->_customers_id);
            $this->_customer->_customer($this->_customers_id);
            $this->_customer->_setAdress($this->_orderFields['delivery_address_book_id'], 'shipping');
            $this->_customer->_setAdress($this->_orderFields['billing_address_book_id'], 'payment');

            $this->_customers_status_id = $this->_customer->customer_info['customers_status'];
            $this->_customers_status = new customers_status($this->_customer->customer_info['customers_status']);

            if ($_REQUEST['products_id'])
            {
                $this->_products_id = (int) $_REQUEST['products_id'];
            }

            self::$_active = true;
        }
        else if ($_REQUEST['parentNode']=='node_order' && $_REQUEST['pg']=='overview' && $_REQUEST['edit_id'])
        {

        }
    }

    private function initFrameworkClasses()
    {
        global $language, $plugin;
        $language = new language();
        $language->_setLocale();
        $language->_getLanguageContent(USER_POSITION);
    }

    public static function isActive()
    {
        return self::$_active;
    }

    public function getOrder()
    {
        if (empty($this->_order))
        {
            global $tax, $price, $currency;
            if (empty($tax) || empty($price) || empty($currency) || empty($this->_orders_id))
            {
                return false;
            }
            $this->_order = new order($this->_orders_id, $this->_orderFields['customers_id']);
        }

        $priceOverride = array();
        foreach($this->_order->order_products as $p)
        {
            $priceOverride[$this->_orders_id][$p['products_id']] = $p['products_price']['plain'];
        }
        $_SESSION['order_edit_priceOverride'] = $priceOverride;

        return $this->_order;
    }

    public function getProduct($qty = 1)
    {
        if (!self::init()) return;

        $this->_product = new product($this->_products_id, 'default', $qty, $this->_orderFields['language_code']);
        return $this->_product;
    }

    public function setOrder($order, $mode = 'update', $coupon_code = null)
    {
        // cart berechnen
        $_SESSION['cart']->_refresh();
		$order_status = isset($order->order_data['orders_status_id']) ? $order->order_data['orders_status_id'] : $order->order_data['orders_status'];
        $data = array(
            'payment_code' => $order->order_data['payment_code'],
            'subpayment_code' => $order->order_data['subpayment_code'],
            'shipping_code' => $order->order_data['shipping_code'],
            'currency_code' => $order->order_data['currency_code'],
            'currency_value' => $order->order_data['currency_value'],
            'orders_status' => $order_status,
            'account_type' => $order->order_data['account_type'],
            'allow_tax' => $order->order_data['allow_tax'],
            'comments' => $order->order_data['comments'],
            'source_id' => $order->order_data['source_id'],
            'customers_id' => $order->order_data['customers_id'],
            'shop_id' => $order->order_data['shop_id'],
            'customers_ip' => $order->order_data['customers_ip'],
            'delivery' => $this->_customer->customer_shipping_address,
            'billing' => $this->_customer->customer_payment_address

        );
        
        // tweak des store_handler, es soll immer der aus der bestellung sein
        global $store_handler;
        $storeId = $store_handler->shop_id;
        $store_handler->shop_id = $order->order_data['shop_id'];

        $savedOrder = $order->_setOrder($data,'complete',$mode, $order->oID);

        self::setStats($order);
        self::cleanSession();

        // store_handler tweak entfernen
        $store_handler->shop_id = $storeId;

        return $savedOrder;
    }

    public static function setCartSubContent($order, $mode = 'EDIT_PRODUCT', $coupon_code = null)
    {
        global $xtPlugin;

        $result = new stdClass();
        $result->errors = false;

        $r = self::setShipping($order);
        if ($r->errors)
        {
            return $r;
        }
        $r = self::setPayment($order);
        if ($r->errors)
        {
            return $r;
        }
        $couponsActive = self::isCouponPluginActive();
        if ($couponsActive)
        {
            $r = self::processCoupon($order, $mode, $coupon_code);
            if ($r->errors || $r->couponRemoved)
            {
                return $r;
            }
        }

        // allow other plugins to set
        // their own order_totals
        ($plugin_code  =  $xtPlugin->PluginCode('class.order_edit_controller.php:setCartSubContent_bottom'))  ?  eval($plugin_code) : false;

        return $result;
    }

    public static function setShipping($order)
    {
        $result = new stdClass();
        $result->errors = false;

        $_SESSION['cart']->_refresh(); // TODO mal checken, ob das nicht zu oft gerufen wird

        // shipping anfügen
        $checkout = new checkout();
        $tmp_shipping_data = $checkout->_getShipping();

        $shipping_data = $tmp_shipping_data[$order->order_data['shipping_code']];

        $shipping_class_path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$shipping_data['shipping_dir'].'/classes/';
        $shipping_class_file = 'class.'.$shipping_data['shipping_code'].'.php';

        if (file_exists($shipping_class_path . $shipping_class_file)) {
            require_once($shipping_class_path.$shipping_class_file);
            $shipping_module_data = new $shipping_data['shipping_code']($shipping_data);
        }

        $shipping_data_array = array('customer_id' => $order->customer,
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

        return $result;
    }

    public static function setPayment($order)
    {
        $result = new stdClass();
        $result->errors = false;

        // payment anfügen
        $checkout = new checkout();
        $tmp_payment_data = $checkout->_getPayment();

        $_payment = $order->order_data['payment_code'];
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

        if($payment_data['payment_price']['plain_otax']){
            // payment discount ?

            if ($payment_data['payment_price']['discount']==1) {

            } else {

                $payment_data_array = array(
                    'customer_id' => $_SESSION['registered_customer'],
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
        if (!empty($payment_data_array)){
            $_SESSION['cart']->_addSubContent($payment_data_array);
        }

        $_SESSION['cart']->_refresh();

        return $result;
    }

    /*
     * Fügt Shipping, Payment und Coupon dem Session-Cart hinzu
     *
     * Verbindung mit xt_coupons v1.3.8
     * Es wir davon ausgegangen, dass ein an der Order hängeder Gutschein bei Hinzufügen gültig war
     * Ein bereits an der Order/cart vorhandener Gutschein könnte bei mode=EDIT_PRODUCT entfernt werden, weil
     * a) Gutschein mittlerweilen deaktiviert wurde                     -> TEXT_COUPON_ERROR_CODE_NOT_FOUND
     * b) oder mittlerweilen abgelaufen                                 -> TEXT_COUPON_ERROR_CODE_NOT_FOUND
     * c) mindest Bestellwert nicht mehr erreicht                       -> TEXT_COUPON_ERROR_MIN_ORDER_VALUE_FAILED
     * d) Kundengruppe hat sich geändert                                -> TEXT_COUPON_ERROR_CUSTOMER_GROUP_NOT_ALLOWED
     * e) Produkt nicht mehr im cart                                    -> TEXT_COUPON_ERROR_NO_MATCHED_PRODUCT
     * f) min Bestellwert des produkt zu gering                         -> TEXT_COUPON_ERROR_MIN_ORDER_VALUE_FAILED
     * g) Gutschein gesamt verwendung überschritten                     -> TEXT_COUPON_ERROR_MAX_REDEEM
     * h) Gutschein Kunde verwendung überschritten                      -> TEXT_COUPON_ERROR_MAX_CUSTOMER_REEDEM
     * i) XT_COUPONS_USE_LEFTOVER = false & produkt min nicht erreicht  -> TEXT_COUPON_ERROR_MIN_ORDER_VALUE_FAILED
     * j) XT_COUPONS_USE_LEFTOVER = true &  bestell min nicht erreicht  -> TEXT_COUPON_ERROR_MIN_ORDER_VALUE_FAILED
     * k) Gutschein Code bereits verwendet                              -> TEXT_COUPON_ERROR_TOKEN_USED
     * l) xt_socialcommerce - Kunde bereits in TABLE_SOCIAL_PROMOTION_ACTIVITIES -> TEXT_COUPON_ERROR_MAX_CUSTOMER_REEDEM
     * m) xt_socialcommerce - Gutschein nicht erlaubt für social netwok          -> TEXT_COUPON_ERROR_CUSTOMER_NOT_ALLOWED
     *
     * Umgang mit Gutschein Fehlern
     * 1) Fehler die unabhängig von Produkten sind
     *    a b d g h k l m
     *    diese Fehler dürfen den Gutschein nicht von der Order trennen
     *
     * 2) Fehler im Zusammenhang mit Produkten (eindeutig)
     *    e
     *    Gutschein muss von der Order getrennt werden
     *
     * 3) Fehler im Zusammenhang mit Produkten und Mindestbestellwert (nicht eindeutig)
     *    c f i j (zb weil Menge geändert oder Produkt mitllerweilen billiger)
     *    Gutschein WIRD von der Order getrennt werden
     *
     * Problematisch:
     * - keine eindeutigen Fehlercodes sondern, insbesondere bei TEXT_COUPON_ERROR_MIN_ORDER_VALUE_FAILED
     *
     * @param $order
     * @param $mode - EDIT_PRODUCT (default) oder EDIT_COUPON
     * @param $coupon_code
     *
     * coupon_code == null Coupon der Order wird übernommen (wenn vorhanden)
     * coupon_code == ''   Coupon wird nicht aus order in cart übernommen
     * !empty(coupon_code) es wird versucht den Coupon dem Cart hinzufufügen
     *
     * @return ohne
     */


    public static function processCoupon($order, $mode = 'EDIT_PRODUCT', $couponCodeRequested = null)
    {
        if ($mode == 'EDIT_PRODUCT')
        {
            self::setCoupon($order, $mode);
        }
        else if (!empty($couponCodeRequested))
        {
            self::setCoupon($order, $mode, $couponCodeRequested);
        }
        else
        {
            self::removeCoupon($order);
        }
    }
	
	 public static function updateProductOrderPercentageDiscount($order,$coupon){
        global $db; 
        
        $xt_coupons = new xt_coupons();
        $oci = self::getCouponForOrder($order->oID);
        $arr_coupon = $oci->xt_coupon;
        if (!$arr_coupon) $dicount = 0;
        else $dicount = $arr_coupon['coupon_percent'];
        if ($order->order_products)
        {
            foreach($order->order_products as $p)
            {
                $db->Execute(
                    "Update ".TABLE_ORDERS_PRODUCTS." SET products_discount = ? WHERE orders_id = ? and products_id = ?",
                    array($dicount, $order->oID, $p['products_id'])
                );
            }
        }
    }
	
    public static function removeCoupon($order)
    {
        $result = new stdClass();
        $result->errors = false;

        $oci = self::getCouponForOrder($order->oID);
		$arr_coupon = $oci->xt_coupon;
        $xt_coupons = new xt_coupons();
        
        if ($arr_coupon['coupon_percent']>0){
           
            if ($order->order_products)
            {
                $d = array();
                foreach($order->order_products as $p)
                {
                    $coupon_product_discount = $xt_coupons->coupon_product_percent($p,1,$arr_coupon);
                    $p_info = new product($p['products_id'], 'full', '', '', 'product_info');
                    $price = $p_info->build_price($p['products_id'], $p_info->data['products_price'],  $p_info->data['products_tax_class_id']);
                    $priceOverride[$order->oID][$p['products_id']] = $price;
                }
                
            }
            $_SESSION['order_edit_priceOverride'] = $priceOverride;
            $_SESSION['cart']->_refresh();
            
        }
        if ($oci) {
            global $db;
            // token aktualisieren
            if ($oci->isToken)
            {
                $sql = "UPDATE ".DB_PREFIX."_coupons_token SET `coupon_token_order_id`= 0, `coupon_token_amount`= (`coupon_token_amount`+".$oci->lastRedeemAmount.") WHERE `coupon_token_order_id`=? AND `coupons_token_id` = ?";
                $db->Execute($sql, array($order->oID, $oci->xt_coupon_token['coupons_token_id']));
            }
            // db xt_coupons updaten, gutschein verwendung aktualisieren
            $sql = "UPDATE ".DB_PREFIX."_coupons SET `coupon_order_ordered`=(`coupon_order_ordered`-1) WHERE `coupon_id` = ?";
            $db->Execute($sql, array($oci->xt_coupon['coupon_id']));
            // redeem eintrag entfernen
            if ($oci->isToken) {
                $sql = "DELETE FROM ".DB_PREFIX."_coupons_redeem WHERE `order_id` = ? AND `coupon_token_id`=?";
                $db->Execute($sql, array($order->oID, $oci->xt_coupon_token['coupons_token_id']));
            } else {
                $sql = "DELETE FROM ".DB_PREFIX."_coupons_redeem  WHERE `order_id` =? AND `coupon_id`=?";
                $db->Execute($sql, array($order->oID, $oci->xt_coupon['coupon_id']));
            }
        }

        return $result;
    }

    public static function setCoupon($order, $mode = 'EDIT_PRODUCT', $couponCodeRequested = null)
    {
        $result = new stdClass();
        $result->errors = false;
        $result->couponRemoved = false;

        $couponCode2Apply = null;
        $tmpOci = false;
        $restoreOci = false;

        $xt_coupons = new xt_coupons();
        $xt_coupons->setPosition('admin');

        // coupon_code == null Coupon der Order wird übernommen (wenn vorhanden)
        if (is_null($couponCodeRequested))
        {
            // in session einen entfernten coupon suchen
            $oci = self::sessionGetBackupCoupon($order->oID);

            // in oder_total suchen
            if (!$oci && is_array($order->order_total_data))
            {
                foreach($order->order_total_data as $total)
                {
                    if ($total['orders_total_key'] == 'xt_coupon')
                    {
                        $oci = self::getCouponForOrder($total['orders_id']);
                    }
                }
            }
            else
            {
                $restoreOci = true;
            }

            // wenn gefunden in session oder order_total
            if ($oci)
            {
                // tmp coupon erstellen
                $tmpOci = self::createTmpCoupon($oci);
                $couponCode2Apply = $tmpOci->isToken ? $tmpOci->xt_coupon_token['coupon_token_code'] : $tmpOci->xt_coupon['coupon_code'];
            }
            else {
                // kein coupon zu verarbeiten, weder aus session noch aus order_total
                return $result;
            }
        }
        else if (!empty($couponCodeRequested))
        {
            $couponCode2Apply = $couponCodeRequested;
        }

        // wenn bis jetzt ein anzuwendender coupon ermittelt wurde: jetzt anfügen
        if (!empty($couponCode2Apply))
        {
            global $db; // für ff include

            $couponApplied = $xt_coupons->_addToCart($couponCode2Apply);

			$xt_coupons = new xt_coupons();
            $arr_coupon = $xt_coupons->_check_coupon_avail($couponCode2Apply);
            $discount = 0;

            if ($arr_coupon['coupon_percent']>0){
               
                if ($order->order_products)
                {
                    $d = array();
                    foreach($order->order_products as $p)
                    {
                        $coupon_product_discount = $xt_coupons->coupon_product_percent($p,1,$arr_coupon);
                        $discount += $coupon_product_discount;
                        $new_price = $xt_coupons->coupon_product_price($p,1,$arr_coupon);
                        $priceOverride[$order->oID][$p['products_id']] = $new_price["plain"];
                        
                    }
                }
                //$order->order_products = $d;
                
                $_SESSION['cart']->total_discount =$discount;
                $_SESSION['cart']->discount =true;
                $_SESSION['order_edit_priceOverride'] = $priceOverride;
                
            }
            if($couponApplied)
            {
                // update der coupon daten in db
                global $db; // für ff include
                include _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_coupons/hooks/module_checkout.phpcheckout_proccess_bottom.php';

                if($tmpOci)
                {
                    // daten umkopieren/wiederherstellen in originalen coupon

                    // token update
                    if ($oci->isToken)
                    {
                        // db coupons_token
                        //$sqlOrderId = "SELECT `coupon_token_order_id` FROM ".DB_PREFIX."_coupons_token WHERE `coupons_token_id`=".$tmpOci->xt_coupon_token['coupons_token_id'];
                        //$orderId = $db->GetOne($sqlOrderId);
                        $sqlTokenLeftoverAmount  = "SELECT `coupon_token_amount` FROM ".DB_PREFIX."_coupons_token WHERE `coupons_token_id`=?";
                        $tokenLeftoverAmount = $db->GetOne($sqlTokenLeftoverAmount, array($tmpOci->xt_coupon_token['coupons_token_id']));
                        $sql = "UPDATE ".DB_PREFIX."_coupons_token SET `coupon_token_order_id`= ?, `coupon_token_amount`=? WHERE `coupon_token_order_id`=? AND `coupons_token_id` = ?";
                        $db->Execute($sql, array($order->oID, $tokenLeftoverAmount, $order->oID, $oci->xt_coupon_token['coupons_token_id']));
                    }

                    // redeem update
                    // wenn wiederherstellung von orig coupon, dann neue redeem zeile anlege; sonst: update weiter unten
                    if ($restoreOci)
                    {
                        require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.xt_coupons_redeem.php';
                        $xt_coupons_redeem = new xt_coupons_redeem();
                        $xt_coupons_redeem->setPosition('admin');

                        if ($oci->isToken)
                        {
                            $sqlRedeemId = "SELECT `coupons_redeem_id` FROM ".DB_PREFIX."_coupons_redeem WHERE `order_id` = ? AND `coupon_token_id`=?";
                            $redeemId = $db->getOne($sqlRedeemId, array($order->oID, $tmpOci->xt_coupon_token['coupons_token_id']));
                        }
                        else {
                            $sqlRedeemId = "SELECT `coupons_redeem_id` FROM ".DB_PREFIX."_coupons_redeem WHERE `order_id` = ? AND `coupon_id`=?";
                            $redeemId = $db->getOne($sqlRedeemId, array($order->oID, $tmpOci->xt_coupon['coupon_id']));
                        }

                        $redeem = $xt_coupons_redeem->_get($redeemId);
                        $redeem = $redeem->data[0];
                        $redeem['coupon_id'] = $oci->xt_coupon['coupon_id'];

                        if ($oci->isToken)
                        {
                            $redeem['coupon_token_id'] = $oci->xt_coupon_token['coupons_token_id'];
                        }
                        unset($redeem['coupons_redeem_id']);
                        $xt_coupons_redeem->_set($redeem, 'new');
                    }
                    else // redeem amount update
                    {
                        if ($oci->isToken)
                        {
                            $sqlRedeemAmount = "SELECT `redeem_amount` FROM ".DB_PREFIX."_coupons_redeem WHERE `order_id` = ? AND `coupon_token_id`=?";
                            $redeemAmount = $db->GetOne($sqlRedeemAmount, array($order->oID, $tmpOci->xt_coupon_token['coupons_token_id']));
                            $sql = "UPDATE ".DB_PREFIX."_coupons_redeem SET `redeem_amount`= ? WHERE `order_id` = ? AND `coupon_token_id` = ?";
                            $db->Execute($sql, array($redeemAmount, $order->oID, $oci->xt_coupon_token['coupons_token_id']));
                        } else {
                            $sqlRedeemAmount = "SELECT `redeem_amount` FROM ".DB_PREFIX."_coupons_redeem WHERE `order_id` = ? AND `coupon_id`=?";
                            $redeemAmount = $db->GetOne($sqlRedeemAmount, array($order->oID, $tmpOci->xt_coupon['coupon_id']));
                            $sql = "UPDATE ".DB_PREFIX."_coupons_redeem SET `redeem_amount`= ? WHERE `order_id` = ? AND `coupon_id` = ?";
                            $db->Execute($sql, array($redeemAmount, $order->oID, $oci->xt_coupon['coupon_id']));
                        }
                    }

                    self::sessionRemoveBackupCoupon($order->oID);
                    self::deleteTmpToken($order->oID, $tmpOci);
                }

                if ($restoreOci /* TODO ? || */)
                {
                    // db xt_coupons updaten, gutschein verwendung aktualisieren
                    $sql = "UPDATE ".DB_PREFIX."_coupons SET `coupon_order_ordered`=(`coupon_order_ordered`+1) WHERE `coupon_id` = ?";
                    $db->Execute($sql, array($oci->xt_coupon['coupon_id']));
                }

            }
            else if ($tmpOci) // wir sind offensichlich mit einem tmpCoupon gescheitert
            {
                // daten umkopieren in originalen coupon wenn token
                if ($oci->isToken)
                {
                    // db coupons_token
                    $sqlTokenLeftoverAmount  = "SELECT `coupon_token_amount` FROM ".DB_PREFIX."_coupons_token WHERE `coupons_token_id`=?";
                    $tokenLeftoverAmount = $db->GetOne($sqlTokenLeftoverAmount, array($tmpOci->xt_coupon_token['coupons_token_id']));
                    $sql = "UPDATE ".DB_PREFIX."_coupons_token SET `coupon_token_order_id`= 0, `coupon_token_amount`=? WHERE `coupons_token_id` = ?";
                    $db->Execute($sql, array($tokenLeftoverAmount, $oci->xt_coupon_token['coupons_token_id']));
                }
                // db xt_coupons updaten, gutschein verwendung aktualisieren
                $sql = "UPDATE ".DB_PREFIX."_coupons SET `coupon_order_ordered`=(`coupon_order_ordered`-1) WHERE `coupon_id` = ?";
                $db->Execute($sql, array($oci->xt_coupon['coupon_id']));
                // redeem eintrag entfernen
                if ($tmpOci->isToken) {
                    $sql = "DELETE FROM ".DB_PREFIX."_coupons_redeem WHERE `order_id` = ? AND `coupon_token_id`=?";
                    $db->Execute($sql, array($order->oID, $oci->xt_coupon_token['coupons_token_id']));
                } else {
                    $sql = "DELETE FROM ".DB_PREFIX."_coupons_redeem  WHERE `order_id` = ? AND `coupon_id`=?";
                    $db->Execute($sql, array($order->oID, $oci->xt_coupon['coupon_id']));
                }

                // den coupon für späteres anfügen sichern
                $o = unserialize(serialize($tmpOci));
                $o->xt_coupon['coupon_id'] = $oci->xt_coupon['coupon_id'];
                $o->xt_coupon['coupon_code'] = $oci->xt_coupon['coupon_code'];
                if ($tmpOci->isToken)
                {
                    $o->xt_coupon_token['coupons_token_id'] = $oci->xt_coupon_token['coupons_token_id'];
                    $o->xt_coupon_token['coupon_token_code'] = $oci->xt_coupon_token['coupon_token_code'];
                    $o->xt_coupon_token['coupon_id'] = $oci->xt_coupon['coupon_id'];
                }
                self::sessionAddBackupCoupon($order->oID, $o);
                self::deleteTmpToken($order->oID, $tmpOci);

                $result->errors[] = $xt_coupons->error_info;
                $result->couponRemoved = true;
                return $result;
            }
        }
        return $result;
    }

    public static function sessionGetBackupCoupon($orderId)
    {
        $coupons = $_SESSION['order_edit_coupons'];
        if(!is_null($coupons) && is_array($coupons) && array_key_exists($orderId, $coupons))
        {
            $oci = self::ociFromStdClass($coupons[$orderId]);
            return $oci;
        }
        return false;
    }

    public static function sessionRemoveBackupCoupon($orderId)
    {
        $coupons = $_SESSION['order_edit_coupons'];
        if(!is_null($coupons) && is_array($coupons))
        {
            unset($_SESSION['order_edit_coupons'][$orderId]);
        }
    }

    public static function sessionAddBackupCoupon($orderId, $oci)
    {
        $coupons = $_SESSION['order_edit_coupons'];
        if(is_null($coupons) || !is_array($coupons))
        {
            $coupons = array();
        }
        $coupons[$orderId] = self::stdClassFromOci($oci);
        $_SESSION['order_edit_coupons'] = $coupons;
    }

    public static function deleteTmpToken($orderId, $oci)
    {
        global $db;
        // tmpCoupon löschen
        // xt_couons kaskadiert, aber ohne redeem tabelle
        $xt_coupons = new xt_coupons();
        $xt_coupons->setPosition('admin');
        $xt_coupons->_unset($oci->xt_coupon['coupon_id']);
        if ($oci->isToken)
        {
            $sql = "DELETE FROM ".DB_PREFIX."_coupons_redeem WHERE  `order_id` = ? AND`coupon_token_id`=?";
            $db->Execute($sql, array($orderId, $oci->xt_coupon_token['coupons_token_id']));
        } else {
            $sql = "DELETE FROM ".DB_PREFIX."_coupons_redeem WHERE  `order_id` = ? AND`coupon_id`=?";
            $db->Execute($sql, array($orderId, $oci->xt_coupon['coupon_id']));
        }
    }

    public static function createTmpCoupon($oci)
    {
        $tmpApx = uniqid("-tmp-");
        $tmpCouponTokenData = null;

        // der gutschein selbst
        $xt_coupons = new xt_coupons();
        $tmpCouponData = $oci->xt_coupon;
        unset($tmpCouponData['coupon_id']);
        $tmpCouponData['coupon_code'] = $tmpCouponData['coupon_code'] . $tmpApx;
        $tmpCouponData['coupon_expire_date'] = date('Y-m-d', time()+60*60*24);
        $tmpCouponData['coupon_order_ordered'] = $tmpCouponData['coupon_order_ordered'] - 1; // kann/ darf nie < 0 werden
        $tmpCouponData['coupon_max_per_customer'] = 0;
        $r = $xt_coupons->_set($tmpCouponData, 'new');
        $tmpCouponData['coupon_id'] = $r->new_id;

        // gutschein code
        if ($oci->isToken)
        {
            require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.xt_coupons_token.php';
            $xt_coupons_token = new xt_coupons_token();

            $tmpCouponTokenData = $oci->xt_coupon_token;
            unset($tmpCouponTokenData['coupons_token_id']);
            $tmpCouponTokenData['coupon_id'] = $tmpCouponData['coupon_id'];
            $tmpCouponTokenData['coupon_token_code'] = $tmpCouponTokenData['coupon_token_code'] . $tmpApx;
            $tmpCouponTokenData['coupon_token_order_id'] = 0;
            $tmpCouponTokenData['coupon_token_amount'] = $tmpCouponTokenData['coupon_token_amount'] + $oci->lastRedeemAmount;
            $r = $xt_coupons_token->_set($tmpCouponTokenData, 'new');
            $tmpCouponTokenData['coupons_token_id'] = $r->new_id;
        }

        // produkte
        $productIds = $xt_coupons->_get_products_for_coupon($oci->xt_coupon['coupon_id']);
        if (is_array($productIds) && count ($productIds)>0)
        {
            require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.xt_coupons_products.php';
            $xt_coupons_products = new xt_coupons_products();
            $xt_coupons_products->url_data = array('coupon_id' => $tmpCouponData['coupon_id']);
            foreach($productIds as $pid)
            {
                $xt_coupons_products->_set($pid, 'new');
            }
        }

        // kundenzuordnung
        require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.xt_coupons_customers.php';
        $xt_coupons_customers = new xt_coupons_customers();
        $customerIds = $xt_coupons_customers->_getIDs($oci->xt_coupon['coupon_id']);
        if (is_array($customerIds) && count ($customerIds)>0)
        {
            $xt_coupons_customers->url_data = array('coupon_id' => $tmpCouponData['coupon_id']);
            foreach($customerIds as $cid)
            {
                $xt_coupons_customers->_set($cid, 'new');
            }
        }

        $tmpCoupon = new orderCouponInfo();
        $tmpCoupon->xt_coupon = $tmpCouponData;
        $tmpCoupon->isToken = $oci->isToken;
        $tmpCoupon->xt_coupon_token = $tmpCouponTokenData;

        return $tmpCoupon;
    }

    public static function setStats($order){
        global $db;

        $tmp_order = new order($order->oID,$order->order_customer['customers_id']); // fix no csutomer issue // new order($oID);

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
    }

    public function hook_getCurrency_top(&$force_code)
    {
        if (!self::init()) return;

        if (_SYSTEM_ORDER_EDIT_USE_CUSTOMER_CURRENCY === 'true')
        {
            $force_code =  $this->_orderFields['currency_code'];
        }
    }

    public function hook_customersStatus_top(&$c_status)
    {
        global $customer;
        if (!self::init()) return;

        global $order_edit_controller;

        $_SESSION['registered_customer'] = $this->_customer_id;
        $_SESSION['customer'] = $order_edit_controller->_customer;
    }

    public function hook_tax_build(&$taxInstance)
    {
        if (!self::init()) return;

        $taxInstance->country_code = $this->_customer->customer_shipping_address['customers_country_code'];
        $taxInstance->zone_id = $this->_customer->customer_shipping_address['customers_zone'];
    }

    public function hook_price_top(&$price_group, &$master_price_group, &$force_currency='')
    {
        if (!self::init()) return;

        $price_group = $this->_customers_status->customers_status_id;
        $master_price_group = $this->_customers_status->customers_status_master;

        if (_SYSTEM_ORDER_EDIT_USE_CUSTOMER_CURRENCY === 'true')
        {
            $force_currency =  self::$_inst->_orderFields['currency_code'];
        }
    }

    public function hook_cart_getContent_data($cart)
    {
        if (!self::init()) return;

        $_SESSION['selected_payment'] = $this->_orderFields['payment_code'];


        $payment = new payment();
        $d = array(
            'language' => '',
            'customer_default_address' => $this->_customer->customer_payment_address,
            'products' => $cart->show_content,
            'count' => $cart->content_count,
            'total' => $cart->total,
            'customers_status_id' => $this->_customers_status_id

        );
        $payment->_payment($d);

    }

    public function cmpOrderProducts($a, $b)
    {
        return $a['order_products_id'] < $b['order_products_id'];
    }

    public static function cleanSession()
    {
        unset($_SESSION['selected_payment']);
        unset($_SESSION['selected_payment_discount']);
        unset($_SESSION['cart']);
    }

    public static function getCouponForOrder($orderId)
    {
        $couponInfo = new orderCouponInfo();

        global $db;
        $couponIds = $db->GetOne(
            "SELECT concat( `coupon_id`,',',IFNULL(`coupon_token_id`,0)) FROM `".DB_PREFIX."_coupons_redeem` WHERE `order_id` = ?",
            array($orderId)
        );
        $couponIds = explode(',', $couponIds);
        $couponId = $couponIds[0];
        $couponTokenId = $couponIds[1];

        require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.xt_coupons.php';
        $xt_coupons = new xt_coupons();
        $xt_coupons->setPosition('admin');
        $tmp_coupon = $xt_coupons->_get($couponId);
        if ($tmp_coupon && $tmp_coupon->totalCount > 0) {
            $couponInfo->xt_coupon = $tmp_coupon->data[0];
        } else {
            return false;
        }

        if ($couponTokenId)
        {
            $couponInfo->isToken = true;

            require_once _SRV_WEBROOT.'plugins/xt_coupons/classes/class.xt_coupons_token.php';
            $xt_coupons_token = new xt_coupons_token();
            $xt_coupons_token->setPosition('admin');

            $tmp_coupon_token = $xt_coupons_token->_get($couponTokenId);
            if ($tmp_coupon_token && $tmp_coupon_token->totalCount > 0)
            {
                $couponInfo->xt_coupon_token = $tmp_coupon_token->data[0];
            }
        }
        // last redeem amount
        $lastRedeemAmount = (float) $db->GetOne(
            "SELECT `redeem_amount` FROM `".DB_PREFIX."_coupons_redeem` WHERE `order_id` = ?",
            array($orderId)
        );
        $couponInfo->lastRedeemAmount = $lastRedeemAmount;

        return $couponInfo;
    }

    public static function ociFromStdClass($stdClazz)
    {
        if($stdClazz==null)
        {
            return false;
        }
        $oci = new orderCouponInfo();
        foreach($stdClazz as $k=>$v)
        {
            $oci->$k = $v;
        }
        return $oci;
    }

    public static function stdClassFromOci($oci)
    {
        $stdClazz = new stdClass();
        foreach($oci as $k=>$v)
        {
            $stdClazz->$k = $v;
        }
        return $stdClazz;
    }

    public static function isCouponPluginActive()
    {
        $plg = new plugin();

        $plugins = $plg->getInstalledPlugins();
        foreach($plugins as $p)
        {
            if ($p['code']=='xt_coupons')
            {
                return $p['plugin_status']==1 ? true : false;
            }
        }
        return false;
    }

    public static function isOrderSourcePluginActive()
    {
        $plg = new plugin();

        $plugins = $plg->getInstalledPlugins();
        foreach($plugins as $p)
        {
            if ($p['code']=='xt_order_source')
            {
                return $p['plugin_status']==1 ? true : false;
            }
        }
        return false;
    }
}