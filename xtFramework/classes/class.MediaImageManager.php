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

require_once (_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaGallery.php');
require_once(_SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.recursive.php');

class MediaImageManager extends MediaImages
{
    function __construct ()
    {
        parent::__construct();
        $this->indexID = time() . '-MediaManager';
        $this->trashCat = 4;
        $this->getImageUrl = 'adminHandler.php?load_section=MediaImages&get_data=true';
        $this->getTreeUrl = 'adminHandler.php?load_section=MediaGallery&pg=getAlbums&';
        $this->getSaveUrl = 'adminHandler.php?load_section=MediaGallery&pg=setData&';
        $this->getLinkUrl = 'adminHandler.php?load_section=MediaImages&pg=getCurrentNodeData&';
        $this->getLinkSaveUrl = 'adminHandler.php?load_section=MediaImages&pg=setCurrentNodeData&';

        if (preg_match('/subcat_/', $this->url_data['currentId']))
            $this->url_data['currentId'] = str_replace('subcat_', '', $this->url_data['currentId']);

    }

    function display ()
    {
        // image template
        $Tpl = $this->_getImageTemplate();

        // window
        $window = $this->_getPreviewWindow();
        $accordionPanel = new PhpExt_Panel();
        $accordionPanel->setTitle("Accordion Sample")
            ->setWidth(200)
            ->setHeight(300);

        $panel = new PhpExt_Panel();
        $panel->setWidth(200);

        $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText(__define('TEXT_ALBUMS'))
            ->setExpanded(true)
            ->setId('root')
            ->setAllowDrag(false)
            ->setAllowDrop(false);

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->getTreeUrl);

        $newTreeNode = new PhpExt_Tree_TreeNode();
        $newTreeNode->setText("new Album '+ (++newIndex) +'")
            ->setCssClass("album-node")
            ->setId("newcat_'+ (newIndex) +'")
            ->setAllowDrag(true)
            ->setAllowDrop(true);


        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('TEXT_ALBUMS'))
            ->setRoot($root)
            ->setLoader($tl)
        //->setRootVisible(true)
            ->setAutoScroll(true)
            ->setCollapsible(false)
            ->setWidth(200)
            ->setEnableDD(true)
            ->setDdGroup("organizerDD");

        $tb = $tp->getTopToolbar();

        $teditor = $this->_getTreeEditor($tp);
        $pluginDS = $this->_getDragSelector();

        $panel->addItem($tp);

        if ($this->url_data['currentType']) {

            if (preg_match('/subcat_/', $this->url_data['currentId']))
                $this->url_data['currentId'] = str_replace('subcat_', '', $this->url_data['currentId']);

            $className = $this->url_data['currentType'];
            $param = (int)$this->url_data['currentId'];
            $class = new $className($param);

            if ($class->_display_key) {
                $name = $class->data[$class->_display_key];
            }
            if ($class->data['text']) {
                $name = $class->data['text'];
            }
            if (!$name) {
                $name = __define('TEXT_' . $this->url_data['currentType']);
            }

            $root_current = new PhpExt_Tree_AsyncTreeNode();
            $root_current->setText($name)
                ->setExpanded(true)
                ->setId('root')
                ->setAllowDrag(false)
                ->setAllowDrop(true);
            $tl_current = new PhpExt_Tree_TreeLoader();
            $tl_current->setDataUrl($this->getLinkUrl . 'currentType=' . $this->url_data['currentType'] . '&currentId=' . $this->url_data['currentId']);


            $tp_current = new PhpExt_Tree_TreePanel();
            $tp_current->setTitle(__define('TEXT_' . $this->url_data['currentType']))
                ->setRoot($root_current)
                ->setLoader($tl_current)
                ->setAutoScroll(true)
                ->setCollapsible(false)
                ->setWidth(200)
                ->setEnableDD(true)
                ->setDdGroup("organizerDD");

            $panel->addItem($tp_current);
        }

        $reader = new PhpExt_Data_JsonReader();
        $reader->setRoot("data")
            ->setTotalProperty("totalCount")
            ->setId("id");
        $reader->addField(new PhpExt_Data_FieldConfigObject("id"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("name"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("shortname"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("size", null, "float"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("lastmod", null, "date", "n/j h:ia"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("url"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("url_full"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("class"));
        $reader->addField(new PhpExt_Data_FieldConfigObject("download_status"));

        $store = new PhpExt_Data_Store();
        $store->setProxy(new PhpExt_Data_HttpProxy($this->getImageUrl))
            ->setReader($reader)->setAutoLoad(true)
            ->setBaseParams(array("limit" => $this->_getParam('PageSize')));

        $v = new PhpExt_DataView('div.thumb-wrap');
        $v->setStore($store)
            ->setMultiSelect(true)
            ->setTemplate($Tpl);

        $paging = new PhpExt_Toolbar_PagingToolbar();
        $paging->setStore($store)
            ->setPageSize($this->_getParam('PageSize'))
            ->setDisplayInfo("Topics {0} - {1} of {2}")
            ->setEmptyMessage("No topics to display");

        $searchField = new PhpExtUx_App_SearchField();
        $searchField->setStore($store)
            ->setWidth(120);

        $toolbar = new PhpExt_Toolbar_Toolbar ();

        $toolbar->addTextItem(1, __define('TEXT_SEARCH'));
        $toolbar->addSpacer(2);
        $toolbar->addItem(3, $searchField);
        $toolbar->addSpacer(4);

        $toolbar->addButton(5, __define('TEXT_NEW_ALBUM'), 'images/icons/add.png', new PhpExt_Handler(PhpExt_Javascript::stm($this->_addButtonStm($newTreeNode))));
        $toolbar->addButton(6, __define('TEXT_IMPORT_IMAGES'), 'images/icons/camera_add.png', new PhpExt_Handler(PhpExt_Javascript::stm($this->_ImportButtonStm($newTreeNode))));
        $toolbar->addButton(7, __define('TEXT_DELETE_TRASH'), 'images/icons/delete.png', new PhpExt_Handler(PhpExt_Javascript::stm($this->_delButtonStm())));

        $p = new PhpExt_Panel();
        $p->setTitle(__define('TEXT_IMAGES'))
            ->setAutoScroll(true)
            ->setId("images")
            ->setTopToolbar($toolbar)
            ->setBottomToolbar($paging)
            ->setLayout(new PhpExt_Layout_FitLayout());

        $p->addItem($v);

        $layout = new PhpExt_Panel();
        $layout->setLayout(new PhpExt_Layout_BorderLayout());
        $layout->setAutoWidth(true)
            ->setHeight(500);
        $layout->addItem($panel, PhpExt_Layout_BorderLayoutData::createWestRegion());
        $layout->addItem($p, PhpExt_Layout_BorderLayoutData::createCenterRegion());

        $layout->setRenderTo(PhpExt_Javascript::variable("Ext.get('" . $this->indexID . "')"));

        $imageDragZone = new PhpExtUx_ImageDragZone($v, new PhpExt_DD_DragZoneConfigObject('organizerDD'));
        if (!$this->url_data['currentType']) {
            $js = PhpExt_Ext::OnReady(
                PhpExt_Javascript::stm(PhpExt_QuickTips::init()),
                PhpExt_Javascript::stm("var newIndex = 0; var selected = '';"),
                $store->getJavascript(false, "mediaStore"),
                $root->getJavascript(false, "root"),
                $tp->getJavascript(false, "tree"),
                $panel->getJavascript(false, "panel"),
                $teditor->getJavascript(false, "ge"),
                $v->getJavascript(false, "view"),
                $p->getJavascript(false, "images"),
                $layout->getJavascript(false, "layout"),
                $imageDragZone->getJavascript(false, "dragZone"),
                //    $window->getJavascript(false, "win"),
                PhpExt_Javascript::stm("
            " . $this->_getActionStm_TreeNodeDrop() . "
            " . $this->_getActionStm_TreeClick() . "
            " . $this->_getActionStm_Complete() . "
            ")
            );
        } else {
            $js = PhpExt_Ext::OnReady(
                PhpExt_Javascript::stm(PhpExt_QuickTips::init()),
                PhpExt_Javascript::stm("var newIndex = 0; var selected = '';"),
                $store->getJavascript(false, "mediaStore"),
                $root->getJavascript(false, "root"),
                $root_current->getJavascript(false, "root_current"),
                $tp->getJavascript(false, "tree"),
                $tp_current->getJavascript(false, "tree_current"),
                $panel->getJavascript(false, "panel"),
                $teditor->getJavascript(false, "ge"),
                $v->getJavascript(false, "view"),
                $p->getJavascript(false, "images"),
                $layout->getJavascript(false, "layout"),
                $imageDragZone->getJavascript(false, "dragZone"),
                //    $window->getJavascript(false, "win"),
                PhpExt_Javascript::stm("
            " . $this->_getActionStm_TreeNodeDrop() . "
            " . $this->_getActionStm_TreeNodeDropCurrent() . "
            " . $this->_getActionStm_TreeClick() . "
            " . $this->_getActionStm_Complete() . "
            ")
            );

        }
        $js .= $this->_getLoadingStm();

        return $this->_getScriptIncludes() . '<script type="text/javascript">' . $js . '</script><div id="' . $this->indexID . '"></div>';

    }


    function _getImageTemplate ()
    {
        $Tpl = new PhpExt_XTemplate(
            '<tpl for=".">',
            '<div class="thumb-wrap" id="{id}">',
            '<div class="thumb"><img src="{url}" class="thumb-img"  ext:qtip="Id:{id}<br />{name}<br />Class:{class}<br />DL-Status: {download_status}<br />{desc}"></div>',
            '<span>{shortname}</span></div>',
            '</tpl>');
        return $Tpl;
    }


    function _getPreviewWindow ()
    {
        // window
        $window = new PhpExt_Window();
        $window->setTitle(__define("TEXT_PREVIEW"))
            ->setWidth(550)
            ->setHeight(450)
            ->setMinWidth(200)
            ->setMinHeight(200)
            ->setId('window_preview')
            ->setAutoScroll(true)
            ->setLayout(new PhpExt_Layout_FitLayout())
            ->setPlain(true)
            ->setBodyStyle("padding:6px")
            ->setButtonAlign(PhpExt_Ext::HALIGN_CENTER)
            ->setHtml("<center><div id=\"image_details\"><img src=\"'+ selected.get('url_full') + '\" class=\"thumb-img\"></div></center><br /><ul><li>' + selected.get('name') + '</li></ul>");
        $window->addButton(PhpExt_Button::createTextButton("close", new PhpExt_Handler(PhpExt_Javascript::inlinestm("win.destroy();"))));
        return $window;
    }

    function _getTreeEditor ($tp)
    {
        $teditor = new PhpExt_TreeEditor($tp);
        $teditor->setCancelOnEsc(true)
            ->setCompleteOnEnter(true);

        return $teditor;
    }

    function _getDragSelector ()
    {
        $pluginDS = new PhpExtUx_DragSelector();
        $pluginDS->setDragSafe(true);
        return $pluginDS;
    }

    function _addButtonStm ($newTreeNode)
    {
        $js = "var node = root.appendChild(" . $newTreeNode->getJavascript() . ");
                     tree.getSelectionModel().select(node);
                     setTimeout(function(){
                     ge.editNode = node;
                     ge.startEdit(node.ui.textNode);
                     }, 10);";
        return $js;
    }

    function _delButtonStm ()
    {
        $js = "
    		 	var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'adminHandler.php?load_section=MediaImageManager&pg=_deleteTrash&',
                 method:'GET',
                 params: {},
                 succsses: function(){ root.reload(); },
                 failure: function(){ Ext.Msg.alert('Error','Error'); },
                 callback: function(){ root.reload(); },
                 waitMsg:'Loading..' });

                  ";

        return $js;
    }

    function _importButtonStm ()
    {
        $js = "
    		 	var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'adminHandler.php?load_section=MediaImageManager&pg=_importImages&',
                 method:'GET',
                 params: {},
                 succsses: function(){ root.reload(); },
                 failure: function(){ Ext.Msg.alert('Error','Error'); },
                 callback: function(){ root.reload(); },
                 waitMsg:'Loading..' });

                  ";

        return $js;
    }

    function _getLoadingStm ($show = false)
    {
        if (!$show)
            return ' Ext.get("loadingId").hide(); ';
        return ' Ext.get("loadingId").show(); ';
    }

    function _getActionStm_Complete ()
    {

        $js = "ge.on('complete', function (e, val, startval){
                    " . $this->_getLoadingStm(true) . "
                    var tsm = tree.getSelectionModel();
                    var node = tsm.getSelectedNode();
                    Ext.Ajax.request({
                        url: '" . $this->getSaveUrl . "',
                        params: { id: node.id, name:e.getValue(), oldname: startval},
                        succsses: function(){ root.reload(); },
                        failure: function(){ Ext.Msg.alert('Error','Data Not Saved'); },
                        callback: function(){ root.reload(); },
                        waitMsg:'Loading..' });
                    " . $this->_getLoadingStm() . "
                });
                ";

        return $js;
    }

    function _getActionStm_TreeClick ()
    {
        $js = "tree.on('click', function (e){
                    " . $this->_getLoadingStm(true) . "
                    mediaStore.load({
                        params: {id: e.id, start: 0},
                        waitMsg:'Loading..',
                        callback: function(){ " . $this->_getLoadingStm() . "}
                        });
                    });";

        return $js;
    }

    function _getActionStm_TreeNodeDrop ()
    {
        $js = "	tree.on('nodedrop', function (e){
                     var dropId;
                     var targetId;
                     dropId = e.dropNode.id;
                     targetId = e.target.id;
                     var dropData = '';
                     if (e.dropNode.length > 0){
                     for(var i = 0, len = e.dropNode.length; i < len; i++){
                        dropData += '{id:' + e.dropNode[i].id + ',text:' + e.dropNode[i].text + '},';
                        // dropData += '[' + e.dropNode[i].id + '|' + e.dropNode[i].text + '],';
                     }
                     }

                     " . $this->_getLoadingStm(true) . "

                     Ext.Ajax.request({
                     url: '" . $this->getSaveUrl . "',
                     params: { targetId: targetId, dropId:dropId, name:e.dropNode.text, dropData:dropData},
                     succsses: function(){
                                if (e.dropNode.length > 0)
                                root.reload();
                               },
                     waitMsg:'Loading..',
                     failure: function(){  Ext.Msg.alert('Error','Data Not Saved'); },
                     callback: function(){
                            root.reload();
                               }
                     });

                     " . $this->_getLoadingStm() . "
                });";

        return $js;
    }

    function _getActionStm_TreeNodeDropCurrent ()
    {
        $js = "	tree_current.on('nodedrop', function (e){
                     var dropId;
                     var targetId;
                     dropId = e.dropNode.id;
                     targetId = e.target.id;

                        console.log(' drop: ' + dropId + ' target: ' + targetId + ' ');

                     var dropData = '';
                     if (e.dropNode.length > 0){
                     for(var i = 0, len = e.dropNode.length; i < len; i++){
                        dropData += '{id:' + e.dropNode[i].id + ',text:' + e.dropNode[i].text + '},';
                        // dropData += '[' + e.dropNode[i].id + '|' + e.dropNode[i].text + '],';
                     }
                     }

                     " . $this->_getLoadingStm(true) . "

                     Ext.Ajax.request({
                     url: '" . $this->getLinkSaveUrl . 'currentType=' . $this->url_data['currentType'] . '&currentId=' . $this->url_data['currentId'] . "',
                     params: { targetId: targetId, dropId:dropId, name:e.dropNode.text, dropData:dropData},
                     succsses: function(){
                                if (e.dropNode.length > 0)
                                root_current.reload();
                               },
                     waitMsg:'Loading..',
                     failure: function(){  Ext.Msg.alert('Error','Data Not Saved'); },
                     callback: function(){
                            root_current.reload();
                               }
                     });

                     " . $this->_getLoadingStm() . "
                });";

        return $js;
    }

    function _getScriptIncludes ()
    {
        $includes = '
<link rel="stylesheet" type="text/css" href="../xtFramework/library/ext/ux/css/organizer.css"/>
<div id="loadingId" class="ext-el-mask-msg loading-indicator">
			<div>Loading...</div>
</div>
';

        return $includes;
    }

    function _importImages ()
    {
        $this->setAutoReadFolderData();
    }

    function _deleteTrash ($id = '', $del_gal = 'true')
    {
        global $db;

        $id = $this->trashCat;

        $record = $db->Execute(
            "SELECT mg.m_id, m.file FROM " . TABLE_MEDIA_TO_MEDIA_GALLERY . " mg left join " . TABLE_MEDIA . " m on m.id = mg.m_id where mg.mg_id=?",
            array((int)$id)
        );
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $mIDs[] = $record->fields;
                $record->MoveNext();
            }
            $record->Close();
        }

        $this->_deleteImage($mIDs);

        if ($del_gal == 'true')
            $this->_deleteGallery($this->trashCat);

    }

    function _deleteGallery ($trash_cat)
    {
        global $db;

        $record = $db->Execute(
            "SELECT mg_id FROM " . TABLE_MEDIA_GALLERY . " where parent_id=?",
            array((int)$this->trashCat)
        );
        if ($record->RecordCount() > 0) {
            while (!$record->EOF) {
                $mgIDs[] = $record->fields['mg_id'];

                $d = new recursive(TABLE_MEDIA_GALLERY, 'mg_id');
                $data = $d->_getLevelItems($record->fields['mg_id']);

                if (is_array($data) && count($data) > 0) {
                    foreach ($data as $key => $val) {
                        $mgIDs[] = $val['mg_id'];
                    }
                }

                $record->MoveNext();
            }
            $record->Close();
        }

        if (is_array($mgIDs) && count($mgIDs) > 0) {
            foreach ($mgIDs as $mkey => $mval) {
                $this->_deleteTrash($mval, 'false');

                $db->Execute("DELETE FROM " . TABLE_MEDIA_GALLERY . " WHERE mg_id = ?", array($mval));
                $db->Execute("DELETE FROM " . TABLE_MEDIA_GALLERY_DESCRIPTION . " WHERE mg_id = ?", array($mval));
                $db->Execute("DELETE FROM " . TABLE_MEDIA_TO_MEDIA_GALLERY . " WHERE mg_id = ?", array($mval));
            }
        }
    }

    function _deleteImage ($data)
    {
        global $db;

        $types = $this->getImageTypes();
        $dir = _SRV_WEBROOT . _SRV_WEB_IMAGES;

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $val) {
                $db->Execute("DELETE FROM " . TABLE_MEDIA . " WHERE id = ?", array($val['m_id']));
                $db->Execute("DELETE FROM " . TABLE_MEDIA_DESCRIPTION . " WHERE id = ?", array($val['m_id']));
                $db->Execute("DELETE FROM " . TABLE_MEDIA_LINK . " WHERE m_id = ?", array($val['m_id']));
                $db->Execute("DELETE FROM " . TABLE_MEDIA_TO_MEDIA_GALLERY . " WHERE m_id = ?", array($val['m_id']));

                foreach ($types as $tkey => $tval) {
                    unlink($dir . $tval['folder'] . '/' . $val['file']);
                }
            }
        }

        $obj = new stdClass;
        $obj->success = true;
        return $obj;
    }
}