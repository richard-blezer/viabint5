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
include_once _SRV_WEBROOT . 'xtFramework/classes/class.category.php';

class custom_link
{
    var $current_category_id;
    var $current_category_data;
    var $level;
    var $admin = false;
    var $type = '';

    public $_master_key = 'categories_id';
    public $_image_key = 'categories_image';
    public $_table = TABLE_CATEGORIES;
    public $_display_key = 'categories_name';
    protected $_table_lang = TABLE_CATEGORIES_DESCRIPTION;
    protected $_table_seo = TABLE_SEO_URL;
    public $store_field_exists = false;
    public $_store_field = 'categories_store_id';


    function _setAdmin()
    {
        $this->admin = true;
    }

    function getPermission()
    {

        global $store_handler, $customers_status, $xtPlugin;

        $this->perm_array = array(
            'shop_perm' => array('type' => 'shop',
                'table' => TABLE_CATEGORIES_PERMISSION,
                'key' => $this->_master_key,
                'simple_permissions' => 'true',
                'simple_permissions_key' => 'permission_id',
                'pref' => 'c'
            ),

            'group_perm' => array('type' => 'group_permission',
                'table' => TABLE_CATEGORIES_PERMISSION,
                'key' => $this->_master_key,
                'simple_permissions' => 'true',
                'simple_permissions_key' => 'permission_id',
                'pref' => 'c'
            )
        );

        ($plugin_code = $xtPlugin->PluginCode(__CLASS__ . ':getPermission')) ? eval($plugin_code) : false;

        $this->permission = new item_permission($this->perm_array);

        return $this->perm_array;

    }

    function create_getCategorySQL_query()
    {
        global $xtPlugin;
        $this->sql_categories = new getCategorySQL_query();
        ($plugin_code = $xtPlugin->PluginCode('custom_link:create_getCategorySQL_query')) ? eval($plugin_code) : false;
    }

    /**
     * get parent id of category
     *
     * @param int $catID
     * @return int
     */
    function getParentID($catID)
    {
        $array = array('categories_id' => $catID);
        $data = $this->buildData($array);
        if (is_data($data))
            return $data['parent_id'];
        else
            return 0;
    }

    function _getParentID($catID)
    {
        global $db;

        $catID = (int)$catID;

        $rs = $db->Execute("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id=?", (int)$catID);
        if ($rs->RecordCount() == 1) {
            return $rs->fields['parent_id'];
        }
    }

    /**
     * get parent data of category
     *
     * @param int $catID
     * @return array
     */
    function getParentData($catID)
    {
        $array = array('categories_id' => $catID);
        $data = $this->buildData($array);
        if (is_array($data))
            return $data;
        else
            return 0;
    }


    /**
     * remove category from database, move products into other category if selected
     *
     * @param int $catID
     * @param boolean $move_products
     * @param int $target_catID
     */
    function removeCategory($catID)
    {
        global $db;

        $category_ids = array();
        $cat = new category();
        $category_ids = $cat->getChildCategoriesIDs($catID);
        $category_ids[] = $catID;

        // now delete categories
        foreach ($category_ids as $key => $id) {
            $this->_delete($id);
        }

    }

    /**
     * delete category and products from database
     *
     * @param int $id categories id
     */
    function _delete($id)
    {
        global $db, $xtPlugin;

        $id = (int)$id;

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_delete_top')) ? eval($plugin_code) : false;

        if (is_int($id)) {

            $this->getPermission();
            $set_perm = new item_permission($this->perm_array);
            $set_perm->_deleteData($id);


            $db->Execute("DELETE FROM " . TABLE_CATEGORIES . " WHERE categories_id = ?", array($id));
            $db->Execute("DELETE FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE categories_id = ?", array($id));
            saveDeletedUrl($id, 2);
            $db->Execute("DELETE FROM " . TABLE_SEO_URL . " WHERE link_id = ? and link_type='2'", array($id));
            $db->Execute("DELETE FROM " . TABLE_MEDIA_LINK . " WHERE link_id = ? and class='category'", array($id));
            $db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = ?", array($id));
            $db->Execute("DELETE FROM " . TABLE_CATEGORIES_CUSTOM_LINK_URL . " WHERE categories_id = ?", array($id));

            ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_delete_bottom')) ? eval($plugin_code) : false;
        }
    }

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        global $language, $xtPlugin, $store_handler;
        $params = array();

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_getParams_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        if (StoreIdExists($this->_table_lang, $this->_store_field)) {
            $this->store_field_exists = true;
        }

        if ($this->store_field_exists)
            $params['languageStoreTab'] = true;

        $header['categories_id'] = array('type' => 'hidden');
        $header['parent_id'] = array('type' => 'hidden');
        $header['permission_id'] = array('type' => 'hidden');
        $header['categories_owner'] = array('type' => 'hidden');
        $header['category_custom_link'] = array('type' => 'hidden');
        $header['categories_image'] = array('type' => 'image');


        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_getParams_header')) ? eval($plugin_code) : false;

        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;

        $params['exclude'] = array('external_id', 'date_added', 'last_modified', 'products_sorting', 'products_sorting2', 'top_category', 'categories_template',
            'listing_template', 'google_product_cat');
        $stores = $store_handler->getStores();
        foreach ($stores as $store) {
            foreach ($language->_getLanguageList() as $key => $val) {
                $add_to_f = '';
                if ($this->store_field_exists) $add_to_f = 'store' . $store['id'] . '_';
                $tmp_array = array('external_id_' . $add_to_f . $val['code'], 'categories_heading_title_' . $add_to_f . $val['code'],
                    'categories_description_' . $add_to_f . $val['code'],
                    'categories_description_bottom_' . $add_to_f . $val['code'], 'meta_title_' . $add_to_f . $val['code'],
                    'meta_keywords_' . $add_to_f . $val['code'], 'meta_description_' . $add_to_f . $val['code'],
                    'url_text_' . $add_to_f . $val['code'], 'categories_store_id_' . $add_to_f . $val['code']);
                $params['exclude'] = array_merge($params['exclude'], $tmp_array);

            }
        }

        $check_id = $this->getCurrentCatId($this->url_data['edit_id']);;

        $header['category_custom_link_type_perview'] = array('readonly' => 1);//array('type' => 'hidden');
        $header['category_custom_link_type'] = array('type' => 'hidden');
        $header['categories_left'] = array('type' => 'hidden');
        $header['categories_right'] = array('type' => 'hidden');

        if (isset($this->url_data['edit_id'])) $type = $this->getCustomLinkData($check_id);
        else $type = $_GET['link_type'];

        if (($type == 'custom')) {
            $header['category_custom_link_id'] = array('type' => 'hidden');
        } else {
            $header['category_custom_link_id'] = array(
                'type' => 'dropdown',
                'width' => 310,
                'url' => 'DropdownData.php?get=category_custom_link_id&custom_link_type=' . $type);
            $stores = $store_handler->getStores();
            foreach ($stores as $store) {
                foreach ($language->_getLanguageList() as $key => $val) {
                    $add_to_f = '';
                    if ($this->store_field_exists) {
                        $add_to_f = 'store' . $store['id'] . '_';
                    }
                    $header['link_url_' . $val['code']] = array('type' => 'hidden');
                }
            }

        }


        $params['header'] = $header;
        $params['rowActions'] = isset($rowActions) ? $rowActions : array();
        $params['rowActionsFunctions'] = isset($rowActionsFunctions) ? $rowActionsFunctions : array();

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_getParams_bottom')) ? eval($plugin_code) : false;

        return $params;
    }

    function getCustomLinkData($edit_id)
    {
        global $db;

        $query = "select category_custom_link_type from " . TABLE_CATEGORIES . " where categories_id = ?";

        $type = '';
        $record = $db->Execute($query, array($edit_id));
        if ($record->RecordCount() > 0) {
            $type = $record->fields['category_custom_link_type'];
        }
        $record->Close();

        return $type;
    }

    function getCustomLinkTypeDate()
    {
        global $db, $language, $store_handler, $xtPlugin;
        $type = $this->type;
        $add_sql = '';
        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:getCustomLinkTypeDate')) ? eval($plugin_code) : false;
        switch ($type) {
            case "product":
                if (StoreIdExists($this->_table_lang, $this->_store_field)) $add_sql = ' and d.products_store_id = ' . $store_handler->shop_id;
                $query = "select p.products_id as ID, CONCAT(d.products_name , ' (', p.products_id , ' ; ',p.products_ean,')' ) as name
				         from " . TABLE_PRODUCTS . " p
				         	  LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " d ON p.products_id = d.products_id
				         	  WHERE d.language_code = '" . $language->code . "' " . $add_sql;
                break;

            case "category":
                if (StoreIdExists($this->_table_lang, $this->_store_field)) $add_sql = ' and d.categories_store_id = ' . $store_handler->shop_id;
                $query = "select p.categories_id as ID, d.categories_name as name
				         from " . TABLE_CATEGORIES . " p
				         	  LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " d ON p.categories_id = d.categories_id
				         	  WHERE d.language_code = '" . $language->code . "' and d.categories_name!='' and p.category_custom_link=0 " . $add_sql;
                break;

            case "content":
                $query = "select p.content_id as ID, p.content_title as name
				         from " . TABLE_CONTENT_ELEMENTS . " p
				         	  WHERE p.language_code = '" . $language->code . "' and p.content_title!='' " . $add_sql;
                break;
            case "plugin":
                $query = "select DISTINCT p.plugin_id as ID, p.name as name 
                         from " . TABLE_PLUGIN_PRODUCTS . " p
                              INNER JOIN " . TABLE_SEO_URL . " d ON d.link_id = p.plugin_id and d.link_type=1000 ";

                break;
        }


        $data = array();
        $data[] = array('id' => '0', 'name' => TEXT_EMPTY_SELECTION);
        $record = $db->Execute($query);
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $records = $record->fields;

                $data[] = array('id' => $records['ID'], 'name' => ($records['name'] == '') ? ('no-name') : $records['name']);
                $record->MoveNext();
            }
            $record->Close();
        }
        $record->Close();

        return $data;
    }

    function checkCustomLink($catID)
    {
        global $db, $language;

        $query = "select category_custom_link from " . TABLE_CATEGORIES . " WHERE categories_id = ? ";
        $record = $db->Execute($query, array($catID));
        if ($record->RecordCount() > 0) {
            return $record->fields['category_custom_link'];
        }
        return -1;
    }


    function getCurrentCatId($catID)
    {
        $catID = str_replace('subcat_', '', $catID);
        list($current_category, $store_id) = explode('_catst_', $catID);
        return (int)$current_category;
    }

    function _get($catID = 0)
    {
        global $db, $language, $xtPlugin, $store_handler;

        $cat = new category();
        if ($_GET['pg'] == 'CheckItem') {
            $cat->CheckItem($catID, true);
            die();
        }
        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_get_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;


        $catID = $this->getCurrentCatId($catID);

        if (!isset($_GET['new'])) {
            if ($catID > 0) {
                $res = $cat->checkCategoryCustomLink($catID);

                if ($res == 0) {
                    echo '<script>alert("' . TEXT_ALERT_NOT_A_CUSTOM_LINK . '");';
                    echo 'contentTabs.remove(contentTabs.getActiveTab());';
                    echo '</script>';

                }
            }
        }
        if ($_GET['new'] == 'true') $catID = 'new';
        if ($catID === 'new' && isset($_GET['master_node'])) {
            $master = $_GET['master_node'];
            $master = $this->getCurrentCatId($_GET['master_node']);;
            $obj = $this->_set(array('parent_id' => $master, 'category_custom_link_type' => $_GET['link_type']), 'new');
            $catID = $obj->new_id;
        }

        $this->getPermission();
        if (_SYSTEM_SIMPLE_GROUP_PERMISSIONS == 'false' || $this->_getParentID($catID) == 0) {
            $permissions = $this->perm_array;
        } else {
            $permissions = '';
        }

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_get_data')) ? eval($plugin_code) : false;

        $store_field = '';
        if ($this->store_field_exists) {
            $store_field = $this->_store_field;
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $permissions, '', '', $store_field);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($catID) {
            $data = $table_data->getData($catID);

            $data[0]['group_permission_info'] = _getPermissionInfo();
            $data[0]['shop_permission_info'] = _getPermissionInfo();
            $data[0]['category_custom_link'] = 1;

            if (($data[0]['category_custom_link_type'] == 'custom')) {
                $stores = $store_handler->getStores();
                foreach ($stores as $store) {
                    foreach ($language->_getLanguageList() as $key => $val) {
                        $add_to_f = '';
                        $add_to_url = '';
                        if ($this->store_field_exists) {
                            $add_to_f = 'store' . $store['id'] . '_';
                            $add_to_url = "and store_id ='" . $store['id'] . "'";
                        }
                        $query = "SELECT * FROM " . TABLE_CATEGORIES_CUSTOM_LINK_URL . " WHERE categories_id=? and language_code=? " . $add_to_url;

                        $record = $db->Execute($query, array($catID, $val['code']));
                        if ($record->RecordCount() > 0) {
                            while (!$record->EOF) {
                                $records = $record->fields;

                                $data[0]['link_url_' . $add_to_f . $val['code']] = $records['link_url'];
                                $record->MoveNext();
                            }
                            $record->Close();
                        } else $data[0]['link_url_' . $add_to_f . $val['code']] = '';
                        $record->Close();
                    }
                }
            }
            $data[0]['category_custom_link_type_perview'] = $data[0]['category_custom_link_type'];
        } else {
            $data = $table_data->getHeader();
        }

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_get_bottom')) ? eval($plugin_code) : false;

        $obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;
    }

    function _set($data, $set_type = 'edit')
    {
        global $db, $language, $filter, $seo, $xtPlugin, $store_handler;

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_set_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        $obj = new stdClass;

        unset($data['categories_image']);

        if ($set_type == 'new') {
            $nested_set = new nested_set();
            $nested_set->setTable(TABLE_CATEGORIES);
            $nested_set->setTableDescription(TABLE_CATEGORIES_DESCRIPTION);
            $nested_set->buildNestedSet();

            list($left, $right) = $nested_set->getCategoryLeftRight($data['parent_id']);
            $data['categories_left'] = $left;
            $data['categories_right'] = $right;
        }
        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        if ($set_type == 'new') {    // edit existing
            $obj->new_id = $objC->new_id;
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
        }

        $oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__, $this->store_field_exists);
        $objCD = $oCD->saveDataSet();

        // Build Seo URLS
        $save_data = array();
        $st = new multistore();
        $stores = $store_handler->getStores();
        foreach ($stores as $store) {
            foreach ($language->_getLanguageList() as $key => $val) {
                $stor_f = '';
                $store_f_update = '';
                if ($this->store_field_exists) {
                    $stor_f = 'store' . $store['id'] . '_';
                    $store_f_update = $store['id'];
                }
                $save_data['language_code'] = $val['code'];
                $save_data['store_id'] = $store['id'];
                $save_data['categories_id'] = $data['categories_id'];
                $save_data['link_url_' . $stor_f . $val['code']] = $data['link_url_' . $stor_f . $val['code']];
                $URLs = new adminDB_DataSave(TABLE_CATEGORIES_CUSTOM_LINK_URL, $save_data, true, __CLASS__, $this->store_field_exists);
                $objCs = $URLs->saveDataSet();
            }
        }

        $this->getPermission();
        $set_perm = new item_permission($this->perm_array);
        $set_perm->_saveData($data, $data[$this->_master_key]);

        $cat = new category();
        $catdata = $cat->getChildCategoriesIDs($data['categories_id']);

        $set_perm->_setSimplePermissionRecursiv($data['categories_id'], $catdata);

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_set_bottom')) ? eval($plugin_code) : false;

        if ($objC->success && $objCD->success) {
            $obj->success = true;
        } else {
            $obj->failed = true;
        }

        return $obj;
    }


    function _unset($id = 0)
    {
        global $db, $link_params, $xtPlugin;


        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_unset_top')) ? eval($plugin_code) : false;
        if (isset($plugin_return_value))
            return $plugin_return_value;

        if ($id == 0) {
            if (is_array($link_params) && isset($link_params['edit_id'])) {
                $id = $this->getCurrentCatId($link_params['edit_id']);
                $id = (int)$id;
            } else {
                return false;
            }
        } else {
            $id = (int)$id;
        }


        if (!is_int($id)) return false;
        if ($id == 0) return false;

        $this->removeCategory($id);

        ($plugin_code = $xtPlugin->PluginCode('class.custom_link.php:_unset_bottom')) ? eval($plugin_code) : false;

        $obj = new stdClass;
        $obj->success = true;
        return $obj;

    }

    function _setImage($id, $file)
    {
        global $xtPlugin, $db, $language, $filter, $seo;
        if ($this->position != 'admin') return false;

        ($plugin_code = $xtPlugin->PluginCode('class.category.php:_setImage_top')) ? eval($plugin_code) : false;

        $obj = new stdClass;

        $data[$this->_master_key] = $id;
        $data['categories_image'] = $file;

        $o = new adminDB_DataSave($this->_table, $data);
        $obj = $o->saveDataSet();

        $obj->totalCount = 1;
        if ($obj->success) {
            $obj->success = true;
        } else {
            $obj->failed = true;
        }

        ($plugin_code = $xtPlugin->PluginCode('class.category.php:_setImage_bottom')) ? eval($plugin_code) : false;
        return $obj;
    }

    /*
    media images
    */
    function get_media_images($id)
    {
        global $mediaImages, $xtPlugin;

        if (USER_POSITION == 'admin') return false;

        if ($data['tmp_images'] = $mediaImages->_getMediaFiles($id, __CLASS__, 'images', 'free')) {

            foreach ($data['tmp_images'] as $key => $val) {
                $data['images'][$key]['file'] = __CLASS__ . ':' . $val['file'];
                $data['images'][$key]['data'] = $val;
            }

            ($plugin_code = $xtPlugin->PluginCode(__CLASS__ . ':get_media_images')) ? eval($plugin_code) : false;

            return $data;
        } else {
            return false;
        }
    }
}