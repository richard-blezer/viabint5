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

class MediaImageList extends MediaImages
{

    protected $_table = TABLE_MEDIA;
    protected $_table_lang = TABLE_MEDIA_DESCRIPTION;
    protected $_table_seo = null;
    protected $_master_key = 'id';

    function __construct ()
    {
        $this->getPermission();
        $this->path = _SRV_WEB_IMAGES;
        $this->urlPath = _SYSTEM_BASE_HTTP . _SRV_WEB_UPLOAD;
        $this->type = 'images';
    }

    function getPermission ()
    {
        global $store_handler, $customers_status, $xtPlugin;

        $this->perm_array = array(
            'group_perm' => array(
                'type' => 'group_permission',
                'key' => $this->_master_key,
                'value_type' => 'media_file',
                'pref' => 'mi'
            )
        );

        ($plugin_code = $xtPlugin->PluginCode(__CLASS__ . ':getPermission')) ? eval($plugin_code) : false;

        $this->permission = new item_permission($this->perm_array);

        return $this->perm_array;
    }

    function setPosition ($position)
    {
        $this->position = $position;
    }

    function _getParams ()
    {
        global $language;

        $params = array();

        foreach ($language->_getLanguageList() as $key => $val) {
            $header['media_description_' . $val['code']] = array('type' => 'htmleditor');
        }

        $header['download_status'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=download_status'
        );

        $header['status'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=status_truefalse'
        );

        $header['id'] = array('type' => 'hidden');
        $header['file'] = array('type' => 'image');

        $params['default_sort'] = 'sort_order';
        $params['header'] = $header;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;
        $params['PageSize'] = 10;


        $extF = new ExtFunctions();

        if ($this->url_data['pg'] == 'overview' && !$this->url_data['link_id'] && !($this->url_data['new'] || $this->url_data['edit'])) {
            $params['include'] = array('id', 'sort_order', 'file', 'media_name_' . $language->code, 'status');
        } else {
            if (!($this->url_data['new'] && $this->url_data['edit'])) {
                $rowActionsFunctions['sort_up'] = $extF->_MultiButton_stm(TEXT_SORT_UP, 'sort_up');
                $rowActionsFunctions['sort_down'] = $extF->_MultiButton_stm(TEXT_SORT_DOWN, 'sort_down');
                $rowActionsFunctions['image_processing'] = $extF->_MultiButton_stm(TEXT_IMAGE_PROCESSING, 'image_processing');
                $rowActions[] = array('iconCls' => 'sort_up', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_SORT_UP);
                $rowActions[] = array('iconCls' => 'sort_down', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_SORT_DOWN);
                $rowActions[] = array('iconCls' => 'image_processing', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_IMAGE_PROCESSING);
                $params['rowActions'] = $rowActions;
                $params['rowActionsFunctions'] = $rowActionsFunctions;
            }
            $params['exclude'] = array('owner', 'download_status', 'date_added', 'last_modified', 'type', 'class', 'type', 'max_dl_count', 'max_dl_days');
        }

        $params['display_searchPanel'] = false;
        $params['display_checkCol'] = true;
        $params['display_statusTrueBtn'] = false;
        $params['display_GetSelectedBtn'] = false;
        $params['display_statusFalseBtn'] = false;
        $params['display_editBtn'] = true;
        $params['display_newBtn'] = false;

        return $params;
    }

    function _getSearchIds ($search_data)
    {
        global $xtPlugin, $db, $language;

        $searchQry = '';

        $searchArray = array($this->_table . '.file', $this->_table_lang . '.media_name', $this->_table_lang . '.media_description');
        $searchQry = ' ';
        foreach ($searchArray as $key) {
            $searchQry .= "  " . $key . " like '%" . $search_data . "%'  or ";
        }
        $searchQry = substr($searchQry, 0, -4);
        $searchQry .= " ";
        if ($this->url_data['currentType'])
            $searchQry .= "and class = '" . $this->url_data['currentType'] . "' ";
        $ids = array();
        $record = $db->Execute("SELECT distinct " . $this->_table . "." . $this->_master_key . "
        						FROM " . $this->_table . "
        						LEFT JOIN " . $this->_table_lang . "
        						ON " . $this->_table . "." . $this->_master_key . " = " . $this->_table_lang . "." . $this->_master_key . "
        						WHERE  " . $searchQry);

        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $ids[] = $record->fields[$this->_master_key];
                $record->MoveNext();
            }
            $record->Close();
        }
        return $ids;
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
        $ID = (int)$ID;

        $qry = " type = " . $db->Quote($this->type) . " ";

        $searchQry = '';
        $ids = array();
        if ($this->url_data['query']) {
            $ids = $this->_getSearchIDs($this->url_data['query']);
        }

        $sortedIds = array();
        if ($this->url_data['link_id']) {
            $oCurrent = $this->getCurrentIds();
            $currentIds = $oCurrent->currentIds;
            $sortedIds = $oCurrent->sortedIds;
            if (count($ids) == 0 && count($currentIds) > 0) {
                $ids = $currentIds;
            } elseif (count($ids) > 0 && count($currentIds) > 0) {
                $ids = array_diff($ids, $currentIds);
            }
        }

        if (count($ids) > 0) {
            $ids = array_unique($ids);
            $searchQry = " and " . $this->_table . ".id IN (" . implode(',', $ids) . ") ";
        }
        // set limit if not set
        if (!$ID) {
            $this->sql_limit = "0,50";
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $qry . $searchQry, $this->sql_limit, $this->perm_array);

        if (count($ids) > 0) {
            if ($this->url_data['get_data']) {
                $data = $table_data->getData();
                if (is_array($data)) {
                    foreach ($data as $count => $d) {
                        if ($sortedIds[$d['id']] > 0) {
                            $data[$count]['sort_order'] = $sortedIds[$d['id']];
                        } else {
                            $data[$count]['sort_order'] = 0;
                        }

                        if (!$data[$count]['media_name_' . $language->code])
                            $data[$count]['media_name_' . $language->code] = $data[$count]['file'];

                    }
                }

                $data = $this->matrixSort($data, 'sort_order');

                $data = $data;
            } elseif ($ID) {
                $data = $table_data->getData($ID);
            } else {
                $data = $table_data->getHeader();
                //add grid header sort_order
                $data[0]['sort_order'] = '';
            }
        } else {
            $data = $table_data->getHeader();
            //add grid header sort_order
            $data[0]['sort_order'] = '';
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
        global $db, $language, $filter, $seo, $db;


        $obj = new stdClass;

        if ($this->url_data['get_select_flag']) {
            $linkData = array(
                'm_id' => $data,
                'link_id' => $this->url_data['link_id'],
                'class' => $this->url_data['currentType'],
                'type' => $this->type
            );

            $this->setMediaLink($linkData);
            return $obj;
        }

        if(!isset($data['download_status']))
            $data['download_status'] = 'free';

        $data['type'] = $this->type;
        if ($this->url_data['m_ids']) {
            $_ids = preg_split('/,/', $this->url_data['m_ids']);
            foreach ($_ids as $id) {
                if ($id) {
                    $data = array('m_id' => $id,
                        'sort_order' => 0);
                }
            }
            return $this->_setSortOrder($data);
        }
        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        if ($set_type == 'new') { // edit existing
            $obj->new_id = $objC->new_id;
            $data['date_added'] = $db->DBTimeStamp(time());
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
        }

        $oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
        $objCD = $oCD->saveDataSet();


        $set_perm = new item_permission($this->perm_array);

        if ($objC->success && $objCD->success) {
            $obj->success = true;
        } else {
            $obj->failed = true;
        }

        return $obj;
    }

    function _unset ($id = 0)
    {
        global $db;
        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id = (int)$id;
        if (!is_int($id)) return false;

        $fileName = $this->_getMediaFileName($id);

        $checkMainFile = $this->isMainFile($this->url_data['link_id'], $this->url_data['currentType'], $fileName);

        if ($checkMainFile)
            $this->unsetMainFile($id, $this->url_data['link_id'], $this->url_data['currentType']);

        $this->unsetMediaLink($this->url_data['link_id'], $id, $this->type);
    }


    function sort ()
    {

        $mIds = $this->getCurrentIds();
        $currentElement = $this->url_data['m_ids'];

        $sortPos = array_search($currentElement, $mIds->currentIds);

        $count = count($mIds->currentIds);

        if ($this->url_data['pos'] == 'up' && $sortPos > -1) {
            if ($sortPos == 1) {
                // main element (main image)
                $main = $currentElement;
                $swap = $mIds->currentIds[0];
            } else {
                // media element (more images)
                $newPos = $sortPos - 1;
            }
        }

        if ($this->url_data['pos'] == 'down' && $sortPos < $count) {
            if ($sortPos == 0) {
                // main element (main image)
                $main = $mIds->currentIds[1];
                $swap = $currentElement;
            } else {
                // media element (more images)
                $newPos = $sortPos + 1;
            }
        }

        if ($newPos) {
            $swapElement = $mIds->currentIds[$newPos];
            $obj = new stdClass();

            if (($currentElement && $newPos) && ($swapElement && $sortPos)) {
                $currentData = array('m_id' => $currentElement, 'sort_order' => $newPos);
                $swapData = array('m_id' => $swapElement, 'sort_order' => $sortPos);
                $this->_setSortOrder($currentData);
                $this->_setSortOrder($swapData);

                $obj->success = true;
            }
        }

        if ($main && $swap) {
            $this->setMainFile($main, $this->url_data['link_id'], $this->url_data['currentType']);
            $this->updateMediaLink_mId($main, $swap, $this->url_data['link_id'], $this->url_data['currentType']);
        }

        if (!$obj->success)
            $obj->failed = true;
    }

    function image_processing ()
    {
        if ($filename = $this->_getMediaFileName($this->url_data['m_ids'], $this->url_data['currentType'])) {
            $this->processImage($filename, true);
        }
    }
	
	function image_importing ()
    {
        if ($filename = $this->_getMediaFileName($this->url_data['m_ids'], $this->url_data['currentType'])) {
            $this->processImage($filename, true);
        }
    }
	
    function matrixSort (&$matrix, $sortKey, $sort = 'ASC')
    {
        if (count($matrix) == 0) return false;

        foreach ($matrix as $key => $subMatrix) {
            $tmpArray[$key] = $subMatrix[$sortKey];
        }

        if ($sort != 'ASC') {
            arsort($tmpArray);
        } else {
            asort($tmpArray);
        }

        while (list($key, $value) = each($tmpArray)) {
            $ArrayNew[] = $matrix[$key];
        }

        return $ArrayNew;
    }
}