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

class xt_master_slave
{

    public $_table = TABLE_PRODUCTS_ATTRIBUTES;
    public $_table_lang = TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION;
    public $_table_seo = null;
    public $_master_key = 'attributes_id';
    public $_image_key = 'attributes_image';
    public $_display_key = 'attributes_name';

    function setPosition($position)
    {
        $this->position = $position;
    }

    function _getParams()
    {
        global $language, $xtPlugin, $db;

        $params = array();

        foreach ($language->_getLanguageList() as $key => $val) {
            $header['attributes_description_' . $val['code']] = array('type' => 'htmleditor');
        }

        $header[$this->_master_key] = array('type' => 'hidden');

        $header['attributes_parent'] = array(
            'type' => 'dropdown',                                // you can modyfy the auto type
            'url' => 'DropdownData.php?get=attrib_parent&plugin_code=xt_master_slave');

        $header['attributes_templates_id'] = array(
            'type' => 'dropdown',                                // master_slave template
            'url' => 'DropdownData.php?get=attribute_templates&plugin_code=xt_master_slave');


        $header['attributes_image'] = array(
            'type' => 'image',
            'path' => 'org'
        );


        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = "status";

        $params['GroupField'] = "attributes_parent";
        $params['SortField'] = "attributes_parent";
        $params['SortDir'] = "ASC";
        $params['PageSize'] = 25;

        $params['display_checkItemsCheckbox'] = true;
        $params['display_checkCol'] = true;
        $params['display_statusTrueBtn'] = true;
        $params['display_statusFalseBtn'] = true;
        $params['display_searchPanel'] = true;

        ($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave.php:_getParams_bottom')) ? eval($plugin_code) : false;

        if (!$this->url_data['edit_id'] && !$this->url_data['new']) {
            $params['include'] = array('attributes_id', 'attributes_parent', 'attributes_model', 'name', 'sort_order', 'tmpl', 'status', 'attributes_image');
        }
        return $params;
    }

    function getSearch2($search_data)
    {
        global $db, $filter;
        $sql_where = array();
        $sql_tablecols = array('attributes_id', 'attributes_parent', 'attributes_model');

        foreach ($sql_tablecols as $tablecol) {
            array_push($sql_where, "(" . $tablecol . " LIKE '%" . $filter->_filter($search_data) . "%')");
        }

        $where = implode(" or ", $sql_where);

        if ($where != '') $where = 'and (' . $where . ')';

        $record = $db->Execute("SELECT  DISTINCT attributes_id FROM " . $this->_table . " WHERE attributes_id>0 " . $where);
        if ($record->RecordCount() > 0) {

            while (!$record->EOF) {
                $records = $record->fields;
                $data[] = $records['attributes_id'];
                $record->MoveNext();
            }
            $record->Close();
        }

        return $data;
    }

    function _get($ID = 0)
    {
        global $xtPlugin, $db, $language;

        if ($this->position != 'admin') return false;

        $obj = new stdClass;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
            $ID = $obj->new_id;
        }


        $sql_where = '';
        if ($this->url_data['get_data'] && $this->url_data['query']) {

            $tmp_search_result = $this->getSearch2($this->url_data['query']);
            if ($tmp_search_result != null)
                $sql_where = "  attributes_id IN (" . implode(',', $tmp_search_result) . ")";
            else $sql_where = "  attributes_id IN (null)";

        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
            if (count($data) > 0) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i]['name'] = $data[$i]['attributes_name_' . $language->code];
                    if ($data[$i]['attributes_templates_id'] > 0) {
                        $r = $db->Execute("Select attributes_templates_name FROM " . TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES . " WHERE attributes_templates_id = ?",array((int)$data[$i]['attributes_templates_id']));
                        if ($r->RecordCount() > 0)
                            $data[$i]['tmpl'] = $r->fields['attributes_templates_name'];
                    } else {
                        if ($data[$i]['attributes_parent'] > 0) $data[$i]['tmpl'] = 'inherit parent';
                        else $data[$i]['tmpl'] = 'default';
                    }

                }
            }

        } elseif ($ID) {

            $data = $table_data->getData($ID);
        } else {
            $data = $table_data->getHeader();
            $data[0]['tmpl'] = '';
        }

        ($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave.php:_get_bottom')) ? eval($plugin_code) : false;

        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);


        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set($data, $set_type = 'edit')
    {
        global $db, $language, $filter, $seo;

        $obj = new stdClass;

        foreach ($data as $key => $val) {

            if ($val == 'on')
                $val = 1;
            if ($val == 'Bild') $val = '';
            $data[$key] = $val;

        }

        unset($data['attributes_image']);

        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        if ($set_type == 'new') {    // edit existing
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

    function _setImage($id, $file)
    {
        global $xtPlugin, $db, $language, $filter, $seo;
        if ($this->position != 'admin') return false;

        ($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave.php:_setImage_top')) ? eval($plugin_code) : false;

        $obj = new stdClass;

        $data[$this->_master_key] = $id;
        $data['attributes_image'] = $file;

        $o = new adminDB_DataSave($this->_table, $data);
        $obj = $o->saveDataSet();

        $obj->totalCount = 1;
        if ($obj->success) {
            $obj->success = true;
        } else {
            $obj->failed = true;
        }

        ($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave.php:_setImage_bottom')) ? eval($plugin_code) : false;
        return $obj;
    }

    function _setStatus($id, $status)
    {
        global $db, $xtPlugin;

        $id = (int)$id;
        if (!is_int($id)) return false;

        $db->Execute("update " . $this->_table . " set status = ? where " . $this->_master_key . " = ?",array($status,(int)$id));

    }

    function _unset($id = 0)
    {
        global $db;
        if ($id == 0) return false;

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?",array($id));
        if ($this->_table_lang !== null)
            $db->Execute("DELETE FROM " . $this->_table_lang . " WHERE " . $this->_master_key . " = ?",array($id));

    }

    function getAllAttributesList($data_array = '', $parent_id = '0', $spacer = '')
    {
        global $xtPlugin, $db, $language;

        if (!is_array($data_array)) $data_array = array();

        $query = "select distinct a.*, ad.* 
                    from " . $this->_table . " a 
                    LEFT JOIN " . $this->_table_lang . " ad ON a." . $this->_master_key . " = ad." . $this->_master_key . " 
                    where ad.language_code = ? and a.attributes_parent =? order by ad.attributes_name ";

        $record = $db->Execute($query,array($language->code,(int)$parent_id));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {

                $tmp_data = array();
                $tmp_data = $record->fields;

                $tmp_data['attributes_name'] = $spacer . $tmp_data['attributes_name'];
                $tmp_data['text'] = $tmp_data['attributes_name'];
                $tmp_data['id'] = $tmp_data[$this->_master_key];

                $data_array[] = $tmp_data;

                if ($tmp_data[$this->_master_key] != $parent_id) {
                    $data_array = $this->getAllAttributesList($data_array, $tmp_data[$this->_master_key], $spacer . '&nbsp;&nbsp;');
                }

                $record->MoveNext();
            }
            $record->Close();
        }

        return $data_array;
    }

    function getAllParentAttributesList()
    {
        global $xtPlugin, $db, $language;

        $query = "select distinct a.*, ad.* from " . $this->_table . " a 
                    LEFT JOIN " . $this->_table_lang . " ad ON a." . $this->_master_key . " = ad." . $this->_master_key . "
                    where ad.language_code = ? and a.attributes_parent = '0' order by ad.attributes_name ";

        $record = $db->Execute($query,array($language->code));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {

                $tmp_data = array();
                $tmp_data = $record->fields;

                $tmp_data['attributes_name'] = $tmp_data['attributes_name'];
                $tmp_data['text'] = $tmp_data['attributes_name'];
                $tmp_data['id'] = $tmp_data[$this->_master_key];

                $data_array[] = $tmp_data;

                $record->MoveNext();
            }
            $record->Close();
        }

        return $data_array;
    }

    function getAttribTree()
    {

        $data = array();

        if (is_array($_POST) && array_key_exists('query', $_POST)) {
            $_data = $this->getAllAttributesList($data_array = '', $parent_id = '0', $spacer = ' ');
            foreach ($_data as $adata) {
                $data[] = array('id' => $adata['attributes_id'],
                    'name' => $adata['attributes_name'],
                    'desc' => $adata['attributes_description']);

            }
        }
        return $data;
    }

    function getAttribParent()
    {

        $data = array();

        if (is_array($_POST) && array_key_exists('query', $_POST)) {
            $data[] = array('id' => '',
                'name' => TEXT_EMPTY_SELECTION,
                'desc' => '');

            $_data = $this->getAllParentAttributesList();
            foreach ($_data as $adata) {
                if ($adata['attributes_name'] != null) {
                    $data[] = array('id' => $adata['attributes_id'],
                        'name' => $adata['attributes_name'] . " (" . $adata['attributes_model'] . ")",
                        'desc' => $adata['attributes_description']);
                }
            }
        }
        return $data;
    }

    function getAttributeTemplate()
    {

        $data = array();

        if (is_array($_POST) && array_key_exists('query', $_POST)) {
            $data[] = array('id' => '',
                'name' => 'default',
            );

            $_data = $this->getAllTemplates();
            foreach ($_data as $adata) {
                if ($adata['attributes_templates_id'] != null) {
                    $data[] = array('id' => $adata['attributes_templates_id'],
                        'name' => $adata['attributes_templates_name']
                    );
                }
            }
        }
        return $data;
    }


    function getAllTemplates()
    {
        global $xtPlugin, $db, $language;

        $query = "select * from " . TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES . " ";

        $record = $db->Execute($query);
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {

                $tmp_data = array();
                $tmp_data = $record->fields;

                $tmp_data['attributes_templates_name'] = $tmp_data['attributes_templates_name'];
                $tmp_data['id'] = $tmp_data['attributes_templates_id'];

                $data_array[] = $tmp_data;

                $record->MoveNext();
            }
            $record->Close();
        }

        return $data_array;
    }

    function getProductsMaster()
    {
        global $xtPlugin, $db;

        $data = array();

        if (is_array($_POST) && array_key_exists('query', $_POST)) {

            $this->sql_products = new getProductSQL_query();
            $this->sql_products->setPosition('getMasterModels');
            //		$this->sql_products->setSQL_COLS(" as id, p.products_model as name, pd.products_name as desc");
            $this->sql_products->setFilter('Language');
            $this->sql_products->setSQL_WHERE("and p.products_model != '' and p.products_master_flag = '1' ");
            $this->sql_products->setSQL_SORT(' p.products_model ASC');

            $query = "" . $this->sql_products->getSQL_query("p.products_id, p.products_model as name, pd.products_name") . "";

            $data[] = array('id' => '',
                'name' => TEXT_EMPTY_SELECTION,
                'desc' => '');


            $record = $db->Execute($query);
            if ($record->RecordCount() > 0) {
                while (!$record->EOF) {
                    $fields = $record->fields;
                    $fields['id'] = $fields['name'];
                    $fields['desc'] = $fields['products_name'];
                    unset($fields['products_id']);
                    $data[] = $fields;
                    $record->MoveNext();
                }
                $record->Close();

                return $data;
            } else {
                return false;
            }

        } else {
            return $data;
        }
    }

}

?>