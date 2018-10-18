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

class MediaData extends MediaFileTypes
{

    var $path;
    var $FileTypes;

    protected $_table_media = TABLE_MEDIA;
    protected $_table_media_desc = TABLE_MEDIA_DESCRIPTION;
    protected $_media_master_key = 'id';
    protected $_table_gallery = TABLE_MEDIA_GALLERY;
    protected $_table_gallery_desc = TABLE_MEDIA_GALLERY_DESCRIPTION;
    protected $_gallery_master_key = 'mg_id';
    protected $_table_media_to_gallery = TABLE_MEDIA_TO_MEDIA_GALLERY;
    protected $_table_media_link = TABLE_MEDIA_LINK;

    function __construct ()
    {
        $this->class = 'default';
    }

    /*---------------------------------------------------------------------------------------------*/
    // SETTINGS	START
    /*---------------------------------------------------------------------------------------------*/

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function setClass ($value)
    {
        $this->class = $value;
    }

    function getClass ()
    {
        return $this->class;
    }

    function setFileTypes ($value)
    {
        $this->FileTypes = $value;
    }

    function getFileTypes ()
    {
        return $this->FileTypes;
    }

    function setPath ($value)
    {
        $this->path = $value;
    }

    function getPath ()
    {
        return $this->path;
    }

    /*---------------------------------------------------------------------------------------------*/
    // SETTINGS	END
    /*---------------------------------------------------------------------------------------------*/

    /*---------------------------------------------------------------------------------------------*/
    // HELPER START
    /*---------------------------------------------------------------------------------------------*/

    function _getFileTypesByExtension ($filename)
    {
        global $db;

        $extension = $this->_getExtension($filename);

        $record = $db->Execute(
            "SELECT file_type FROM " . $this->_table . " where file_ext = ? ",
            array($extension)
        );
        if ($record->RecordCount() > 0) {
            return $record->fields['file_type'];
        } else {
            return 'files';
        }
    }

    function _getExtension ($filename)
    {
        $extension = strtolower(strrchr($filename, "."));
        return substr($extension, 1);
    }

    function renameTypeField ($records)
    {
        return $records;
    }

    function setAutoReadFolderData ()
    {
        return false;
    }

    function getClassFile ($code, $link_data = '')
    {
        if (is_file(_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.Media' . ucfirst($code) . '.php')) {
            require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.Media' . ucfirst($code) . '.php';
            return true;
        } else {
            echo 'class not exsits: ' . _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.Media' . ucfirst($code) . '.php';
            return false;
        }
    }

    function Upload ($filename)
    {
        $type = $this->_getFileTypesByExtension($filename);

        if ($this->getClassFile($type)) {
            $code = 'Media' . ucfirst($type) . '';
            $m = new $code;

            $m->class = $this->class;
            $obj = $m->Upload($filename);
            return $obj;
        }
    }

    function _setMediaGallery ($data, $m_id)
    {
        global $db;

        if ($data['mgID'] == '') {
            $gal_query = "SELECT mg_id FROM " . $this->_table_gallery . " WHERE class = ? and parent_id='0'";
            $rs = $db->Execute($gal_query, array($data['class']));
            if ($rs->RecordCount() != 0) {
                $gal_data['mg_id'] = $rs->fields['mg_id'];
            }
        } else {
            $gal_data['mg_id'] = $data['mgID'];
        }

        $gal_data['m_id'] = $m_id;

        $oG = new adminDB_DataSave($this->_table_media_to_gallery, $gal_data);
        $objG = $oG->saveDataSet();

    }

    function setMediaLink ($data)
    {
        if (!$data['class'] && $this->class)
            $data['class'] = $this->class;
        $oMD = new adminDB_DataSave($this->_table_media_link, $data);
        $objMD = $oMD->saveDataSet();

    }

    function unsetMediaLink ($link_id, $m_id, $type)
    {
        global $db, $filter;
        $db->Execute(
            "DELETE FROM " . $this->_table_media_link . " WHERE link_id = ? and m_id = ? and type = ?",
            array((int)$link_id, $m_id, $type)
        );
    }

    function unsetAllMediaLink ($link_id, $class, $type)
    {
        global $db, $filter;
        $db->Execute(
            "DELETE FROM " . $this->_table_media_link . " WHERE link_id = ? and class= ? and type = ?",
            array((int)$link_id, $class, $type)
        );
    }

    function _getMediaID ($file, $class = '')
    {
        global $db, $filter;
        if ($class) {
            require_once (_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaGallery.php');
            $gal = new MediaGallery();
            $gallery = $gal->_getGalleryIDs($class);
            if (is_array($gallery) && !empty($gallery)) $where = " and tm2g.mg_id IN (" . implode(',', $gallery) . ")";
        }
        $qry = "SELECT tm.id FROM " . $this->_table_media . " tm, " . $this->_table_media_to_gallery . " tm2g where tm.id = tm2g.m_id and tm.file = ? " . $where;
        $record = $db->Execute($qry, array($file));
        if ($record->RecordCount() > 0) {
            return $record->fields['id'];
        }
    }

    function _getMediaFileName ($mId, $class = '')
    {
        global $db;

        $where = '';
        if ($class) {
            $where = " and class=" . $db->Quote($class) . "";
        }

        $qry = "SELECT file FROM " . $this->_table_media . " where id = ? " . $where;
        $record = $db->Execute($qry, $mId);
        if ($record->RecordCount() > 0) {
            return $record->fields['file'];
        }
    }

    function _getMediaLinkID ($mId, $class, $link_id)
    {
        global $db;

        $qry = "SELECT ml_id FROM " . $this->_table_media_link . " where m_id = ? and class=? and link_id =? ";
        $record = $db->Execute($qry, array($mId, $class, $link_id));
        if ($record->RecordCount() > 0) {
            return $record->fields['ml_id'];
        }
    }

    function _getIcon ($filename)
    {
        $icon = strtolower(strrchr($filename, "."));
        $icon = substr($icon, 1);
        $icon = 'icon_' . $icon . '.gif';

        return $icon;
    }

    function _getMediaFiles ($id, $class, $type = 'images', $download_status = 'free')
    {
        global $db, $language;

        $qry = "SELECT * FROM " . $this->_table_media . " m  left join " . $this->_table_media_link . " ml on m.id = ml.m_id where link_id = ? and ml.class=? and ml.type = ? and m.download_status = ? order by ml.sort_order";

        $record = $db->Execute($qry, array((int)$id, $class, $type, $download_status));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {

                //get name & description
                $query = "SELECT media_name,media_description,language_code FROM " . $this->_table_media_desc . " WHERE language_code=? AND id=? LIMIT 1";
                $res = $db->Execute($query, array($language->code, (int)$record->fields['id']));
                if ($res->RecordCount() > 0) {
                    $record->fields = array_merge($record->fields, $res->fields);
                }

                $files[] = $record->fields;

                $record->MoveNext();
            }
            $record->Close();

            return $files;
        }
    }

    function getCurrentData ()
    {
        global $db, $filter;
        $default = array(
            'id' => 0,
            'sort_order' => 1,
            'allowDrag' => true,
            'allowChildren' => false,
            'disabled' => false,
            'leaf' => true
        );

        if (!$this->url_data['currentType'] && !$this->url_data['currentId']) return false;

        $className = $this->url_data['currentType'];

        if (preg_match('/subcat_/', $this->url_data['currentId']))
            $this->url_data['currentId'] = str_replace('subcat_', '', $this->url_data['currentId']);

        $param = (int)$this->url_data['currentId'];

        $class = new $className($param);
        if ($class->_image_key && $class->_master_key & $class->_table) {
            $qry = "SELECT " . $class->_image_key . " as text FROM " . $class->_table . " WHERE " . $class->_master_key . " = ?";

            $record = $db->Execute($qry, array($param));
            if ($record->RecordCount() > 0) {
                $default['icon'] = $this->urlPath . $this->path . 'thumb/' . $record->fields['text'];
                $default['image'] = $record->fields['text'];
                $default['id'] = $this->_getMediaID($record->fields['text'], $this->url_data['currentType']);
                $data[] = array_merge($default, $record->fields);
            }
        }

        $qry = "SELECT m.id, m.file text, ml.sort_order FROM " . $this->_table_media . " m  left join " . $this->_table_media_link . " ml on m.id = ml.m_id where link_id = ? and m.class=? and ml.type = ? order by sort_order";

        $record = $db->Execute($qry, array((int)$this->url_data['currentId'], $this->url_data['currentType'], $this->type));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $default['icon'] = $this->urlPath . $this->path . 'thumb/' . $record->fields['text'];
                $default['image'] = $record->fields['text'];
                $data[] = array_merge($default, $record->fields);
                $record->MoveNext();
            }
            $record->Close();
        }

        return $data;
    }

    function _setSortOrder ($data)
    {
        global $db;

        $where = ' link_id = ? and class = ? and m_id = ?';
		$where2 = ' link_id = '.$this->url_data['link_id'].' and class = "'.$this->url_data['currentType'].'" and m_id = '.$data['m_id'];
        $qryCheck = "SELECT * FROM " . $this->_table_media_link . " m WHERE " . $where;
        $record = $db->Execute($qryCheck, array($this->url_data['link_id'], $this->url_data['currentType'], $data['m_id']));
        if ($record->RecordCount() == 0) {
            $default = array(
                'link_id' => $this->url_data['link_id'],
                'class' => $this->url_data['currentType'],
                'type' => $this->type);
            $data = array_merge($default, $data);
            $db->AutoExecute($this->_table_media_link, $data, 'INSERT');
        } else {
            $newData['sort_order'] = $data['sort_order'];
            $db->AutoExecute($this->_table_media_link, $newData, 'UPDATE', $where2);
        }

        $obj = new stdClass();
        $obj->success = true;
        return $obj;
    }

    function getIcon ($filename)
    {
        $icon = strtolower(strrchr($filename, "."));
        $icon = substr($icon, 1);
        $icon = 'icon_' . $icon . '.gif';

        return $icon;
    }

    function setMediaData ($data)
    {
        global $db;

        if (!$data['class'] && $this->class) {
            $data['class'] = $this->class;
        }

        // if download_status is empty then problems with product images will occur
        if (!$data['download_status']) {
            $data['download_status'] = 'free';
        }

        $qry = "SELECT * FROM " . $this->_table_media . " WHERE file = ? and type = ?";

        $record = $db->Execute($qry, array($data['file'], $data['type']));
        if ($record->RecordCount() == 0) {

            $oMD = new adminDB_DataSave($this->_table_media, $data);
            $objMD = $oMD->saveDataSet();

            $m_id = $objMD->new_id;

            if ($data['language_code']) {
                $oMDD = new adminDB_DataSave($this->_table_media_desc, $data, true);
                $objMDD = $oMDD->saveDataSet();
            }

            $this->_setMediaGallery($data, $m_id);

        } else {

            $m_id = $record->fields['id'];
            $record->fields['class'] = $data['class'];
            $this->_setMediaGallery($record->fields, $m_id);
        }

        return $m_id;
    }

	public function unsetMediaData($id)
	{
		global $db;

		$id = (int)$id;

		$db->Execute('DELETE FROM '.$this->_table_media.' WHERE id = ?', array($id));
		$db->Execute('DELETE FROM '.$this->_table_media_desc.' WHERE id = ?', array($id));
		$db->Execute('DELETE FROM '.$this->_table_media_to_gallery.' WHERE m_id = ?', array($id));
		$db->Execute('DELETE FROM '.$this->_table_media_link.' WHERE m_id = ?', array($id));
		$db->Execute("DELETE FROM ".TABLE_CONTENT_PERMISSION." WHERE pid = ? AND type = 'media_file'", array($id));
	}
}