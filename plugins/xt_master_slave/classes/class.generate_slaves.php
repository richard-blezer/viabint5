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
include_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_master_slave/classes/class.generated_slaves.php';

class generate_slaves {

	public $_table = TABLE_TMP_PRODUCTS;
	public $_table_lang = null;
	public $_table_seo = null;
	public $_master_key = 'products_id';
	public $_image_key = 'attributes_image';
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
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_getParams_top')) ? eval($plugin_code) : false;
		if ($_GET['save_all']==true)
		{ 
			$this->UpdateGrid();
		}
		
		
		
		$header['products_name'] = array('type' => 'text');
		$header['products_id'] = array('type' => 'hidden');
		$header['attributes'] = array('type' => 'text');
		$header['products_price'] = array('type' => 'text');
		
		
		$button['edit'] = array('status'=>false);
		
 		$header[$this->_master_key] = array('type' => 'hidden');
		$params['panelSettings']  = $panelSettings;
		$params['gridType'] = 'EditGrid';
		
		$params['master_key']     = $this->_master_key;
		$params['PageSize']       = 50;
		
		
		$params['display_statusTrueBtn']  = false;
		$params['display_statusFalseBtn']  = false;
		$params['display_copyBtn']  = false;
		$params['display_editBtn']  = false;
		$params['display_cancelBtn']  = false;
		$params['display_newBtn']  = false;
		$params['display_checkItemsCheckbox']  = true;
		$params['display_checkCol']  = true;
		$params['display_saveBtn'] = true;
		$params['display_searchPanel']  = true;
		
			
			$sjs = "var edit_id = ".$this->url_data['products_id']." ;";
			$sjs .= "addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=setStepOne&products_id='+edit_id+'&gridHandle=Step1','".TEXT_GENERATE_SLAVES_STEP_1."')";
			        
			$UserButtons['prevstep'] = array('text' => 'TEXT_GENERATE_SLAVES_STEP_1', 'style' => 'prevstep', 'icon' => 'arrow_left.png', 'acl' => 'edit', 'stm' => $sjs);
			$params['display_prevstepBtn'] = true;
			
			$sjs = "var edit_id = ".$this->url_data['products_id']." ;";
			
			$ext = new generate_slaves_ExtAdminHandler($this->_AdminHandler);
			$ext->setMasterKey($this->_master_key);
			$sjs .= $ext->multiselectStm2 ('record_ids');
			//$sjs .= "contentTabs.remove(contentTabs.getActiveTab());";
			//$sjs .= "var gh=Ext.getCmp('generated_slavesgridForm'); if (gh) contentTabs.remove('node_generated_slaves'); ";
			
			//$sjs .= "addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generated_slaves&pg=overview&products_id='+edit_id+'&record_ids='+record_ids+'&parentNode=node_generated_slaves','".TEXT_GENERATE_SLAVES_STEP_3."');";
			//$sjs .="var gh=Ext.getCmp('generated_slavesgridForm');alert(gh);if (gh) gh.getStore().reload(); ";
			
			$UserButtons['nextstep'] = array('text' => 'TEXT_GENERATE_SLAVES_STEP_3', 'style' => 'nextstep', 'icon' => 'arrow_right.png', 'acl' => 'edit', 'stm' => $sjs);
			$params['display_nextstepBtn'] = true;
			
			$params['UserButtons'] = $UserButtons;
		
		
		$head_ar = array ('products_id','products_model', 'products_name','products_quantity','products_weight','products_price');
		
		$r = $db->Execute("SELECT DISTINCT 	tpa.attributes_parent_id, paad.attributes_name FROM  " . TABLE_TMP_PRODUCTS_TO_ATTRIBUTES . " tpa 
							INNER JOIN  ".TABLE_PRODUCTS_ATTRIBUTES." pad ON tpa.attributes_parent_id = pad.attributes_id  
							INNER JOIN  ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." paad ON tpa.attributes_parent_id = paad.attributes_id  
				WHERE products_id = ? and paad.language_code = ? ",array((int)$this->url_data['products_id'],$language->code));
		$i=1;
		if($r->RecordCount() > 0)
		{
			while(!$r->EOF)
			{
				/*array_push($head_ar,'attribute_'.$i);//array_push($head_ar,$r->fields['attributes_name']); */// these are attributes columns - removed
				//$head_ar[count($head_ar)+$i]= 'attribute_'.$i;
				//$header[$r->fields['attributes_name']] = array('type' => 'hidden');
				//$header['attribute_'.$i] = array('type' => 'hidden'); // these are attributes columns - removed
				$i++;
				$r->MoveNext();
			}$r->Close();
			
		}
		$params['header']         = $header;
		$params['include'] = $head_ar;
		
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_getParams_bottom')) ? eval($plugin_code) : false;
		return $params;	
	}
	
	function getSearch($search_data) {
		global $db,$filter,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getSearch_top')) ? eval($plugin_code) : false;
		$sql_where = array();
		$sql_tablecols = array('products_ean','products_id','products_model','products_name');
		
		foreach ($sql_tablecols as $tablecol) {
			array_push($sql_where,"(".$tablecol." LIKE '%".$filter->_filter($search_data)."%')");
		}
		
		$where = implode(" or ",$sql_where);
		if ($where !='') $where = 'and ('.$where.')';
		
		$record = $db->Execute("SELECT  DISTINCT products_id FROM ".TABLE_TMP_PRODUCTS." WHERE saved=0 ".$where);
		if ($record->RecordCount() > 0) {

			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['products_id'];
				$record->MoveNext();
			} $record->Close();
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getSearch_bottom')) ? eval($plugin_code) : false;
		return $data;
	}

	function UpdateGrid()
	{ global $db;
		$data = array();
		
		$_totalEditCount = $_POST['_totalEditCount'];
		$edit_data =$_POST['edit_data'];
		$arr = explode("_next_##",$edit_data);
		foreach($arr as $r)
		{
			$new_ar = explode('##_key_##', $r);
			foreach($new_ar as $n_ar)
			{
				if ($n_ar!='')
				{
					$sss = explode('##_val_##', $n_ar);
					$data[$sss[0]] = $sss[1];
                    $data["name_changed"] = "1";				}
			}
			
			if ($data['products_id']!='')
			{
				$res = $this->_set($data);
			}
		}
		
		var_dump($res);
	
	}
	
	function setProductsId ($id) {
	    $this->pID = $id;
	}
	function getProductsId () {
        return $this->pID;
	}
	
	function getStarted($data)
	{
		$this->url_data = $data;
		
		return $this->setStepOne();
		
	}
	
	function setStepOne()
	{	global $xtPlugin;
		
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:setStepOne_top')) ? eval($plugin_code) : false;	
		if ($this->url_data['products_id'])
		{
			$p = new  product($this->url_data['products_id']);
			if (($p->data['products_model']=='') ||($p->data['products_model']==null))
			{
				echo '<br />  '.TEXT_GENERATE_SLAVES_NO_PRODUCTS_MODEL;
				die();
			}
			if ($p->data['products_master_flag']!=1) 
			{
				echo '<br />  '.TEXT_GENERATE_SLAVES_NO_MASTER_PRODUCT;
				die();
			}
			
			
		}

        $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
		
		$attribute_tree = new product_to_attributes();
		
		if ($this->url_data['products_id'])
			$attribute_tree->setProductsId($this->url_data['products_id']);
			
	    $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText("Attributes")
              ->setId('root');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl('adminHandler.php?plugin=xt_master_slave&load_section=product_to_attributes'.$add_to_url.'&pg=getNode&tmp=1&');
        if ($attribute_tree->getProductsId())
        $tl->setBaseParams(array('products_id' => $attribute_tree->getProductsId()));


		
        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('TEXT_PRODUCTS_TO_ATTRIBUTES'))
          ->setRoot($root)
          ->setLoader($tl)
 //         ->setRootVisible(false)
          ->setAutoScroll(true)
//          ->setCollapsible(true)
          ->setAutoWidth(true);
         $tb = $tp->getBottomToolbar();
			
				$func = " var checked = Ext.encode(tree.getChecked('id'));";
				$func .= "var edit_id = " . $this->url_data['products_id'] . ";";
				$func .= "addTab('adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=overview&products_id='+edit_id+'&parentNode=node_generate_slaves','".TEXT_GENERATE_SLAVES_STEP_2."');";
				$func .= "var gh=Ext.getCmp('generate_slavesgridForm');if (gh) gh.getStore().reload(); ";
		  
                $tb->addButton(1,__define('TEXT_MS_NEXT'), $this->_icons_path.'arrow_right.png',new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));
                 var conn = new Ext.data.Connection();
				 var edit_id = " . $this->url_data['products_id'] . ";
                 conn.request({
                 url: 'adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves".$add_to_url."&pg=setStepTwo',
                 method:'POST',
                 params: {'products_id': ".$attribute_tree->getProductsId().", attIds: checked},
                 error: function(responseObject) {
                            Ext.Msg.alert('".__define('TEXT_ALERT')."', '".__define('TEXT_NO_SUCCESS')."');
                          },
                 waitMsg: 'SAVED..',
				 success: function(responseObject) {
				 			
				 			var n = String(responseObject.responseText);
				 			var ind= n.indexOf('duplicates');
				 			var add_text = n.replace('-duplicates-','');
							if (ind>0)Ext.Msg.alert('".__define('TEXT_ALERT')."', '".__define('TEXT_DUPLICATE_SLAVES')."'+add_text+'');
							
							contentTabs.remove(contentTabs.getActiveTab());
							".$func."
							 
                          }
                 });")));
				 
				//'adminHandler.php?type=generate_slaves&plugin=xt_master_slave&load_section=generate_slaves&pg=setStepTwo&gridHandle=Step2'
       $tp->setRenderTo(PhpExt_Javascript::variable("Ext.get('".$attribute_tree->indexID."')"));
		
        $js = PhpExt_Ext::OnReady(
            PhpExt_Javascript::stm(PhpExt_QuickTips::init()),

            $root->getJavascript(false, "root"),
        	$tp->getJavascript(false, "tree")

        );
		($plugin_code = $xtPlugin->PluginCode('generate_slaves:setStepOne_bottom')) ? eval($plugin_code) : false;	
        return '<script type="text/javascript">'. $js .'</script><div id="'.$attribute_tree->indexID.'"></div>';
		
	}
	
	function setStepTwo() {
        global $db,$language,$xtPlugin;
        
        ($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:setStepTwo_top')) ? eval($plugin_code) : false;
		$to_message='';
		 $this->url_data['checked'] = $this->url_data['attIds'];
		if ($this->url_data['products_id']) $this->setProductsId($this->url_data['products_id']);
			
        if ($this->url_data['checked'] && $this->url_data['products_id']) {
			
			$db->Execute("DELETE FROM " . TABLE_TMP_PRODUCTS_TO_ATTRIBUTES . " WHERE products_id = ?",array((int)$this->url_data['products_id']));
			
            $this->url_data['checked'] = str_replace(array('[',']','"','\\'), '', $this->url_data['checked']);
	        $att_ids = split(',', $this->url_data['checked']);
			
			$parants= array();
			$parants_list= array();
			$parants_name= array();
			$parants2 = array();
	        for ($i = 0; $i < count($att_ids); $i++) {

	            if ($att_ids[$i]) 
				{
					
					$record = $db->Execute("SELECT pa.attributes_parent, padp.attributes_name  AS  attributes_name_parent, pad.attributes_name AS attributes_name_slave 
											FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
													INNER JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." padp ON pa.attributes_parent = padp.attributes_id 
													INNER JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad ON pa.attributes_id = pad.attributes_id 
											WHERE pa.attributes_id = ? and pad.language_code = ? and padp.language_code = ? ",
											array((int)$att_ids[$i],$language->code,$language->code));
					if($record->RecordCount() > 0)
					{
						$parent = $record->fields['attributes_parent'];
						
						$data = array('attributes_id' => (int)$att_ids[$i], 'attributes_parent_id'=>$parent, 'products_id' => (int)$this->url_data['products_id'],'main'=>'1');
						$o = new adminDB_DataSave(TABLE_TMP_PRODUCTS_TO_ATTRIBUTES, $data, false, __CLASS__);
						$obj = $o->saveDataSet();
					
						if (!in_array($parent, $parants_list))
						{
							array_push($parants_list,$parent);
							$parants_name[$parent] = $record->fields['attributes_name_parent'];
						}
						
						$parants[$parent][]=	$att_ids[$i];
						$parants2[$parent][$att_ids[$i]]=	$record->fields['attributes_name_slave'];
					}
				
	            }
	        }
			
	    }
	   
	 // $this->generateCodes($parants,array(),0);
	  $combinations = $this->getUniqueCombinations($parants);
      $products_arr = array();
	  
	  //$combinations = $this->final_arr;
	  
	 
	  if (count($combinations)>0) 
	  {
		
		
		$record2 = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS . " p
									INNER JOIN ". TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id = p.products_id
								WHERE p.products_id = ? and pd.language_code = ?",
                                array((int)$this->url_data['products_id'], $language->code));
		$i=1;
		
		$r= $db->Execute("SELECT * FROM " . TABLE_TMP_PRODUCTS . " WHERE products_master_model =? ",array($record2->fields['products_model']));
		
		if($r->RecordCount() > 0)
		{
			while(!$r->EOF)
			{
				$db->Execute("DELETE FROM " . TABLE_TMP_PRODUCTS_TO_ATTRIBUTES . " WHERE products_id = ?",array($r->fields['products_id']));
				
				$r->MoveNext();
			}$r->Close();
			$db->Execute("DELETE FROM " . TABLE_TMP_PRODUCTS . " WHERE products_master_model = ?",array($record2->fields['products_model']));
		}
		
		
		foreach($combinations as $k=>$com)
		{	
			$exsisting= true;
			$tr= $com;
			$j=0;
			if ($record2->fields['products_ean']!='')
			     $s = array('products_ean'=>trim($record2->fields['products_ean']));
            else $s = array('products_ean'=>'');
			$str = array();
			$to_name = '';
			$attributes='';
			$string_id = '';
			
			$data = $record2->fields;
			
			foreach($tr AS $t)
			{
				
				$s = array_merge($s,array('atr_'.$j=>$t));
				if ($to_name!='') $to_name .=' / ';
				$to_name .= $parants2[$parants_list[$j]][$t];
				
				if ($attributes!='') $attributes .=' - ';
				$attributes .= $parants_name[$parants_list[$j]].': '. $parants2[$parants_list[$j]][$t];
				if ($string_id!='') $string_id .='_';
				$string_id .= $t;
				
				//$data['atr_'.$j]=$t;
				$j++;
			}
			$s = array_merge($s, array('products_name'=>$record2->fields['products_name'].' '.$to_name,
										'atributes_id'=>$string_id
										)
					);
		
			
			
			$data['products_id']='';
			 if ($record2->fields['products_ean']!='')
			     $data['products_ean']=trim($record2->fields['products_ean']);
            else $data['products_ean']=trim($record2->fields['products_ean']);
			$data['products_model']=trim($record2->fields['products_model']).'-'.$i;
			$data['products_name']=$record2->fields['products_name'].' '.$to_name;
			$data['products_master_model'] = $record2->fields['products_model'];
			$data['products_master_flag'] = 0;
			$data['attributes'] = $attributes;
			
		
			$ttt = $this->_set_tmp($data);
			$j=0;
			if ($ttt->new_id)
			{
				foreach($tr AS $t)
				{
				
					
					$data = array('attributes_id' => (int)$t, 'attributes_parent_id'=>$parants_list[$j], 'products_id' => (int)$ttt->new_id,'main'=>'0');
        	        $o = new adminDB_DataSave(TABLE_TMP_PRODUCTS_TO_ATTRIBUTES, $data, false, __CLASS__);
        		    $obj = $o->saveDataSet();
					
					
					$j++;
				}
				
				$gen = new generated_slaves;
				$ret = $gen->checkProductExists((int)$ttt->new_id,$record2->fields['products_model']);
				
				if ($ret===false) 
				{
					$to_message.= $to_name.'<br />';
					
					$db->Execute("DELETE FROM " . TABLE_TMP_PRODUCTS . " WHERE products_id = ? ",array((int)$ttt->new_id));
					
				}
				
			}
			
			array_push($products_arr,$s);
			$i++;
		}
		
	  }
		if ($to_message!='') $exsisting= '-duplicates-'.'<br /><br />'.$to_message;
		
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:setStepTwo_bottom')) ? eval($plugin_code) : false;
	   return $exsisting;
	}
	
	function _set_tmp($data) {
		global $db,$language,$xtPlugin;
		if ($this->position != 'admin') return false;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_set_tmp_top')) ? eval($plugin_code) : false;
		$obj = new stdClass;

		$data['date_added'] = $db->BindTimeStamp(time());


		// UNSET SOME FIELDS;
		$exclude_fields = array( 'flag_has_specials', 'price_flag_graduated_all');
			
        $data['products_status'] = 0;
        $data['main_products_id'] = $this->getProductsId();
		$oP = new adminDB_DataSave(TABLE_TMP_PRODUCTS, $data);
		$oP->setExcludeFields($exclude_fields);
		
		$objP = $oP->saveDataSet();
		
		
		$obj->new_id = $objP->new_id;
		if ($objP->success) {
			$obj->success = true;
		} else {
			$obj->failed = true;
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_set_tmp_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}
	
	
	function SaveProducts($record_ids,$products_model)
	{
		global $db,$language,$xtPlugin;
		if ($this->position != 'admin') return false;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:SaveProducts_top')) ? eval($plugin_code) : false;
		$obj = new stdClass;
		
		$r = $db->Execute("SELECT * FROM " . TABLE_TMP_PRODUCTS . " WHERE products_id in (?) ",array(substr_replace($record_ids ,"",-1)));
		
		if($r->RecordCount() > 0)
		{
			while(!$r->EOF)
			{
				$data = $r->fields;
				$data['date_added'] = $db->BindTimeStamp(time());
				$data['products_id'] = '';
				$data['products_status'] = 0;
				$data['products_model'] = $products_model;
				$exclude_fields = array('products_id','products_image', 'flag_has_specials', 'price_flag_graduated_all');
				$oP = new adminDB_DataSave(TABLE_PRODUCTS, $data);
				$oP->setExcludeFields($exclude_fields);
				$objP = $oP->saveDataSet();
				
	
				if ($objP->new_id) 
				{
					$obj->new_id = $objP->new_id;
					$data[$this->master_id] = $objP->new_id;
					$data['products_id'] =$objP->new_id;
				}
				
				foreach ($language->_getLanguageList() as $key => $val) {

					$data['products_name_'.$val['code']] = trim($data['products_name']);

				}

				$oPD = new adminDB_DataSave(TABLE_PRODUCTS_DESCRIPTION, $data, true);
				$objPD = $oPD->saveDataSet();
		
				$r->MoveNext();
			}$r->Close();
		}
		
		
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:SaveProducts_bottom')) ? eval($plugin_code) : false;
		return $obj;
	
	}
	
	
	function generateCodes($arr,$codes,$pos) 
	{	global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:generateCodes_top')) ? eval($plugin_code) : false;
		if (count($arr)) 
		{
			for ($i = 0, $c=count($arr[key($arr)]); $i < $c; $i++) {
			
				$tmp = $arr;
				$codes[key($arr)] = $arr[key($arr)][$i];
				
				$tarr = array_shift($tmp);
				$pos++;
				$this->generateCodes($tmp,$codes,$pos);
			}
		} 
		else 
		{
			array_push($this->final_arr,$codes);
		}
		$pos--;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:generateCodes_bottom')) ? eval($plugin_code) : false;
	}
	
	
	function getUniqueCombinations(array $keyValues)
	{	global $xtPlugin;
		if (count($keyValues) == 0) {
		   
			return array();
		}
		$iterators = array();
		$total = 1;
		$keys = array_keys($keyValues);
	   
		foreach ($keyValues as $key => $values) {
			$iterators[$key] = 0;
			$total = $total * count($values);
		}
		$i = 0;
		$combinations = array();
		while ($i < $total) {
			$combination = array();
			foreach ($keyValues as $key => $values) {
				$combination[$key] = $values[$iterators[$key]];
			}
			$combinations[] = $combination;
			foreach ($keys as $key) {
				if ($iterators[$key] + 1 >= count($keyValues[$key])) {
					/* this means we need to increment the next key
					   iterator and reset this one to 0 */
					$iterators[$key] = 0;
					continue;
				} else {
					/* we're moving on to the next one for this key */           
					$iterators[$key]++;
					break;
				}
			}
			$i++;
		}

		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getUniqueCombinations')) ? eval($plugin_code) : false;
		return $combinations;
	}
	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
		
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_get_top')) ? eval($plugin_code) : false;
		 $this->url_data['parentNode'] = 'generate_slaves_Step2';
		 //$data = array();
		if ($this->position != 'admin') return false;
		
		$obj = new stdClass;
	
		if ($ID === 'new') {
			   $obj = $this->_set(array(), 'new');
			   $ID = $obj->new_id;
		}
		
		$sql_where='';
		if ($this->url_data['get_data']&& $this->url_data['query']) {
			
			$tmp_search_result = $this->getSearch($this->url_data['query']);
			if ($tmp_search_result!=null)
				$sql_where = " and products_id IN (".implode(',', $tmp_search_result).")";
			else $sql_where = " and products_id IN (null)";
			
		}

		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key,' main_products_id= '.$this->url_data['products_id'].' and saved=0 '.$sql_where);
		
		
		//or $this->url_data['products_id']
		if ($this->url_data['get_data'] ){
			$data = $table_data->getData();	
			
			if(is_array($data)){
				foreach ($data as $key => $val) {                     
					$t = new product($data[$key]['products_id']);
					$data[$key]['products_price'] = $t->build_price($data[$key]['products_id'], $data[$key]['products_price'], $data[$key]['products_tax_class_id']);
				}
			}			
			
		}elseif($ID){
			$data = $table_data->getData($ID);
		}else{
			$data = $table_data->getHeader();
		}
	   if (count($data)==0) $data="[]";
	   
		if($table_data->_total_count!=0 || !$table_data->_total_count)
			$count_data = $table_data->_total_count;
		else
			$count_data = count($data);
			
		
		if ($count_data>0)
		{
			for($i=0;$i<$count_data;$i++)
			{
				$r = $db->Execute("SELECT pad.attributes_name AS attributes_name_slave, padp.attributes_name AS attributes_name_parent FROM " . TABLE_TMP_PRODUCTS_TO_ATTRIBUTES . " tpa 
										INNER JOIN ". TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION ." pad ON pad.attributes_id = tpa.attributes_id
										INNER JOIN ". TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION ." padp ON padp.attributes_id = tpa.attributes_parent_id
										WHERE tpa.products_id = ? and  pad.language_code = ? and  
										padp.language_code = ?",
										array($data[$i]['products_id'],$language->code,$language->code));
	
				
				$j=1;
				if($r->RecordCount() > 0)
				{
					while(!$r->EOF)
					{
						
						//$data[$i][$r->fields['attributes_name_parent']] = $r->fields['attributes_name_slave'];
						$data[$i]['attribute_'.$j] = $r->fields['attributes_name_slave'];
						
						$j++;
						$r->MoveNext();
					}$r->Close();
				}
				
			
			}
		}
		
		$obj->totalCount = $count_data;
		$obj->data = $data;
		
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_get_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}

	function _set($data1, $set_type = 'edit') {
		global $db,$language,$filter,$seo,$price,$tax,$xtPlugin;

		 $obj = new stdClass;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_set_top')) ? eval($plugin_code) : false;
		foreach ($data1 as $key => $val) {

			if($val == 'on')
			   $val = 1;
			   
			if ( ($key=='products_model') || ($key=='products_name') || ($key=='products_quantity')|| ($key=='products_weight')|| ($key=='products_price')|| ($key=='name_changed'))
			{
				if ($key=='products_price') 
				{
					if (_SYSTEM_USE_PRICE=='true'){
						$t = new product($data['products_id']);
						$r = $db->Execute("SELECT products_tax_class_id FROM " . TABLE_TMP_PRODUCTS." WHERE products_id = ?",array($data['products_id']));
						if($r->RecordCount() > 0)
						{
							$tax_percent = $tax->data[$r->fields['products_tax_class_id']];
							$val = $price->_removeTax(floatval ($val), $tax_percent);
						}
					}
				}
				$r = $db->Execute("UPDATE ".$this->_table." SET ".$key."=? WHERE products_id = ? ",
                array(html_entity_decode($val),$data['products_id']));
				
			}
				$data[$key] = $val;
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_set_bottom')) ? eval($plugin_code) : false;
		return true;
	}

	function _setImage($id, $file) {
		global $xtPlugin,$db,$language,$filter,$seo;
		if ($this->position != 'admin') return false;

		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_setImage_top')) ? eval($plugin_code) : false;

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

		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_setImage_bottom')) ? eval($plugin_code) : false;
		return $obj;
	}		
	
	function _setStatus($id, $status) {
		global $db,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_setStatus')) ? eval($plugin_code) : false;
		$id = (int)$id;
		if (!is_int($id)) return false;

		$db->Execute("update ". TABLE_PRODUCTS ." set status = ? where ".$this->_master_key." = ?",array($status,$id));

	}	
	
	function _unset($id = 0) {
	    global $db,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_unset_top')) ? eval($plugin_code) : false;
	    if ($id == 0) return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));
	    if ($this->_table_lang !== null)
	    $db->Execute("DELETE FROM ". $this->_table_lang ." WHERE ".$this->_master_key." = ?",array($id));
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:_unset_bottom')) ? eval($plugin_code) : false;
	}

	function getAllAttributesList ($data_array = '', $parent_id = '0', $spacer = '') {
		global $xtPlugin, $db, $language;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAllAttributesList_top')) ? eval($plugin_code) : false;
		if (!is_array($data_array)) $data_array = array();

		$query = "select distinct a.*, ad.* from ".$this->_table." a LEFT JOIN ".$this->_table_lang." ad ON a.".$this->_master_key." = ad.".$this->_master_key." 
		          where ad.language_code = ? and a.attributes_parent = ? order by ad.attributes_name ";

		$record = $db->Execute($query,array($language->code,(int)$parent_id));
		if($record->RecordCount() > 0){
			while(!$record->EOF){

				$tmp_data = array();
				$tmp_data = $record->fields;

				$tmp_data['attributes_name'] =  $spacer.$tmp_data['attributes_name'];
				$tmp_data['text'] = $tmp_data['attributes_name'];
				$tmp_data['id'] = $tmp_data[$this->_master_key];

				$data_array[] = $tmp_data;

				if ($tmp_data[$this->_master_key] != $parent_id) {
					$data_array = $this->getAllAttributesList($data_array, $tmp_data[$this->_master_key], $spacer . '&nbsp;&nbsp;');
				}

				$record->MoveNext();
			}$record->Close();
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAllAttributesList_bottom')) ? eval($plugin_code) : false;
		return $data_array;
	}

	function getAllParentAttributesList() {
		global $xtPlugin, $db, $language;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAllParentAttributesList_top')) ? eval($plugin_code) : false;

		$query = "select distinct a.*, ad.* from ".$this->_table." a LEFT JOIN ".$this->_table_lang." ad ON a.".$this->_master_key." = ad.".$this->_master_key." 
		          where ad.language_code = ? and a.attributes_parent = '0' order by ad.attributes_name ";

		$record = $db->Execute($query,array($language->code));
		if($record->RecordCount() > 0){
			while(!$record->EOF){

				$tmp_data = array();
				$tmp_data = $record->fields;

				$tmp_data['attributes_name'] = $tmp_data['attributes_name'];
				$tmp_data['text'] = $tmp_data['attributes_name'];
				$tmp_data['id'] = $tmp_data[$this->_master_key];

				$data_array[] = $tmp_data;

				$record->MoveNext();
			}$record->Close();
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAllParentAttributesList_bottom')) ? eval($plugin_code) : false;
		return $data_array;
	}

	function getAttribTree () 
	{	global $xtPlugin;
		$data = array();
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAttribTree_top')) ? eval($plugin_code) : false;
		if(is_array($_POST) && array_key_exists('query', $_POST)){
			$_data = $this->getAllAttributesList ($data_array = '', $parent_id = '0', $spacer = ' ');
			foreach ($_data as $adata) {
				$data[] =  array('id' => $adata['attributes_id'],
	                             'name' => $adata['attributes_name'],
	                             'desc' => $adata['attributes_description']);
	
			}
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAttribTree_bottom')) ? eval($plugin_code) : false;
		return $data;
	}

	function getAttribParent () 
	{	global $xtPlugin;
		$data = array();
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAttribParent_top')) ? eval($plugin_code) : false;
		if(is_array($_POST) && array_key_exists('query', $_POST)){
				$data[] =  array('id' => '',
                         'name' => TEXT_EMPTY_SELECTION,
                         'desc' => '');

		$_data = $this->getAllParentAttributesList ();
		foreach ($_data as $adata) {
            if ($adata['attributes_name']!=null) {
			$data[] =  array('id' => $adata['attributes_id'],
                             'name' => $adata['attributes_name'],
                             'desc' => $adata['attributes_description']);
            }
		}
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAttribParent_bottom')) ? eval($plugin_code) : false;
		return $data;
	}
	
	function getAttributeTemplate () 
	{	global $xtPlugin;
		$data = array();
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAttributeTemplate_top')) ? eval($plugin_code) : false;
		if(is_array($_POST) && array_key_exists('query', $_POST)){
				$data[] =  array('id' => '',
                         'name' => TEXT_EMPTY_SELECTION,
                         );

		$_data = $this->getAllTemplates ();
		foreach ($_data as $adata) {
            if ($adata['attributes_templates_id']!=null) {
			$data[] =  array('id' => $adata['attributes_templates_id'],
                             'name' => $adata['attributes_templates_name']
                             );
            }
		}
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAttributeTemplate_bottom')) ? eval($plugin_code) : false;
		return $data;
	}
	
	
	function getAllTemplates() {
		global $xtPlugin, $db, $language;

		$query = "select * from ".TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES." ";

		$record = $db->Execute($query);
		if($record->RecordCount() > 0){
			while(!$record->EOF){

				$tmp_data = array();
				$tmp_data = $record->fields;

				$tmp_data['attributes_templates_name'] = $tmp_data['attributes_templates_name'];
				$tmp_data['id'] = $tmp_data['attributes_templates_id'];

				$data_array[] = $tmp_data;

				$record->MoveNext();
			}$record->Close();
		}
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getAllTemplates')) ? eval($plugin_code) : false;
		return $data_array;
	}
	function getProductsMaster() {
		global $xtPlugin, $db;
		($plugin_code = $xtPlugin->PluginCode('class.generate_slaves.php:getProductsMaster_top')) ? eval($plugin_code) : false;
		$data = array();
		
		if(is_array($_POST) && array_key_exists('query', $_POST)){
		
			$this->sql_products = new getProductSQL_query();
			$this->sql_products->setPosition('getMasterModels');
			//		$this->sql_products->setSQL_COLS(" as id, p.products_model as name, pd.products_name as desc");
			$this->sql_products->setFilter('Language');
			$this->sql_products->setSQL_WHERE("and p.products_model != '' and p.products_master_flag = '1' ");
			$this->sql_products->setSQL_SORT(' p.products_model ASC');
			
			$query = "".$this->sql_products->getSQL_query("p.products_id, p.products_model as name, pd.products_name")."";
	
			$data[] =  array('id' => '',
	                         'name' => TEXT_EMPTY_SELECTION,
	                         'desc' => '');
	
	
			$record = $db->Execute($query);
			if($record->RecordCount() > 0){
				while(!$record->EOF){
					$fields = $record->fields;
					$fields['id'] = $fields['name'];
					$fields['desc'] = $fields['products_name'];
					unset($fields['products_id']);
					$data[] = $fields;
					$record->MoveNext();
				}$record->Close();
	
				return $data;
			}else{
				return false;
			}
		
		}else{
			return $data;
		}
	}

}

?>