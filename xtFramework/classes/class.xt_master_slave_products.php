<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.xt_master_slave_products.php 4732 2011-05-11 14:57:55Z dev_af $
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
		global $xtLink;
		$this->pID = $pID;

		// this is either the pID of the master product or false, but NOT an array
		$is_slave = $this->isSlave();
// TO DO
		if ($is_slave && _PLUGIN_MASTER_SLAVE_SHOW_SLAVE_PRODUCTS == 'false') {
			$p_info = new product($is_slave, 'full', '', '', 'product_info');
            $link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text']);
        
			$xtLink->_redirect($xtLink->_link($link_array));
		}
	}

	
	/**
	 * 
	 * redirect to given product with ID
	 * trying to pass filter as GET; not working, yet
	 * 
	 * @param int $id product ID
	 */
	function _redirect($id){
		global $xtLink;
		// copy options from current to next product
		$_SESSION['select_ms'][$id]['id'] = $_SESSION['select_ms'][$this->pID]['id'];
		
        $p_info = new product($id,'full', '', '', 'product_info');
        // add action_ms parameter
        $link_array = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text'], 'params'=>'action_ms=1');
		$xtLink->_redirect($xtLink->_link($link_array));
	}

	
	/**
	 * 
	 * unset filter in SESSION
	 */
	function unsetFilter() {
		if (($_SESSION['select_ms']['action'] != 1 and $_GET['action_ms'] != 1) or $_GET['reset_ms'] == 1) {
			unset($_SESSION['select_ms']/*[$this->pID]*/);
		}
	}

	
	/**
	 * 
	 * set filter in SESSION
	 * 
	 * @param array $data option and its value
	 */
	function setFilter($data) {

		foreach ($data as $key => $val) {
			if ($val != 0) {
				$_SESSION['select_ms'][$this->pID]['id'][$key] = $val;
			} else {
				unset($_SESSION['select_ms'][$this->pID]['id'][$key]);
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
	function getFilter () {
		if (!is_array($_SESSION['select_ms'][$this->pID]['id'])) return array();
		reset($_SESSION['select_ms'][$this->pID]['id']);
		return $_SESSION['select_ms'][$this->pID]['id'];
	}

	
	/**
	 * 
	 * check if filter is set (for product ID)
	 * 
	 * @return bool
	 */
	function isFilter () {
		if (count($_SESSION['select_ms'][$this->pID]['id']) > 0)
			return true;
		return false;
	}

	
	/**
	 * 
	 * aka main; is called by plugin
	 * 
	 * @return bool|void
	 */
	function getMasterSlave(){

		if($this->pID!=''){
			// get (master) model number; might be false
			$model = $this->getModel($this->pID);
			
			// use GET parameters to set filter
			$get_bol = $this->setFilterByGET($_GET);

			if($model!='') {
				$this->getPossibleData($model);
	
				$this->productOptions = $this->getOptions();
				$this->showProductList = $this->getProductList();
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
	public function mergeOptions($possibleOptions_arr, $allOptions_arr) {
		///
		if ( !is_array($possibleOptions_arr) or !is_array($allOptions_arr) ) return false;
		
		$mergedOptions_arr = $allOptions_arr;
		
		foreach ($mergedOptions_arr as $key => $oneDropdown) {
			$data_arr = $oneDropdown['data'];
			foreach ($data_arr as $count_num => $dropdownField_arr) {
				$fieldStatus_str = $this->checkStatus($dropdownField_arr['id'], $possibleOptions_arr);
				if ($fieldStatus_str == 'missing') {
					$mergedOptions_arr[$key]['data'][$count_num]['disabled'] = true;
				}
				elseif ($fieldStatus_str == 'selected') {
					$mergedOptions_arr[$key]['data'][$count_num]['selected'] = true;
				}
			}
		}
		
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
	public function checkStatus($id_num, $options_arr) {
		///
		
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
		///
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
		global $product, $db;

			$pID = (int)$pID;

			$sql_tablecols = 'p.products_model, p.products_master_model';
			$model_sql_products = new getProductSQL_query();
			$model_sql_products->setPosition('plugin_ms_getModel');
			$model_sql_products->setSQL_COLS(", " . $sql_tablecols);
			$model_sql_products->setSQL_WHERE("and p.products_id = '" . $pID . "'");

			$query = "".$model_sql_products->getSQL_query()."";

			$record = $db->Execute($query);
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
		global $product, $db;
			$this->isSlave = true;

			// check whether product has a master model number
			$sql_tablecols = 'p.products_master_model';
			$model_sql_products = new getProductSQL_query();
			$model_sql_products->setPosition('plugin_ms_isSlave_model');
			$model_sql_products->setSQL_COLS(", " . $sql_tablecols);
			$model_sql_products->setSQL_WHERE("and p.products_master_model != '' and p.products_id = '" . $this->pID . "'");

			$model_query = "".$model_sql_products->getSQL_query()."";
			$record = $db->Execute($model_query);
			
			// get pID of product with detected master model number
			if($record->RecordCount() > 0){

				$master_sql_tablecols = 'p.products_master_model';
				$master_model_sql_products = new getProductSQL_query();
				$master_model_sql_products->setPosition('plugin_ms_isSlave_master_model');
				$master_model_sql_products->setSQL_COLS(", " . $master_sql_tablecols);
				$master_model_sql_products->setSQL_WHERE("and p.products_master_model = '' and p.products_model = '".$record->fields['products_master_model']."'");

				$query = "".$master_model_sql_products->getSQL_query()."";
				$master_record = $db->Execute($query);
				
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


	/**
	 * 
	 * writes possibleProducts, possibleOptions, possibleValues into class attributes
	 * 
	 * @param string $model model number
	 */
	function getPossibleData($model=''){
		global $xtPlugin, $product, $db, $current_product_id;
		
			// get options belonging to master model number
			$data_sql_tablecols = 'pa.products_id, pa.attributes_id, pa.attributes_parent_id';
			$data_sql_products = new getProductSQL_query();
			$data_sql_products->setPosition('plugin_ms_getPossibleData_data');
			$data_sql_products->setSQL_COLS(", " . $data_sql_tablecols);
			$data_sql_products->setSQL_TABLE("LEFT JOIN " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa ON pa.products_id = p.products_id");
			$data_sql_products->setSQL_WHERE("and p.products_master_model = '".$model."'");
			$data_sql_products->setSQL_SORT(' p.products_sort ASC');

			$data_query = "".$data_sql_products->getSQL_query()."";
			$data_record = $db->Execute($data_query);
				
			// moved here from following if-branch
            $possibleData = array();
			
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
				$master_sql_products->setSQL_WHERE("p.products_model = '".$model."'");

				$master_query = "".$master_sql_products->getSQL_query()."";
				$master_record = $db->Execute($master_query);

				if($master_record->RecordCount()>0){

					$master_data_sql_tablecols = 'pa.products_id, pa.attributes_id, pa.attributes_parent_id';
					$master_data_sql_products = new getProductSQL_query();
					$master_data_sql_products->setPosition('plugin_ms_getPossibleData_master_data');
					$master_data_sql_products->setSQL_COLS(", " . $master_data_sql_tablecols);
					$master_data_sql_products->setSQL_TABLE("LEFT JOIN " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa ON pa.products_id = p.products_id");
					$master_data_sql_products->setSQL_WHERE("p.products_master_model = '".$master_record->fields['products_master_model']."'");

					$master_data_query = "".$master_data_sql_products->getSQL_query()."";
					$master_data_record = $db->Execute($master_data_query);

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
				if(($_POST['action'] == 'select_ms' or $_GET['action_ms'] == 1) && $this->isFilter()) {
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

				if ($tmp_options) {
					// does not seem to make a difference whether this is disabled
					$option_where.= " and attributes_parent_id IN (".implode(", ", $tmp_options).")";
				}

				if (is_array($possibleProducts)) {
					// array with counts of products
					$_pcount = array_count_values ($possibleProducts);
					$possibleProducts = array_unique($possibleProducts);

					$qry = "select products_id, attributes_id, attributes_parent_id from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " where products_id in (".implode(",", $possibleProducts).")".$option_where."";
					
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

					if(_PLUGIN_MASTER_SLAVE_REDIRECT_TO_SLAVE=='true' && count($this->possibleProducts)==1 && $this->possibleProducts[0] != $current_product_id){
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
				$sdata_sql_products->setSQL_WHERE("and p.products_master_model = '".$model."'");
                $sdata_sql_products->setSQL_SORT(' p.products_sort ASC');

				$sdata_query = "".$sdata_sql_products->getSQL_query()."";
				$sdata_record = $db->Execute($sdata_query);

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
			
			//Sort by products_sort
			$this->possibleProducts  = $this->sortPossibleProducts($this->possibleProducts );
	}

	
	/**
	 * 
	 * Returns dropdown boxes with different options as HTML
	 * 
	 * @return string $optionData HTML of options
	 */
	function getOptions(){
		global $product,$xtPlugin, $xtLink;

		if(_PLUGIN_MASTER_SLAVE_SHOW_OPTIONS == 'true') {
				$option_set = $this->buildOptionSet();

				// build two option sets with possible and all options and then merge them
				$optionSet_arr = $this->buildOptionSet('all');
				$mergedOptions_arr = $this->mergeOptions($option_set, $optionSet_arr);
				$master_pID = $this->isSlave();
				if ($master_pID) {
					// create link to master article
					$p_info = new product($master_pID,'full', '', '', 'product_info');
					$link_arr = array('page'=> 'product', 'type'=>'product', 'name'=>$p_info->data['products_name'], 'id'=>$p_info->data['products_id'],'seo_url'=>$p_info->data['url_text'], 'params'=>'reset_ms=1');
					$masterLink = $xtLink->_link($link_arr);
				}
				else {
					$masterLink = false;
				}
				
				// use $mergedOptions_arr instead of $option_set
				$tpl_data = array(
								'options' => $mergedOptions_arr,
								'pID' => $this->pID,
								'masterLink' => $masterLink
				);

				$tpl = _getSingleValue(array('value'=>'products_option_template', 'table'=>TABLE_PRODUCTS, 'key'=>'products_model', 'key_val'=>$this->master_model));

				if(!$tpl)
				$tpl = 'ms_default.html';
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

		// build either all or possible (default) options
		if ($mode_str == 'possible') {
			$modeOptions_arr = $this->possibleOptions;
			$modeValues_arr  = $this->possibleValues;
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
            
            $odata = $db->Execute("select pa.*,pad.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on pa.attributes_id = pad.attributes_id where pad.language_code = '" . $language->code . "' and pa.attributes_id in (".implode(",", $modeOptions_arr).") and pa.status = 1 order by pa.sort_order, pad.attributes_name");

			$rows = 0;		// = options
			$cols = 1;		// = values
			if($odata->RecordCount() > 0){
				while(!$odata->EOF){

				$select = false;
				$selected_value = $_SESSION['select_ms'][$this->pID]['id'][$odata->fields['attributes_id']];

				$oData_array[$rows] = array('id' => $odata->fields['attributes_id'],
									   		'text' => $odata->fields['attributes_name'],
											'desc' => $odata->fields['attributes_desc'],
											'model'=>$odata->fields['attributes_model'],
											'image'=>$odata->fields['attributes_image'],
									   		'data' => ''
											);

					$vdata = $db->Execute("select pa.*,pad.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on pa.attributes_id = pad.attributes_id where pa.attributes_parent = ".$odata->fields['attributes_id']." and pad.language_code = '" . $language->code . "' and pa.attributes_id in (".implode(",", $modeValues_arr).") and pa.status = 1 order by pa.sort_order, pad.attributes_name");

					if($vdata->RecordCount() > 0){
						$oData_array[$rows]['data'][0] = array('id' => 0,
															   'text' => TEXT_NO_SELECTION,
															   'desc' => ''
															);

						while(!$vdata->EOF){
						// check if selected
						
						$oData_array[$rows]['data'][$cols] = array('id' => $vdata->fields['attributes_id'],
																   'text' => $vdata->fields['attributes_name'],
																   'desc' => $vdata->fields['attributes_desc'],
																	'text' => $vdata->fields['attributes_name'],
																	'model'=>$vdata->fields['attributes_model'],
																	'image'=>$vdata->fields['attributes_image'],
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

			return $oData_array;
	}

	
    /**
    * get array with assigned options
    * 
    * @param mixed $pID products id
    * @return bool|array $data options of current product or false
    */
	function getAttributesData ($pID) {
		global $db;

		$option_data = $db->Execute("select products_id, attributes_id, attributes_parent_id from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " where products_id = '" . (int)$pID . "'");
        if ($option_data->RecordCount()==0) return false;
        $data = array();
        while (!$option_data->EOF) {
           $data[$option_data->fields['attributes_parent_id']]=$option_data->fields;
           $option_data->MoveNext(); 
        }

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
		global $db, $language;
		
		$option_data = $db->Execute("select padv.attributes_id as option_id, padv.attributes_name as option_name, pad.attributes_id as option_value_id, pad.attributes_name as option_value_name from " . TABLE_PRODUCTS_TO_ATTRIBUTES . " pa left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." pad on (pa.attributes_id = pad.attributes_id and pad.language_code = '" . $language->code . "') left join ".TABLE_PRODUCTS_ATTRIBUTES_DESCRIPTION." padv on (pa.attributes_parent_id = padv.attributes_id and padv.language_code = '" . $language->code . "') where pa.products_id = '" . $pID . "'");
		if($option_data->RecordCount() > 0){
			while(!$option_data->EOF){
				$data[] = $option_data->fields;
				$option_data->MoveNext();
			}
			$option_data->Close();
		}

		return $data;
	}

	
	/**
	 * 
	 * returns slave list as HTML or void, if no match is found
	 * 
	 * @param unknown_type $data NOT USED
	 * @return string|void $page_data slave products as HTML or void
	 */
	function getProductList($data=''){
		global $xtPlugin, $product, $template, $db;

		$tmp_attributes = array();

		if(_PLUGIN_MASTER_SLAVE_SHOW_SLAVE_LIST=='true'){
                
				if (count($this->possibleProducts) == 0) return;

				$count_products = count($this->possibleProducts);
				foreach ($this->possibleProducts as $pdata) {

					$tmp_data = & new product($pdata);
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
					$_pdata[$key]['products_link'] .= $glue_str."action_ms=1".$msGET;
				}

				// currently this is an all-or-nothing decision !!
				// TODO: show only part of the list up to specified max value - or maybe use split_page()
				if($count_products > 0 && $count_products <= _PLUGIN_MASTER_SLAVE_SHOW_MAX_PRODUCTS) {

					$tpl_data = array('product_listing' => $_pdata, 'options' => $tmp_attributes);

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

}
?>
