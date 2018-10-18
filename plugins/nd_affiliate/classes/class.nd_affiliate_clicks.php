<?php
/*------------------------------------------------------------------------------
	$Id: class.nd_affiliate_clicks.php 61 2011-10-06 09:43:59Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class nd_affiliate_clicks {
	
	public $_table = TABLE_AFFILIATE_CLICKTHROUGHS;
	public $_table_lang = null;
	public $_table_seo = null;
	public $_master_key = 'affiliate_clickthrough_id';
	var $period = '';
	var $entriesPerPage = 25;
	
	function newClick() {
	    global $db, $store_handler;
	    
	    if($_GET['ref']) {
    		$_SESSION['affiliate_ref'] = (int)$_GET['ref'];
    		if ($_GET['product']) $affiliate_products_id = (int)$_GET['product'];
    		if ($_GET['inventory']) $affiliate_banner_id = (int)$_GET['inventory'];
    		
    		if(!$affiliate_products_id && $current_product_id > 0) {
    			$affiliate_products_id = $current_product_id;
    		}
			
    		$now = $db->BindTimestamp(time());
    		
    		if($_SESSION['affiliate_id'] != $_SESSION['affiliate_ref']) {
    			$sql_data_array = array('affiliate_id' => $_SESSION['affiliate_ref'],
             			                'affiliate_clientdate' => $now,
                	    		        'affiliate_clientbrowser' => $_SERVER["HTTP_USER_AGENT"],
                    	        		'affiliate_clientip' => $_SERVER["REMOTE_ADDR"],
	                        	        'affiliate_clientreferer' => $_SERVER["HTTP_REFERER"],
    		                	        'affiliate_products_id' => $affiliate_products_id,
            		        	        'affiliate_banner_id' => $affiliate_banner_id,
            		        	        'affiliate_shop_id' => $store_handler->shop_id);
            		        	        
            	$db->AutoExecute(TABLE_AFFILIATE_CLICKTHROUGHS, $sql_data_array, 'INSERT');
            	$_SESSION['affiliate_clickthroughs_id'] = $db->Insert_ID();
            }
            
            // Banner has been clicked, update stats:
            if ($affiliate_banner_id && $_SESSION['affiliate_ref'] && $_SESSION['affiliate_id'] != $_SESSION['affiliate_ref']) {
            	$today = date('Y-m-d');
            	$record = $db->EXECUTE("SELECT * 
            							FROM " . TABLE_AFFILIATE_INVENTORY_HISTORY . " 
            							WHERE inventory_id = '" . $affiliate_banner_id  . "' 
            							AND inventory_affiliate_id = '" . $_SESSION['affiliate_ref'] . "' 
            							AND inventory_history_date = '" . $today . "'
            							AND shop_id = '" . $store_handler->shop_id . "'");
            	if($record->RecordCount() == 1) {
            		$db->Execute("UPDATE " . TABLE_AFFILIATE_INVENTORY_HISTORY . " 
            						SET inventory_clicks = inventory_clicks + 1 
            						WHERE inventory_id = '" . $affiliate_banner_id . "' 
            						AND inventory_affiliate_id = '" . $_SESSION['affiliate_ref'] . "' 
            						AND inventory_history_date = '" . $today . "'
            						AND shop_id = '" . $store_handler->shop_id . "'");
            	// Initial entry if banner has not been shown
            	} else {
            		$sql_data_array = array('inventory_id' => $affiliate_banner_id,
            				                'inventory_affiliate_id' => $_SESSION['affiliate_ref'],
                            				'inventory_clicks' => '1',
	                                		'inventory_history_date' => $today,
	                                		'shop_id' => $store_handler->shop_id);
	                                		
	                $db->AutoExecute(TABLE_AFFILIATE_INVENTORY_HISTORY, $sql_data_array, 'INSERT');
	            }
	        }
	        
	        // Set Cookie if the customer comes back and orders it counts
	        setcookie('affiliate_ref', $_SESSION['affiliate_ref'], time() + AFFILIATE_COOKIE_LIFETIME);
	        setcookie('affiliate_clickthroughs_id', $_SESSION['affiliate_clickthroughs_id'], time() + AFFILIATE_COOKIE_LIFETIME);
	    }
	    if ($_COOKIE['affiliate_ref']) { // Customer comes back and is registered in cookie
	    	$_SESSION['affiliate_ref'] = (int)$_COOKIE['affiliate_ref'];
	    	
	   	}
	   	if($_COOKIE['affiliate_clickthroughs_id']) {
	   		$_SESSION['affiliate_clickthroughs_id'] = (int)$_COOKIE['affiliate_clickthroughs_id'];
	   	}
	}
	
	function buildPeriodSelector ($affiliate_id) {
		global $db;
		
		$start = $db->Execute("SELECT MONTH(affiliate_date_account_created) as start_month, YEAR(affiliate_date_account_created) as start_year FROM " . TABLE_AFFILIATE . " WHERE affiliate_id = " . $affiliate_id . "");
		
		$return_array = array(array('id' => '', 'text' => AFFILIATE_TEXT_ALL));

		for($period_year = $start->fields['start_year']; $period_year <= date("Y"); $period_year++ ) {
			for($period_month = 1; $period_month <= 12; $period_month++ ) {
				if ($period_year == $start->fields['start_year'] && $period_month < $start->fields['start_month']) continue;
				if ($period_year ==  date("Y") && $period_month > date("m")) continue;
					$return_array[] = array( 'id' => $period_year . '-' . $period_month, 'text' => $period_year . '-' . $period_month) ;
			}
		}
		
		return $return_array;
	}
	
	function getClicks ($affiliate_id) {
		global $db, $language, $store_handler;
		
		if($this->period != '') {
			$period_split = split('-', $this->period);
			$period_clause = " AND YEAR(a.affiliate_clientdate) = " . $period_split[0] . " and MONTH(a.affiliate_clientdate) = " . $period_split[1];
		}
		
		if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
			$shop_clause = " AND a.affiliate_shop_id = '" . $store_handler->shop_id . "'";
		}

		$affiliate_clicks = "SELECT a.affiliate_clientreferer, " . $db->SQLDate('d.m.Y H:i:s', 'a.affiliate_clientdate') . " as a_date, pd.products_name 
									FROM " . TABLE_AFFILIATE_CLICKTHROUGHS . " a
									LEFT JOIN " . TABLE_PRODUCTS . " p on (p.products_id = a.affiliate_products_id)
									LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd on (pd.products_id = p.products_id and pd.language_code = '" . $language->code . "')
									WHERE a.affiliate_id = '" . $affiliate_id . "'
									" . $period_clause . $shop_clause . "
									ORDER BY a.affiliate_clientdate DESC";
		
		$affiliate_clicks_split = new split_page($affiliate_clicks, $this->entriesPerPage);
		
		return $affiliate_clicks_split;
	}
	
	function getClicksTotal($affiliate_id) {
		global $db, $store_handler;
		
		$clicks = 0;
		
		if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
			$shop_clause = " AND affiliate_shop_id = '" . $store_handler->shop_id . "'";
		}
		
		$affiliate_clicks_history = $db->Execute("SELECT count(*) as count 
													FROM " . TABLE_AFFILIATE_CLICKTHROUGHS .  " 
													WHERE affiliate_id  = '" .  $affiliate_id . "'
													" . $shop_clause . "");
		
		$clicks = $affiliate_clicks_history->fields['count'];
		
		return $clicks;
	}
	
	function setPosition ($position) {
		$this->position = $position;
	}
	
	function _getParams() {
		global $language;
		
		$header['affiliate_clickthroughs_id'] = array('type' => 'hidden');
		$header['affiliate_clientdate'] = array('type' => 'date');
		
		$header['affiliate_shop_id'] = array('type' => 'dropdown', 								
											 'url'  => 'DropdownData.php?get=stores');
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['SortField']      = $this->_master_key;
		$params['SortDir']        = "DESC";
		
		$params['display_newBtn'] = false;
		$params['display_editBtn'] = false;

		if($this->url_data['edit_id']) {
			$params['exclude'] = array('');
		} else {
			$params['include'] = array('affiliate_clickthrough_id',
									   'affiliate_id',
									   'affiliate_products_id',
									   'affiliate_banner_id',
									   'affiliate_clientdate',
									   'affiliate_clientip',
									   'affiliate_clientbrowser',
									   'affiliate_shop_id');
		}

		return $params;
	}
	
	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

		if ($this->position != 'admin') return false;

		$obj = new stdClass;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}
		$ID=(int)$ID;
		
		if(isset($this->url_data['aID'])) {
			$where = 'affiliate_id=' . (int)$this->url_data['aID'];
		}

		if (!$ID && !isset($this->sql_limit)) {
			$this->sql_limit = "0,25";
		}			
		
		$table_data = new adminDB_DataRead($this->_table, $this->_table_lang, $this->_table_seo, $this->_master_key, $where, $this->sql_limit);

		if ($this->url_data['get_data']){
			$data = $table_data->getData();
		}elseif($ID){
			$data = $table_data->getData($ID);
		}else{
			$data = $table_data->getHeader();
		}

		if($table_data->_total_count!=0 || !$table_data->_total_count) {
			$count_data = $table_data->_total_count;
		} else {
			$count_data = count($data);
		}

		$obj->totalCount = $count_data;
		$obj->data = $data;

		return $obj;
	}
	
	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter;

		$obj = new stdClass;
		$oC = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $oC->saveDataSet();
		
		return $obj;
	}
	
	function _unset($id = 0) {
		global $db;
		
		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

	    $db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ".$id);
	}
}
?>