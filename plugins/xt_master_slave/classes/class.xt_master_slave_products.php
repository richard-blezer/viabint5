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

class master_slave_products {

	var $pID;
	var $possibleProducts;
	var $possibleOptions;
	var $possibleValues;
	var $fullData;
	var $productOptions;
	///
	var $unset;
	var $delete_image;
	var $master_model;
	var $showProductList;
	var $allOptions;
	var $allValues;

	var $possibleProducts_primary;
	var $possibleOptions_primary;
	var $possibleValues_primary;
	var $not_master_slave_pr = false;
	var $slave_no_options=false;
	/**
	 * 
	 * constructor
	 */
	function master_slave_products(){
		$this->delete_image = 'small_delete.gif';
		$this->unset = false;
	}

	
	/**
	 * 
	 * unknown
	 * 
	 * @param int $pID
	 * @return void
	 */
	function getProductLink ($pID) {
		$link = xtc_product_link($pID, xtc_get_products_name($pID));
	}

	
	/**
	 * 
	 * set product ID - maybe redirect to master product
	 * 
	 * @param int $pID ID of product (master or slave)
	 */
	function setProductID($pID) {
		global $xtLink,$xtPlugin;
		$this->pID = $pID;
	}

	
	/**
	 * 
	 * redirect to given product with ID
	 * trying to pass filter as GET; not working, yet
	 * 
	 * @param int $id product ID
	 */
	function _redirect($id){
		global $xtLink,$xtPlugin;
		// copy options from current to next product
		$_SESSION['select_ms'][$id]['id'] = $_SESSION['select_ms'][$this->pID]['id'];
	
        $p_info = new product($id,'full', '', '', 'product_info');
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:_redirect')) ? eval($plugin_code) : false;
		if ((_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='true') )
			$link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text'], 'params'=>'action_ms=1');
		else $link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text']); //, 'params'=>'action_ms=1');
		$xtLink->_redirect($xtLink->_link($link_array));
	}

	
	/**
	 * 
	 * unset filter in SESSION
	 */
	function unsetFilter($add_to_sesstion_name='') {
		if (($_SESSION['select_ms']['action'] != 1 and $_GET['action_ms'] != 1) or $_GET['reset_ms'] == 1) {
			unset($_SESSION['select_ms'.$add_to_sesstion_name]/*[$this->pID]*/);
		}
	}

	
	/**
	 * 
	 * set filter in SESSION
	 * 
	 * @param array $data option and its value
	 */
	function setFilter($data,$add_to_sesstion_name='') {
		
		
		foreach ($data as $key => $val) {
			if ($val != 0) {
				$_SESSION['select_ms'.$add_to_sesstion_name][$this->pID]['id'][$key] = $val;
			} else {
				unset($_SESSION['select_ms'.$add_to_sesstion_name][$this->pID]['id'][$key]);
				$this->unset = true;
			}
		}
	}
	
	
	
	
	
	/**
	 * 
	 * set filter by GET parameters in SESSION
	 * 
	 * @param array $get_arr
	 * @return bool
	 */
	public function setFilterByGET($get_arr) {
		///
		if (!is_array($get_arr)) return false;
		
		foreach ($get_arr as $key => $val) {
			if (substr($key, 0, 4) == 'opt_') {
				$_SESSION['select_ms'][$this->pID]['id'][substr($key, 4)] = $val;
			}
		}
		
		return true;
	}

	
	/**
	 * 
	 * get filter (for given product ID) from SESSION
	 * 
	 * @return array
	 */
	function getFilter ($add_to_sesstion_name='') {
		if (!is_array($_SESSION['select_ms'.$add_to_sesstion_name][$this->pID]['id'])) return array();
		reset($_SESSION['select_ms'.$add_to_sesstion_name][$this->pID]['id']);
		return $_SESSION['select_ms'.$add_to_sesstion_name][$this->pID]['id'];
	}

	
	/**
	 * 
	 * check if filter is set (for product ID)
	 * 
	 * @return bool
	 */
	function isFilter ($add_to_sesstion_name='') {
		if (count($_SESSION['select_ms'.$add_to_sesstion_name][$this->pID]['id']) > 0)
			return true;

	}

	
	/**
	 * 
	 * aka main; is called by plugin
	 * 
	 * @return bool|void
	 */
	function getMasterSlave($all=0,$slaves_order=false){
		global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getMasterSlave_top')) ? eval($plugin_code) : false;
		if($this->pID!=''){
			// get (master) model number; might be false
			$model = $this->getModel($this->pID);
			
			// use GET parameters to set filter
			$get_bol = $this->setFilterByGET($_GET);
			
			if($model!='') {
				$this->getPossibleData($model);
				$this->productOptions = $this->getOptions();
				if (XT_MASTER_SLAVE_FILTERLIST_ON_SELECTION=='true' && count($this->possibleProducts)>1) {
					$this->showProductList = $this->getProductList();
				}else{
					$this->showProductList = $this->getProductList('',$all,$slaves_order);
				}
				
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	
	/**
	 * 
	 * merge two option arrays into one
	 * 
	 * @param array $possibleOptions_arr
	 * @param array $allOptions_arr
	 * @return array|bool $mergedOptions_arr array with merged options or false
	 */
	public function mergeOptions($possibleOptions_arr, $allOptions_arr,$optionSet_arr_primary='') {
		///
		global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:mergeOptions_top')) ? eval($plugin_code) : false;
		if ( !is_array($possibleOptions_arr) or !is_array($allOptions_arr) ) return false;
		//var_dump($optionSet_arr_primary);
		$mergedOptions_arr = $allOptions_arr;
		
		foreach ($mergedOptions_arr as $key => $oneDropdown) {
			$data_arr = $oneDropdown['data'];
			foreach ($data_arr as $count_num => $dropdownField_arr) {
				$fieldStatus_str = $this->checkStatus($dropdownField_arr['id'], $possibleOptions_arr, $optionSet_arr_primary);
				if ($fieldStatus_str == 'missing') {
					$mergedOptions_arr[$key]['data'][$count_num]['disabled'] = true;
					
				}
				elseif ($fieldStatus_str == 'selected') {
					$mergedOptions_arr[$key]['data'][$count_num]['selected'] = true;
					$mergedOptions_arr[$key]['selected'] = $dropdownField_arr['text'];
				}
			}
		}
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:mergeOptions_bottom')) ? eval($plugin_code) : false;
		return $mergedOptions_arr;
	} 
	
	
	/**
	 * 
	 * check status of option - selected, missing or normal
	 * 
	 * @param int $id_num ID of option
	 * @param array $options_arr
	 * @return string
	 */
	public function checkStatus($id_num, $options_arr, $option_arr_primary='') {
		///
		global $xtPlugin;
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:checkStatus')) ? eval($plugin_code) : false;
		foreach ($options_arr as $key => $oneDropdown) {
			$data_arr = $oneDropdown['data'];
			
			foreach ($data_arr as $count_num => $dropdownField_arr) {
				if ($dropdownField_arr['id'] == $id_num) {
					if (isset($dropdownField_arr['selected'])) {
						return 'selected';
					}
					else {
							
						return 'normal';
					}
				}
			}
		}
		//var_dump($option_arr_primary);
		if (count($option_arr_primary)>0 && is_array($option_arr_primary)) 
		{
			
			foreach ($option_arr_primary as $oneDropdown2) {
				
				
				$data_arr = $oneDropdown2['data'];
				
				foreach ($data_arr as $count_num => $dropdownField_arr2) {
					if ($dropdownField_arr2['id'] == $id_num) {
						if (isset($dropdownField_arr2['selected'])) {
							return 'selected';
						}
						else {
								
							return 'normal';
						}
					}
				}
			}
		}
		return 'missing';
	}
	
	
	/**
	 * 
	 * write all options and values into class attributes
	 * 
	 * @param array $possibleData_arr
	 * @return bool
	 */
	public function setAllOptions($possibleData_arr = array()) {
		global $xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:setAllOptions')) ? eval($plugin_code) : false;
		if (empty($possibleData_arr)) {
			return false;
		}
		else {
			$this->allOptions = array();
			$this->allValues = array();
			foreach ($possibleData_arr as $key => $val_arr) {
				$this->allOptions[] = $val_arr['attributes_parent_id'];
				$this->allValues[]  = $val_arr['attributes_id'];
			}
			
			// remove duplicates
			$this->allOptions = array_unique($this->allOptions);
			$this->allValues = array_unique($this->allValues);
			
			return true;
		}
	}
	
	
	/**
	 * 
	 * returns master model number if slave, else model number
	 * or false if pID not found
	 * 
	 * @param int $pID product ID
	 * @return string|bool
	 */
	function getModel($pID){
		global $product, $db,$xtPlugin;

			$pID = (int)$pID;
			($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getModel')) ? eval($plugin_code) : false;
			$sql_tablecols = 'p.products_model, p.products_master_model';
			$model_sql_products = new getProductSQL_query();
			$model_sql_products->setPosition('plugin_ms_getModel');
			$model_sql_products->setSQL_COLS(", " . $sql_tablecols);
			$model_sql_products->setSQL_WHERE(" AND p.products_id = ?");

			$query = "".$model_sql_products->getSQL_query()."";

			$record = $db->Execute($query,array((int)$pID));
			if($record->RecordCount() > 0){

				if(!$record->fields['products_master_model']){
					$this->master_model = $record->fields['products_model'];
					return $record->fields['products_model'];
				}else{
					$this->master_model = $record->fields['products_master_model'];
					return $record->fields['products_master_model'];
				}

			}else{
				return false;
			}

	}

	
	/**
	 * 
	 * verify whether current product is a slave
	 * 
	 * @return bool|int|void false or product ID of master product or NOTHING
	 */
	function isSlave(){
		global $product, $db,$xtPlugin;
			($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:isSlave')) ? eval($plugin_code) : false;
			$this->isSlave = true;

			// check whether product has a master model number
			$sql_tablecols = 'p.products_master_model';
			$model_sql_products = new getProductSQL_query();
			$model_sql_products->setPosition('plugin_ms_isSlave_model');
			$model_sql_products->setSQL_COLS(", " . $sql_tablecols);
			$model_sql_products->setSQL_WHERE(" AND p.products_master_model != '' and p.products_id = ?");

			$model_query = "".$model_sql_products->getSQL_query()."";
			$record = $db->Execute($model_query,array((int)$this->pID));
			
			// get pID of product with detected master model number
			if($record->RecordCount() > 0){

				$master_sql_tablecols = 'p.products_master_model';
				$master_model_sql_products = new getProductSQL_query();
				$master_model_sql_products->setPosition('plugin_ms_isSlave_master_model');
				$master_model_sql_products->setSQL_COLS(", " . $master_sql_tablecols);
				$master_model_sql_products->setSQL_WHERE(" AND p.products_master_model = '' and p.products_model = ?");

				$query = "".$master_model_sql_products->getSQL_query()."";
				$master_record = $db->Execute($query,array($record->fields['products_master_model']));
				
				// return pID if found
				if($master_record->RecordCount() > 0){
					return $master_record->fields['products_id'];
				}
				// ELSE ???

			}else{
				$this->isSlave = false;
				return false;
			}

	}
	
	function getMaster($id = 0){
		global $product, $db,$xtPlugin;
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getMaster')) ? eval($plugin_code) : false;
			// check whether product has a master model number
			$sql_tablecols = 'p.products_master_model, su.url_text';
			$model_sql_products = new getProductSQL_query();
			$model_sql_products->setPosition('plugin_ms_isSlave_model');
			$model_sql_products->setSQL_COLS(", " . $sql_tablecols);
			if ($id >0){
			    $model_sql_products->setSQL_WHERE(" and p.products_id = ? and p.products_master_flag = 1");
                 $sec_key = array((int)$id);
			}
            else {
                $model_sql_products->setSQL_WHERE(" and p.products_id = ? and p.products_master_flag = 1");
                $sec_key = array((int)$this->pID);
            }
			$model_query = "".$model_sql_products->getSQL_query()."";
		
			$record = $db->Execute($model_query,$sec_key);
			
			// get pID of product with detected master model number
			if($record->RecordCount() > 0){
				return $record->fields;
			}else{
				
				return false;
			}

	}

	/**
	 * 
	 * writes possibleProducts, possibleOptions, possibleValues into class attributes
	 * 
	 * @param string $model model number
	 */
	function getPossibleData($model=''){
		global $xtPlugin, $product, $db, $current_product_id,$xtPlugin;
			($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getPossibleData_top')) ? eval($plugin_code) : false;
			// get options belonging to master model number
			$data_sql_tablecols = 'pa.products_id, pa.attributes_id, pa.attributes_parent_id';
			$data_sql_products = new getProductSQL_query();
			$data_sql_products->setPosition('plugin_ms_getPossibleData_data');
			$data_sql_products->setSQL_COLS(", " . $data_sql_tablecols);
			$data_sql_products->setSQL_TABLE("LEFT JOIN " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa ON pa.products_id = p.products_id");
			$data_sql_products->setSQL_WHERE(" AND p.products_master_model = ?");
			$data_sql_products->setSQL_SORT(' p.products_sort ASC');

			$data_query = "".$data_sql_products->getSQL_query()."";
			$data_record = $db->Execute($data_query,array($model));
			
			// moved here from following if-branch
            $possibleData = array();
			if ($data_record->RecordCount()==0) $this->not_master_slave_pr = true;
			
			
			if($data_record->RecordCount()>0 || !$this->isFilter()) {
				// $possibleData = array();
				while (!$data_record->EOF) {
					if($data_record->fields['products_id'])
						$possibleData[] = $data_record->fields;
					$data_record->MoveNext();
				}
				$data_record->Close();
			}
			
			// superfluous ???
			// gets the master model number first
			// then gets the same infos as above
			else {
				
				$master_sql_tablecols = 'p.products_master_model';
				$master_sql_products = new getProductSQL_query();
				$master_sql_products->setPosition('plugin_ms_getPossibleData_master');
				$master_sql_products->setSQL_COLS(", " . $master_sql_tablecols);
				$master_sql_products->setSQL_WHERE(" AND p.products_model = ?");

				$master_query = "".$master_sql_products->getSQL_query()."";
				$master_record = $db->Execute($master_query,array($model));
				
				if($master_record->RecordCount()>0){

					$master_data_sql_tablecols = 'pa.products_id, pa.attributes_id, pa.attributes_parent_id';
					$master_data_sql_products = new getProductSQL_query();
					$master_data_sql_products->setPosition('plugin_ms_getPossibleData_master_data');
					$master_data_sql_products->setSQL_COLS(", " . $master_data_sql_tablecols);
					$master_data_sql_products->setSQL_TABLE("LEFT JOIN " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa ON pa.products_id = p.products_id");
					$master_data_sql_products->setSQL_WHERE(" AND p.products_master_model = ?");

					$master_data_query = "".$master_data_sql_products->getSQL_query()."";
					
					
					$master_data_record = $db->Execute($master_data_query,array($master_record->fields['products_master_model']));
					
					if($master_data_record->RecordCount()>0){
						while (!$master_data_record->EOF) {

							if($master_data_record->fields['products_id'])
							$possibleData[] = $master_data_record->fields;

							$master_data_record->MoveNext();
						}$master_data_record->Close();
					}
				}

			}
			
			// save all options in additional class attribute
			$setAllOptions_bol = $this->setAllOptions($possibleData);
			
			/* ------------------------------------------------------------------ */
			
			$pos_count = count($possibleData);

			if($pos_count > 0) {

				$fcount = 0;
				$tmp_options = array();
				if(($_POST['action'] == 'select_ms' or $_GET['action_ms'] == 1 ) && $this->isFilter()) {
					$filter = $this->getFilter();
					$fcount = count($filter);
					
					
					while (list ($key, $value) = each($filter)) {
						if($value!=0){
							$tmp_options[] = $key;
							$tmp_values[] = $value;
						}
					}

					for ($i = 0; $i < $pos_count; $i++) {
						if ($tmp_values)
						if(in_array($possibleData[$i]['attributes_id'], $tmp_values)){
							$possibleProducts[] = $possibleData[$i]['products_id'];
						}

					}
				}
				else {
					// default: use all products found in possibleData
					for ($i = 0; $i < $pos_count;$i++) {
							$possibleProducts[] = $possibleData[$i]['products_id'];
					}

				}

				// partially superfluous
				for ($i = 0; $i < $pos_count;$i++) {
					if (!in_array($possibleData[$i]['attributes_parent_id'], $tmp_options))	{
						$tmp_options[] = $possibleData[$i]['attributes_parent_id'];
						$tmp_other_values[] = $possibleData[$i]['attributes_id'];
					}
				}

				if ($tmp_options[0]) {
					// does not seem to make a difference whether this is disabled
					$option_where.= " and attributes_parent_id IN (".implode(", ", $tmp_options).")";
				}
				else
					$this->slave_no_options=true;

				if (is_array($possibleProducts)) {
					// array with counts of products
					$_pcount = array_count_values ($possibleProducts);
					$possibleProducts = array_unique($possibleProducts);

					$qry = "select products_id, attributes_id, attributes_parent_id from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " 
					where products_id in (".implode(",", $possibleProducts).")".$option_where."";
					
					$record = $db->Execute($qry);
					if($record->RecordCount() > 0){
						while (!$record->EOF) {
							$option_data[] = $record->fields;
							$record->MoveNext();
						}$record->Close();
					}
					
					$possibleProducts = array();

					$count_data = count($option_data);
					for ($i = 0; $i < $count_data; $i++) {

						$possibleOptions[] = $option_data[$i]['attributes_parent_id'];

						if ($fcount == $_pcount[$option_data[$i]['products_id']] || $fcount == 0) {
							$possibleProducts[] = $option_data[$i]['products_id'];
							$possibleValues[] = $option_data[$i]['attributes_id'];
						}
					}

					if (is_array($possibleProducts))
					$this->possibleProducts = array_unique($possibleProducts);

					if (is_array($possibleOptions))
					$this->possibleOptions = array_unique($possibleOptions);

					if (is_array($possibleValues))
					$this->possibleValues = array_unique($possibleValues);
					
					
					if((_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='true')&& count($this->possibleProducts)==1 && $this->possibleProducts[0] != $current_product_id){
						$this->_redirect($this->possibleProducts[0]);
					}
				}
				else {
					// not specified
				}
			}
			else {

				$sdata_sql_products = new getProductSQL_query();
				$sdata_sql_products->setPosition('plugin_ms_getPossibleData_sdata');
				$sdata_sql_products->setSQL_WHERE(" AND p.products_master_model = ?");
                $sdata_sql_products->setSQL_SORT(' p.products_sort ASC');

				$sdata_query = "".$sdata_sql_products->getSQL_query()."";
				$sdata_record = $db->Execute($sdata_query,array($model));
				
				if($sdata_record->RecordCount()>0|| !$this->isFilter()){
					while (!$sdata_record->EOF) {
						if($sdata_record->fields['products_id'])
						$possibleData[] = $sdata_record->fields;
						$sdata_record->MoveNext();
					}$sdata_record->Close();

				}

				$pos_count = count($possibleData);
				
				for ($i = 0; $i < $pos_count;$i++) {
					$possibleProducts[] = $possibleData[$i]['products_id'];
				}

				if (is_array($possibleProducts))
				$this->possibleProducts = array_unique($possibleProducts);
			}
			
			($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getPossibleData_bottom')) ? eval($plugin_code) : false;
			//Sort by products_sort
			$this->possibleProducts  = $this->sortPossibleProducts($this->possibleProducts );
			
			$this->SetPossible_primary($possibleData);
			
	}

	
	function SetPossible_primary($possibleData)
	{
		global $db,$xtPlugin;
			
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:SetPossible_primary_top')) ? eval($plugin_code) : false;
		$pos_count = count($possibleData);
		/* set possibeValues Based on primary filters*/
		if($pos_count > 0) 
		{			
			$fcount_primary = 0;
			$tmp_options_primary = array();
			
			if(($_POST['action'] == 'select_ms' or $_GET['action_ms'] == 1 ) && $this->isFilter('_primary')) {
				
				$filter_primary = $this->getFilter('_primary');
				$fcount_primary = count($filter_primary);
				
				
				while (list ($key, $value) = each($filter_primary)) {
					if($value!=0){
						$tmp_options_primary[] = $key;
						$tmp_values_primary[] = $value;
					}
				}
	
				for ($i = 0; $i < $pos_count; $i++) {
					if ($tmp_values_primary)
					if(in_array($possibleData[$i]['attributes_id'], $tmp_values_primary)){
						$possibleProducts_primary[] = $possibleData[$i]['products_id'];
					}
	
				}
			}
			else {
				// default: use all products found in possibleData
				for ($i = 0; $i < $pos_count;$i++) {
						$possibleProducts_primary[] = $possibleData[$i]['products_id'];
				}
	
			}
	
			// partially superfluous
			for ($i = 0; $i < $pos_count;$i++) {
				if (!in_array($possibleData[$i]['attributes_parent_id'], $tmp_options_primary))	{
					$tmp_options_primary[] = $possibleData[$i]['attributes_parent_id'];
					$tmp_other_values_primary[] = $possibleData[$i]['attributes_id'];
				}
			}
	
			if ($tmp_options_primary[0]) {
				// does not seem to make a difference whether this is disabled
				$option_where_primary.= " and attributes_parent_id IN (".implode(", ", $tmp_options_primary).")";
			}
			else
				$this->slave_no_options=true;
			
			if (is_array($possibleProducts_primary)) {
				// array with counts of products
				$_pcount_primary = array_count_values ($possibleProducts_primary);
				$possibleProducts_primary = array_unique($possibleProducts_primary);
	
				$qry = "select products_id, attributes_id, attributes_parent_id from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " 
				where products_id in (".implode(",", $possibleProducts_primary).")".$option_where_primary."";
				
				$record = $db->Execute($qry);
				if($record->RecordCount() > 0){
					while (!$record->EOF) {
						$option_data_primary[] = $record->fields;
						$record->MoveNext();
					}$record->Close();
				}
				
				$possibleProducts_primary = array();
	
				$count_data_primary = count($option_data_primary);
				for ($i = 0; $i < $count_data_primary; $i++) {
	
					$possibleOptions_primary[] = $option_data_primary[$i]['attributes_parent_id'];
	
					if ($fcount_primary == $_pcount_primary[$option_data_primary[$i]['products_id']] || $fcount_primary == 0) {
						$possibleProducts_primary[] = $option_data_primary[$i]['products_id'];
						$possibleValues_primary[] = $option_data_primary[$i]['attributes_id'];
					}
				}
	
				if (is_array($possibleProducts_primary))
				$this->possibleProducts_primary = array_unique($possibleProducts_primary);
	
				if (is_array($possibleOptions_primary))
				$this->possibleOptions_primary = array_unique($possibleOptions_primary);
	
				if (is_array($possibleValues_primary))
				$this->possibleValues_primary = array_unique($possibleValues_primary);
			
				
			/* send of et possibeValues Based on primary filters*/
			
			}
		}
		else
			{

				$sdata_sql_products = new getProductSQL_query();
				$sdata_sql_products->setPosition('plugin_ms_getPossibleData_sdata');
				$sdata_sql_products->setSQL_WHERE(" AND p.products_master_model = ?");
                $sdata_sql_products->setSQL_SORT(' p.products_sort ASC');

				$sdata_query = "".$sdata_sql_products->getSQL_query()."";
				$sdata_record = $db->Execute($sdata_query,array($model));

				if($sdata_record->RecordCount()>0|| !$this->isFilter()){
					while (!$sdata_record->EOF) {
						if($sdata_record->fields['products_id'])
						$possibleData[] = $sdata_record->fields;
						$sdata_record->MoveNext();
					}$sdata_record->Close();

				}

				$pos_count = count($possibleData);
				
				for ($i = 0; $i < $pos_count;$i++) {
					$possibleProducts_primary[] = $possibleData[$i]['products_id'];
				}

				if (is_array($possibleProducts_primary))
				$this->possibleProducts_primary = array_unique($possibleProducts_primary);
			}
			
		$this->possibleProducts_primary  = $this->sortPossibleProducts($this->possibleProducts_primary );
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:SetPossible_primary_bottom')) ? eval($plugin_code) : false;
	}
	/**
	 * 
	 * Returns dropdown boxes with different options as HTML
	 * 
	 * @return string $optionData HTML of options
	 */
	function getOptions(){
		global $product,$xtPlugin, $xtLink;
		
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getOptions_top')) ? eval($plugin_code) : false;
		if((_PLUGIN_MASTER_SLAVE_SHOW_OPTIONS == 'true') && (!$this->not_master_slave_pr) && (!$this->slave_no_options)) {
				$option_set = $this->buildOptionSet();
				$error_message='';
				$optionSet_arr_primary = $this->buildOptionSet('primary');
				// build two option sets with possible and all options and then merge them
				$optionSet_arr = $this->buildOptionSet('all');
				//var_dump($optionSet_arr);
				$mergedOptions_arr = $this->mergeOptions($option_set, $optionSet_arr,$optionSet_arr_primary);
				
				$master_pID = $this->isSlave();
				if ($master_pID) {
					// create link to master article
					$p_info  = $this->getMaster($master_pID);
					//$p_info = new product($master_pID,'full', '', '', 'product_info');
					$link_arr = array('page'=> 'product', 'type'=>'product', 'id'=>$p_info['products_id'],'seo_url'=>$p_info['url_text'], 'params'=>'reset_ms=1');
					$masterLink = $xtLink->_link($link_arr);
				}
				else {
					$p_info  = $this->getMaster();
					if ($p_info)
					{
						if (_SYSTEM_MOD_REWRITE=='true'){
							$link_arr = array('page'=> 'product', 'type'=>'product', 'id'=>$p_info['products_id'],'seo_url'=>$p_info['url_text'], 'info'=>$_GET['info']);
						}else{
							$link_arr = array('page'=> 'product', 'type'=>'product', 'id'=>$p_info['products_id'], 'info'=>$_GET['info'], 'params'=>'info='.$_GET['info']);
						}
						$masterLink = $xtLink->_link($link_arr);
					}
				}
				 
				 
				
				if (!$mergedOptions_arr && _PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE!='ajax') 
				{	
					$tm = '?';
					if (strpos($masterLink,'?')!==false) $tm = '&';
					$xtLink->_redirect($masterLink.$tm.'error_no_stock=1');
				}
				else
				{
					if (((_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='ajax')&& (!$mergedOptions_arr)) || ($_GET['error_no_stock']==1) )
					{
						$error_message=TEXT_XT_MASTER_SLAVE_NO_STOCK;
						unset($_GET['error_no_stock']);
					}
				}
				// use $mergedOptions_arr instead of $option_set
				
				
				if(_SYSTEM_FULL_SSL == true)
                    $tmp = _SYSTEM_BASE_HTTPS;
                else
                    $tmp = _SYSTEM_BASE_URL;
				$tpl_data = array(
								'options' => $mergedOptions_arr,
								'pID' => $this->pID,
								'masterLink' => $masterLink,
								'error_message'=>$error_message,
								'not_selected'=>$_SESSION['xt_master_slave'][$p_info['products_id']]['error'],
								'javascript_file'=>$tmp . str_replace('xtAdmin/','',_SRV_WEB) . _SRV_WEB_PLUGINS . 'xt_master_slave/js/ajax_call.js'
				);

				$tpl = _getSingleValue(array('value'=>'products_option_template', 'table'=>TABLE_PRODUCTS, 'key'=>'products_model', 'key_val'=>$this->master_model));

				if(!$tpl)
				{
					$tpl = 'ms_default.html';
					if (_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='ajax') $tpl = 'ms_default_ajax.html';
				}
 				($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:_getOptions_center')) ? eval($plugin_code) : false;   
				// $plugin_return_value: origin unknown, propably undefined
 				if(isset($plugin_return_value))
                    return $plugin_return_value;  
				$template = new Template();
				$template->getTemplatePath($tpl, 'xt_master_slave', 'options', 'plugin');

				$optionData = $template->getTemplate('xt_master_slave_default_smarty', $tpl, $tpl_data);

			return $optionData;
		}
	}

    
	/**
	 * 
	 * get array with options
	 * 
	 * @return array $oData_array
	 */
	function buildOptionSet($mode_str = 'possible') {
		global $xtPlugin, $product, $language, $db;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:buildOptionSet_top')) ? eval($plugin_code) : false;
		// build either all or possible (default) options
		if ($mode_str == 'possible') {
			$modeOptions_arr = $this->possibleOptions;
			$modeValues_arr  = $this->possibleValues;
		}
		elseif ($mode_str == 'primary') {
			$modeOptions_arr = $this->possibleOptions_primary;
			$modeValues_arr  = $this->possibleValues_primary;
		}
		else {
			$modeOptions_arr = $this->allOptions;
			$modeValues_arr  = $this->allValues;
		}
		
		// instead of:
		// if (!$this->possibleOptions) return;
		if ( !is_array($modeOptions_arr) or empty($modeOptions_arr) or !is_array($modeValues_arr) or empty($modeValues_arr) ) return;
		
           
			// options of current product
            $product_option_data = $this->getAttributesData($this->pID);

            $odata = $db->Execute("select distinct pa.*,pad.*, pat.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
										left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on pa.attributes_id = pad.attributes_id 
										LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES." pat ON pat.attributes_templates_id = pa.attributes_templates_id
								   where pad.language_code = ? and pa.attributes_id in (".implode(",", $modeOptions_arr).") and 
								   pa.status = 1 order by pa.sort_order, pad.attributes_name",
                                   array($language->code));
			
			$rows = 0;		// = options
			$cols = 1;		// = values
			if($odata->RecordCount() > 0){
				while(!$odata->EOF){

				$select = false;
				$selected_value = $_SESSION['select_ms'][$this->pID]['id'][$odata->fields['attributes_id']];

				if ($odata->fields['attributes_image']!='') $odata->fields['attributes_image'] = _SRV_WEB_IMAGES.'org/'.$odata->fields['attributes_image'];
				$oData_array[$rows] = array('id' => $odata->fields['attributes_id'],
									   		'text' => $odata->fields['attributes_name'],
											'desc' => $odata->fields['attributes_desc'],
											'model'=>$odata->fields['attributes_model'],
											'image'=>$odata->fields['attributes_image'],
											'attributes_templates'=>$odata->fields['attributes_templates_name'],
											'data' => '',
											);

					$vdata = $db->Execute("select distinct pa.*,pad.*, pat.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
												left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on pa.attributes_id = pad.attributes_id 
												LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_TEMPLATES." pat ON pat.attributes_templates_id = pa.attributes_templates_id
										  where pa.attributes_parent = ? and pad.language_code = ? and 
										  pa.attributes_id in (".implode(",", $modeValues_arr).") and pa.status = 1 order by pa.sort_order, pad.attributes_name",
                                          array((int)$odata->fields['attributes_id'],$language->code));
						
					
					if($vdata->RecordCount() > 0){
						if ($odata->fields['attributes_templates_name']=='select')
						{
							$oData_array[$rows]['data'][0] = array('id' => 0,
																   'text' => TEXT_NO_SELECTION,
																   'desc' => ''
																);
						}

						while(!$vdata->EOF){
						// check if selected
						
						if ($vdata->fields['attributes_image']!='') $vdata->fields['attributes_image'] = _SRV_WEB_IMAGES.'org/'.$vdata->fields['attributes_image'];
						$oData_array[$rows]['data'][$cols] = array('id' => $vdata->fields['attributes_id'],
																   'text' => $vdata->fields['attributes_name'],
																   'desc' => $vdata->fields['attributes_desc'],
																	'text' => $vdata->fields['attributes_name'],
																	'model'=>$vdata->fields['attributes_model'],
																	'image'=>$vdata->fields['attributes_image'],
																	'attributes_templates'=>$vdata->fields['attributes_templates_name']
																   );

						if ($selected_value == $vdata->fields['attributes_id'] || ($vdata->RecordCount() == 1 && !$this->unset)) {
							// set selected flag only in mode: possible
							if ($mode_str == 'possible') $oData_array[$rows]['data'][$cols]['selected'] = true;
						}
							

                        if (isset($product_option_data[$odata->fields['attributes_id']]['attributes_id'])) {
                            if ($product_option_data[$odata->fields['attributes_id']]['attributes_id']==$vdata->fields['attributes_id']) {
                            	// set selected flag only in mode: possible
								if ($mode_str == 'possible') $oData_array[$rows]['data'][$cols]['selected'] = true;
                            }
                        }

                            ($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:_buildOptionSet_value')) ? eval($plugin_code) : false;
                            
							$cols ++;
							$vdata->MoveNext();
						}$vdata->Close();
					}
					
					if (!is_array($oData_array[$rows]['data'])) {
						unset($oData_array[$rows]);
					} else {
						$rows ++;
					}
					$odata->MoveNext();
				}$odata->Close();
			}
			
			
			($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:buildOptionSet_bottom')) ? eval($plugin_code) : false;
			return $oData_array;
	}

	
    /**
    * get array with assigned options
    * 
    * @param mixed $pID products id
    * @return bool|array $data options of current product or false
    */
	function getAttributesData ($pID) {
		global $db,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getAttributesData_top')) ? eval($plugin_code) : false;
		$option_data = $db->Execute("select products_id, attributes_id, attributes_parent_id from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " 
		                  where products_id = ?",array((int)$pID));
        if ($option_data->RecordCount()==0) return false;
        $data = array();
        while (!$option_data->EOF) {
           $data[$option_data->fields['attributes_parent_id']]=$option_data->fields;
           $option_data->MoveNext(); 
        }
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getAttributesData_bottom')) ? eval($plugin_code) : false;
        return $data;
	}
	
	
	/**
	 * 
	 * get current options of product with names
	 * 
	 * @param int $pID product ID
	 * @return array $data 
	 */
	function getFullAttributesData ($pID) {
		global $db, $language,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getFullAttributesData_top')) ? eval($plugin_code) : false;
		$option_data = $db->Execute("select padv.attributes_id as option_id, padv.attributes_name as option_name, pad.attributes_id as option_value_id, pad.attributes_name as option_value_name 
		                              from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa 
		                              left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on (pa.attributes_id = pad.attributes_id and pad.language_code = '" . $language->code . "') 
		                              left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." padv on (pa.attributes_parent_id = padv.attributes_id and padv.language_code = '" . $language->code . "') 
		                              where pa.products_id = ?",array((int)$pID));
		if($option_data->RecordCount() > 0){
			while(!$option_data->EOF){
				$data[] = $option_data->fields;
				$option_data->MoveNext();
			}
			$option_data->Close();
		}
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getFullAttributesData_bottom')) ? eval($plugin_code) : false;
		return $data;
	}
	
	function getAllSlaves($slaves_order=false)
	{ 
		global $db,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getAllSlaves_top')) ? eval($plugin_code) : false;
		$all_p = array();
		
		$sql_where=' and p.products_master_model =? ';
		
		$sdata_sql_products = new getProductSQL_query();
		$sdata_sql_products->setPosition('plugin_ms_sdata_sql_products_sdata');
		$sdata_sql_products->setSQL_WHERE($sql_where);
		if ($slaves_order)
		  $sdata_sql_products->setSQL_SORT('products_master_slave_order '.XT_MASTER_SLAVE_SLAVE_ORDER);
        $sdata_query = $sdata_sql_products->getSQL_query();
		
		$odata = $db->Execute($sdata_query,array($this->master_model));
     
		//$db->Execute("select products_id from " . TABLE_PRODUCTS . " p where p.products_master_model ='".$this->master_model."' and p.products_status=1 ". $sql_where);
			
		if($odata->RecordCount() > 0){
			while(!$odata->EOF){
					
				array_push($all_p,$odata->fields['products_id']);
				$odata->MoveNext();
			}
			$odata->Close();
		}

		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getAllSlaves_bottom')) ? eval($plugin_code) : false;
		return $all_p; 
	}
	
	function getSlaveOptions($id)
	{
		global $db,$xtPlugin;
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getSlaveOptions_top')) ? eval($plugin_code) : false;
		$all_p = array();
		$odata = $db->Execute(" SELECT pa.* FROM   " . TABLE_PRODUCTS_TO_ATTRIBUTES." pa where pa.products_id=?",array((int)$id));
			
		if($odata->RecordCount() > 0){
			while(!$odata->EOF){
				
				$data[$odata->fields['attributes_parent_id']] = $odata->fields['attributes_id'];
				array_push($all_p,$data);
				$odata->MoveNext();
			}
			$odata->Close();
		}
		($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getSlaveOptions_bottom')) ? eval($plugin_code) : false;
		return $all_p;
		
	}
	/**
	 * 
	 * returns slave list as HTML or void, if no match is found
	 * 
	 * @param unknown_type $data NOT USED
	 * @return string|void $page_data slave products as HTML or void
	 */
	function getProductList($data='',$all=0,$slaves_order=false){
		global $xtPlugin, $product, $template, $db;

		$tmp_attributes = array();

		if(_PLUGIN_MASTER_SLAVE_SHOW_SLAVE_LIST=='true'){
                
				$all_p = $this->getAllSlaves($slaves_order); 
			
				if ($all==1) $this->possibleProducts = $all_p;
			
				if (count($this->possibleProducts) == 0) return;
				
				$count_products = count($this->possibleProducts);
				
				foreach ($this->possibleProducts as $pdata) {

					$tmp_data = new product($pdata);
					$tmp_pdata = $tmp_data->data;
                    $attrib_data = $this->getFullAttributesData ($tmp_data->data['products_id']);
                    if (is_array($attrib_data)) {
                        while (list($count, $optiondata) = each($attrib_data)) {
                            $tmp_attributes[$optiondata['option_id']] = $optiondata;
                        }
                        $tmp_pdata['attributes'] = $attrib_data;
                    }
					$_pdata[] = $tmp_pdata;
				}
				
				
				// add GET params to product links
				foreach ($_pdata as $key => $product) {
					$att_arr = $product['attributes'];
					
					$msGET = "";
					foreach ($att_arr as $a => $options) {
						$msGET .= "&opt_".$options['option_id']."=".$options['option_value_id'];
					}
					$glue_str = (strpos($_pdata[$key]['products_link'], "?") === false) ? "?" : "&";
					//$_pdata[$key]['products_link'] .= $glue_str."action_ms=1".$msGET;
				}

				// currently this is an all-or-nothing decision !!
				// TODO: show only part of the list up to specified max value - or maybe use split_page()
				
				
				if ($count_products > _PLUGIN_MASTER_SLAVE_SHOW_MAX_PRODUCTS) $count_products = _PLUGIN_MASTER_SLAVE_SHOW_MAX_PRODUCTS;
				if($count_products > 0 && $count_products <= _PLUGIN_MASTER_SLAVE_SHOW_MAX_PRODUCTS) {

					$tpl_data = array('product_listing' => $output = array_slice($_pdata, 0, $count_products), 'options' => array_slice($_pdata, 0, $tmp_attributes));

					$tpl = _getSingleValue(array('value'=>'products_option_list_template', 'table'=>TABLE_PRODUCTS, 'key'=>'products_model', 'key_val'=>$this->master_model));

					if(!$tpl)
					$tpl = 'ms_product_list_default.html';
 					
					($plugin_code = $xtPlugin->PluginCode('class.xt_master_slave_products.php:getProductList_center')) ? eval($plugin_code) : false;
					// $plugin_return_value: origin unknown, propably undefined
					if(isset($plugin_return_value))
                    	return $plugin_return_value;
                    	  		
					$template = new Template();

					$template->getTemplatePath($tpl, 'xt_master_slave', 'product_listing', 'plugin');
					
					$page_data = $template->getTemplate('xt_master_slave_smarty', $tpl, $tpl_data);

					return $page_data;
				}
		}
	}
	
	
	/**
	 * 
	 * sort possible products by products_sort
	 * 
	 * @param array $data product IDs
	 * @return array sorted product IDs
	 */
	public function sortPossibleProducts($data){
		global $db;
		if(is_array($data) and !empty($data)){
			$query = 'SELECT products_id FROM '.TABLE_PRODUCTS . ' WHERE products_id IN ('.implode(',',$data).') ORDER BY products_sort';
			$res = $db->getAll($query);
			if(!empty($res)){
				$pids = array();
				foreach($res as $p){
					$pids[] = 	$p['products_id'];
				}
				return $pids;
			}
			else{
				return $data;
			}
		}
		return $data;
	}
	
	public function getProductsOptions_ajax()
	{
		
		echo "tukkkkkkkkkkkkkkk";
		return "tukkk";
	}

}
?>