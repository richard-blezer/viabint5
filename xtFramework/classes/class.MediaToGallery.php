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

require_once(_SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.recursive.php');
class MediaToGallery {

	public $_table = TABLE_MEDIA_GALLERY;
	public $_table_link = TABLE_MEDIA_TO_MEDIA_GALLERY;
	public $_table_lang = TABLE_MEDIA_GALLERY_DESCRIPTION;
	public $_table_media = TABLE_MEDIA;
	public $_table_seo = null;
	public $_master_key = 'mg_id';
	public $_master_media_key = 'id';
	public $_master_value_key = 'mgID';
	public $_display_key = 'name';
	public $_value_id = 'value_ids';
	public $_edit_type_key = 'editType';
    protected $_icons_path = "images/icons/";
    
    function __construct() {
       $this->indexID = time().'-'.__CLASS__.'2Cat';

       $this->getTreeUrl = 'adminHandler.php?load_section='.__CLASS__.'&pg=getNode&';
       $this->getSaveUrl = 'adminHandler.php?load_section='.__CLASS__.'&pg=setData&';
    }

	function setPosition ($position) {
		$this->position = $position;
	}

	function setMasterId ($id) {		
	    $this->ID = $id;
	}
	
	function getMasterId () {
        return $this->ID;
	}
	
	function setValueId ($id) {
	    $this->vID = $id;
	}
	
	function getValueId () {
        return $this->vID;
	}
		
	function setSourceGalType ($value) {
	    $this->sourceGalType = $value;
	}
	
	function getSourceGalType () {
        return $this->sourceGalType;
	}
	
	function setEditType ($val) {		
	    $this->editType = $val;
	}
	
	function getEditType () {
        return $this->editType;
	}	
		
	function getTreePanel() {
		if ($this->url_data[$this->_master_value_key])
		$this->setMasterId($this->url_data[$this->_master_value_key]);
		
		if ($this->url_data[$this->_value_id])
		$this->setValueId($this->url_data[$this->_value_id]);		
		
		if ($this->url_data['galType'])
		$this->setSourceGalType($this->url_data['galType']);		
		
		if ($this->url_data[$this->_edit_type_key])
		$this->setEditType($this->url_data[$this->_edit_type_key]);			
		
	    $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText(__define('TEXT_CATEGORIES_SELECTION'))
             ->setId('croot');

        $tl = new PhpExt_Tree_TreeLoader();
        
        if($this->url_data['galType'])
        $gal = 'galType='.$this->getSourceGalType();
        
        $tl->setDataUrl($this->getTreeUrl.$gal);
        
        if ($this->getMasterId())
        $tl->setBaseParams(array($this->_master_value_key => $this->getMasterId()));

        $tp = new PhpExt_Tree_TreePanel();
          $tp->setRoot($root)
          ->setLoader($tl)
          ->setAutoScroll(true)
          ->setAutoWidth(true);

         $tb = $tp->getBottomToolbar();

                $tb->addButton(1,__define('TEXT_SAVE'), $this->_icons_path.'disk.png',new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));
                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: '".$this->getSaveUrl.$this->_edit_type_key.'='.$this->getEditType().'&'.$this->_master_value_key.'='.$this->getMasterId().'&'.$this->_value_id.'='.$this->getValueId()."',
                 method:'POST',
                 params: {'".$this->_master_value_key."': ".$this->getMasterId().", catIds: checked},
                 error: function(responseObject) {
                            Ext.Msg.alert('".__define('TEXT_ALERT')."', '".__define('TEXT_NO_SUCCESS')."');
                          },
                 waitMsg: 'SAVED..',
                 success: function(responseObject) {
                            Ext.Msg.alert('".__define('TEXT_ALERT')."','".__define('TEXT_SUCCESS')."');
                          }
                 });")));
       $tp->setRenderTo(PhpExt_Javascript::variable("Ext.get('".$this->indexID."')"));

        $js = PhpExt_Ext::OnReady(
            PhpExt_Javascript::stm(PhpExt_QuickTips::init()),

            $root->getJavascript(false, "croot"),
        	$tp->getJavascript(false, "tree")

        );


        return '<script type="text/javascript">'. $js .'</script><div id="'.$this->indexID.'"></div>';

	}	
	
	function getNode() {
		if ($this->url_data[$this->_master_value_key])
		$this->setMasterId($this->url_data[$this->_master_value_key]);

		if ($this->url_data[$this->_value_id])
		$this->setValueId($this->url_data[$this->_value_id]);	
		
		if ($this->url_data['galType'])
		$this->setSourceGalType($this->url_data['galType']);			
		
		$d = new recursive($this->_table, $this->_master_key);

        $d->setLangTable($this->_table_lang);
        $d->setDisplayKey($this->_display_key);
        
        $gal_Check = $this->getSourceGalType();
        if(!preg_match('/files/', $gal_Check)){
        	$d->setWhereQuery("and class NOT LIKE '%files%' ");
        }else{
        	$d->setWhereQuery("and class LIKE '%files%' ");
        }
        
        $d->setDisplayLang(true);
        $data = $d->_getLevelItems($this->url_data['node']);

        if(is_array($data)){
	        foreach ($data as $cat_data) {
	            $checked = false;
	            if(is_array($cat_data) && isset($cat_ids) && is_array($cat_ids)){
		            if (in_array($cat_data[$this->_master_key], $cat_ids)) {
		                $checked = true;
		            }
	            }

	            $expanded = false;	            
	            $new_cats[] = array('id' => $cat_data[$this->_master_key], 'text' => $cat_data[$d->getDisplayKey()], 'checked' => $checked, 'expanded' => $expanded);
	        }
        }
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        return json_encode($new_cats);
	}	
	
	function _getParams() {
		global $language;

		$params = array();

		return $params;
	}	
	
	function setData($dont_die=FALSE) {
        global $db;
        
        $obj = new stdClass;
                        
	   	if($this->url_data['value_ids'] && $this->url_data['value_ids']!='undefined'){
			$value_ids = preg_split('/,/', $this->url_data['value_ids']);
       	}else{
			$obj->failed = true;
       	}

		if ($this->url_data['catIds']) {
			$this->url_data['catIds'] = str_replace(array('[',']','"','\\'), '', $this->url_data['catIds']);
	        $cat_ids = preg_split('/,/', $this->url_data['catIds']);
        }else{
			$obj->failed = true;
        }        

        $gal = new MediaGallery();
        $galType = $gal->_getParentClass($this->url_data['mgID']);
        $this->setSourceGalType($galType);        
        
        if(!$obj->failed){
        	
	       if($this->url_data['editType']=='copy'){
	       		$obj = $this->_copyToCategory($cat_ids, $value_ids);
	       		$obj->success = true;
	       }
    	       
	 	   if($this->url_data['editType']=='move'){
				$obj = $this->_moveToCategory($cat_ids, $value_ids);	       	
	       		$obj->success = true;
	       }
        }
	}	
	   
	protected function _moveToCategory($cat_ids, $value_ids){
		global $db;
		
		foreach ($value_ids as $key => $id){
			
			$source_table_data = new adminDB_DataRead($this->_table_media, NULL, NULL, $this->_master_media_key);
			$source_data =  $source_table_data->getData($id);
			$source_data  = $source_data[0];
			
			$db->Execute("DELETE FROM " . $this->_table_link . " WHERE m_id = ?", array((int)$id));
			
			foreach ($cat_ids as $c_key => $c_id){
				
				$target_table_data = new adminDB_DataRead($this->_table, NULL, NULL, $this->_master_key);
				$target_data =  $target_table_data->getData($c_id);
				$target_data  = $target_data[0];	

				$source_data['class'] = $target_data['class'];
				
				$oM = new adminDB_DataSave($this->_table_media, $source_data);
				$objM = $oM->saveDataSet();				
				
		        $data = array(
					'm_id'  => $id,
					'mg_id' => $c_id
				);
		        
		        $source_gal_type = $this->getSourceGalType();
		        $target_gal_type = $target_data['class'];
		        
		        if($source_gal_type != $target_gal_type){
			        if(!preg_match('/files/', $source_gal_type) && !preg_match('/files/', $target_gal_type)){
			        	$mi = new MediaImages();
			        	$mi->setClass($target_gal_type);
			        	$mi->processImage($source_data['file'], true);
			        }
		        }
	        	$o = new adminDB_DataSave($this->_table_link, $data, false, __CLASS__);
	        	$obj = $o->saveDataSet();
			}
	
		}

	}
        
	protected function _copyToCategory($cat_ids, $value_ids){
		global $db;
		
		foreach ($value_ids as $key => $id){
			
			$source_table_data = new adminDB_DataRead($this->_table_media, NULL, NULL, $this->_master_media_key);
			$source_data =  $source_table_data->getData($id);
			$source_data  = $source_data[0];
			
			foreach ($cat_ids as $c_key => $c_id){
				
				$target_table_data = new adminDB_DataRead($this->_table, NULL, NULL, $this->_master_key);
				$target_data =  $target_table_data->getData($c_id);
				$target_data  = $target_data[0];			
			
				unset($source_data['id']);
				$source_data['class'] = $target_data['class'];
				
				$oM = new adminDB_DataSave($this->_table_media, $source_data);
				$objM = $oM->saveDataSet();

		        $link_data = array(
					'm_id'  => $objM->new_id,
					'mg_id' => $c_id
				);

				$source_gal_type = $this->getSourceGalType();
		        $target_gal_type = $target_data['class'];
		        
		        if($source_gal_type != $target_gal_type){
			        if(!preg_match('/files/', $source_gal_type) && !preg_match('/files/', $target_gal_type)){
			        	$mi = new MediaImages();
			        	$mi->setClass($target_gal_type);
			        	$mi->processImage($source_data['file'], true);
			        }
		        }   
				
				$ol = new adminDB_DataSave($this->_table_link, $link_data, false, __CLASS__);
	        	$objL = $ol->saveDataSet();
			}
		}
	}
}