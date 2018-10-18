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

require_once(_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.recursive.php');

class ProductToCategories
{

    public $_table = TABLE_CATEGORIES;
    public $_table_link = TABLE_PRODUCTS_TO_CATEGORIES;
    public $_table_lang = TABLE_CATEGORIES_DESCRIPTION;
    public $_table_seo = null;
    public $_master_key = 'categories_id';
    public $_master_value_key = 'source_cat';
    public $_display_key = 'categories_name';
    public $_value_id = 'value_ids';
    public $_edit_type_key = 'editType';
    protected $_icons_path = "images/icons/";

    function __construct()
    {
        $this->indexID = time() . '-' . __CLASS__ . '2Cat';

        $add_to_url = (isset($_SESSION['admin_user']['admin_key'])) ? '&sec=' . $_SESSION['admin_user']['admin_key'] : '';
        $this->getTreeUrl = 'adminHandler.php?load_section=' . __CLASS__ . '&pg=getNode' . $add_to_url . '&';
        $this->getSaveUrl = 'adminHandler.php?load_section=' . __CLASS__ . '&pg=setData' . $add_to_url . '&';
    }

    function setPosition($position)
    {
        $this->position = $position;
    }

    function setMasterId($id)
    {
        $this->ID = $id;
    }

    function getMasterId()
    {
        if ($this->ID)
            return $this->ID;
        else
            return 1;
    }

    function setValueId($id)
    {
        $this->vID = $id;
    }

    function getValueId()
    {

        if ($this->vID)
            return $this->vID;
        else
            return 1;
    }

    function setEditType($val)
    {
        $this->editType = $val;
    }

    function getEditType()
    {
        return $this->editType;
    }

    function getTreePanel()
    {
        if ($this->url_data[$this->_master_value_key])
            $this->setMasterId($this->url_data[$this->_master_value_key]);

        if ($this->url_data[$this->_value_id])
            $this->setValueId($this->url_data[$this->_value_id]);

        if ($this->url_data[$this->_edit_type_key])
            $this->setEditType($this->url_data[$this->_edit_type_key]);

        $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText(__define('TEXT_CATEGORIES_SELECTION'))
            ->setId('croot');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->getTreeUrl);
        if ($this->getMasterId())
            $tl->setBaseParams(array($this->_master_value_key => $this->getMasterId()));

        $tp = new PhpExt_Tree_TreePanel();
        //$tp->setTitle(__define('TEXT_CATEGORIES_SELECTION'))
        $tp->setRoot($root)
            ->setLoader($tl)
            ->setAutoScroll(true)
            ->setAutoWidth(true);

        $tb = $tp->getBottomToolbar();

        $tb->addButton(1, __define('TEXT_SAVE'), $this->_icons_path . 'disk.png', new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));

                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: '" . $this->getSaveUrl . $this->_edit_type_key . '=' . $this->getEditType() . '&' . $this->_master_value_key . '=' . $this->getMasterId() . '&' . $this->_value_id . '=' . $this->getValueId() . "',
                 method:'POST',
                 params: {'" . $this->_master_value_key . "': " . $this->getMasterId() . ", catIds: checked},
                 error: function(responseObject) {
                            Ext.Msg.alert('" . __define('TEXT_ALERT') . "', '" . __define('TEXT_NO_SUCCESS') . "');
                          },
                 waitMsg: 'SAVED..',
                 success: function(responseObject) {
                            if (responseObject.responseText>1)
							{
								Ext.Msg.alert('" . __define('TEXT_ALERT') . "','" . __define('TEXT_MORE_THAN_ONE_MAIN_CATEGORY_SELECTED') . "');
							}
							else 
							{
								Ext.Msg.alert('" . __define('TEXT_ALERT') . "','" . __define('TEXT_SUCCESS') . "');
							}
                          }
                 });")));
        $tp->setRenderTo(PhpExt_Javascript::variable("Ext.get('" . $this->indexID . "')"));

        $js = PhpExt_Ext::OnReady(
            PhpExt_Javascript::stm(PhpExt_QuickTips::init()),

            $root->getJavascript(false, "croot"),
            $tp->getJavascript(false, "tree")

        );


        return '<script type="text/javascript">' . $js . '</script><div id="' . $this->indexID . '"></div>';

    }

    function getStoresNode()
    {
        if ($this->url_data['products_id'])
            $this->setProductsId($this->url_data['products_id']);
        //if ($this->url_data['node']=='store_1') {return $this->getNode(); }
        $store = new multistore();
        $stores = $store->getStores();

        if (is_array($stores)) {
            foreach ($stores as $st) {
                $expanded = false;
                if ($_GET['store_id'] == $st['id']) {
                    $expanded = true;
                }
                $this->getTreeUrl = $this->getTreeUrl . '&current_store=' . $st['id'];
                $new_cats[] = array('id' => 'store_' . $st['id'], 'text' => $st['text'], 'expanded' => $expanded);
            }
        }
        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        return json_encode($new_cats);
    }

    function getNode()
    {
        if ($this->url_data['node'] == 'croot') {
            return $this->getStoresNode();
        }

        $add_sql = '';
        $add_sql2 = '';
        $ad_table = '';
        $add_to_display_key = '';
        $add_to_id = '';
        if (StoreIdExists(TABLE_CATEGORIES_DESCRIPTION, 'categories_store_id')) {

            $ar = explode("_", $this->url_data['node']);

            //	if ($_GET['store_id']){
            if (count($ar) == 2) {
                if (strpos($this->url_data['node'], 'store_') !== false) {
                    $store_id = $ar[1];
                } else {
                    $store_id = $ar[0];
                }

                $add_sql = ' and categories_store_id = ' . $store_id;
                $add_sql2 = ' and store_id = ' . $store_id;
                $ad_table = ' INNER JOIN ' . TABLE_CATEGORIES_DESCRIPTION . ' d ON c.' . $this->_master_key . '=d.' . $this->_master_key;
                $ad_table .= " left JOIN " . TABLE_CATEGORIES_PERMISSION . " pm" . $store_id . "
					ON (pm" . $store_id . ".pid = c.categories_id and pm" . $store_id . ".pgroup = 'shop_" . $store_id . "') ";

                if (_SYSTEM_GROUP_PERMISSIONS == 'blacklist') {
                    $add_sql .= " and pm" . $store_id . ".permission IS NULL";
                } elseif (_SYSTEM_GROUP_PERMISSIONS == 'whitelist') {
                    $add_sql .= " and pm" . $store_id . ".permission = 1";
                }
                $add_to_display_key = '_store' . $store_id;
                $add_to_id = $store_id . '_';

            } else {
                $st = new multistore();
                $add_to_display_key = '_store' . $st->shop_id;
                $add_to_id = $st->shop_id . '_';
            }
            $store_field = 'categories_store_id' . $_GET['store_id'];
        }

        if ($this->url_data[$this->_master_value_key])
            $this->setMasterId($this->url_data[$this->_master_value_key]);

        if ($this->url_data[$this->_value_id])
            $this->setValueId($this->url_data[$this->_value_id]);

        $d = new recursive($this->_table . ' c ', $this->_master_key);

        $d->setLangTable($this->_table_lang);
        $d->setDisplayKey($this->_display_key . $add_to_display_key);
        $d->setDisplayLang(true);
        if (StoreIdExists(TABLE_CATEGORIES_DESCRIPTION, 'categories_store_id')) {
            $d->setStoreID('categories_store_id');
        }
        $d->setJoinedTable($ad_table);
        $d->setWhereQuery($add_sql);

        $a = explode("_", $this->url_data['node']);
        if (count($a) == 2) {
            if ($a[0] != 'store')
                $node = $a[1];
            else $node = $this->url_data['node'];
        } else $node = $this->url_data['node'];

        $data = $d->_getLevelItems($node);

        if (is_array($data)) {
            foreach ($data as $cat_data) {
                $checked = false;
                if (is_array($cat_data) && isset($cat_ids) && is_array($cat_ids)) {
                    if (in_array($cat_data[$this->_master_key], $cat_ids)) {
                        $checked = true;
                    }
                }

                $expanded = false;
                $new_cats[] = array('id' => $add_to_id . $cat_data[$this->_master_key], 'text' => $cat_data[$d->getDisplayKey()], 'checked' => $checked, 'expanded' => $expanded);
            }
        }
        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        return json_encode($new_cats);
    }

    function _getParams()
    {
        global $language;

        $params = array();

        return $params;
    }

    function setData($dont_die = FALSE)
    {
        global $db;

        $obj = new stdClass;

        if ($this->url_data['value_ids'] && $this->url_data['value_ids'] != 'undefined') {
            $value_ids = preg_split('/,/', $this->url_data['value_ids']);
        } else {
            $obj->failed = true;
        }


        if ($this->url_data['catIds']) {
            $this->url_data['catIds'] = str_replace(array('[', ']', '"', '\\'), '', $this->url_data['catIds']);
            $cat_ids = preg_split('/,/', $this->url_data['catIds']);
        } else {
            $obj->failed = true;
        }


        if ((count($cat_ids) > 1) && (($this->url_data['editType'] == 'copy') || ($this->url_data['editType'] == 'move'))) {
            echo json_encode(count($cat_ids));
            die;
        }

        foreach ($value_ids as $key => $dummy) {
            if (empty($dummy)) {
                unset($value_ids[$key]);
            }
        }

        if (!$obj->failed) {

            if ($this->url_data['editType'] == 'copy') {
                $obj = $this->_copyToCategory($cat_ids, $value_ids);
                $obj->success = true;
            }

            if ($this->url_data['editType'] == 'link') {
                $obj = $this->_linkToCategory($cat_ids, $value_ids);
                $obj->success = true;
            }

            if ($this->url_data['editType'] == 'move') {
                $obj = $this->_moveToCategory($cat_ids, $value_ids);
                $obj->success = true;
            }

        }

    }

    protected function _linkToCategory($cat_ids, $value_ids)
    {
        global $db;

        foreach ($value_ids as $key => $id) {
            $db->Execute(
                "DELETE FROM " . $this->_table_link . " WHERE master_link!=1 and products_id = ?",
                array((int)$id)
            );

            foreach ($cat_ids as $cat_key => $cat_id) {
                $expl = explode("_", $cat_id);
                $data = array($this->_master_key => (int)$expl[1],
                    'products_id' => $id);
                if (StoreIdExists($this->_table_link, 'store_id')) {
                    $data = array_merge($data, array('store_id' => (int)$expl[0]));
                }
                $o = new adminDB_DataSave($this->_table_link, $data, false, __CLASS__);
                $obj = $o->saveDataSet();
            }
        }
        return $obj;
    }

    protected function _moveToCategory($cat_ids, $value_ids)
    {
        global $db;

        foreach ($value_ids as $key => $id) {
            if (empty($id))
                continue;

            $db->Execute("DELETE FROM " . $this->_table_link . " WHERE master_link=1 and products_id = ?", array((int)$id));
            $expl = explode("_", $cat_ids[0]);
            $data = array($this->_master_key => (int)$expl[1],
                'products_id' => $id,
                'master_link' => (int)1);
            if (StoreIdExists($this->_table_link, 'store_id')) {
                $data = array_merge($data, array('store_id' => (int)$expl[0]));
            }

            $o = new adminDB_DataSave($this->_table_link, $data, false, __CLASS__);
            $obj = $o->saveDataSet();
        }
        return $obj;
    }

    protected function _copyToCategory($cat_ids, $value_ids)
    {
        global $db;

        $p = new product();
        $p->setPosition('admin');
        if (StoreIdExists(TABLE_PRODUCTS_DESCRIPTION, 'products_store_id'))
            $p->store_field_exists = true;
        foreach ($value_ids as $key => $id) {
            if ($id) {
                $obj = $p->_copy($id, false);
                $val_ids[0] = $obj->new_pID;
                $this->_moveToCategory($cat_ids, $val_ids);
                $this->_checkPermissionsForStore($cat_ids, $val_ids);
            }
        }
        return $obj;
    }

    protected function _checkPermissionsForStore($cat_ids, $value_ids)
    {
        global $db;
        $expl = explode("_", $cat_ids[0]);

        $rs = $db->Execute(
            "SELECT * FROM " . TABLE_PRODUCTS_PERMISSION . " WHERE pid=? and pgroup = 'shop_" . $expl[0] . "'",
            array($value_ids[0])
        );
        if ($rs->RecordCount() > 0) {
            if (_SYSTEM_GROUP_PERMISSIONS == 'blacklist') {
                $rs2 = $db->Execute(
                    "DELETE  FROM " . TABLE_PRODUCTS_PERMISSION . " WHERE pid=? and pgroup = 'shop_" . $expl[0] . "'",
                    array($value_ids[0])
                );
            }
        } else {
            if (_SYSTEM_GROUP_PERMISSIONS == 'whitelist') {
                $rs2 = $db->Execute("INSERT IGNORE INTO " . TABLE_PRODUCTS_PERMISSION . " (pid,pgroup) VALUES('" . $value_ids[0] . "', 'shop_" . $expl[0] . "')");
            }
        }
    }
}