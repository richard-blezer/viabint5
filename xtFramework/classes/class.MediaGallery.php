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

require_once (_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.recursive.php');

class MediaGallery extends recursive
{
    protected $_table_lang = null;
    protected $_lang_field = null;
    protected $_table_media_to_gallery = null;

    function __construct ()
    {
        $this->_master_key = 'mg_id';
        $this->_table_lang = TABLE_MEDIA_GALLERY_DESCRIPTION;
        $this->_default_language_code = 'de';
        $this->_lang_field = 'language_code';
        $this->_table_media_to_gallery = TABLE_MEDIA_TO_MEDIA_GALLERY;

        parent::__construct(TABLE_MEDIA_GALLERY, 'mg_id', 'parent_id');
    }

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function _setAdmin ()
    {
        $this->admin = true;
    }

    function _getParams ()
    {
        global $language;

        $params = array();

        $header['parent_id'] = array('type' => 'hidden');
        $header['u_id'] = array('type' => 'hidden');

        $params['default_sort'] = 'sort_order';
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;

        return $params;
    }

    function _get ($ID = 0)
    {
        global $db, $language, $xtPlugin;

        $ID = str_replace('media_subcat_', '', $ID);

        if ($ID === 'new' && isset($_GET['master_node'])) {
            $master = $_GET['master_node'];
            $master = str_replace('media_subcat_', '', $_GET['master_node']);
            $master_class = $this->_getParentClass($master);
            $obj = $this->_set(array('parent_id' => $master, 'class' => $master_class), 'new');
            $ID = $obj->new_id;
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
        } elseif ($ID) {
            $data = $table_data->getData($ID);
        } else {
            $data = $table_data->getHeader();
        }

        $obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;
    }

    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $xtPlugin;

        $obj = new stdClass;

        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        if ($set_type == 'new') { // edit existing
            $obj->new_id = $objC->new_id;
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
        }

        $oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
        $objCD = $oCD->saveDataSet();

        if ($objC->success && $objCD->success) {
            $obj->success = true;
        } else {
            $obj->failed = true;
        }

        return $obj;
    }

    function _unset ($id = 0)
    {
        global $db, $link_params;

        if ($id == 0) {
            if (is_array($link_params) && isset($link_params['edit_id'])) {
                $id = str_replace('media_subcat_', '', $link_params['edit_id']);
                $id = (int)$id;
            } else {
                return false;
            }
        } else {
            $id = (int)$id;
        }

        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        if ($id == 1) return false;

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?", array($id));
        $db->Execute("DELETE FROM " . $this->_table_lang . " WHERE " . $this->_master_key . " = ?", array($id));

        $db->Execute("update " . $this->_table_media_to_gallery . " set mg_id = 1 where mg_id = ?", array($id));

    }

    function _getParentClass ($id)
    {
        global $db, $xtPlugin;

        $record = $db->Execute("select class from " . $this->_table . " where mg_id = ?", array((int)$id));
        if ($record->RecordCount() > 0) {
            return $record->fields['class'];
        } else {
            return false;
        }
    }

    function _getParentID ($id)
    {
        global $db, $xtPlugin;

        $record = $db->Execute("select parent_id from " . $this->_table . " where mg_id = ?", array((int)$id));
        if ($record->RecordCount() > 0) {
            return $record->fields['parent_id'];
        } else {
            return false;
        }
    }

    function _getGalleryID ($class)
    {
        global $db, $xtPlugin;

        $record = $db->Execute("select mg_id from " . $this->_table . " where class = ?", array($class));
        if ($record->RecordCount() > 0) {
            return $record->fields['mg_id'];
        } else {
            return false;
        }
    }

    function _getGalleryIDs ($class)
    {
        global $db, $xtPlugin;

        $record = $db->Execute("select mg_id from " . $this->_table . " where class = ?", array($class));
        if ($record->RecordCount() > 0) {
            $listOfIds_arr = array();
            while (!$record->EOF) {
                $listOfIds_arr[] = $record->fields['mg_id'];
                $record->MoveNext();
            }
            return $listOfIds_arr;
        } else {
            return false;
        }
    }

    function category_has_subcategories ($id)
    {
        global $db, $xtPlugin;

        $record = $db->Execute("select count(*) as count from " . $this->_table . " where parent_id = ?", array((int)$id));
        if ($record->fields['count'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    function getCategoryListing ($ID = '')
    {
        global $xtPlugin, $db, $language;

        $query = "select m.*, md.* from " . $this->_table . " m LEFT JOIN " . $this->_table_lang . " md ON m.mg_id = md.mg_id where md.language_code = ? and m.parent_id = ? order by m.sort_order, md.name";

        $record = $db->Execute($query, array($language->code, $ID));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $data[] = $record->fields;
                $record->MoveNext();
            }
            $record->Close();
            return $data;
        } else {
            return false;
        }
    }

    function checkDefaultAlbums ($item)
    {
        global $db;
        $record = $db->Execute(
            "SELECT * FROM " . $this->_table . " WHERE class = ? and parent_id = '0' LIMIT 1",
            array($item['class'])
        );
        if ($record->RecordCount() == 0) {
            $oM = new adminDB_DataSave($this->_table, $item, false, __CLASS__);
            $objM = $oM->saveDataSet();
        }
    }

    function getDefaultAlbums ()
    {

        $default[] = array('class' => 'product', 'status' => 1);
        $default[] = array('class' => 'category', 'status' => 1);
        $default[] = array('class' => 'manufacturer', 'status' => 1);
        $default[] = array('class' => 'content', 'status' => 1);

        $count = count($default);
        for ($i = 0; $i < $count; $i++) {
            $this->checkDefaultAlbums($default[$i]);
        }
    }

    function _getLevelNoteItemData ($item)
    {
        global $db;
        $default = array('id' => $item[$this->getMasterKey()],
            'text' => null,
            'allowDrag' => true,
            'allowChildren' => true,
            'disabled' => false,
            'cls' => 'album-node',
            'expandable' => true);

        if ($item[$this->getParentKey()] == 0 && $item['class']) {

            $default['text'] = __define('TEXT_' . $item['class']);
            $default['disabled'] = true;
            $default['allowDrag'] = false;
        } else {
            $record = $db->Execute(
                "SELECT * FROM " . $this->_table_lang . " WHERE " . $this->getMasterKey() . " = ? and " . $this->_lang_field . " = ? LIMIT 1",
                array($item[$this->getMasterKey()], $this->_default_language_code)
            );
            if ($record->RecordCount() > 0)
                $default['text'] = $record->fields['name'];

        }
        unset($item[$this->getMasterKey()]);
        $item = array_merge($default, $item);
        return $item;

    }

    function getAlbums ()
    {

        // todo
        if ($_POST['node'])
            $this->setParentItemId($_POST['node']);
        $tree = $this->_getLevelItems();

        if (count($tree) == 0) {
            $this->getDefaultAlbums();
            $tree = $this->_getLevelItems();
        }

        foreach ($tree as $node) {
            $_tree[] = $node;
        }

        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        echo json_encode($_tree);
        die;
    }

    function setData ($data)
    {
        global $db;

        $obj = new stdClass();
        $obj->success = false;
        // set id to mg_id
        if ($data['id'])
            $data['mg_id'] = $data['id'];

        if ($data['mg_id'] && $data['name'] != '' && !($data['targetId'] && $data['dropId'])) {

            $oM = new adminDB_DataSave($this->_table, $data, false, __CLASS__);

            $objM = $oM->saveDataSet();

            if ($objM->new_id) {
                $new_data[$this->getMasterKey()] = $objM->new_id;
            }
            $new_data['name_' . $this->_default_language_code] = $data['name'];


            $oMI = new adminDB_DataSave($this->_table_lang, $new_data, true, __CLASS__);
            $objMI = $oMI->saveDataSet();

            $obj->success = true;

        }

        if ($data['targetId'] && $data['dropId']) {
            $this->_moveNode($data['dropId'], $data['targetId']);

            $record = $db->Execute(
                "SELECT class FROM " . $this->_table . " WHERE mg_id = ? LIMIT 1 ",
                array($data['targetId'])
            );
            if ($record->RecordCount() > 0) {
                $db->AutoExecute($this->_table, array('class' => $record->fields['class']), 'UPDATE', 'mg_id =' . (int)$data['dropId']);
            }
            $obj->success = true;
        }

        if ($data['dropData'] && $data['targetId']) {
            $drop_data = $this->encodeData($data['dropData']);
            $this->_linkData($drop_data, $data['targetId']);
            $obj->success = true;
        }

        header('Content-Type: application/json; charset=' . _SYSTEM_CHARSET);
        echo json_encode($obj);
        die;
    }


    function _linkData ($data, $targetId)
    {
        global $db;

        foreach ($data as $row) {

            $record = $db->Execute(
                "SELECT * FROM " . $this->_table_media_to_gallery . " WHERE mg_id =? and m_id = ? ",
                array($targetId, $row['id'])
            );
            if ($record->RecordCount() == 0) {
                $db->AutoExecute($this->_table_media_to_gallery, array('mg_id' => $targetId, 'm_id' => $row['id']));
            }
        }

    }

    function encodeData ($data)
    {
        $s1 = preg_split('/\},\{/', $data);

        $counter = 0;
        foreach ($s1 as $row) {
            $row = preg_replace('/[{}]/', '', $row);
            $s2 = preg_split('/,/', $row);
            foreach ($s2 as $p) {
                list($key, $val) = preg_split('/:/', $p);
                if ($key)
                    $new_data[$counter][$key] = $val;
            }

            $counter++;
        }
        return $new_data;
    }
}