<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: hermes_ExtAdminHandler.php 6481 2013-09-02 07:21:40Z mario $
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


class generate_slaves_ExtAdminHandler  extends  ExtAdminHandler
{
    function __construct($extAdminHandler)
    {
        foreach ($extAdminHandler as $key => $value)
        {
            $this->$key = $value;
        }
    }
    

    function multiselectStm ($var = 'record_ids') {
        $js = "
             var records = new Array();
             records = ".$this->code."ds.getModifiedRecords();
             ".$this->_getGridModifiedRecordsData()."
		 	 var ".$var." = '';
		 	 for (var i = 0; i < records.length; i++) {
		 	     if (records[i].get('selectedItem'))
		 	      ".$var." += records[i].get('".$this->getMasterKey()."') + ',';
		 	 }


		 	 ";
        return $js;
    }

   function UploadImage($id='')
   {
		if ($id)
			$u_js = "var edit_id = ".$id.";";
		else $u_js = "var edit_id = record.data.products_id;";

		$mg = new MediaGallery();
		$code = $mg->_getParentClass(2);
	
		$mediaWindow = $this->getMediaWindow2(true, true, true, 'images', "&mgID=2&link_id='+edit_id+'",$code);
		$u_js .= $mediaWindow->getJavascript(false, "new_window") . "new_window.show();";
		return $u_js;
   }
	
	function getMediaWindow2($show_grid=true, $show_flash_upload=true, $show_simple_upload=true, $type='images', $params='', $code){
		
		if ($_GET['typ']) $this->url_data['edit_id'] = $edit_id;
		
		
        // tab 1 grid
        if($show_grid){
			$tab[] = array('url' => "adminHandler.php?load_section=MediaImageList&pg=overview&currentType=".$code."&link_id=".$_GET['link_id'],
							'url_short' => true,
							'params' => ''.$params,
							'title' => "TEXT_IMAGES");
        }
        
        // tab 2 search
	    if($show_grid){
			$tab[] = array('url' => "adminHandler.php?load_section=MediaImageSearch&pg=overview&currentType=".$code."&link_id=".$_GET['link_id'],
							'url_short' => true,
							'params' => ''.$params,
							'title' => "TEXT_SEARCH_IMAGES");
        }        
        
        // tab 3 upload
        if($show_flash_upload){
	        $tab[] = array('url' => 'upload.php',
							'url_short' => true,
							'params' => 'uploadtype=multiple&type='.$type.'&currentType='.$code."&link_id=".$_GET['link_id'].$params,
							'title' => "TEXT_MULTIPLE_UPLOAD");
        }
        				
        // tab 4 simple upload
        if($show_simple_upload){
	        $tab[] = array('url' => 'upload.php',
							'url_short' => true,
							'params' => 'uploadtype=single&type='.$type.'&currentType='.$code."&link_id=".$_GET['link_id'].$params,
							'title' => "TEXT_SIMPLE_UPLOAD");
        } 
					
		$mediaWindow = $this->_TabRemoteWindow("TEXT_MEDIA_MANAGER", $tab);
 		
		return $mediaWindow;
	}
	
	function multiselectStm2 ($var = 'record_ids') {
        $js = "
             var records = new Array();
             var p = '';
             records = ".$this->code."ds.getModifiedRecords();
             ".$this->_getGridModifiedRecordsData()."
		 	 var ".$var." = '';
		 	 var not_saved_data='';
		 	 for (var i = 0; i < records.length; i++) {
		 	     if (records[i].modified.products_name || records[i].modified.products_quantity || 
                     records[i].modified.products_weight || records[i].modified.products_price || records[i].modified.products_model){
                    not_saved_data += records[i].get('".$this->getMasterKey()."') + ',';
                 }
		 	     if (records[i].get('selectedItem')){
		 	        ".$var." += records[i].get('".$this->getMasterKey()."') + ',';
                 }
				 p += records[i].get('".$this->getMasterKey()."') + ',';
		 	 }
		 	 
		 	if (not_saved_data!='')
            {
                   Ext.Msg.show({
                   title:'".TEXT_MASTER_SLAVE."',
                   msg: '".TEXT_MASTER_SLAVE_UNSAVED_DATA."',
                   buttons: Ext.Msg.YESNO,
                   animEl: 'elId',
                   fn: function(btn){runUNsavedDataCheck(btn);},
                   icon: Ext.MessageBox.QUESTION
                });
                
                function runUNsavedDataCheck(btn){
                    if (btn == 'yes') {
                        contentTabs.remove(contentTabs.getActiveTab());
                        var gh=Ext.getCmp('generated_slavesgridForm'); if (gh) contentTabs.remove('node_generated_slaves'); 
                        addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generated_slaves&pg=overview&products_id='+edit_id+'&record_ids='+p+'&parentNode=node_generated_slaves','". TEXT_GENERATE_SLAVES_STEP_3 ."');
                    }else {
                        addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=overview&products_id='+edit_id+'&parentNode=node_generate_slaves','". TEXT_GENERATE_SLAVES_STEP_2 ."');
                    }
        
                };
                
                return true;
            }
			if (".$var."=='')
			{
    			   Ext.Msg.show({
    			   title:'".TEXT_MASTER_SLAVE."',
    			   msg: '".TEXT_MASTER_SLAVE_NO_ITEMS_SELECTED."',
    			   buttons: Ext.Msg.YESNO,
    			   animEl: 'elId',
    			   fn: function(btn){runSelectedItemsChecked(btn);},
    			   icon: Ext.MessageBox.QUESTION
    			});
				
                
                
				function runSelectedItemsChecked(btn){
			  		if (btn == 'yes') {
			  			contentTabs.remove(contentTabs.getActiveTab());
						var gh=Ext.getCmp('generated_slavesgridForm'); if (gh) contentTabs.remove('node_generated_slaves'); 
						addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generated_slaves&pg=overview&products_id='+edit_id+'&record_ids='+p+'&parentNode=node_generated_slaves','". TEXT_GENERATE_SLAVES_STEP_3 ."');
					}else {
						addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=overview&products_id='+edit_id+'&parentNode=node_generate_slaves','". TEXT_GENERATE_SLAVES_STEP_2 ."');
					}
		
				};
			}
			else
			{
				contentTabs.remove(contentTabs.getActiveTab());
				var gh=Ext.getCmp('generated_slavesgridForm'); if (gh) contentTabs.remove('node_generated_slaves'); 
				addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generated_slaves&pg=overview&products_id='+edit_id+'&record_ids='+p+'&parentNode=node_generated_slaves','". TEXT_GENERATE_SLAVES_STEP_3 ."');
					
			}";
			
        return $js;
    }
}
?>