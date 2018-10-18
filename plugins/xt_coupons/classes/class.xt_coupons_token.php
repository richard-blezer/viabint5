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

 
  class xt_coupons_token extends ExtFunctions{
    protected $_table = TABLE_COUPONS_TOKEN;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'coupons_token_id';

    function setPosition ($position) {
        $this->position = $position;
    }
    
    function _getParams() {


        $params = array();

        $header['coupons_token_id'] = array('type' => 'hidden');
        $header['coupon_id'] = array('type' => 'dropdown',
                                       'url' => 'DropdownData.php?get=coupon&plugin_code=xt_coupons'); 
         $header['coupon_token_order_id'] = array('readonly' => 1);

        $params['header']         = $header;
        $params['display_searchPanel']  = true;
        
        $params['display_checkCol']  = false;
        $params['master_key']     = $this->_master_key;
        
        // Row_Action Imort
        //$rowActions[] = array('iconCls' => 'coupon_token_im_export', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_COUPON_TOKEN_IM_EXPORT);
        
        $js = '';
        if ($this->url_data['coupon_id'])
          $js .= "var coupon_id = ".$this->url_data['coupon_id'].";";
        else if ($this->url_data['edit_id'])
          $js = "var coupon_id = ".$this->url_data['edit_id'].";";
        //else 
//           $js = "var coupon_id = record.id;";
                     
          $extF = new ExtFunctions();
         $js .= "addTab('adminHandler.php?plugin=xt_coupons&load_section=xt_coupons_token_im_export','".TEXT_COUPON_TOKEN_IM_EXPORT."');";          
        //$rowActionsFunctions['coupon_token_im_export'] = $js;
		$UserButtons['coupon_token_im_export'] = array('text'=>'TEXT_COUPON_TOKEN_IM_EXPORT', 'style'=>'coupon_token_im_export', 'icon'=>'arrow_inout.png', 'acl'=>'edit', 'stm' => $js);
 		$params['display_coupon_token_im_exportBtn'] = true;
                        
        $extF = new ExtFunctions();       
        $js = "addTab('adminHandler.php?plugin=xt_coupons&load_section=xt_coupons_token_generator','".TEXT_COUPON_TOKEN_GENERATE."');";        
        $UserButtons['options_add'] = array('text'=>'TEXT_COUPON_TOKEN_GENERATE', 'style'=>'options_add', 'icon'=>'cog.png', 'acl'=>'edit', 'stm' => $js);
		$params['display_options_addBtn'] = true;
		
		$params['rowActions']             = $rowActions;
        $params['rowActionsFunctions']    = $rowActionsFunctions;

        $params['UserButtons']      = $UserButtons;
       
        $params['display_statusTrueBtn']  = false;
        $params['display_statusFalseBtn']  = false;

        

        return $params;
    }
    
    function helloworld() {
        
        $p = new PhpExt_Panel();
$p->setTitle("My Panel")
  ->setCollapsible(true)
  ->setRenderTo(PhpExt_Javascript::variable("Ext.get('formo_2')"))
  ->setWidth(400);

        
       $form = new PhpExt_Form_FormPanel();
       //$form = $this->_getLabelPos($form);  
 
       
       $data = PhpExt_Form_TextField::createTextField('label', 'name');
       $form->addItem($data);        
       
       $form->setRenderTo(PhpExt_Javascript::variable("Ext.get('formo_div')")); 
       
        $js = PhpExt_Ext::OnReady(
            $p->getJavascript(false, "var_formo")
        );

 
//       return $js;       
       return '<script type="text/javascript">'. $js .'</script><div id="formo_div"></div><div id="formo_2"></div>';       
       return htmlentities('<script type="text/javascript">'. $js .'</script><div id="formo_div"></div><div id="formo_2"></div>');       
       
  
    }
    
    function _get($ID = 0) {
              global $xtPlugin, $db, $language;
        if ($this->position != 'admin') return false;

        $obj = new stdClass;

        if ($ID === 'new') {
               $obj = $this->_set(array(), 'new');
               $ID = $obj->new_id;
        }

        $where='';
        if ($this->url_data['query'])
            $where = $this->_getSearchIDs($this->url_data['query']);
        
        $table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key,$where);

        if ($this->url_data['get_data']){
            $data = $table_data->getData();
        }elseif($ID){
            $data = $table_data->getData($ID);
        }else{
            $data = $table_data->getHeader();
        }
       //$data[0]['TEST TEST'] = 'TEST Inhalt';

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
		if ($data['coupon_token_code']==''){
            $obj->success = false;
            $obj->error_message=TEXT_XT_COUPONS_EMPTY_VAUCHER_CODE;
            return $obj;
        }
		
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        $obj->success = true;

        return $obj;
    }

      function _getSearchIDs($search_data)
      {
          global $filter, $xtPlugin;

          $sql_tablecols = array('coupon_token_code', 'coupon_id','coupon_token_order_id');

          ($plugin_code = $xtPlugin->PluginCode('class.xt_coupons_token.php:_getSearchIDs')) ? eval($plugin_code) : false;

          foreach ($sql_tablecols as $tablecol)
          {
              $sql_where[]= "(".$tablecol." LIKE '%".$filter->_filter($search_data)."%')";
          }

          if (is_array($sql_where))
              $sql_data_array = ' ('.implode(' OR ', $sql_where).')';

          return $sql_data_array;
      }


    function _unset($id = 0) {
        global $db;
        if ($id == 0) return false;
        if ($this->position != 'admin') return false;
        $id=(int)$id;
        if (!is_int($id)) return false;

        $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?",array($id));

    }

   function _setStatus($id, $status) {
        global $db,$xtPlugin;
        $id = (int)$id;
        if (!is_int($id)) return false;

        $db->Execute("update " . $this->_table . " set coupon_token_status = ? where ".$this->_master_key." = ?",array($status,$id));

    }
    
  }
?>