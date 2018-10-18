<?php
/*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Enterprise
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright �2007-2008 xt:Commerce GmbH. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~~~~ xt:Commerce VEYTON 4.0 Enterprise IS NOT FREE SOFTWARE ~~~~~~~~~~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: class.xt_sperrgut.php 8 2009-02-10 20:43:24Z mzanier $
 # @copyright xt:Commerce GmbH, www.xt-commerce.com
 #
 # @author Mario Zanier, xt:Commerce GmbH	mzanier@xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce GmbH, Bachweg 1, A-6091 Goetzens (AUSTRIA)
 # office@xt-commerce.com
 #
 #########################################################################
 */


defined('_VALID_CALL') or die('Direct Access is not allowed.');


class xt_sperrgut{


	protected $_table = TABLE_XT_SPERRGUT;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'id';

	function xt_sperrgut() {
		global $db;
		$result = array();
		$rs = $db->Execute("SELECT * FROM ".TABLE_XT_SPERRGUT);
		while (!$rs->EOF) {
			$result[$rs->fields['id']] =  array('id' => $rs->fields['id'],
                             'name' => $rs->fields['description'],
							 'price' => $rs->fields['price'],
                             'desc' => '');
			$rs->MoveNext();
		}
		$this->prices = $result;
	}

	function setPosition ($position) {
		$this->position = $position;
	}

	function _get($ID = 0) {
		global $xtPlugin, $db, $language;

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

		$obj = new stdClass;
		$obj->totalCount = count($data);
		$obj->data = $data;

		return $obj;
	}

	function _set($data, $set_type = 'edit') {
		global $db,$language,$filter;

		$obj = new stdClass;
		$o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
		$obj = $o->saveDataSet();

		return $obj;
	}

	function _unset($id = 0) {
		global $db;

		if ($id == 0) return false;
		if ($this->position != 'admin') return false;
		$id=(int)$id;
		if (!is_int($id)) return false;

		$db->Execute("DELETE FROM ". $this->_table ." WHERE ".$this->_master_key." = ?", array($id));

	}

	function _getParams() {
		$params = array();

		$header['description'] = array('type' => 'input');
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = $this->_master_key;
		$params['languageTab']    = false;
		$params['edit_masterkey'] = false;

		return $params;
	}

	function _determinePrice() {

		$items = $_SESSION['cart']->show_content;

		$ship_price = 0;
        switch(XT_SPERRGUT_CALCULATE_MODULE){
            case 'total':
                foreach ($items as $key => $arr) {

                    if ($arr['xt_sperrgut_class']>0) {
                        if (is_array($this->prices[$arr['xt_sperrgut_class']]))
                            $ship_price+=$this->prices[$arr['xt_sperrgut_class']]['price']*$arr['products_quantity'];
                    }
                }
                break;
            case 'single':
                foreach ($items as $key => $arr) {

                    if ($arr['xt_sperrgut_class']>0) {
                        if (is_array($this->prices[$arr['xt_sperrgut_class']]))
                            $ship_price+=$this->prices[$arr['xt_sperrgut_class']]['price'];
                    }
                }
                break;
            case 'onemax':
                $minmax = 0;
                foreach ($items as $key => $arr) {

                    if ($arr['xt_sperrgut_class']>0) {
                        if (is_array($this->prices[$arr['xt_sperrgut_class']]) ){
                            $ship_price = $this->prices[$arr['xt_sperrgut_class']]['price'];
                            if($ship_price > $minmax){
                                $minmax = $ship_price;
                            }
                        }

                    }
                }
                $ship_price = $minmax;
                break;
            case 'onemin':
                $minmax = 100000;
                foreach ($items as $key => $arr) {

                    if ($arr['xt_sperrgut_class']>0) {
                        if (is_array($this->prices[$arr['xt_sperrgut_class']]) ){
                            $ship_price = $this->prices[$arr['xt_sperrgut_class']]['price'];
                            if($ship_price<$minmax){
                                $minmax = $ship_price;
                            }
                        }

                    }
                }
                $ship_price = $minmax;
                break;
        }

		$this->price = $ship_price;

	}

	function _addToCart() {
		unset($_SESSION['cart']->show_sub_content['xt_sperrgut']);
		unset($_SESSION['cart']->sub_content['xt_sperrgut']);
		$sperrgut_data_array = array('customer_id' => $_SESSION['registered_customer'],
											 'qty' => '1',
											 'name' => TEXT_XT_SPERRGUT_TITLE,
											 'price' => $this->price,
											 'tax_class' => XT_SPERRGUT_TAX_CLASS,
											 'sort_order' => XT_SPERRGUT_TAX_SORTING,
											 'type' => 'xt_sperrgut'
											 );

		if ($this->price>0) {
			$_SESSION['cart']->_addSubContent($sperrgut_data_array);
			$_SESSION['cart']->_refresh();
		}

	}

	function _displaySperrgutTpl() {
		global $price;
		
		if ($this->price>0) {
			$tpl_data = array('sperrgut_info'=>sprintf(TEXT_XT_SPERRGUT_INFO,$_SESSION['cart']->show_sub_content['xt_sperrgut']['products_price']['formated']),'sperrgut_price'=>$this->price);
			$tpl = 'xt_sperrgut_shipping.html';

			$template = new Template();
			$template->getTemplatePath($tpl, 'xt_sperrgut', '', 'plugin');

			$page_data = $template->getTemplate('xt_sperrgut_smarty', $tpl, $tpl_data);
			return $page_data;
		}
		return;
	}
}
?>