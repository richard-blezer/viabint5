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

class MediaList
{

    protected $_table = TABLE_MEDIA;
    protected $_table_lang = TABLE_MEDIA_DESCRIPTION;
    protected $_table_seo = null;
    protected $_master_key = 'id';
    protected $_table_media_link = TABLE_MEDIA_LINK;

    function __construct ()
    {
        $this->getPermission();
        $this->path = _SRV_WEB_IMAGES;
        $this->urlPath = _SYSTEM_BASE_HTTP . _SRV_WEB_UPLOAD;
        $this->type = 'images';
    }


    function setPosition ($position)
    {
        $this->position = $position;
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

    function _getParams ()
    {
        global $language;

        $params = array();

        foreach ($language->_getLanguageList() as $key => $val) {
            $header['media_description_' . $val['code']] = array('type' => 'htmleditor');
        }
		
		if (($this->url_data['mgID']==6) or ($this->url_data['mgID']==7))
		{
			$groupingPosition = 'language';
			foreach ($language->_getLanguageList() as $key => $val) {
	            $header['lang_'.$val['code'].'_tab'] = array('type' => 'status','text'=>$val['name']);
				$grouping['lang_'.$val['code'].'_tab'] = array('position' => $groupingPosition);
				$grouping['group_permission_info'] = array('position' => $groupingPosition);
	        }
	    }
        $header['id'] = array('type' => 'hidden');
        $header['owner'] = array('type' => 'hidden');
        $header['total_downloads'] = array('type' => 'hidden');


        $header['download_status'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=download_status'
        );

        $header['status'] = array(
            'type' => 'dropdown', // you can modyfy the auto type
            'url' => 'DropdownData.php?get=status_truefalse'
        );

        $params['default_sort'] = 'sort_order';
        $params['header'] = $header;
		$params['grouping']         = $grouping;
        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;
        $params['PageSize'] = 50;

        $params['display_searchPanel'] = true;
        $params['display_checkCol'] = true;
        $params['display_statusTrueBtn'] = true;
        $params['display_statusFalseBtn'] = true;
        $params['display_newBtn'] = false;

        if ($this->url_data['mgID']) {
            $js = "var edit_id = " . $this->url_data['mgID'] . ";";

            $extF = new ExtFunctions();

            $mg = new MediaGallery();
            $code = $mg->_getParentClass($this->url_data['mgID']);

            $extF->setCode($code);

            $mediaWindow = $extF->getMediaWindow(false, true, true, $this->url_data['galType'], '&mgID=' . $this->url_data['mgID']);
            $u_js = $mediaWindow->getJavascript(false, "new_window") . "new_window.show();";

            $UserButtons['upload'] = array('text' => 'TEXT_UPLOAD', 'style' => 'upload', 'icon' => 'picture_add.png', 'acl' => 'edit', 'stm' => $u_js);
            $params['display_uploadBtn'] = true;

			$i_js = "var currentType = '" . $this->url_data['galType'] . "';";
			$i_js .= "var mgID = '" . $this->url_data['mgID'] . "';";
            $i_js .= "addTab('row_actions.php?type=image_importing&seckey="._SYSTEM_SECURITY_KEY."&currentType='+currentType+'&mgID='+mgID,'" . TEXT_IMPORT . "')";
			
            $UserButtons['import'] = array('text' => 'TEXT_IMPORT', 'style' => 'import', 'icon' => 'picture_go.png', 'acl' => 'edit', 'stm' => $i_js);
            $params['display_importBtn'] = true;

            $sjs = "var edit_id = " . $this->url_data['mgID'] . ";";
            $sjs .= "addTab('row_actions.php?type=image_processing&seckey="._SYSTEM_SECURITY_KEY."&mgID='+edit_id,'" . TEXT_IMAGE_PROCESSING . "')";


            $UserButtons['process'] = array('text' => 'TEXT_PROCESS', 'style' => 'process', 'icon' => 'arrow_inout.png', 'acl' => 'edit', 'stm' => $sjs);
            $params['display_processBtn'] = true;

        }

        $menuGroups[] = array('group' => 'edit_data', 'group_name' => TEXT_EDIT, 'ToolbarPos' => 'Toolbar', 'Pos' => 'grid'); // Toolbarpos = TopToolbar or Toolbar, Pos = grid / edit / both
        $params['menuGroups'] = $menuGroups;

        $menuActions['edit_data']['multi_move'] = array(
            'text' => 'TEXT_MULTI_MOVE',
            'status' => true,
            'acl' => 'edit',
            'style' => 'multi_move',
            'icon' => 'picture_go.png',
            'stm' => $extF->_MultiButton_stm('BUTTON_MULTI_MOVE', 'doMultiMove'),
            'func' => 'doMultiMove',
            'flag' => 'multiFlag_move',
            'flag_value' => 'true',
            'page' => 'tab',
            'page_url' => 'adminHandler.php?load_section=MediaToGallery&mgID=' . $this->url_data['mgID'] . '&galType=' . $this->url_data['galType'] . '&editType=move&pg=getTreePanel',
            'page_title' => 'TEXT_MULTI_MOVE'
        );

        $menuActions['edit_data']['multi_copy'] = array('text' => 'TEXT_MULTI_COPY',
            'status' => true,
            'acl' => 'edit',
            'style' => 'multi_copy',
            'icon' => 'picture_go.png',
            'stm' => $extF->_MultiButton_stm('BUTTON_MULTI_COPY', 'doMultiCopy'),
            'func' => 'doMultiCopy',
            'flag' => 'multiFlag_copy',
            'flag_value' => 'true',
            'page' => 'tab',
            'page_url' => 'adminHandler.php?load_section=MediaToGallery&mgID=' . $this->url_data['mgID'] . '&galType=' . $this->url_data['galType'] . '&editType=copy&pg=getTreePanel',
            'page_title' => 'TEXT_MULTI_COPY'
        );

        $params['display_multi_moveMn'] = true;
        $params['display_multi_copyMn'] = true;
        $params['menuActions'] = $menuActions;

        $params['UserButtons'] = $UserButtons;

        if ($this->url_data['pg'] == 'overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true) {

            $params['include'] = array('id', 'file', 'media_name_' . $language->code, 'class');

            if (preg_match('/files/', $this->url_data['galType'])) {
                $download_array = array('download_status', 'max_dl_count', 'max_dl_days', 'status');
                $params['include'] = array_merge($params['include'], $download_array);
            } else {
                $params['header']['file'] = array('type' => 'image');
            }

        } else {
            $params['exclude'] = array('file', 'class', 'type', 'date_added', 'last_modified');

            if (!preg_match('/files/', $this->url_data['galType'])) {
                $download_array = array('max_dl_days', 'max_dl_count');
                $params['exclude'] = array_merge($params['exclude'], $download_array);
            }
        }
		
        if (isset($this->url_data['products_id'])) {        
        	$params['display_GetSelectedBtn'] = true;
        	$params['display_multi_moveMn'] = false;
        	$params['display_multi_copyMn'] = false;
        	$params['display_checkCol'] = true;
        	$params['display_statusTrueBtn'] = false;
        	$params['display_statusFalseBtn'] = false;
        	$params['display_newBtn'] = false;
        	$params['display_editBtn'] = false;
        	$params['display_deleteBtn'] = false;
        	$params['display_importBtn'] = false;
        	$params['display_processBtn'] = false;
        }
        
        return $params;
    }

    function _getMGData ($id)
    {
        global $db;

        $record = $db->Execute("SELECT m_id FROM " . TABLE_MEDIA_TO_MEDIA_GALLERY . " WHERE mg_id=?", array($id));
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $ids[] = $record->fields['m_id'];
                $record->MoveNext();
            }
            $record->Close();
        }

        return $ids;
    }

    function _getSearchIds ($search_data, $qry = '')
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

        $ids = array();
        $record = $db->Execute("SELECT distinct " . $this->_table . "." . $this->_master_key . "
        						FROM " . $this->_table . "
        						LEFT JOIN " . $this->_table_lang . "
        						ON " . $this->_table . "." . $this->_master_key . " = " . $this->_table_lang . "." . $this->_master_key . "
        						WHERE  " . $qry . $searchQry);
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

        if (preg_match('/files/', $this->url_data['galType'])) {
            $qry = " type='files'";
        } else {
            $qry = " type='images'";
        }

        $ids = array();
        if ($this->url_data['query']) {
            $ids = $this->_getSearchIDs($this->url_data['query']);
        }

        if ($this->url_data['mgID']) {
            $mg_ids = $this->_getMGData($this->url_data['mgID']);
            if (is_array($mg_ids) && count($ids) == 0) {
                $ids = $mg_ids;
                $is_data = true;
            } else {
                $is_data = false;
            }
        } else {
            $is_data = true;
        }

        if (is_array($ids) && count($ids) > 0) {
            $qry .= " and id IN (" . implode(',', $ids) . ")";
            $is_data = true;
        }

        // set limit if not set
        if (!$ID && !isset($this->sql_limit)) {
            $this->sql_limit = "0,25";
        }

        if (!preg_match('/files/', $this->url_data['galType'])) {
            $this->perm_array = '';
        }

        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $qry . '', $this->sql_limit, $this->perm_array);
		
        if ($is_data) {

            if ($this->url_data['get_data']) {
                $data = $table_data->getData();
                if (is_array($data)) {
                    foreach ($data as $key => $val) {
                        if (!$data[$key]['media_name_' . $language->code])
                            $data[$key]['media_name_' . $language->code] = $data[$key]['file'];
                    }
                }
            } elseif ($ID) {
            	
                $data = $table_data->getData($ID);
				
				if (($this->url_data['mgID']==6) or ($this->url_data['mgID']==7))
				{
					$record = $db->Execute("SELECT * FROM " . TABLE_MEDIA_LANGUAGES . ' WHERE m_id=?', array($ID));
		            if ($record->RecordCount() > 0) {
		            	$langs = array();
		                while(!$record->EOF)
		                {
			    			array_push($langs,$record->fields['language_code']);
			    			$record->MoveNext();
			    		} $record->Close();
		            }
					
					foreach ($language->_getLanguageList() as $key => $val) {
						
			            if (in_array($val['code'],$langs)) $data[0]['lang_'.$val['code'].'_tab'] = 1 ;
						else $data[0]['lang_'.$val['code'].'_tab'] = 0;
			        }
					
					$data[0]['group_permission_info']=_getPermissionInfo();
				}
            } else {
                $data = $table_data->getHeader();
            }

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
    
    function setMediaLinkToProduct ($data)
    {
    	if (!$data['class'])
    		$data['class'] = 'product';
    	
    	$oMD = new adminDB_DataSave($this->_table_media_link, $data);
    	$objMD = $oMD->saveDataSet();
    
    }


    function _set ($data, $set_type = 'edit')
    {
        global $db, $language, $xtPlugin;
		
        $obj = new stdClass;
        if ($this->url_data['get_select_flag'] && $this->url_data['products_id']) {
        $linkData = array('m_id' => $data,
        		'link_id' => $this->url_data['products_id'],
        		'class' => 'product',
        		'type' => 'media');
        
        $this->setMediaLinkToProduct($linkData);
        return $obj;
        }

        if (preg_match('/files/', $this->url_data['galType'])) {
            if ($this->url_data['download_status'] == 'free') {
                $data['class'] = 'files_free';
            } elseif ($this->url_data['download_status'] == 'order') {
                $data['class'] = 'files_order';
            }
            $db->Execute('DELETE FROM ' . TABLE_MEDIA_TO_MEDIA_GALLERY . ' WHERE m_id=?', array($this->url_data['edit_id']));
            $record = $db->Execute("SELECT mg_id FROM " . TABLE_MEDIA_GALLERY . ' WHERE class=?', array($data['class']));
            if ($record->RecordCount() > 0) {
                $mg_id = $record->fields['mg_id'];
            }

            $db->Execute('INSERT INTO ' . TABLE_MEDIA_TO_MEDIA_GALLERY . '( ml_id,m_id,mg_id ) VALUES("",' . (int)$this->url_data['edit_id'] . ',' . (int)$mg_id . ')');
        }

        $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $objC = $oC->saveDataSet();

        if ($set_type == 'new') { // edit existing
            $obj->new_id = $objC->new_id;
            $data = array_merge($data, array($this->_master_key => $objC->new_id));
        }
        if (preg_match('/files/', $this->url_data['galType'])) {
            $data['class'] = $this->url_data['galType'];
        }
        $oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
        $objCD = $oCD->saveDataSet();

        if (preg_match('/files/', $this->url_data['galType'])) {
            $set_perm = new item_permission($this->perm_array);
            $set_perm->_saveData($data, $data[$this->_master_key]);
        }
		$db->Execute("DELETE FROM " . TABLE_MEDIA_LANGUAGES . " WHERE m_id = ?", array($this->url_data['edit_id']));
		foreach ($language->_getLanguageList() as $key => $val) {
            if ($data['lang_'.$val['code'].'_tab'] == 1) {
                $db->Execute("INSERT INTO " . TABLE_MEDIA_LANGUAGES . "( m_id,language_code ) VALUES('" . (int)$this->url_data['edit_id'] . "'," . $db->Quote($val['code']) . ")");
            }
        }
			
        if ($objC->success && $objCD->success) {
            $obj->success = true;
        } else {
            $obj->failed = true;
        }

        return $obj;
    }

    function _unset ($id = 0)
    {
        global $db,$xtPlugin;

        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id = (int)$id;
        if (!is_int($id)) return false;

        ($plugin_code = $xtPlugin->PluginCode('class.MediaList.php:_unset_top')) ? eval($plugin_code) : false;

        $table_data = new adminDB_DataRead($this->_table, NULL, NULL, $this->_master_key);
        $tmp_data = $table_data->getData($id);
        $data = $tmp_data[0];

        if ($data['type'] == 'images') {
            $mi = new MediaImages();
            $mi->remove($data);
        } else {
            $mf = new MediaFiles();
            $mf->remove($data);
        }
        //delete data from media_link
        $db->Execute("DELETE FROM ". TABLE_MEDIA_LINK ." WHERE m_id = ?", array($id));
		$db->Execute("DELETE FROM " . TABLE_MEDIA_LANGUAGES . " WHERE m_id = ?", array($id));

        ($plugin_code = $xtPlugin->PluginCode('class.MediaList.php:_unset_bottom')) ? eval($plugin_code) : false;
    }

    function _importImages ()
    {
        if (preg_match('/files_/', $this->url_data['currentType'])) {
            $mi = new MediaFiles;
            $this->url_data['download_status'] = str_replace('files_', '', $this->url_data['currentType']);
            $mi->setUrlData($this->url_data);
            $return = $mi->setAutoReadFolderData();
        } else {
            $mi = new MediaImages();
            $mi->setUrlData($this->url_data);
            $mi->setAutoReadFolderData();
        }
    }
}