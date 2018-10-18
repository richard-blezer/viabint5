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

 
  class xt_coupons_redeem {
    protected $_table = TABLE_COUPONS_REDEEM;
    protected $_table_lang = null;
    protected $_table_seo = null;
    protected $_master_key = 'coupons_redeem_id';

    function setPosition ($position) {
        $this->position = $position;
    }
    
    function _getParams() {


        $params = array();

        $header['coupons_redeem_id'] = array('type' => 'hidden');


        $params['display_deleteBtn']  = false;
        $params['display_newBtn']  = false;
        $params['display_searchPanel']  = true;

        $params['header']         = $header;
        $params['master_key']     = $this->_master_key;
        
        $rowActions = array();
        $rowActionsFunctions = array();

        $params['rowActions']             = $rowActions;
        $params['rowActionsFunctions']    = $rowActionsFunctions;

        if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
            $params['include'] = array ('coupon_id, coupon_token_id, redeem_date, redeem_ip, customers_id, order_id, redeem_amount');
         }        

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
            $data['coupons_redeem_id'] = (int)$this->url_data['coupons_redeem_id'];
        }


        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        $obj->success = true;

        return $obj;
    }

      function _getSearchIDs($search_data)
      {
          global $filter, $xtPlugin;

          $sql_tablecols = array('order_id', 'customers_id','coupon_token_id');

          ($plugin_code = $xtPlugin->PluginCode('class.xt_coupons_redeem.php:_getSearchIDs')) ? eval($plugin_code) : false;

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

    
    
  }
?>