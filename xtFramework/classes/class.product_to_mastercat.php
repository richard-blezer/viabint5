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
class product_to_mastercat extends product {

	public $_table = TABLE_PRODUCTS_TO_CATEGORIES;
	public $_table_lang = TABLE_CATEGORIES_DESCRIPTION;
	public $_table_seo = null;
	public $_master_key = 'categories_id';
    protected $_icons_path = "images/icons/";
	public $_store_id = '';

    function __construct() {
       parent::__construct();
       $this->indexID = time().'-Prod2Cat';

       $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
	   if ($_GET['store_id']){
	   	$add_to_url.= '&store_id='.$_GET['store_id'];
		$this->_store_id = $_GET['store_id'];
	   }
	  
       $this->getTreeUrl = 'adminHandler.php?load_section=product_to_mastercat&pg=getNode'.$add_to_url.'&';
       $this->getSaveUrl = 'adminHandler.php?load_section=product_to_mastercat&pg=setData'.$add_to_url.'&';
	  
    }

	function setPosition ($position) {
		$this->position = $position;
	}

	function setProductsId ($id) {
	    $this->pID = $id;
	}
	function getProductsId () {
        return $this->pID;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
		}
		$store_field ='';
		$add_sql='';
		if (StoreIdExists(TABLE_CATEGORIES_DESCRIPTION,'categories_store_id')){
			if ($_GET['store_id']){
				$add_sql= ' and categories_store_id = '.$_GET['store_id'];
			}
			$store_field = 'categories_store_id';
		}
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, 'master_link=1 and products_id='.$this->getProductsId().$add_sql, '', '','','',$store_field);
		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}elseif($this->url_data['edit_id']){
			$data = $table_data->getData($this->url_data['edit_id']);
		}else{
			$data = $table_data->getHeader();
		}

		$obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;

        return $obj;
	}

/* SetElement
 *
 * Assign product to category
 *
 * @param (int) ($products_id) - product to assign
 * @param (string) ($catIds) - categories to which the product will be assigned 
 * @param (int) $store_id - store id
 * 
 */
	function SetElement($products_id,$cat_ids,$store_id)
	{	global $db;
		$obj = new stdClass;
		$add_sql='';
		if (StoreIdExists($this->_table,'store_id')) 
					$add_sql= ' and store_id = '.$store_id;
		$db->Execute("DELETE FROM " . $this->_table . " WHERE master_link=1 and products_id = ? ".$add_sql, array($products_id));
    	if ($cat_ids) {
            $data = array($this->_master_key => (int)$cat_ids, 'products_id' => $products_id, 'master_link'=>1, 'store_id'=>$store_id);
			$rs = $db->Execute(
				"SELECT * FROM " . $this->_table . " WHERE master_link=0 and products_id = ? and ".$this->_master_key."=? ".$add_sql,
				array($products_id, (int)$cat_ids[0])
			);
	     	if ($rs->RecordCount() > 0){
	     		$db->Execute(
					"DELETE FROM " . $this->_table . " WHERE master_link=0 and products_id = ? and ".$this->_master_key."=? ".$add_sql,
					array($products_id, (int)$cat_ids[0])
				);
	     	}
	     	$db->AutoExecute($this->_table, $data,'INSERT');
		    $obj->new_id = $db->Insert_ID();
			$obj->success = true;
         }	
		return $obj;
	}

	function setData($dont_die=FALSE) {
        global $db;

		$obj = new stdClass;
		
        if ($this->url_data['catIds'] && $this->url_data['products_id']) {
           
            $this->url_data['catIds'] = str_replace(array('[',']','"','\\'), '', $this->url_data['catIds']);
	        $cat_ids_all = preg_split('/,/', $this->url_data['catIds']);
			$cat_ids = array();
			$all_stores = array();
			$too_many_selected = false;
			foreach($cat_ids_all as $a){
				$expl = explode("_",$a);
				if ( in_array($expl[0],$all_stores)) $too_many_selected = true; 
				else array_push($all_stores,$expl[0]);
				array_push($cat_ids,$expl[1]); 
			}
			
			if ($too_many_selected) {
				echo json_encode(count($cat_ids));
				die;
			}

			foreach($cat_ids_all as $a){
				$expl = explode("_",$a);
				$obj = $this->SetElement($this->url_data['products_id'],$expl[1],$expl[0]);
			}
	    }

        if( $dont_die )
          return json_encode($obj);
		
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        echo json_encode($obj);
        die;
	}

	function getTreePanel() {
		if ($this->url_data['products_id'])
		$this->setProductsId($this->url_data['products_id']);
		
		$st = new multistore();
		$stores = $st->getStores();
	
		$root = new PhpExt_Tree_AsyncTreeNode();
    	$root->setText('Categories')
          	->setId('croot');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->getTreeUrl);
        if ($this->getProductsId())
        $tl->setBaseParams(array('products_id' => $this->getProductsId()));

        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('TEXT_PRODUCT_TO_MASTERCAT'))
          ->setRoot($root)
          ->setLoader($tl)
         // ->setRootVisible(false)
          ->setAutoScroll(true)
     //     ->setCollapsible(true)
          ->setAutoWidth(true);
        $savebutton = _SYSTEM_SAVEBUTTON_POSITION;
        if($savebutton == 'bottom' || $savebutton == 'both'){
            $tb = $tp->getBottomToolbar();

                $tb->addButton(1,__define('TEXT_SAVE'), $this->_icons_path.'disk.png',new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));
                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: '".$this->getSaveUrl."',
                 method:'POST',
                 params: {'products_id': ".$this->getProductsId().", catIds: checked},
                 error: function(responseObject) {
                            Ext.Msg.alert('".__define('TEXT_ALERT')."', '".__define('TEXT_NO_SUCCESS')."');
                          },
                 waitMsg: 'SAVED..',
                 success: function(responseObject) {
                            if (responseObject.responseText>1)
							{
								Ext.Msg.alert('".__define('TEXT_ALERT')."','".__define('TEXT_MORE_THAN_ONE_MAIN_CATEGORY_SELECTED')."');
							}
							else 
							{
								Ext.Msg.alert('".__define('TEXT_ALERT')."','".__define('TEXT_SUCCESS')."');
							}
                          }
                 });")));
        }
        
        if($savebutton == 'top' || $savebutton == 'both'){
            $tt = $tp->getTopToolbar();
            $tt->addButton(1,__define('TEXT_SAVE'), $this->_icons_path.'disk.png',new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));
                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: '".$this->getSaveUrl."',
                 method:'POST',
                 params: {'products_id': ".$this->getProductsId().", catIds: checked},
                 error: function(responseObject) {
                            Ext.Msg.alert('".__define('TEXT_ALERT')."', '".__define('TEXT_NO_SUCCESS')."');
                          },
                 waitMsg: 'SAVED..',
                 success: function(responseObject) {
                            Ext.Msg.alert('".__define('TEXT_ALERT')."','".__define('TEXT_SUCCESS')."');
                          }
                 });")));
        }
       $tp->setRenderTo(PhpExt_Javascript::variable("Ext.get('".$this->indexID."')"));

        $js = PhpExt_Ext::OnReady(
            PhpExt_Javascript::stm(PhpExt_QuickTips::init()),

            $root->getJavascript(false, "croot"),
        	$tp->getJavascript(false, "tree")

        );

        return '<script type="text/javascript">'. $js .'</script><div id="'.$this->indexID.'"></div>';

	}
	
	function getStoresNode() {
		if ($this->url_data['products_id'])
		$this->setProductsId($this->url_data['products_id']);
		//if ($this->url_data['node']=='store_1') {return $this->getNode(); }
		$store= new multistore();
		$stores = $store->getStores();
		
        if(is_array($stores)){
	        foreach ($stores as $st) {
	            $expanded = false;
	            if ($_GET['store_id']==$st['id']){
	                $expanded = true;
	            }
	            $this->getTreeUrl =  $this->getTreeUrl.'&current_store='.$st['id'];
	            $new_cats[] = array('id' => 'store_'.$st['id'], 'text' => $st['text'], 'expanded' => $expanded);
	        }
        }
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        return json_encode($new_cats);
	}

	function getNode() {
		if ($this->url_data['products_id'])
		$this->setProductsId($this->url_data['products_id']);
		if ($this->url_data['node']=='croot') {
			return $this->getStoresNode();
		}
		$add_sql='';
		$add_sql2='';
		$ad_table='';$add_to_display_key='';
		$add_to_id = '';
		if (StoreIdExists(TABLE_CATEGORIES_DESCRIPTION,'categories_store_id')){
			
			$ar = explode("store_",$this->url_data['node']);
			
		//	if ($_GET['store_id']){
			if (count($ar)==2){
				$store_id = $ar[1];
				$add_sql= ' and categories_store_id = '.$store_id;
				$add_sql2 = ' and store_id = '.$store_id;
				$ad_table =' INNER JOIN '.TABLE_CATEGORIES_DESCRIPTION.' d ON c.'.$this->_master_key.'=d.'.$this->_master_key;
				$ad_table .= " left JOIN " . TABLE_CATEGORIES_PERMISSION . " pm".$store_id."
					ON (pm".$store_id.".pid = c.categories_id and pm".$store_id.".pgroup = 'shop_".$store_id."') ";
				
				if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
					$add_sql .= " and pm".$store_id.".permission IS NULL";
				}elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
					$add_sql .= " and pm".$store_id.".permission = 1";
				}
				$add_to_display_key='_store'.$store_id;
				$add_to_id = $store_id.'_';
		
			}else {
				list($store_id,$parent_id) = explode("_",$this->url_data['node']);
                $add_to_display_key='_store'.$store_id;
				$add_to_id = $store_id.'_';
				$ad_table .= " left JOIN " . TABLE_CATEGORIES_PERMISSION . " pm".$store_id."
                    ON (pm".$store_id.".pid = c.categories_id and pm".$store_id.".pgroup = 'shop_".$store_id."') ";
                 if(_SYSTEM_GROUP_PERMISSIONS=='blacklist'){
                    $add_sql .= " and pm".$store_id.".permission IS NULL";
                }elseif(_SYSTEM_GROUP_PERMISSIONS=='whitelist'){
                    $add_sql .= " and pm".$store_id.".permission = 1";
                }
			}
			
		}
		
		$table_data = new adminDB_DataRead($this->_table, null, null, $this->_master_key, 'master_link=1 and products_id='.$this->getProductsId().$add_sql2);

		$d = new recursive(TABLE_CATEGORIES.' c', $this->_master_key);
	
		$categoriesData = $table_data->getData();
		$expand = array();
		if(is_array($categoriesData)){
			foreach ($categoriesData as $cdata) {
			     if ($store_id==$cdata['store_id']){
			        $path = $d->getPath($cdata[$this->_master_key]);
                    $expand = array_merge($expand, $path);
                    $cat_ids[] = $cdata[$this->_master_key];
                }
			}
		}

        $d->setLangTable(TABLE_CATEGORIES_DESCRIPTION);
        $d->setDisplayKey('categories_name'.$add_to_display_key);
        $d->setDisplayLang(true);
       
    	if (StoreIdExists(TABLE_CATEGORIES_DESCRIPTION,'categories_store_id')) {
    		$d->setStoreID('categories_store_id');
		}
		
		$d->setJoinedTable($ad_table);
		$d->setWhereQuery($add_sql);
		
		$a = explode("_",$this->url_data['node']);
		if (count($a)==2) {
			if ($a[0]!='store')
				$node = $a[1];
			else $node = $this->url_data['node'];
		}
		else $node = $this->url_data['node'];
		
        $data = $d->_getLevelItems($node);

        if(is_array($data)){
	        foreach ($data as $cat_data) {
	            $checked = false;
	            if(is_array($cat_data)&&is_array($cat_ids)){
		            if (in_array($cat_data[$this->_master_key], $cat_ids)) {
		                $checked = true;
		            }
	            }

	            $expanded = false;
	            if (in_array($cat_data[$this->_master_key], $expand)) {
	                $expanded = true;
	            }
	            $new_cats[] = array('id' => $add_to_id.$cat_data[$this->_master_key], 'text' => $cat_data[$d->getDisplayKey()], 'checked' => $checked, 'expanded' => $expanded);
	        }
        }
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        return json_encode($new_cats);
	}
}