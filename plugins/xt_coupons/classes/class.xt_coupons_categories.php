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
  class xt_coupons_categories {
    protected $_table = TABLE_COUPONS_CATEGORIES;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'pk';
    protected $couponID = null;
    protected $nodeUrl = 'adminHandler.php?plugin=xt_coupons&load_section=xt_coupons_categories&pg=getNode&';
    protected $saveUrl = 'adminHandler.php?plugin=xt_coupons&load_section=xt_coupons_categoris&pg=setData&';
    protected $_icons_path = 'images/icons/';

    function __construct() {
      // parent::__construct();
      $this->indexID = time().'-coup2cat';
      $this->nodeUrl = 'adminHandler.php?plugin=xt_coupons&load_section=xt_coupons_categories&pg=getNode&';
      $this->saveUrl = 'adminHandler.php?plugin=xt_coupons&load_section=xt_coupons_categories&pg=setData&';
      
    
    }
    
    function setPosition ($position) {
        $this->position = $position;
    }
    
    function _getParams() {


        $params = array();

        $header['pk'] = array('type' => 'hidden');
        $params['header']         = $header;
        $params['display_searchPanel']  = true;
        $params['master_key']     = $this->_master_key;
       
        return $params;
    }
    
    function _get($ID = 0) {
      global $xtPlugin, $db, $language;
        $obj = new stdClass;
        if ($this->position != 'admin') return false;

        if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
        }

        
        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key);

        if ($this->url_data['get_data']){
            $data = $table_data->getData();
        }elseif($ID){
            $data = $table_data->getData($ID);
        }else{
            $data = $table_data->getHeader();
        }
        if($table_data->_total_count!=0 || !$table_data->_total_count)
        $count_data = $table_data->_total_count;
        else
        $count_data = count($data);

        $obj->totalCount = $count_data;
        $obj->data = $data;

        return $obj;
    }

    function _set($data, $set_type='edit'){
        global $db,$language,$filter;

        if($set_type=='new'){
            $data['products_id'] = (int)$this->url_data['products_id'];
        }


        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        $obj->success = true;

        return $obj;
    }


    function _unset($id = 0) {
        global $db;
        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id=(int)$id;
        if (!is_int($id)) return false;

        $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ? ",array($id));

    }
    
    function setCouponId ($id) {
        $this->couponID = $id;
    }
    function getCouponId () {
        return $this->couponID;
    }

        function getTreePanel() {     
        if ($this->url_data['coupon_id'])
        $this->setCouponId($this->url_data['coupon_id']);
        $root = new PhpExt_Tree_AsyncTreeNode();
        $root->setText("Category")
              ->setId('croot');

        $tl = new PhpExt_Tree_TreeLoader();
        $tl->setDataUrl($this->nodeUrl);
        if ($this->getCouponId())
        $tl->setBaseParams(array('coupon_id' => $this->getCouponId()));



        $tp = new PhpExt_Tree_TreePanel();
        $tp->setTitle(__define('TEXT_COUPON_CATEGORIES'))
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
                 url: '".$this->saveUrl."',
                 method:'POST',
                 params: {'coupon_id': ".$this->getCouponId().", catIds: checked},
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
        if ($this->url_data['coupon_id'])
        $this->setCouponId($this->url_data['coupon_id']);

        $table_data = new adminDB_DataRead($this->_table, null, null, $this->_master_key, 'coupon_id='.$this->getCouponId());

        $d = new recursive(TABLE_CATEGORIES, 'categories_id'); //$this->_master_key);

        $categoriesData = $table_data->getData();
        $expand = array();
        if(is_array($categoriesData)){
            foreach ($categoriesData as $cdata) {
                $path = $d->getPath($cdata['categories_id']);


                $expand = array_merge($expand, $path);
                $cat_ids[] = $cdata['categories_id'];
            }
        }

        $d->setLangTable(TABLE_CATEGORIES_DESCRIPTION);
        $d->setDisplayKey('categories_name');
        $d->setDisplayLang(true);
        $data = $d->_getLevelItems($this->url_data['node']);

        if(is_array($data)){
            foreach ($data as $cat_data) {
                $checked = false;
                if(is_array($cat_data)&&is_array($cat_ids)){
                    if (in_array($cat_data['categories_id'], $cat_ids)) {
                        $checked = true;
                    }
                }

                $expanded = false;
                if (in_array($cat_data['categories_id'], $expand)) {
                    $expanded = true;
                }
                $new_cats[] = array('id' => $cat_data['categories_id'], 'text' => $cat_data[$d->getDisplayKey()], 'checked' => $checked, 'expanded' => $expanded);
            }
        }
        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        return json_encode($new_cats);
    }
    
    function setData($dont_die=FALSE) {
        global $db;
        $obj = new stdClass;
        if ($this->url_data['catIds'] && $this->url_data['coupon_id']) {
            $db->Execute("DELETE FROM " . $this->_table . " WHERE coupon_id = ?",array($this->url_data['coupon_id']));

            $this->url_data['catIds'] = str_replace(array('[',']','"','\\'), '', $this->url_data['catIds']);
            $cat_ids = split(',', $this->url_data['catIds']);

            for ($i = 0; $i < count($cat_ids); $i++) {


                if ($cat_ids[$i]) {
                $data = array('categories_id' => (int)$cat_ids[$i], 'coupon_id' => $this->url_data['coupon_id']);
                    $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
                    $obj = $o->saveDataSet();

                }
            }
        }

        if( $dont_die===true )
          return $obj;

        header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
        echo json_encode($obj);
        die;
//        return
    }    
    
  }
?>