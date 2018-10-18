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

defined('_VALID_CALL') OR die('Direct Access is not allowed.');

class xt_reviews
{

    public $navigation_count, $navigation_pages;

    protected $position;

    protected $_table = TABLE_PRODUCTS_REVIEWS;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'review_id';

    public function __construct()
    {
        $this->getPermission();
    }

    function _addReview($data, $use_id = false)
    {
        global $db, $filter, $language, $xtPlugin, $store_handler;

        if ($use_id === false) {
            if (!$_SESSION['registered_customer'] && XT_REVIEWS_ALLOW_GUEST_REVIEWS == 'false') return false;

            if (isset($_SESSION['customer'])) {
                $customers_id = $_SESSION['customer']->customers_id;
            } else {
                $customers_id = 0;
            }
        } elseif (empty($data['customers_id'])) return false;
        else $customers_id = $data['customers_id'];

        $review_rating = (int)$data['review_rating'];
        $review_title = $filter->_filter($data['review_title']);
        $review_text = $filter->_filter($data['review_text']);
        $products_id = (int)$data['products_id'];

        if (empty($review_rating) OR empty($review_title) OR empty($review_text) OR empty($products_id)) return false;

        if (XT_REVIEWS_AUTO_ACTIVATE == 'true') {
            $data['review_status'] = 1;
        }

        // insert values
        $data_array = array(
            'products_id' => (int)$products_id,
            'customers_id' => (int)$customers_id,
            'review_rating' => (int)$review_rating,
            'review_title' => $review_title,
            'review_date' => $db->BindTimeStamp(time()),
            'review_text' => $review_text,
            'language_code' => $language->code
        );

        isset($data['orders_id']) && $data_array['orders_id'] = (int)$data['orders_id'];
        isset($data['review_source']) && $data_array['review_source'] = $data['review_source'];
        isset($data['review_status']) && $data_array['review_status'] = $data['review_status'];

        ($plugin_code = $xtPlugin->PluginCode('class.xt_reviews.php:_addReview')) ? eval($plugin_code) : false;

        $db->AutoExecute(TABLE_PRODUCTS_REVIEWS, $data_array, 'INSERT');

        // Insert default permissions
        $data_array['review_id'] = $db->Insert_ID();

        foreach ($store_handler->getStores() as $store) {
            if (_SYSTEM_GROUP_PERMISSIONS === 'whitelist' && $store['id'] === $store_handler->shop_id)
                $data_array['shop_' . $store_handler->shop_id] = '1';
            elseif (_SYSTEM_GROUP_PERMISSIONS === 'blacklist' && $store['id'] !== $store_handler->shop_id)
                $data_array['shop_' . $store['id']] = '1';
        }

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($data_array, $data_array[$this->_master_key]);
        if (XT_REVIEWS_NOTIFY_ADMIN == 'true') {
            $this->notifyAdmin($data_array);
        }
        return true;
    }


    function notifyAdmin($data)
    {
        global $db, $language;

        $mail = new xtMailer('review-notification-mail');
        $mail->_addReceiver(_CORE_DEBUG_MAIL_ADDRESS, _STORE_NAME);
        $mail->_assign('orders_id', $data['orders_id']);
        $mail->_assign('review_date', $data['review_date']);
        $mail->_assign('review_status', $data['review_status']);
        $mail->_assign('language_code', $data['language_code']);
        $mail->_assign('review_text', $data['review_text']);
        $mail->_assign('review_rating', $data['review_rating']);
        $mail->_sendMail();

    }

    function _deleteReview($data)
    {
    }

    public function getPermission()
    {
        global $store_handler, $customers_status, $xtPlugin;

        $this->perm_array = array(
            'shop_perm' => array(
                'type' => 'shop',
                'table' => TABLE_PRODUCTS_REVIEWS_PERMISSION,
                'key' => $this->_master_key,
                'pref' => 'r'
            )
        );

        ($plugin_code = $xtPlugin->PluginCode(__CLASS__ . ':getPermission')) ? eval($plugin_code) : false;
        $this->permission = new item_permission($this->perm_array);
    }

    /**
     * Display product
     *
     * @param    int $product_id
     * @return    array
     */
    public function displayProduct($product_id)
    {
        global $p_info, $store_handler;

        $product_id = (int)$product_id;
        if ($product_id < 1)
            return array();

        $module_content = $this->getReviewsListing($product_id);
        $tpl_data = array(
            'reviews_data' => $module_content,
            'show_reviews_button' => XT_REVIEWS_ALL_REVIEWS_PAGE,
            'link_reviews_list' => $p_info->data['link_reviews_list'],
            'link_reviews_write' => $p_info->data['link_reviews_write'],
            'store_domain' => $store_handler->domain
        );

        $tpl = 'products_reviews_list.html';
        $template = new Template();
        $template->getTemplatePath($tpl, 'xt_reviews', '', 'plugin');

        return $template->getTemplate('xt_reviews_p_list_smarty', $tpl, $tpl_data);
    }

    /**
     * Get reviews data list
     *
     * @param    int $products_id
     * @param    bool $all_reviews Whether this is for the "all reviews" page
     * @return    array
     */
    public function getReviewsListing($products_id, $all = false)
    {
        global $db, $xtLink, $xtPlugin, $language;

        $products_id = (int)$products_id;
        if ($products_id < 1)
            return array();

        $sql_data = $this->_getSQL($products_id, $all);
        $sql_data['where'] .= ' and review_date<=NOW() ';
        $query = 'SELECT r.* FROM ' . $sql_data['table'] . ' WHERE ' . $sql_data['where'];

        if ($all === true) {
            $pages = new split_page($query, 5, $xtLink->_getParams(array('next_page')) . '&page_action=show');

            $this->navigation_count = $pages->split_data['count'];
            $this->navigation_pages = $pages->split_data['pages'];

            $reviews =& $pages->split_data['data'];
        } else {
            $result = $db->CacheExecute($query);
            if ($result->RecordCount() == 0)
                return array();

            $reviews = array();
            while (!$result->EOF) {
                $reviews[] = $result->fields;
                $result->MoveNext();
            }

            // shuffle if more than max
            if (XT_REVIEWS_MAX_DISPLAY_PRODUCTS < $result->RecordCount()) {
                shuffle($reviews);
                $reviews = array_slice($reviews, 0, XT_REVIEWS_MAX_DISPLAY_PRODUCTS);
            }

            $result->Close();
        }

        $module_content = array();
        foreach ($reviews as $arr) {
            $arr['review_rating'] = $this->_getReviewsStars($arr['review_id']);

            if ($arr['customers_id'] <> 0) {
                $customer = new customer($arr['customers_id']);
                $arr['review_editor'] = $customer->customer_default_address['customers_firstname'] . ' ' . $customer->customer_default_address['customers_lastname'][0] . '.';
            } else {
                $arr['review_editor'] = TEXT_CUSTOMER_GUEST . '.';
            }
            $module_content[] = $arr;
        }

        return $module_content;
    }

    public function getStars($pid)
    {
        global $db, $language, $xtPlugin;

        $pid = (int)$pid;
        if ($pid < 1) return 1;

        $sql_data = $this->_getSQL($pid);
        $sql_data['where'] .= ' and review_date<=NOW() ';
        $result = $db->Execute('SELECT (SUM(r.review_rating) / COUNT(r.review_id)) AS rating FROM ' . $sql_data['table'] . ' WHERE ' . $sql_data['where']);

        $rating = ($result->RecordCount() == 1)
            ? $result->fields['rating']
            : 0;
        $result->Close();

        return empty($rating) ? 1 : $rating * 100 / 5;
    }

    protected function _getReviewsStars($rid)
    {
        global $db;

        $rs = $db->Execute('SELECT review_rating FROM ' . TABLE_PRODUCTS_REVIEWS . ' WHERE review_status = 1 AND review_id = ?', array((int)$rid));
        $rate = ($rs->RecordCount() > 0) ? $rs->fields['review_rating'] : 0;
        $rs->Close();

        return empty($rate) ? 1 : $rate * 100 / 5;
    }

    public function getReviewsSum($pid)
    {
        global $db;

        $pid = (int)$pid;
        if ($pid < 1)
            return 0;

        $sql_data = $this->_getSQL($pid);
        $sql_data['where'] .= ' and review_date<=NOW() ';
        $result = $db->Execute('SELECT COUNT(r.review_id) AS review_count FROM ' . $sql_data['table'] . ' WHERE ' . $sql_data['where']);
        $reviews_sum = ($result->RecordCount() > 0)
            ? $result->fields['review_count']
            : 0;
        $result->Close();

        return $reviews_sum;
    }

    /**
     * recalulate rating count and AVG rating for given products id or review id
     *
     * @param    int $id
     * @return    bool
     */
    function _reCalculate($id, $wtype = 'products_id')
    {
        global $db;

        $id = (int)$id;

        if ($wtype !== 'products_id') {
            $rs = $db->Execute("SELECT products_id FROM " . TABLE_PRODUCTS_REVIEWS . " WHERE " . $wtype . " = ?", array($id));
            $products_id = ($rs->RecordCount() > 0) ? $rs->fields['products_id'] : 0;
        } else $products_id = $id;

        $rs = $db->Execute("SELECT COUNT(*) AS count, AVG(review_rating) AS rating FROM " . TABLE_PRODUCTS_REVIEWS . " WHERE products_id = ? AND review_status = 1", array($products_id));
        if ($rs->fields['count'] > 0) $this->_updateRate($products_id, $rs->fields['rating'], $rs->fields['count']);
        else $this->_updateRate($products_id, 0, 0);

        return true;
    }

    function _updateRate($products_id, $rate, $count)
    {
        global $db;
        $db->Execute(
            "UPDATE " . TABLE_PRODUCTS . " SET products_average_rating = ?, products_rating_count = ? WHERE products_id = ?",
            array($rate, $count, $products_id)
        );
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        global $language, $xtPlugin;

        $header = array(
            'review_id' => array('type' => 'hidden'),
            'products_id' => array('type' => 'hidden'),
            'language_code_bug' => array('type' => 'hidden'),
            'customers_id' => array('type' => 'hidden'),
            'review_source' => array('type' => 'hidden'),
            'language_code' => array('type' => 'dropdown', 'url' => 'DropDownData.php?get=language_codes'),
            'review_text' => array('type' => 'textarea'),
            'admin_comment' => array('type' => 'textarea'),
            'review_title' => array('type' => 'textfield'),
            'review_rating' => array('type' => 'hidden', 'renderer' => 'starRenderer')
        );

        ($plugin_code = $xtPlugin->PluginCode('class.xt_reviews.php:_getParams_header')) ? eval($plugin_code) : false;

        $params = array(
            'header' => $header,
            'master_key' => $this->_master_key,
            'default_sort' => $this->_master_key,
            'SortField' => $this->_master_key,
            'SortDir' => 'DESC',
            'display_checkItemsCheckbox' => true,
            'display_checkCol' => true,
            'display_statusTrueBtn' => true,
            'display_statusFalseBtn' => true,
            'display_newBtn' => false
        );

        ($plugin_code = $xtPlugin->PluginCode('class.xt_reviews.php:_getParams_bottom')) ? eval($plugin_code) : false;
        return $params;
    }

    function _get($ID = 0)
    {
        global $xtPlugin, $db, $language;

        if ($this->position !== 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        } else {
            $obj = new stdClass;
            $ID = (int)$ID;
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);

        if ($this->url_data['get_data']) $data = $table_data->getData();
        elseif ($ID) {
            $data = $table_data->getData($ID);
            $data[0]['language_code_bug'] = $data[0]['language_code'];
        } else $data = $table_data->getHeader();

        $obj->totalCount = empty($table_data->_total_count) ? count($data) : $table_data->_total_count;
        $obj->data = $data;
        return $obj;
    }

    function _set($data, $set_type = 'edit')
    {
        global $db, $language, $filter, $xtPlugin;

        $data['language_code'] = $data['language_code_bug'];
        unset($data['language_code_bug']);
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        $this->_reCalculate($data['products_id']);

        ($plugin_code = $xtPlugin->PluginCode('class.xt_reviews.php:_set')) ? eval($plugin_code) : false;

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($data, $data[$this->_master_key]);

        return $obj;
    }

    function _unset($id = 0)
    {
        global $db;

        if (empty($id) OR $this->position !== 'admin') return false;

        $id = (int)$id;

        $set_perm = new item_permission($this->perm_array);
        $set_perm->_deleteData($id);

        $rs = $db->Execute("SELECT products_id FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?", array($id));
        if ($rs->RecordCount() > 0) {
            $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?", array($id));
            $this->_reCalculate($rs->fields['products_id']);
        }
    }

    function _setStatus($id, $status)
    {
        global $db, $xtPlugin;

        $id = (int)$id;
        ($plugin_code = $xtPlugin->PluginCode('class.xt_reviews.php:_setStatus')) ? eval($plugin_code) : false;

        $db->Execute("UPDATE " . $this->_table . " SET review_status = " . $status . " WHERE review_id = " . $id);
        $this->_reCalculate($id, 'review_id');
    }

    /**
     * Get SQL
     *
     * Returns the SQL FROM and WHERE clauses (keywords excluded) for fetching reviews data from the database
     *
     * @param    int $product_id Product ID
     * @param    bool $all_reviews Whether this is for the "all reviews" page
     * @return    array
     */
    protected function _getSQL($product_id, $all = false)
    {
        global $db, $xtPlugin, $language;

        $behavior = isset($xtPlugin->active_modules['xt_master_slave'])
            ? XT_REVIEWS_MASTER_SLAVE
            : null; // This will trigger default below

        $sql_data = array(
            'table' => TABLE_PRODUCTS_REVIEWS . ' r',
            'where' => 'r.review_status = 1'
        );

        if ($all !== true) {
            $sql_data['where'] .= " AND r.language_code IN('', '" . $language->code . "')";
        }

        switch ($behavior) {
            case 'master_all':
                // Master products display all reviews, including those from slaves
                $sql_data['table'] .= ' JOIN (SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_status = 1 AND (products_id = ' . $product_id . ' OR (products_master_flag = 0 AND products_master_model = (SELECT products_model FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . $product_id . ')))) p ON r.products_id = p.products_id';
                break;
            case 'master_only':
                // Only master products display reviews, including those from slaves
                $sql_data['table'] .= ' JOIN (SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_status = 1 AND ((products_id = ' . $product_id . ' AND (products_master_flag = 1 OR products_master_model IS NULL)) OR (products_master_flag = 0 AND products_master_model = (SELECT products_model FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . $product_id . ')))) p ON r.products_id = p.products_id';
                break;
            case 'slave_only':
                // Only slave products display reviews
                $sql_data['table'] .= ' JOIN ' . TABLE_PRODUCTS . ' p ON r.products_id = p.products_id';
                $sql_data['where'] .= ' AND r.products_id = ' . $product_id . ' AND (p.products_master_flag IS NULL OR p.products_master_flag = 0)';
                break;
            default:
                $sql_data['where'] .= ' AND r.products_id = ' . $product_id;
                break;
        }

        $sql_data['table'] .= ' ' . $this->permission->_table;
        $sql_data['where'] .= $this->permission->_where;

        return $sql_data;
    }

}