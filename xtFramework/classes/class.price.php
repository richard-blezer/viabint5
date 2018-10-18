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

class price {

	function price($price_group, $master_price_group, $force_currency = '')
	{
		global $xtPlugin, $order_edit_controller;

		$order_edit_controller->hook_price_top($price_group, $master_price_group, $force_currency);

		($plugin_code = $xtPlugin->PluginCode('class.price.php:price_top')) ? eval($plugin_code) : false;
		if (isset($plugin_return_value)) return $plugin_return_value;

		$this->price_group = $price_group;
		$this->master_price_group = $master_price_group;

		if ( ! empty($force_currency)) $this->force_currency = $force_currency;

		$this->p_group = empty($master_price_group)
			? $price_group
			: $master_price_group;
	}

	function _setCurrency($curr){
		global $currency;
		
		$this->force_currency = $curr;
		$currency = new currency($curr);
		
	}

	function _getPrice($data){
		global $xtPlugin, $tax, $customers_status;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getPrice_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$products_tax = $tax->data[$data['tax_class']];

		if ($customers_status->customers_status_show_price_tax == '0')
		$products_tax = '';
		
		// Products Price
		$price = $data['price'];
		$format_type = 'default';

		// Check Currency
		if($data['curr']=='true')
		$price = $this->_calcCurrency($price);

		// Set Price without Tax
		$price_otax = $price;

		// Add Tax
		if(!empty($products_tax))
		$price = $this->_AddTax($price, $products_tax);
		
		$price = $this->_roundPrice($price);
		
		if(!empty($data['qty'])){
			$price = $price * $data['qty'];
			$price_otax = $price_otax * $data['qty'];
		}
		
		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getPrice_afterProductsPrice')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;


		$format_array = array('price'=>$price, 'price_otax'=>$price_otax, 'format'=>$data['format'], 'format_type' => 'default');
		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getPrice_Format')) ? eval($plugin_code) : false;
		$price_data = $this->_Format($format_array);
		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getPrice_bottom')) ? eval($plugin_code) : false;

		
		return $price_data;
	}

	function _AddTax($price, $tax) {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_AddTax_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$price = $price + $price / 100 * $tax;
		return $price;
	}

	function _calcTax($price, $tax) {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_calcTax_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		return $price * $tax / 100;
	}

	function _removeTax($price, $tax) {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_removeTax_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$price = ($price / (($tax +100) / 100));
		return $price;
	}

	function _getTax($price, $tax) {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getTax_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$tax = $price - $this->_removeTax($price, $tax);
		return $tax;
	}

	function _calcCurrency($price){
		global $xtPlugin, $currency;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_calcCurrency_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if ($this->force_currency!='') {
			$currency = new currency($this->force_currency);
		}

		if($currency->default_currency == $currency->code){
            $price = $price*$currency->value_multiplicator;
            return $price;
		}else{
			$price = $price*$currency->value_multiplicator;
			return $price;
		}

	}

	function _getPriceDiscount($price,$discount) {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getPriceDiscount_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$price = $price - ($price/100*$discount);

		return $price;
	}

	function _getDiscount($price,$discount) {
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_getDiscount_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$discount = $price/100*$discount;

		return $discount;
	}

	function _BuildPrice($pprice, $tax_class, $type='show'){
		global $tax;

		$tax_percent = $tax->data[$tax_class];

		$pprice = str_replace(',', '.', $pprice);

		if(_SYSTEM_USE_PRICE=='true'){

			if($type=='show'){
				$pprice = $this->_AddTax($pprice, $tax_percent);
				$pprice = $this->_roundPrice($pprice);
			}elseif($type=='save'){
				$pprice = $this->_removeTax($pprice, $tax_percent);
			}

		}
		
		return $pprice;
	}

	function getTaxClass($get_id, $table, $where_id, $where_value){
		global $db;

		$query = "SELECT ".$get_id." FROM ".$table."  WHERE ".$where_id."='".$where_value."'";
		$rs = $db->Execute($query);
		if ($rs->RecordCount()>0) {
			return $rs->fields[$get_id];
		}
	}

	function _roundPrice($price){
		global $xtPlugin, $currency;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_calcCurrency_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$price = round($price, $currency->decimals);

		return $price;
	}	
	
	function _Format($data) {
		global $xtPlugin, $template, $currency;

		($plugin_code = $xtPlugin->PluginCode('class.price.php:_Format_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
		
		
		if($data['format']== true){

			switch ($data['format_type']) {

				case 'default':

					$price = $data['price'];
					$price_otax = $data['price_otax'];

					$price = (string)$price;
					$price = (float)$price;
					$Fprice= $this->_StyleFormat($price);

					$price_otax = (string)$price_otax;
					$price_otax = (float)$price_otax;
					$Fprice_otax= $this->_StyleFormat($price_otax);

					$tpl_data = array('PRICE' => array('formated'=>$Fprice,'plain'=>$price), 'PRICE_OTAX'=>array('formated'=>$Fprice_otax,'plain'=>$price_otax, 'date_available' => '', 'date_expired' => ''));
					$tpl = 'price.html';
				break;

				case 'special':

					$price = $data['price'];
					$price_otax = $data['price_otax'];
					$old_price = $data['old_price'];
					$old_price_otax = $data['old_price_otax'];

					$price = (string)$price;
					$price = (float)$price;
					$Fprice= $this->_StyleFormat($price);

					$price_otax = (string)$price_otax;
					$price_otax = (float)$price_otax;
					$Fprice_otax= $this->_StyleFormat($price_otax);

					$old_price = (string)$old_price;
					$old_price = (float)$old_price;
					$old_Fprice= $this->_StyleFormat($old_price);

					$old_price_otax = (string)$old_price_otax;
					$old_price_otax = (float)$old_price_otax;
					$old_Fprice_otax= $this->_StyleFormat($old_price_otax);
					
					$saving_price = $old_price-$price;
					$saving_price = (string)$saving_price;
					$saving_price = (float)$saving_price;
					$saving_Fprice= $this->_StyleFormat($saving_price);


					$tpl_data = array('SPECIAL_PRICE_SAVE'=>array('formated'=>$saving_Fprice,'plain'=>$saving_price),'SPECIAL_PRICE' => array('formated'=>$Fprice,'plain'=>$price), 'SPECIAL_PRICE_OTAX'=>array('formated'=>$Fprice_otax,'plain'=>$price_otax),'OLD_PRICE'=>array('formated'=>$old_Fprice,'plain'=>$old_price),'OLD_PRICE_OTAX'=>array('formated'=>$old_Fprice_otax,'plain'=>$old_price_otax), 'date_expired' => $data['date_expired'], 'date_available' => $data['date_available']);
					$add_return_array = array('old_plain'=>$old_price,'old_plain_otax'=>$old_price_otax,'old_plain_formated'=>$old_Fprice, 'date_expired' => $data['date_expired'], 'date_available' => $data['date_available']);
                    $tpl = 'price_special.html';
				break;

				case 'graduated':

					$price = $data['price'];
					$price_otax = $data['price_otax'];
					$cheapest_price = $data['cheapest_price'];
					$cheapest_price_otax = $data['cheapest_price_otax'];

					$price = (string)$price;
					$price = (float)$price;
					$Fprice= $this->_StyleFormat($price);

					$price_otax = (string)$price_otax;
					$price_otax = (float)$price_otax;
					$Fprice_otax= $this->_StyleFormat($price_otax);

					$cheapest_price = (string)$cheapest_price;
					$cheapest_price = (float)$cheapest_price;
					$cheapest_Fprice= $this->_StyleFormat($cheapest_price);

					$cheapest_price_otax = (string)$cheapest_price_otax;
					$cheapest_price_otax = (float)$cheapest_price_otax;
					$cheapest_Fprice_otax= $this->_StyleFormat($cheapest_price_otax);

					$tpl_data = array('PRODUCTS_PRICE' => array('formated'=>$Fprice,'plain'=>$price), 'PRODUCTS_PRICE_OTAX'=>array('formated'=>$Fprice_otax,'plain'=>$price_otax),'CHEAPEST_PRICE'=>array('formated'=>$cheapest_Fprice,'plain'=>$cheapest_price),'CHEAPEST_PRICE_OTAX'=>array('formated'=>$cheapest_Fprice_otax,'plain'=>$cheapest_price_otax), 'date_expired' => $data['date_expired'], 'date_available' => $data['date_available']);
					$tpl = 'price_graduated.html';
					break;

				case 'graduated-table':

					$tpl_data = array('GRADUATED_PRICES'=>$data['prices']);
					$tpl = 'graduated_table.html';
					break;

				default:
				($plugin_code = $xtPlugin->PluginCode('class.price.php:_FormatType_data')) ? eval($plugin_code) : false;
				if(isset($plugin_return_value))
				return $plugin_return_value;
			}


			$template = new Template();
			($plugin_code = $xtPlugin->PluginCode('class.price.php:_Format_data')) ? eval($plugin_code) : false;
			if(isset($plugin_return_value))
			return $plugin_return_value;
			
			$tpl_price = $template->getTemplate('price_smarty','/'._SRV_WEB_CORE.'pages/price/'.$tpl,$tpl_data);
            $return_array = array ('formated' => $tpl_price, 'plain' => $price, 'plain_otax' => $price_otax);
            if (isset($add_return_array)) $return_array=array_merge($return_array,$add_return_array);
			return $return_array;

		} else {
			$price = $data['price'];
			$price_otax = $data['price_otax'];
			return array ('plain' => round($price, $currency->decimals), 'plain_otax' => round($price_otax, $currency->decimals));
		}
	}

	function _StyleFormat($price) {
		global $currency;
		
		$Fprice = number_Format($price, $currency->decimals, $currency->dec_point, $currency->thousands_sep);
		$Fprice = $currency->prefix.' '.$Fprice.' '.$currency->suffix;
		return $Fprice;
	}

	function buildPriceData($price, $tax_class_id = 0, $curr = true) {
	    global $tax;

	    $tax_rate = $tax->data[$tax_class_id];
		// Check Currency
		if($curr == true)
		$price = $this->_calcCurrency($price);
		// Set Price without Tax
		$price_otax = $price;
		// Add Tax
		$price = $this->_AddTax($price, $tax_rate);
        return array(
			'plain_otax' => $price_otax,
			'formated_otax' => $this->_StyleFormat($price_otax),
			'tax_rate' => $tax_rate,
			'tax' => $price - $price_otax,
			'formated_tax' => $this->_StyleFormat($price - $price_otax),
			'plain' => $price,
			'formated' => $this->_StyleFormat($price)
		);
	}
}