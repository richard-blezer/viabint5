<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
* Plugin class
* 
* @author Jens Albert
* @copyright 8works <info@8works.de>
* 
* Don't change anything from here on
* if you don't know what you're doing.
* Otherwise the earth might disappear
* in a large black hole. We'll blame you!
*/
if (!class_exists('ew_2to1_coupon')) {
    class ew_2to1_coupon
    {
        const EW_2TO1_COUPON_COUPON_STATUS = 'ew_2to1_coupon_coupon_status';
        const EW_2TO2_COUPON_PRODUCT = 'ew_2to1_coupon_product';
        const MAX_QTY = 1;
        const FAKE_PRODUCTS_ID = 1000000;
        const FAKE_PRODUCTS_PRICE = 0;

        private static $_coupon = null;
        private static $_cartContent = null;
        private static $_products = null;

        /**
         * @param $price
         * @return bool
         */
        public static function is2to1ProductInPriceRange($price)
        {
            $price = (float)$price;

            if (($min = self::get2to1ProductMinimumPrice()) > 0 &&
                $price < $min) {
                return false;
            }

            if (($max = self::get2to1ProductMaximumPrice()) > 0 &&
                $price > $max) {
                return false;
            }

            return true;
        }

        /**
         * @return float|int
         */
        public static function get2to1ProductMinimumPrice()
        {
            return defined('CONFIG_EW_2TO1_COUPON_PRODUCT_PRICE_MIN') ? (float)CONFIG_EW_2TO1_COUPON_PRODUCT_PRICE_MIN : 0;
        }

        /**
         * @return float|int
         */
        public static function get2to1ProductMaximumPrice()
        {
            return defined('CONFIG_EW_2TO1_COUPON_PRODUCT_PRICE_MAX') ? (float)CONFIG_EW_2TO1_COUPON_PRODUCT_PRICE_MAX : 0;
        }

        /**
         * @return int
         */
        public static function get2to1ProductId()
        {
            return (int)$_SESSION[self::EW_2TO2_COUPON_PRODUCT];
        }

        /**
         * @param string|int $pid products id or cart key
         * @return bool
         */
        public static function set2to1ProductId($pid)
        {
            $_SESSION[self::EW_2TO2_COUPON_PRODUCT] = (int)$pid;

            return self::is2to1ProductSet();
        }

        /**
         * @return bool
         */
        public static function unset2to1ProductFromStorage()
        {
            if (self::is2to1ProductSet()) {
                unset($_SESSION[self::EW_2TO2_COUPON_PRODUCT]);
            } else {
                return false;
            }

            return !self::is2to1ProductSet();
        }

        /**
         * @return bool
         */
        public static function unset2to1ProductFromCart()
        {
            if (isset($_SESSION['cart']->content) &&
                !empty($_SESSION['cart']->content)) {
                $key = $_SESSION['cart']->_genProductsKey(array('product' => self::getFakeProductsId()));
                if (isset($_SESSION['cart']->content[$key])) {
                    $_SESSION['cart']->_deleteContent($key);

                    return !isset($_SESSION['cart']->content[$key]);
                }
            }

            return false;
        }

        /**
         * @param string|int $pid products id or cart key
         * @return bool
         */
        public static function is2to1ProductOrigin($pid)
        {
            return self::get2to1ProductId() === (int)$pid;
        }

        /**
         * @param string|int $pid products id or cart key
         * @return bool
         */
        public static function is2to1ProductSelf($pid)
        {
            return self::getFakeProductsId() === (int)$pid;
        }

        /**
         * @param string|int $pid products id or cart key
         * @return bool
         */
        public static function is2to1Product($pid)
        {
            return self::is2to1ProductSelf($pid) || self::is2to1ProductOrigin($pid);
        }

        /**
         * @param $cartRequest
         * @param $check
         * @return bool
         */
        public static function request2to1Product($cartRequest, $check)
        {
            if (!self::statusCartIsFull())
                return false;

            if (!self::is2to1ProductSet() &&
                isset($check['type']) &&
                $check['type'] == 'update' &&
                isset($cartRequest[self::EW_2TO2_COUPON_PRODUCT]) &&
                (int)$cartRequest[self::EW_2TO2_COUPON_PRODUCT] === (int)$cartRequest['product']) {

                $product = new product($cartRequest['product']);
                if (!isset($product->data['products_price']['plain_otax']) ||
                    !self::is2to1ProductInPriceRange($product->data['products_price']['plain_otax'])) {
                    return false;
                }

                return self::set2to1ProductId($cartRequest[self::EW_2TO2_COUPON_PRODUCT]);
            }

            return false;
        }

        /**
         * @return array|null
         */
        public static function getCartContent()
        {
            if (self::$_cartContent === null) {
                if (isset($_SESSION['cart']->content) &&
                    is_array($_SESSION['cart']->content) &&
                    !empty($_SESSION['cart']->content)) {

                    $content = $_SESSION['cart']->content;
                    foreach ($content as $key => $product) {
                        if (self::isVtFreeProduct($product)) {
                            unset($content[$key]);
                        }
                    }

                    self::$_cartContent = $content;
                }
            }

            return self::$_cartContent;
        }

        /**
         * @param $product
         * @return bool
         */
        public static function isVtFreeProduct($product)
        {
            return isset($product['vt_fp_id']) && (int)$product['vt_fp_id'] !== 0;
        }

        /**
         * @return bool
         */
        public static function is2to1ProductSet()
        {
            return isset($_SESSION[self::EW_2TO2_COUPON_PRODUCT]);
        }

        /**
         * @return int
         */
        public static function getFakeProductsId()
        {
            return (int)self::FAKE_PRODUCTS_ID;
        }

        /**
         * @return float
         */
        public static function get2to1ProductPrice()
        {
            return (float)self::FAKE_PRODUCTS_PRICE;
        }

        /**
         * @param int $qty
         * @return int
         */
        public static function getMaxQtyFor2to1Product($qty = 1)
        {
            return ((int)$qty > self::getMaxQty()) ? self::getMaxQty() : $qty;
        }

        /**
         * @return int
         */
        public static function getMaxQty()
        {
            return (int)self::MAX_QTY;
        }

        /**
         * @return array|null
         */
        public static function getCoupon()
        {
            if (!isset($_SESSION['sess_coupon']['coupon_id']) ||
                ($id = (int)$_SESSION['sess_coupon']['coupon_id']) === 0) {

                return self::$_coupon = null;
            } else if (self::$_coupon === null) {
                $conf = self::EW_2TO1_COUPON_COUPON_STATUS;
                $coupon = self::getRecordFromDB("SELECT * FROM `xt_coupons` WHERE `coupon_id` = '$id'");
                self::$_coupon = isset($coupon[0][$conf]) && (int)$coupon[0][$conf] !== 0 ?
                    $coupon[0] :
                    null;
            }

            return self::$_coupon;
        }

        /**
         * @return array|null
         */
        public static function getCartProducts()
        {
            if (self::$_products === null) {
                if (($cartContent = self::getCartContent()) !== null) {
                    $products = array();
                    foreach ($cartContent as $content) {
                        if (isset($content['products_id']) &&
                            ($id = $content['products_id']) !== 0 &&
                            !self::is2to1Product($content['products_id'])) {

                            $product = new product($id);
                            if (isset($product->data['products_price']['plain_otax']) &&
                                self::is2to1ProductInPriceRange($product->data['products_price']['plain_otax'])) {
                                $products[$id] = $product->data;
                            }
                        }
                    }

                    self::$_products = !empty($products) ? $products : null;
                }
            }

            return self::$_products;
        }

        /**
         * @return bool
         */
        public static function isAdmin()
        {
            return defined('USER_POSITION') && USER_POSITION == 'admin';
        }

        /**
         * @return bool
         */
        public static function status()
        {
            if (!self::statusPlugin())
                return false;

            if (!self::statusOnlyLoggedIn())
                return false;

            if (!self::statusCouponRedeemed())
                return false;

            if (!self::statusCartIsFull())
                return false;

            return true;
        }

        /**
         * @return bool
         */
        public static function statusPlugin()
        {
            if (!self::statusExternalPlugin(__CLASS__))
                return false;

            if (!self::check_conf('CONFIG_' . strtoupper(__CLASS__) . '_STATUS'))
                return false;

            if (!self::statusExternalPlugin('xt_coupons'))
                return false;

            if (ew_2to1_coupon::isAdmin())
                return false;

            return true;
        }

        /**
         * @return bool
         */
        public static function statusCartIsFull()
        {
            return self::getCartContent() !== null;
        }

        /**
         * @return bool
         */
        public static function statusCouponRedeemed()
        {
            return self::getCoupon() !== null;
        }

        /**
         * @param $code
         * @return bool
         */
        public static function statusExternalPlugin($code)
        {
            global $xtPlugin;

            return isset($xtPlugin->active_modules) && isset($xtPlugin->active_modules[$code]);
        }

        /**
         * @return bool
         */
        public static function statusCartPage()
        {
            return (self::get_current_pagename() == 'cart') && self::check_conf('XT_COUPONS_CART_PAGE');
        }

        /**
         * @return bool
         */
        public static function statusCheckoutPage()
        {
            return (self::get_current_pagename() == 'checkout') && self::check_conf('XT_COUPONS_CHECKOUT_PAGE');
        }

        /**
         * @return bool
         */
        public static function statusOnlyLoggedIn()
        {
            if (isset($_SESSION['customer']->customers_id) && (int)$_SESSION['customer']->customers_id !== 0) {
                return true;
            }

            return !self::check_conf('XT_COUPONS_LOGIN');
        }

        /**
        * Adds new mysql table column if not exists and correct syntax
        * 
        * @param string $table The database tablename
        * @param string $column The database table columnname
        * @param string $definition The mysql query
        * @return bool True or false on fail
        */
        public static function mysqlAddColumn($table, $column, $definition)
        {
            global $db;

            if (self::mysqlTableSchemaExists($table, $column))
                return false;

            if (($record = @$db->Execute("ALTER TABLE `$table` ADD `$column` $definition")) !== false && is_object($record)) {
                $record->Close();

                return true;
            }

            return false;
        }

        /**
        * Adds new mysql table column if not exists and correct syntax
        * 
        * @param string $table The database tablename
        * @param string $column The database table columnname
        * @return bool True or false on fail
        */
        public static function mysqlDropColumn($table, $column)
        {
            global $db;

            if (!self::mysqlTableSchemaExists($table, $column))
                return false;

            if (($record = @$db->Execute("ALTER TABLE `$table` DROP `$column`")) !== false && is_object($record)) {
                $record->Close();

                return true;
            }

            return false;
        }

        /**
        * Check if an table or its column(s) exists
        * 
        * @param string $table The database tablename
        * @param string $column The database table columnname (* by default, comma seperated lists allowed)
        * @return bool True or false on fail
        */
        public static function mysqlTableSchemaExists($table, $column = '*')
        {
            global $db;

            if (($record = @$db->Execute("SELECT `$column` FROM `$table` LIMIT 1")) !== false && is_object($record)) {
                $record->Close();

                return true;
            }

            return false;
        }

        /**
         * Get page link
         *
         * @param string $pageName
         * @param string $params Link params like `example=true&test=1&hello=world`
         * @return string
         */
        public static function getPageLink($pageName = __CLASS__, $params = null)
        {
            global $xtLink;

            return $xtLink->_link(array('page' => $pageName, $params));
        }

        /**
        * HELPER TO GET CURRENT PAGE NAME
        */
        public static function get_current_pagename()
        {
            global $page;

            if (isset($page->page_name) && !empty($page->page_name))
                return $page->page_name;

            return (!empty($_GET) && isset($_GET['page']) && !empty($_GET['page'])) ? trim($_GET['page']) : false;
        }

        /**
        * GET CONFIGURATION SETTING
        * 
        * @param    string    Configuration Key / CONSTANT
        * @return    bool    Returns config value 
        */
        public static function check_conf($key)
        {
            if (!is_string($key))
                return false;

            if (!defined($key))
            return false;

            $key = constant($key);
            $erg = false;

            switch (gettype($key)) {
                case 'boolean':
                    $erg = $key;

                    break;

                case 'integer':
                    if ($key == 1)
                        $erg = true;

                    break;

                case 'string':
                    $key = strtolower(trim($key));
                    if ($key == 'true')
                        $erg = true;
                    if ($key == '1')
                        $erg = true;

                    break;
            }

            return $erg;
        }

        /**
         * Get data from database with select query
         *
         * @example classname::getRecordFromDB("SELECT * FROM ".DB_PREFIX."_products WHERE products_master_model != '' LIMIT 999999")
         * @param string $query
         * @return array
         */
        public static function getRecordFromDB($query)
        {
            global $db;

            $data = array();

            if (($record = $db->Execute($query)) == 0)
                return $data;

            while(!$record->EOF) {
                $data[] = $record->fields;
                $record->MoveNext();
            }
            $record->Close();

            return $data;
        }
    }
}
