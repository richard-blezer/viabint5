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

class checkout{

	function checkout(){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:checkout_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

	}

	function _selectShipping(){
		global $xtPlugin, $template,$brotkrumen,$xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_selectShipping_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$contents = $this->_getShipping();

		$content_count = count($contents);

		if(is_data($contents)){
			while (list ($key, $value) = each($contents)) {

				if($content_count==1){
					$value['shipping_hidden'] = true;
				}

				$tpl_data = array();
				$tmp_data = '';
				$tpl_data = $value;

				if(!empty($value['shipping_tpl'])){
					$tpl = $value['shipping_tpl'];
				}else{
					$tpl = 'shipping_default.html';
				}

				$template = new Template();
				$template->getTemplatePath($tpl, $value['shipping_dir'], '', 'shipping');

				($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_selectShipping_tpl_data')) ? eval($plugin_code) : false;
				$tmp_data = $template->getTemplate($value['shipping_code'].'_shipping_smarty', $tpl, $tpl_data);

				$data[] = array('shipping' => $tmp_data);
			}
		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_selectShipping_bottom')) ? eval($plugin_code) : false;
		return $data;
		}
	}

	function _getShipping(){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_getShipping_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$shipping = new shipping();
		$shipping->_shipping();
		$shipping_data = $shipping->shipping_data;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_getShipping')) ? eval($plugin_code) : false;
		return $shipping_data;

	}

	function _setShipping($shipping){
		global $xtPlugin;

		if (!$shipping) return false;
		
		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_setShipping_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$_SESSION['selected_shipping'] = $shipping;

	}

	function _selectPayment(){
		global $xtPlugin, $template,$brotkrumen,$xtLink;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_selectPayment_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$contents = $this->_getPayment();

		$content_count = count($contents);

		if(is_data($contents)){
			while (list ($key, $value) = each($contents)) {

				if($content_count==1){
					$value['payment_hidden'] = true;
				}

				$tpl_data = array();
				$tmp_data = '';
				$tpl_data = $value;
				$tpl_data['payment_country_code']=$_SESSION['customer']->customer_payment_address['customers_country_code'];

				if(!empty($value['payment_tpl'])){
					$tpl = $value['payment_tpl'];
				}else{
					$tpl = 'payment_default.html';
				}

				$template = new Template();

				$template->getTemplatePath($tpl, $value['payment_dir'], '', 'payment');
				
				($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_selectPayment_tpl_data')) ? eval($plugin_code) : false;
				$tmp_data = $template->getTemplate($value['payment_code'].'_payment_smarty', $tpl, $tpl_data);

				$data[] = array('payment' => $tmp_data);
			}
		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_selectPayment_bottom')) ? eval($plugin_code) : false;
		return $data;
		}
	}

	function _getPayment(){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_getPayment_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$payment = new payment();
		$payment->_payment();
		$payment_data = $payment->payment_data;

		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_getPayment')) ? eval($plugin_code) : false;
		return $payment_data;

	}

	function _setPayment($payment){
		global $xtPlugin;
		
		if (!$payment) return false;
		
		($plugin_code = $xtPlugin->PluginCode('class.checkout.php:_setPayment_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$_SESSION['selected_payment'] = $payment;

	}
}