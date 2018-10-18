<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**
 * Plugin class
 *
 * @author    Jens Albert
 * @copyright 8works <info@8works.de>
 *
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 */
if (!class_exists('ew_coupon_giveaway')) {
    class ew_coupon_giveaway
    {
        const EW_COUPON_GIVEAWAY_PRODUCTS_FIELD = 'ew_coupon_giveaway_prodmodels';
        const QTY = 1;
        const FAKE_PRODUCTS_ID_START = 1000050;
        const FAKE_PRODUCTS_PRICE = 0;

        private static $_coupon = null;
        private static $_cartContent = null;
        private static $_products = array();

        /**
         * @param null $model
         * @return bool
         */
        public static function unsetGiveaways($model = null)
        {
            $giveaways = self::getAddedGiveaways();

            if ($model === null || $giveaways === null) {
                unset($_SESSION[self::className()]);
                if (isset($_SESSION['cart']->content) && is_array($_SESSION['cart']->content)) {
                    foreach ($_SESSION['cart']->content as $key => $item) {
                        if ((int)$key >= self::FAKE_PRODUCTS_ID_START) {
                            $_SESSION['cart']->_deleteContent($key);
                        }
                    }
                }

                return true;
            } else {
                foreach ($giveaways as $productsId => $giveaway) {
                    if ($giveaway == $model) {
                        $key = $_SESSION['cart']->_genProductsKey(array('product' => $productsId));
                        if (isset($_SESSION['cart']->content[$key])) {
                            unset($giveaways[$productsId]);
                            self::removeFromSession($productsId);
                            $_SESSION['cart']->_deleteContent($key);

                            return !isset($_SESSION['cart']->content[$key]);
                        }

                        return false;
                    }
                }

                return false;
            }
        }

        /**
         * @return bool
         */
        public static function setGiveaways()
        {
            global $xtLink, $info;

            if (($giveaways = self::getConfiguredGiveaways()) === null) {
                return false;
            }
            $addedGiveaways = self::getAddedGiveaways();
            $productsId = self::FAKE_PRODUCTS_ID_START;

            if (self::array_equal($giveaways, $addedGiveaways)) {
                return false;
            }

            foreach ($giveaways as $productsModel) {
                if (self::addToSession($productsId, $productsModel)) {
                    $cart_product = new product(self::getProductsIdByFakeId($productsId));
                    self::addToCart($productsId, $productsModel);
                    $info->_addInfoSession(sprintf(TEXT_EW_COUPON_GIVEAWAY_PRODUCT_ADDED, $cart_product->data['products_name']), 'success');
                }
                $productsId++;
            }

            $link_array = array('page' => 'cart');
            if (self::get_current_pagename() == 'checkout') {
                $link_array = array('page' => 'checkout', 'paction' => 'confirmation');
            }

            $xtLink->_redirect($xtLink->_link($link_array));
        }

        /**
         * @param $productsId
         * @param $productsModel
         * @return bool
         */
        public static function addToSession($productsId, $productsModel)
        {
            $key = self::className();
            if (!self::isGiveawaySet()) {
                $_SESSION[$key] = array();
            }
            if (self::isGiveawaySet($productsModel)) {
                return false;
            }
            $_SESSION[$key][$productsId] = $productsModel;

            return self::isGiveawaySet($productsModel);
        }

        /**
         * @param $pid
         * @return bool
         */
        public static function removeFromSession($pid)
        {
            $pid = (int)$pid;
            if (!self::isGiveawaySelf($pid)) {
                return false;
            }
            unset($_SESSION[self::className()][$pid]);

            return !self::isGiveawaySelf($pid);
        }

        /**
         * @param $productsId
         * @param $productsModel
         */
        public static function addToCart($productsId, $productsModel)
        {
            $_SESSION['cart']->_addCart(
                array(
                    'action'          => 'add_product',
                    'product'         => $productsId,
                    'qty'             => self::QTY,
                    'info'            => $productsId,
                    'page'            => 'product',
                    self::className() => $productsModel,
                ));
        }

        /**
         * @param $model
         * @return int|null
         */
        public static function getProductsIdByModel($model)
        {
            $table = TABLE_PRODUCTS;
            $model = trim($model);
            $key = 'products_id';

            if (!isset(self::$_products[$model])) {
                $data = self::getRecordFromDB("SELECT `{$key}` FROM `{$table}` WHERE `products_model` = '{$model}'");
                if (isset($data[0][$key]) && (int)$data[0][$key] !== 0) {
                    self::$_products[$model] = (int)$data[0][$key];
                }
            }

            return isset(self::$_products[$model]) ? self::$_products[$model] : null;
        }

        /**
         * @param $fakeId
         * @return int|null
         */
        public static function getProductsIdByFakeId($fakeId)
        {
            if (($model = self::getProductsModelByFakeId($fakeId)) === null) {
                return null;
            }

            return self::getProductsIdByModel($model);
        }

        /**
         * @param $fakeId
         * @return null|string
         */
        public static function getProductsModelByFakeId($fakeId)
        {
            return isset($_SESSION[self::className()][(int)$fakeId]) ? trim($_SESSION[self::className()][(int)$fakeId]) : null;
        }

        /**
         * @param null $model
         * @return bool
         */
        public static function isGiveawaySet($model = null)
        {
            $key = self::className();

            if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key]) || empty($_SESSION[$key])) {
                return false;
            }

            if ($model !== null) {
                return in_array($model, $_SESSION[$key]);
            }

            return true;
        }

        /**
         * @param $model
         * @return bool
         */
        public static function isGiveawayConfiguredByModel($model)
        {
            if (($giveaways = self::getConfiguredGiveaways()) === null) {
                return false;
            }

            return in_array(trim($model), $giveaways);
        }

        /**
         * @param string|int $pid products id or cart key
         * @return bool
         */
        public static function isGiveawaySelf($pid)
        {
            return isset($_SESSION[self::className()][(int)$pid]);
        }

        /**
         * @param string|int $id products id, cart key or products model
         * @return bool
         */
        public static function isGiveaway($id)
        {
            return self::isGiveawaySelf($id) || self::isGiveawaySet($id);
        }

        /**
         * @return array|null
         */
        public static function getAddedGiveaways()
        {
            return self::isGiveawaySet() ? $_SESSION[self::className()] : null;
        }

        /**
         * @return array|null
         */
        public static function getConfiguredGiveaways()
        {
            $coupon = self::getCoupon();
            if (!isset($coupon[self::EW_COUPON_GIVEAWAY_PRODUCTS_FIELD]) || empty($coupon[self::EW_COUPON_GIVEAWAY_PRODUCTS_FIELD])) {
                return null;
            }

            return $coupon[self::EW_COUPON_GIVEAWAY_PRODUCTS_FIELD];
        }

        /**
         * @param $a
         * @param $b
         * @return bool
         */
        public static function array_equal($a, $b)
        {
            return (
                is_array($a) && is_array($b) &&
                count($a) == count($b) &&
                array_diff($a, $b) === array_diff($b, $a)
            );
        }

        /**
         * @return array|null
         */
        public static function getCartContent()
        {
            if (self::$_cartContent === null) {
                if (isset($_SESSION['cart']->content) &&
                    is_array($_SESSION['cart']->content) &&
                    !empty($_SESSION['cart']->content)
                ) {

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
         * @return array|null
         */
        public static function getCoupon()
        {
            if (!isset($_SESSION['sess_coupon']['coupon_id']) ||
                ($id = (int)$_SESSION['sess_coupon']['coupon_id']) === 0
            ) {

                return self::$_coupon = null;
            } else if (self::$_coupon === null) {
                $conf = self::EW_COUPON_GIVEAWAY_PRODUCTS_FIELD;
                $coupon = self::getRecordFromDB("SELECT * FROM `xt_coupons` WHERE `coupon_id` = '$id'");
                if (isset($coupon[0][$conf])) {
                    $coupon[0][$conf] = explode(',', $coupon[0][$conf]);
                    foreach ($coupon[0][$conf] as $k => $i) {
                        $coupon[0][$conf][$k] = trim($i);
                        if (empty($coupon[0][$conf][$k])) {
                            unset($coupon[0][$conf][$k]);
                        }
                    }
                    if (!empty($coupon[0][$conf])) {
                        self::$_coupon = $coupon[0];
                    } else {
                        self::$_coupon = null;
                    }
                } else {
                    self::$_coupon = null;
                }
            }

            return self::$_coupon;
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

            if (self::isAdmin())
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
         * @param string $table      The database tablename
         * @param string $column     The database table columnname
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
         * @param string $table  The database tablename
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
         * @param string $table  The database tablename
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

            while (!$record->EOF) {
                $data[] = $record->fields;
                $record->MoveNext();
            }
            $record->Close();

            return $data;
        }

        /**
         * @return string
         */
        public static function className()
        {
            return __CLASS__;
        }
    }
}
