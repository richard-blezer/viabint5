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
class product_to_attributes extends product {

	public $_table = TABLE_PRODUCTS_TO_ATTRIBUTES;
	public $_table_lang = TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION;
	protected $_table_seo = null;
	public $_master_key = 'attributes_id';
    protected $_icons_path = "images/icons/";

    function __construct() {
       parent::__construct();
       $this->indexID = time().'-Prod2Attrib';

       $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';

       $this->getTreeUrl = 'adminHandler.php?plugin=xt_master_slave&load_section=product_to_attributes'.$add_to_url.'&pg=getNode&';
       $this->getSaveUrl = 'adminHandler.php?plugin=xt_master_slave&load_section=product_to_attributes'.$add_to_url.'&pg=setData&';
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

	function setData() {
        global $db;


        if ($this->url_data['attIds'] && $this->url_data['products_id']) {
            $db->Execute("DELETE FROM " . $this->_table . " WHERE products_id = ?",array((int)$this->url_data['products_id']));

            $this->url_data['attIds'] = str_replace(array('[',']','"','\\'), '', $this->url_data['attIds']);
	        $att_ids = split(',', $this->url_data['attIds']);

	        for ($i = 0; $i < count($att_ids); $i++) {

	            if ($att_ids[$i]) {

		        $record = $db->Execute("select attributes_parent from " . TABLE_PRODUCTS_ATTRIBUTES . " where attributes_id = ? ",array((int)$att_ids[$i]));
				if($record->RecordCount() > 0){
					$parent = $record->fields['attributes_parent'];
				}

	            $data = array($this->_master_key => (int)$att_ids[$i], 'attributes_parent_id'=>$parent, 'products_id' => (int)$this->url_data['products_id']);
        	        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        		    $obj = $o->saveDataSet();

	            }
	        }
	    }
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        echo json_encode($obj);
        die;
//        return
	}

	function getTreePanel() {
		if ($this->url_data['products_id'])
		$this->setProductsId($this->url_data['products_id']);
	    $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText("Attributes")
              ->setId('root');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->getTreeUrl);
        if ($this->getProductsId())
        $tl->setBaseParams(array('products_id' => $this->getProductsId()));



        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('TEXT_PRODUCTS_TO_ATTRIBUTES'))
          ->setRoot($root)
          ->setLoader($tl)
 //         ->setRootVisible(false)
          ->setAutoScroll(true)
//          ->setCollapsible(true)
          ->setAutoWidth(true);
         $tb = $tp->getBottomToolbar();

                $tb->addButton(1,__define('TEXT_SAVE'), $this->_icons_path.'disk.png',new PhpExt_Handler(PhpExt_Javascript::stm("
                 var checked = Ext.encode(tree.getChecked('id'));
                 var conn = new Ext.data.Connection();
                 conn.request({
                 url: '".$this->getSaveUrl."',
                 method:'POST',
                 params: {'products_id': ".$this->getProductsId().", attIds: checked},
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

            $root->getJavascript(false, "root"),
        	$tp->getJavascript(false, "tree")

        );


        return '<script type="text/javascript">'. $js .'</script><div id="'.$this->indexID.'"></div>';

	}

	function getNode() {
		if ($this->url_data['products_id'])
		$this->setProductsId($this->url_data['products_id']);

		$table_data = new adminDB_DataRead($this->_table, null, null, $this->_master_key, 'products_id='.$this->getProductsId());

		//__debug($table_data);

		$d = new recursive(TABLE_PRODUCTS_ATTRIBUTES, $this->_master_key, 'attributes_parent');

		$attributesData = $table_data->getData();

	//	__debug($attributesData);

		$expand = array();
		if(is_array($attributesData)){
			foreach ($attributesData as $adata) {
			    $path = $d->getPath($adata[$this->_master_key]);


			    $expand = array_merge($expand, $path);
			    $att_ids[] = $adata[$this->_master_key];
			}
		}

		//__debug($expand);

        $d->setLangTable(TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION);
        $d->setDisplayKey('attributes_name');
        $d->setDisplayLang(true);
        $data = $d->_getLevelItems($this->url_data['node']);
        if(is_array($data)){
	        foreach ($data as $att_data) {
	            $checked = false;
	            if(is_array($att_data)&&is_array($att_ids)){
		            if (in_array($att_data[$this->_master_key], $att_ids)) {
		                $checked = true;
		            }
	            }
	            $expanded = false;
	            if (in_array($att_data[$this->_master_key], $expand)) {
	                $expanded = true;
	            }

				if($att_data['attributes_parent']!=0)
	            $new_atts[] = array('id' => $att_data[$this->_master_key], 'text' => $att_data[$d->getDisplayKey()] . " (" . $att_data['attributes_model'] . ")", 'checked' => $checked, 'expanded' => $expanded);
	            else
	            $new_atts[] = array('id' => $att_data[$this->_master_key], 'text' => $att_data[$d->getDisplayKey()] . " (" . $att_data['attributes_model'] . ")", 'expanded' => $expanded);
	        }
        }
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        return json_encode($new_atts);
	}
}
?>