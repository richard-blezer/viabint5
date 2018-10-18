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

class xt_coupons_customers extends customer {

	protected $_table_coupons_customers = TABLE_COUPONS_CUSTOMERS;
    public $_table = TABLE_CUSTOMERS;
    public $_table_lang = TABLE_COUPONS_DESCRIPTION;
    public $_table_seo = null;
    public $_master_key = 'customers_id';


	function _getParams() {
		global $language;
        $params = parent::_getParams();
		$params = array();
		$header['coupon_id'] = array('type'=>'hidden');

		$params['display_checkCol']  = true;
		$params['display_editBtn']  = false;
		$params['display_newBtn']  = false;
		$params['display_GetSelectedBtn'] = true;		

		$params['display_searchPanel']  = true;		
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;


		if($this->url_data['pg']=='overview' && !$this->url_data['edit_id'] && $this->url_data['new'] != true){
            $params['include'] = array ('customers_id','customers_gender', 'customers_company', 'customers_email_address','customers_firstname', 'customers_lastname');
            
		}

		return $params;
	}

	function _getIDs($id) {
		global $xtPlugin, $db, $language, $seo;
		$query = "select customers_id from ".$this->_table_coupons_customers." where coupon_id = ? ";
		$record = $db->Execute($query,array((int)$id));
		if ($record->RecordCount() > 0) {
			while(!$record->EOF){
				$records = $record->fields;
				$data[] = $records['customers_id'];
				$record->MoveNext();
			} $record->Close();
		}

		return $data;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;
        
        $customers_selected = $this->_getIDs($this->url_data['coupon_id']);
        if(!is_array($customers_selected)) {
           $customers_selected = Array();   
        }
        if (count($customers_selected)>0) $im = implode(",",$customers_selected);
		else $im='';
		$p_data = parent::_get($ID, $im);
        $data = array();
        if(is_array($p_data->data)) {
            foreach($p_data->data as $key => $item) {
                if ($this->url_data['query']) {
                    if(!in_array($item['customers_id'], $customers_selected)) {
//                      $data[$key] = $item;
                      $data[] = $item;
                    }
                } else {
                    if(in_array($item['customers_id'], $customers_selected)) {
//                      $data[$key] = $item;
                      $data[] = $item;
                    }
                    
                }
            }
        }

        $obj = new stdClass;     
        if (count($data) > 0) {
          $obj->totalCount = count($data);
          $obj->data = $data;        
        } else if (!$this->url_data['get_data']){
          $obj->totalCount = 0;
          $obj->data[] = array('customers_id'=>'',
                                'customers_gender' => '',
                                'customers_firstname' => '',
                                'customers_lastname' => '',
                                'customers_company' => '',
                                'customers_city' => '',
                                'customers_email_address' => '',
                                'shop_id' => '',
                                'date_added' => ''
                                );
        } else {
          $obj->totalCount = 0;
          $obj->data = NULL;                    
        }
        
                                                    
        return $obj;
     
        
		if ($this->position != 'admin') return false;
		
		if(!$this->url_data['query']){

			if ($this->url_data['get_data']){
	
				$search_result = $this->_getIDs($this->url_data['coupon_id']);
	
				if(is_array($search_result) && count($search_result != 0)){
					$sql_where = " customer_id IN (".implode(',', $search_result).")";

					$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $sql_where, '', $this->perm_array);
					$data = $table_data->getData();
				}else{
					$data = array();
				}
			}else{
				$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, '', '', $this->perm_array);
				$data = $table_data->getHeader();
			}

		}else{
		
			$customers_selected = $this->_getIDs((int)$this->url_data['coupon_id']);
            if (is_array($customers_selected) && (count($customers_selected) > 0)) {
              $sql_where .= " custommers_id not in(".implode(',',$customers_selected).') and ';     
            }
            
	
					    
			if ($this->url_data['query']) {
				$search_result = $this->_getSearchIDs($this->url_data['query']);
				if(is_array($search_result) && count($search_result)>0){
					$sql_where .= " customers_id IN (".implode(',', $search_result).")";
				}
			}
	
			if (!isset($this->sql_limit)) {
				$this->sql_limit = "0,25";
			}			

            $table_data = new adminDB_DataRead('xt_customers', '', '', 'customers_id', $sql_where, $this->sql_limit, $this->perm_array);
	
			if ($this->url_data['get_data']){
				$data = $table_data->getData();
			}else{
				$data = $table_data->getHeader();
			}		
		
		}
		
		if($table_data->_total_count!=0 || !$table_data->_total_count)
		$count_data = $table_data->_total_count;
		else
		$count_data = count($data);

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}

	function _set($id, $set_type = 'edit') {
		global $db,$language,$filter;
		 $data = array();
		 $data['coupon_id'] = (int)$this->url_data['coupon_id'];
		 $data['customers_id'] = (int)$id;

		 $obj = new stdClass;
		 $o = new adminDB_DataSave($this->_table_coupons_customers, $data, false, __CLASS__);
		 $obj = $o->saveDataSet();

		return $obj;
	}	
	
	function _unset($id = 0) {
	    global $db, $xtPlugin;

        $coupon_id = (int)$this->url_data['coupon_id']; 
	    if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". $this->_table_coupons_customers ." WHERE customers_id = ? and coupon_id=?",array($id,$coupon_id));

	}
    
    function _xxx_getSearchIDs($search_data) {
        global $xtPlugin, $db, $language, $seo,$filter;

        $query = "SELECT c.customers_id FROM " . TABLE_CUSTOMERS . " c, ".TABLE_CUSTOMERS_ADDRESSES." ca 
                  where c.customers_id = ca.customers_id and ca.address_class='default'";
        $query .= ' and '.parent::_getSearchIDs($search_data);
        $record = $db->Execute($query);
        if ($record->RecordCount() > 0) {

            while(!$record->EOF){
                $records = $record->fields;
                $data[] = $records['customers_id'];
                $record->MoveNext();
            } $record->Close();
        }

        ($plugin_code = $xtPlugin->PluginCode('class.product.php:_getSearchIDs_bottom')) ? eval($plugin_code) : false;
        return $data;   
    }
    
}
?>