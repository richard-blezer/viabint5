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
 # @version $Id: class.xt_countryprices.php 152 2011-03-30 16:39:58Z mzanier $
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

class xt_countryprices {

	var $master_id = 'id';

	function setPosition ($position) {
		$this->position = $position;
	}

	function _getParams() {
		$params = array();


		
		$header['id'] = array('type' => 'hidden');
		$header['products_id'] = array('type' => 'hidden');
		
		$header['country_code'] = array(
									'type' => 'dropdown', 								// you can modyfy the auto type
									'url'  => 'DropdownData.php?get=countries');

        $params['display_checkCol']  = true;
        $params['display_statusTrueBtn']  = true;
        $params['display_statusFalseBtn']  = true;
                                    
		$params['header']         = $header;
		$params['master_key']     = $this->master_id;
		


		return $params;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language, $customers_status;
		if ($this->position != 'admin') return false;

		if ($ID === 'new') {
			$obj = $this->_set(array(), 'new');
			$ID = $obj->new_id;
		}
        $sec_array = array((int)$this->url_data['products_id']);
		if ($ID){
		    $where .= ' and id = ?';
            array_push($sec_array,(int)$ID);
		}
		

		$record = $db->Execute("SELECT * FROM ".TABLE_PRODUCTS_PRICE_COUNTRY." WHERE products_id=? ".$where,$sec_array);

		if($record->RecordCount() >= 1){
			while (!$record->EOF) {
				$data[] = $record->fields;
				$record->MoveNext();
			}
            if(is_array($data)&&count($data)!=0){
                    foreach ($data as $key => $val) {
                        $data[$key]['country_price'] = $this->build_price($data[$key]['products_id'], $data[$key]['country_price']);
                    }
            }
		}else{
			if($this->url_data['get_data']!=true){
				$data[] = getEmptyDataset(TABLE_PRODUCTS_PRICE_COUNTRY);
			}
		}
        $obj = new stdClass;
		if(count($data) > 0){
			$obj->data = $data;
			$obj->totalCount = count($obj->data);
			return $obj;
		}else{
		    $obj->data = false;
			return $obj;
		}
	}

	function _set($data, $set_type = 'edit'){
		global $db,$language,$filter;

		$obj = new stdClass;

		foreach ($data as $key => $val) {

			if($val == 'on')
			$val = 1;

			$data[$key] = $val;
		}

		if($this->url_data['products_id'] && !$data['products_id'])
		$data['products_id'] = $this->url_data['products_id'];
        
        if($set_type=='edit')
        $data['country_price'] = $this->build_price($data['products_id'], $data['country_price'], '', 'save');

		$oP = new adminDB_DataSave(TABLE_PRODUCTS_PRICE_COUNTRY, $data, false, __CLASS__);


		$obj = $oP->saveDataSet();

		return $obj;
	}


	function _unset($id) {
		global $db;

		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if(!is_int($id)) return false;

		$rs = $db->Execute("SELECT products_id FROM ".TABLE_PRODUCTS_PRICE_COUNTRY." WHERE ".$this->master_id." = ?",array($id));

		$db->Execute("DELETE FROM ". TABLE_PRODUCTS_PRICE_COUNTRY ." WHERE ".$this->master_id." = ?",array($id));


	}
    
    function _setStatus($id, $status) {
        global $db,$xtPlugin;

        $id = (int)$id;
        if (!is_int($id)) return false;

        $db->Execute("update " . TABLE_PRODUCTS_PRICE_COUNTRY . " set status = ? where ".$this->master_id." = ?",array((int)$status,$id));

    }
    
    function build_price($id, $pprice, $tax_class='', $type='show'){
        global $price;
        
        if(!$tax_class)
        $tax_class = $price->getTaxClass('products_tax_class_id', TABLE_PRODUCTS, 'products_id', $id);
        
        $pprice = $price->_BuildPrice($pprice, $tax_class, $type);
        return $pprice;
    }
}
?>