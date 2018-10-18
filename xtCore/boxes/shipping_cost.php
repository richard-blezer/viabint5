<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if (is_object($_SESSION['cart']) && count($_SESSION['cart']->show_content) > 0)
{
	include_once 'xtFramework/classes/class.countries.php';
	
	$tpl = 'shipping_cost.html';

	$countries = new countries(TRUE,'store');

	$count_countries = count($countries->countries_list);
	if ($count_countries==1) $_POST["coupons_country"] = $countries->countries_list_sorted[0]["countries_iso_code_2"];
	$cost ='';
	$shipping = new shipping();
	$shipping_data  = '';
	if (($_POST['action']=='select_shipping_cost') || ($count_countries==1))
	{
		$ar = array();$data = array();
		if ($_POST["coupons_country"]!='') 
		{
			$selected_country = $countries->countries_list[$_POST["coupons_country"]]; 
		}
		$data['customer_shipping_address']['customers_country_code'] = $_POST["coupons_country"];
		$data['customer_shipping_address']['customers_zone'] = $selected_country["zone_id"];
		$shipping->_shipping($data);
		$shipping_data = $shipping->shipping_data;
		$count_shipping= count($shipping_data);
		
		if ($count_shipping==1) 
		{
			foreach($shipping_data as $item)
			{
				$_POST["coupons_shipping"]=$item ["shipping_code"];
			}
		}
		
		if ($_POST["coupons_shipping"]!='') 
		{
			$cost = $shipping_data[$_POST["coupons_shipping"]]["shipping_price"]["formated"];
		}
		
	} 
	
	$tpl_data['count_countries'] = $count_countries;
	$tpl_data['count_shipping'] = $count_shipping;
	$tpl_data['coupons_country'] = $countries->countries_list_sorted; //array(array('id'=>1,'text'=>'BG'),array('id'=>2,'text'=>'EN'));
	$tpl_data['coupons_shipping'] =$shipping_data;
	$tpl_data['selected_coupons_shipping'] = $_POST["coupons_shipping"];
	$tpl_data['selected_country'] = $_POST["coupons_country"];
	$tpl_data['cost'] = $cost;
	
	$show_box = true;
}
else $show_box = false;
?>