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

class ImageTypes extends FileHandler
{

    protected $_table = TABLE_IMAGE_TYPE;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'id';

    function __construct ()
    {
        parent::__construct();
    }

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function _getParams ()
    {
        $params = array();

        $header['id'] = array('type' => 'hidden');
        $header['old_folder'] = array('type' => 'hidde');

        $header['watermark'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=conf_truefalse'
        );

        $header['process'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=conf_truefalse'
        );

        $header['class'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=image_classes'
        );

        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = 'class';
        $params['languageTab'] = false;

        return $params;
    }

    function _get ($ID = 0)
    {
        global $xtPlugin, $db, $language;
		$obj = new stdClass;
        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
            $obj = $this->_set(array(), 'new');
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

        if ($table_data->_total_count != 0 || !$table_data->_total_count)
            $count_data = $table_data->_total_count;
        else
            $count_data = count($data);

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $filter;

        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        if ($data['folder']) {

            $this->setMasterDir(_SRV_WEBROOT . _SRV_WEB_IMAGES);

            if (preg_match('%/%', $data['folder'])) {

                $folder_data = explode('/', $data['folder']);
                $parent = '';

                foreach ($folder_data as $key => $val) {

                    $val = $this->cleanDirName($val);
                    $parent .= $val;

                    $this->setParentDir($parent);
                    $parent .= '/';
                    if (!$this->_checkDir()) {
                        $this->_createDir();
                    }
                }
            } else {

                $data['folder'] = $this->cleanDirName($data['folder']);

                $this->setParentDir($data['folder']);
                if (!$this->_checkDir()) {
                    $this->_createDir();
                }
            }
        }

        return $obj;
    }

    function _unset ($id = 0)
    {
        global $db;
        if ($id == 0) return false;
        if ($this->position != 'admin') return false;

        $db->Execute("DELETE FROM " . $this->_table . " WHERE " . $this->_master_key . " = ?", array($id));

    }

    function _getImagePath ($class, $type = 'min', $what = 'width', $min_size = '', $max_size = '')
    {
        global $db;
        $sql_where = '';
        $sql_order = '';

        if ($type == 'min') {

            if ($min_size)
                $sql_where .= ' and ' . $what . ' >= ' . $min_size;

            if ($max_size)
                $sql_where .= ' and ' . $what . ' <= ' . $max_size;

            $sql_order = '  order by ' . $what . ' ASC';

        } else {

            if ($min_size)
                $sql_where .= ' and ' . $what . ' >= ' . $min_size;

            if ($max_size)
                $sql_where .= ' and ' . $what . ' <= ' . $max_size;

            $sql_order .= ' order by ' . $what . ' DESC';
        }

        $qry = "SELECT * from " . $this->_table . " where class = ? " . $sql_where . $sql_order . " LIMIT 1";
        $record = $db->Execute($qry, array($class));
        if ($record->RecordCount() > 0) {
            $data = $record->fields;
            $record->Close();
        } else {
            $qry = "SELECT * from " . $this->_table . " where class = ? " . $sql_order . " LIMIT 1";
            $record = $db->Execute($qry, array($class));
            if ($record->RecordCount() > 0) {
                $data = $record->fields;
                $record->Close();
            }
        }
        return $data;
    }
}