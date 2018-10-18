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

include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.product_to_attributes.php';
include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/generate_slaves_ExtAdminHandler.php';
include_once _SRV_WEBROOT . 'xtFramework/classes/class.product.php';

class generated_slaves {

	public $_table = TABLE_PRODUCTS;
	public $_table_lang = TABLE_PRODUCTS_DESCRIPTION;
	public $_table_seo = null;
	public $_master_key = 'products_id';
	public $_image_key = 'products_image';
	public $_display_key = 'products_name';
	 protected $_icons_path = "images/icons/";
	public $codes = array();
	public $pos = 0;
	public $final_arr = array();
	protected $pID=0;


	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		global $language,$db,$xtPlugin;
		
		$params = array();
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_getParams_top')) ? eval($plugin_code) : false;
		$header['products_name'] = array('type' => 'text');
		$header['products_id'] = array('type' => 'hidden');
		$header['attributes'] = array('type' => 'text');
		/*
		$header['products_image'] = array(
									'type' => 'image',						// you can modyfy the auto type
									'path' =>  'org',
									'currentType' =>  'products'
									);
		*/
		
		$params['gridType'] = 'Template';
		$button['edit'] = array('status'=>false);
		
 		$header[$this->_master_key] = array('type' => 'hidden');
		$params['panelSettings']  = $panelSettings;
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['PageSize']       = 25;
		
		
		$params['display_statusTrueBtn']  = false;
		$params['display_statusFalseBtn']  = false;
		$params['display_copyBtn']  = false;
		$params['display_editBtn']  = false;
		$params['display_cancelBtn']  = false;
		$params['display_newBtn']  = false;
		
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol']  = true;
		$params['display_statusTrueBtn']  = true;
		$params['display_statusFalseBtn']  = true;
		
		
		$rowActions[] = array('iconCls' => 'move_product', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_CATEGORIES);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; \n";
		else
          $js = "var edit_id = record.id; \n";
        $extF = new ExtFunctions();
		$js.= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_CATEGORIES","TEXT_PRODUCTS","adminHandler.php?load_section=product_to_mastercat&pg=getTreePanel&products_id='+edit_id+'", '', array(), 800, 600).' new_window.show();';
		$rowActionsFunctions['move_product'] = $js;
		
		$rowActions[] = array('iconCls' => 'more_categories', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_MORE_CATEGORIES);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; \n";
		else
          $js = "var edit_id = record.id; \n";
		  
        $extF = new ExtFunctions();
		$js.= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_MORE_CATEGORIES","TEXT_PRODUCTS","adminHandler.php?load_section=product_to_cat&pg=getTreePanel&products_id='+edit_id+'", '', array(), 800, 600).' new_window.show();';
		$rowActionsFunctions['more_categories'] = $js;
		
		
		$rowActions[] = array('iconCls' => 'products_media', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_MEDIA);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; \n";
		else
          $js = "var edit_id = record.id; \n";
          $extF = new ExtFunctions();
		$js.= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_MEDIA","TEXT_PRODUCTS","adminHandler.php?load_section=product_to_media&pg=getTreePanel&products_id='+edit_id+'", '', array(), 800, 600).' new_window.show();';

		$rowActionsFunctions['products_media'] = $js;
		
		
		$rowActions[] = array('iconCls' => 'products_special_price', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_SPECIAL_PRICE);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($products_model)."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";

          $js .= "addTab('adminHandler.php?plugin=xt_special_products&load_section=product_sp_price&pg=overview&products_id='+edit_id,'".TEXT_PRODUCTS_SPECIAL_PRICE." ('+edit_name+')', 'product_sp_price'+edit_id)";

		$rowActionsFunctions['products_special_price'] = $js;
		

		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_getParams_row_actions')) ? eval($plugin_code) : false;
		
		$rowActions[] = array('iconCls' => 'products_group_price', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_GROUP_PRICE);
        if ($this->url_data['edit_id'])
		  $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($products_model)."';\n";
		else
          $js = "var edit_id = record.id; var edit_name=record.get('products_model');\n";
        $js .= "addTab('adminHandler.php?load_section=product_price&pg=overview&products_id='+edit_id,'".TEXT_PRODUCTS_GROUP_PRICE." ('+edit_name+')', 'product_price'+edit_id)";

		$rowActionsFunctions['products_group_price'] = $js;
		
		
		
		//if (!$this->url_data['edit_id']) // comment out if you don't want the upload image button on the bottom to be shown
		//{
			$rowActions[] = array('iconCls' => 'upload_image', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_UPLOAD);
			$ext = new generate_slaves_ExtAdminHandler($this->_AdminHandler);
			$u_js = $ext->UploadImage($this->url_data['edit_id']);
			$rowActionsFunctions['upload_image'] = $u_js;
		//}	
		
			$rowActions[] = array('iconCls' => 'edit', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_EDIT);
			$sjs = "var edit_id = record.data.products_id ;";
			$sjs .= "addTab('adminHandler.php?load_section=product&edit_id='+edit_id+'','".__define("TEXT_PRODUCTS").' ' . __define("TEXT_EDIT") ."');";
			$rowActionsFunctions['edit'] = $sjs;
		
			$params['rowActions']             = $rowActions;
			$params['rowActionsFunctions']    = $rowActionsFunctions;
		
		
		if (isset($this->url_data['record_ids']))
		{
			
			$sjs = "var edit_id = ".$this->url_data['products_id']." ;";

			$sjs .= "addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=overview&products_id='+edit_id+'&parentNode=node_generate_slaves','".TEXT_GENERATE_SLAVES_STEP_2."')";
			        // addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=overview&products_id='+edit_id+'&parentNode=node_generate_slaves','".TEXT_GENERATE_SLAVES_STEP_2."');";
			$UserButtons['nextstep'] = array('text' => 'TEXT_GENERATE_SLAVES_STEP_2', 'style' => 'nextstep', 'icon' => 'arrow_left.png', 'acl' => 'edit', 'stm' => $sjs);
			$params['display_nextstepBtn'] = true;
			
			
		}
		
			
		$params['UserButtons'] = $UserButtons;
		
		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] )
			$params['include'] = array ('products_id', 'products_name_'.$language->code, 'products_model', 'products_quantity', 'products_status','products_price', 'products_image' );
		else 
			$params['exclude'] = array ('date_added', 'last_modified', 'products_average_rating', 'products_rating_count', 'products_ordered','products_transactions','language_code', 'external_id', 'products_image' );

		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_getParams_bottom')) ? eval($plugin_code) : false;
		return $params;
		
	}
	
	/* check if slave products already exists */
	function checkProductExists($copied_id,$products_model)
	{
		global $db,$language,$seo,$xtPlugin;		
		
		$recc = $db->Execute("SELECT attributes_id FROM " . TABLE_TMP_PRODUCTS_TO_ATTRIBUTES . " WHERE products_id=? and main=0 ",array((int)$copied_id));		
		
		$sec_key = array($products_model);
		if($recc->RecordCount() > 0)
		{
			$add_to_tables='';
			$add_to_where = '';
			$i=1;
			while(!$recc->EOF)
			{
				$add_to_tables .= " INNER JOIN ".TABLE_PRODUCTS_TO_ATTRIBUTES." pa".$i." ON p.products_id = pa".$i.".products_id ";
				$add_to_where .= " and pa".$i.".attributes_id = ?";
                array_push($sec_key,$recc->fields['attributes_id']);
				$i++;
				$recc->MoveNext();
			}$recc->Close();
		}
				
		$recc2 = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS." p ".$add_to_tables."
								WHERE p.products_master_model=? ".$add_to_where, $sec_key );	
		
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:checkProductExists')) ? eval($plugin_code) : false;
		if($recc2->RecordCount() > 0)
		{
			return false;
		}else return true; 
	}
	
	function SaveProducts($record_ids,$products_model,$parent_id)
	{
		global $db,$language,$seo,$xtPlugin;
		if ($this->position != 'admin') return false;
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:SaveProducts_top')) ? eval($plugin_code) : false;
		$obj = new stdClass;
		$all_ids = substr_replace($record_ids ,"",-1); 
		if ($all_ids!='')
		{
			$r = $db->Execute("SELECT * FROM " . TABLE_TMP_PRODUCTS . " WHERE products_id in (".$all_ids.") and saved = 0 ");
			$rows = $r->RecordCount();
		}
		else $rows = 0;
		if($rows > 0)
		{
			while(!$r->EOF)
			{
				$data = $r->fields;
				$main_products_id = $r->fields['main_products_id'];
				$data['date_added'] = $db->BindTimeStamp(time());
				$copied_id = $data['products_id'];
				$data['products_id'] = '';
				$data['products_status'] = 0;
				if ($r->fields['products_ean']=='') $data['products_ean'] = '';
				$data['products_master_model'] = $products_model;
				$exclude_fields = array('products_id','flag_has_specials', 'price_flag_graduated_all');
				if (XT_MASTER_SLAVE_INHERIT_ASSIGNED_MASTER_IMAGES=='false') array_push($exclude_fields,'products_image');
				
				($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:index.php:SaveProducts')) ? eval($plugin_code) : false;			
				
				$created_slave = $this->checkProductExists($copied_id,$products_model);
				if ($created_slave)
				{
					$oP = new adminDB_DataSave($this->_table, $data);
					$oP->setExcludeFields($exclude_fields);
					$objP = $oP->saveDataSet();
					
		
					if ($objP->new_id) 
					{
						$obj->new_id = $objP->new_id;
						$data[$this->master_id] = $objP->new_id;
						$data['products_id'] =$objP->new_id;
					}
					
					$recc = $db->Execute("SELECT * FROM " . TABLE_TMP_PRODUCTS_TO_ATTRIBUTES . " WHERE products_id=? and main=0 ",array($copied_id));				
					if($recc->RecordCount() > 0)
					{
						while(!$recc->EOF)
						{
							$db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_ATTRIBUTES . " (products_id,attributes_id,attributes_parent_id) 
							VALUES(?,?,?) ",
                            array($data['products_id'],$recc->fields['attributes_id'],$recc->fields['attributes_parent_id']));
							$recc->MoveNext();
						}$recc->Close();
					}
					
					$add_to_name='';
					$ret = $this->getStorePermissionsForMaster($parent_id);	
					if ($this->CheckMultiStoreFunctionality(TABLE_PRODUCTS_DESCRIPTION,'products_store_id')) 
					{	
						foreach($ret as $st)
						{
							foreach ($language->_getLanguageList() as $key => $val) {
								
								$rec = $db->Execute("SELECT * FROM " . $this->_table_lang . " WHERE 
								        products_id =? and language_code=? and products_store_id=? ",
								        array($parent_id,$val['code'],$st['id']));
								if($rec->RecordCount() > 0)
								{
									$data['products_description_store'.$st['id'].'_'.$val['code']] = $rec->fields['products_description'];
									$data['products_short_description_store'.$st['id'].'_'.$val['code']] = $rec->fields['products_short_description'];
									$add_to_name = $this->BuildSlaveName($data['products_id'],$val['code']);
									if ($data["name_changed"]==1){
									   $data['products_name_store'.$st['id'].'_'.$val['code']] = $data['products_name'];
                                    }else{
                                       $data['products_name_store'.$st['id'].'_'.$val['code']] = trim($rec->fields['products_name'].$add_to_name);
                                    }
									$data['products_store_id_store'.$st['id'].'_'.$val['code']] = $st['id'];
									$rec->Close();
								}
								else{
								    if ($data["name_changed"]==1){
								       $data['products_name_store'.$st['id'].'_'.$val['code']] = $data['products_name']; 
								    }else{
								       $data['products_name_store'.$st['id'].'_'.$val['code']] = trim($data['products_name']);
								    }
								}
			
							}
						}
						$oPD = new adminDB_DataSave($this->_table_lang, $data, true,'',true);
						$objPD = $oPD->saveDataSet();
						
					}else
					{
						foreach ($language->_getLanguageList() as $key => $val) {
							
							
							$rec = $db->Execute("SELECT * FROM " . $this->_table_lang . " WHERE 
							products_id =? and language_code=? ",
                            array($parent_id,$val['code']));
							if($rec->RecordCount() > 0)
							{
								$data['products_description_'.$val['code']] = $rec->fields['products_description'];
								$data['products_short_description_'.$val['code']] = $rec->fields['products_short_description'];
								$add_to_name = $this->BuildSlaveName($data['products_id'],$val['code']);
								$data['products_name_'.$val['code']] = trim($rec->fields['products_name'].$add_to_name);
								$rec->Close();
							}
							else{
								$data['products_name_'.$val['code']] = trim($data['products_name']);
							}
		
						}
						$oPD = new adminDB_DataSave($this->_table_lang, $data, true);
						$objPD = $oPD->saveDataSet();
					}
					
					if ($this->CheckMultiStoreFunctionality(TABLE_PRODUCTS_DESCRIPTION,'products_store_id'))
					{	
						foreach($ret as $st)
						{
							$reccc = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id=? and store_id= ?",array($main_products_id,$st['id']));				
							if($reccc->RecordCount() > 0)
							{
								while(!$reccc->EOF)
								{
									$db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id,categories_id,master_link,store_id) 
									           VALUES(?,?,?,?) ",
									           array($data['products_id'],$reccc->fields['categories_id'],$reccc->fields['master_link'],$st['id']));
									$reccc->MoveNext();
								}$reccc->Close();
							}
					
							foreach ($language->_getLanguageList() as $key => $val) 
							{
								$data['url_text_store'.$st['id'].'_'.$val['code']] = str_replace(" ","-",$data['products_name_store'.$st['id'].'_'.$val['code']]);
								$data['url_text_store'.$st['id'].'_'.$val['code']] = $seo->filterAutoUrlText($data['url_text_store'.$st['id'].'_'.$val['code']],$val['code'],'product',$data['products_id']);
								$seo->_UpdateRecord('product',$data['products_id'], $val['code'], $data, true, 'true',$st['id']);				
							}
						}
					}
					else{					
						foreach ($language->_getLanguageList() as $key => $val) 
						{
							$data['url_text_'.$val['code']] = str_replace(" ","-",$data['products_name_'.$val['code']]);
							$data['url_text_'.$val['code']] = $seo->filterAutoUrlText($data['url_text_'.$val['code']],$val['code'],'product',$data['products_id']);
							$seo->_UpdateRecord('product',$data['products_id'], $val['code'], $data, true, 'true');				
						}
					}	
					$this->copyPermissions(TABLE_PRODUCTS_PERMISSION, $data['products_id'], $parent_id);	
                    $this->copySpecialAndGroupPrice($data['products_id'], $parent_id);
					if (XT_MASTER_SLAVE_INHERIT_ASSIGNED_MASTER_IMAGES=='true')
						$this->copyMedia( $data['products_id'], $parent_id);
				}
				
				$db->Execute("Update " . TABLE_TMP_PRODUCTS . " SET saved = 1 WHERE products_id = ?", array($copied_id));
				($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:SaveProducts_loop_end')) ? eval($plugin_code) : false;
				$r->MoveNext();
			}$r->Close();
		}
		
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:SaveProducts_bottom')) ? eval($plugin_code) : false;

		return $obj;
	
	}
	
    /*Copy special and group price from master $target_product 
     * to a slave $target_product*/
    function copySpecialAndGroupPrice($target_product, $source_product){
        global $customers_status,$db;
        // Special Price
        $s_table_data = new adminDB_DataRead(TABLE_PRODUCTS_PRICE_SPECIAL, null, null, 'id', ' products_id='.$source_product);
        $s_data = $s_table_data->getData();

        $s_count = count($s_data);

        if ($s_count>0){
            for ($i = 0; $i < $s_count; $i++) {
                unset($s_data[$i]['id']);
                $s_data[$i]['products_id'] = $target_product;
                $oS = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_SPECIAL, $s_data[$i], false, __CLASS__);
                $objS2P = $oS->saveDataSet();
            } 
            $db->Execute("Update " . TABLE_PRODUCTS . " SET flag_has_specials = 1 WHERE products_id = ? ",array($target_product));
        }
       

        // Group Price:
        foreach ($customers_status->_getStatusList('admin', 'true') as $key => $val) {

            $g_table_data = new adminDB_DataRead(TABLE_PRODUCTS_PRICE_GROUP.$val['id'], null, null, 'id', ' products_id='.$source_product);
            $g_data = $g_table_data->getData();

            $g_count = count($g_data);

            if (count($g_count)>0){
                for ($i = 0; $i < $g_count; $i++) {
                    unset($g_data[$i]['id']);
                    $g_data[$i]['products_id'] = $target_product;
                    $oG = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_GROUP.$val['id'], $g_data[$i], false, __CLASS__);
                    $objG2P = $oG->saveDataSet();
                }
                $r = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS . " WHERE products_id = ?",array($source_product));
                if ($r->RecordCount() > 0){
                    $db->Execute("Update " . TABLE_PRODUCTS . " SET price_flag_graduated_".$val['id']." = ".$r->fields['price_flag_graduated_'.$val['id']]."
                             WHERE products_id = ? ",array($target_product));
                     
                }
                
            }
        }
    }
    
	/*Copy media from master $target_product 
     * to a slave $target_product*/
    function copyMedia($target_product, $source_product){
        global $db;
        $rs = $db->Execute("SELECT * FROM " . TABLE_MEDIA_LINK . " WHERE link_id = ? and class='product'",array($source_product));
        if ($rs->RecordCount()>0){
            while(!$rs->EOF){
                   unset($rs->fields['ml_id']);
                   $rs->fields['link_id'] = $target_product;
                   $db->AutoExecute(TABLE_MEDIA_LINK, $rs->fields);
                $rs->MoveNext();
            }
            
        }
    }
	/*Copy permission of a product $source_product 
	 * for the newly created product $target_product*/
	function copyPermissions($table, $target_product, $source_product){
		global $db;
	
		$record = $db->Execute("SELECT * FROM " . $table ." where pid = ?",array($source_product));
		while(!$record->EOF){

			$record->fields['pid'] = $target_product;
			$db->AutoExecute($table, $record->fields);

			$record->MoveNext();
		}$record->Close();

	}

	/* Retruns store for which the product data should 
	 * be created based on system_group_permissions*/
	function getStorePermissionsForMaster($id)
	{ global $db;
		
		$return = array();
		
		$rec = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_PERMISSION . " WHERE pid =? and pgroup LIKE '%shop_%' ",array($id));
		if($rec->RecordCount() > 0)
		{
			while(!$rec->EOF)
			{
				$n = array();
				$n['id'] = str_replace("shop_", "", $rec->fields['pgroup']);
				$n['name'] = str_replace("shop_", "", $rec->fields['pgroup']);
				array_push($return,$n);
				$rec->MoveNext();
			}
			$rec->Close();
		}

		if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
			$store= new multistore();
			$stores = $store->getStores();
			for($i=0;$i<count($stores);$i++)
			{
				foreach($return as $r){
					if ($r['id']==$stores[$i]['id']) unset($stores[$i]);
				}
			}
			return $stores;
		}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
			return $return;
		}
		return $return;
	}
	
	/*Checks if certain fields exists in db
	 *  so could be added in script logic
	 */
	function CheckMultiStoreFunctionality($table,$column)
	{	global $db;
		
		$rs=$db->Execute("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='"._SYSTEM_DATABASE_DATABASE."' AND table_name='".$table."' AND COLUMN_NAME = '".$column."' ");
		if ($rs->RecordCount()>0)
		{
			return true;
		}
		return false;
	}
	
	function BuildSlaveName($id,$lg)
	{
		global $db;
		$add_to_name_array = array();
		$n_fields = $db->Execute("SELECT pd.attributes_name FROM " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa 
								  LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION . " pd ON pa.attributes_id = pd.attributes_id
								  WHERE pa.products_id =? and pd.language_code=? ",array((int)$id,$lg));
		
		if($n_fields->RecordCount() > 0)
		{
			while(!$n_fields->EOF)
			{
				array_push($add_to_name_array,$n_fields->fields['attributes_name']);
				$n_fields->MoveNext();
			}
			
		}$n_fields->Close();
		$add_to_name = ' '.implode(" / ",$add_to_name_array);
		
		return $add_to_name;
	}
	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language,$xtPlugin;
		
		if ($this->position != 'admin') return false;
		
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_get_top')) ? eval($plugin_code) : false;
		$obj = new stdClass;	
		
		
		
		$rs = $db->Execute("SELECT products_model FROM " . $this->_table . " WHERE products_id = ?",array((int)$this->url_data['products_id']));
			
		if (isset($this->url_data['record_ids']) && !isset($this->url_data['get_data'])) 
			$this->SaveProducts($this->url_data['record_ids'],$rs->fields['products_model'],$this->url_data['products_id']);
		
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key," 	products_master_model= '".$rs->fields['products_model']."'");		
		
		if ($this->url_data['get_data']){
			$data = $table_data->getData();	
			if(is_array($data)){
				foreach ($data as $key => $val) {                     
					$t = new product($data[$key]['products_id']);
					$data[$key]['products_price'] = $t->build_price($data[$key]['products_id'], $data[$key]['products_price'], $data[$key]['products_tax_class_id']);
				}
			}
		}elseif($ID){
			$data = $table_data->getData($ID);
			if(is_array($data)){
				foreach ($data as $key => $val) {                     
                    foreach ($language->_getLanguageList() as $k => $v) {
                       $data[$key]['url_text_'.$v['code']] = urldecode($data[$key]['url_text_'.$v['code']]);
                    }
				}
			}
			
		}else{
			
			$data = $table_data->getHeader();
			
		}
	   
		if($table_data->_total_count!=0 || !$table_data->_total_count)
			$count_data = $table_data->_total_count;
		else
		$count_data = count($data);
		
		$obj->totalCount = $table_data->_total_count;
		$obj->data = $data;
		
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_get_bottom')) ? eval($plugin_code) : false;
		
		return $obj;
	}

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter,$seo,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_set_top')) ? eval($plugin_code) : false;
		 $obj = new stdClass;

		foreach ($data as $key => $val) {

			if($val == 'on')
			   $val = 1;

			$data[$key] = $val;

		}

		 //unset($data['attributes_image']);
		
		 $oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		 $objC = $oC->saveDataSet();

		 if ($set_type=='new') {	// edit existing
		 	 $obj->new_id = $objC->new_id;
			 $data = array_merge($data, array($this->_master_key=>$objC->new_id));
		 }

		 $oCD = new adminDB_DataSave($this->_table_lang, $data, true, __CLASS__);
		 $objCD = $oCD->saveDataSet();

		 if ($objC->success && $objCD->success) {
		     $obj->success = true;
		 } else {
		     $obj->failed = true;
		 }
		
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_set_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}

	function _setImage($id, $file) {
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_setImage_top')) ? eval($plugin_code) : false;

		$obj = new stdClass;

		$data[$this->_master_key] = $id;
		$data['products_image'] = $file;

		$o = new adminDB_DataSave($this->_table, $data);
		$obj = $o->saveDataSet();

		$obj->totalCount = 1;
		if ($obj->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}

		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_setImage_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}		
	
	function _setStatus($id, $status) {
		global $db,$xtPlugin;

		$id = (int)$id;
		if (!is_int($id)) return false;
		
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_setStatus')) ? eval($plugin_code) : false;
		$db->Execute("update ". $this->_table ." set products_status = ".$status." where ".$this->_master_key." = ?",array($id));

	}	
	
	function _unset($id = 0) {
	    global $db,$xtPlugin;
	    if ($id == 0) return false;
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_unset_top')) ? eval($plugin_code) : false;
	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?",array($id));
	    if ($this->_table_lang !== null)
	    $db->Execute("DELETE FROM ". $this->_table_lang ." WHERE ".$this->_master_key." = ?",array($id));
		$db->Execute("DELETE FROM ". TABLE_SEO_URL ." WHERE link_type='1' and link_id = ?",array($id));
		$db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = ?",array($id));
        $db->Execute("DELETE FROM " . TABLE_MEDIA_LINK . " WHERE link_id = ? and class='product'",array($id));
        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_PERMISSION . " WHERE pid = ?",array($id));            
           
		($plugin_code = $xtPlugin->PluginCode('class.generated_slaves.php:_unset_bottom')) ? eval($plugin_code) : false;
	}

}

?>