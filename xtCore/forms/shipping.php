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


$countries = new countries('true','store');
$countries_list = $countries->countries_list_sorted;
$add_data = array('country_data' => $countries_list);

if ($_POST['action']=='query')
{
	$sel_country = substr($_POST['shipping_destination'],0,2);
}
else {
    $sel_country = substr(_STORE_COUNTRY,0,2);
}

	$shipping = new shipping();
	$shipping->group_permission = $customers_status->customers_status_id;
	$shipping->shipping_address=array();
	$shipping->shipping_address['customers_country_code']=$sel_country;
	
	$country = $countries->_getCountryData($sel_country);
	$shipping->shipping_address['customers_zone'] = $country['zone_id'];
	$shipping_data = array();
$country_shipping_data = array();

	$data_array = $shipping->_getPossibleShipping();

    if (is_array($data_array))
    {
        foreach ($data_array as $key => $value)
        {
            if ($value['use_shipping_zone'] == 0)
            {
                $value = $shipping->_filterZone($value);
            $value = $shipping->_filterShippingCountry($value, $sel_country, $country['zone_id']);
            }
            else
            {
                $value = $shipping->_filterShippingZone($value);
            }
        // if we already have at least one $country_shipping_data and current $value has no hasCostForCountry we skip this value
        if (count($country_shipping_data) && $value['hasCostForCountry']== false)
            continue;

        if (is_array($value['costs']) && count($value['costs']))
            {
                foreach ($value['costs'] as $ckey => $cvalue)
                {
                    if ($value['shipping_type'] == 'price')
                    {
                        $tmp_price = $price->_getPrice(array('price' => $cvalue['shipping_type_value_from'], 'qty' => '1', 'tax_class' => '', 'format' => true, 'curr' => true, 'format_type' => 'default'));
                        if ($tmp_price['plain'] == '0')
                        {
                            $value['costs'][$ckey]['shipping_type_value_from'] = '0';
                        }
                        else
                        {
                            $value['costs'][$ckey]['shipping_type_value_from'] = $tmp_price['formated'];
                        }
                        $tmp_price = $price->_getPrice(array('price' => $cvalue['shipping_type_value_to'], 'qty' => '1', 'tax_class' => '', 'format' => true, 'curr' => true, 'format_type' => 'default'));
                        $value['costs'][$ckey]['shipping_type_value_to'] = $tmp_price['formated'];
                    }

                    if ($value['shipping_type'] == 'weight')
                    {
                        $weight_from = $cvalue['shipping_type_value_from'];
                        $weight_from = number_format($weight_from, 2, ',', '.');
                        if ($cvalue['shipping_type_value_from'] == '0')
                        {
                            $weight_from = '0';
                        }
                        $weight_to = $cvalue['shipping_type_value_to'];
                        $weight_to = number_format($weight_to, 2, ',', '.');

                        $value['costs'][$ckey]['shipping_type_value_from'] = $weight_from;
                        $value['costs'][$ckey]['shipping_type_value_to'] = $weight_to;
                    }

                    $tmp_price = $price->_getPrice(array('price' => $cvalue['shipping_price'], 'qty' => '1', 'tax_class' => $value['shipping_tax_class'], 'format' => true, 'curr' => true, 'format_type' => 'default'));
                    $value['costs'][$ckey]['shipping_price'] = $tmp_price['formated'];
                }

            if ($value['hasCostForCountry'])
            {
                $country_shipping_data[] = $value;
            } else
                $shipping_data[] = $value;
            }
        }
    }
	
if (count($shipping_data)==0 && count($country_shipping_data)==0) {
		$info->_addInfo(WARNING_NO_SHIPPING_FOR_ZONE,'warning');
	}
if (count($country_shipping_data))
    $add_data = array_merge($add_data,array('shipping_data' => $country_shipping_data));
else
	$add_data = array_merge($add_data,array('shipping_data' => $shipping_data));


$brotkrumen->_addItem($xtLink->_link(array('page'=>'content', 'params'=>'coID='.$shop_content_data['content_id'],'seo_url' => $shop_content_data['url_text'])),$shop_content_data['title']);

$template = new Template();
$tpl_data = array('message'=>$info->info_content,'data'=>$shop_content_data, 'subdata'=>$subdata,'sel_country'=>$sel_country);
if (is_array($add_data)) $tpl_data = array_merge($tpl_data,$add_data);
$tpl = 'shipping.html';
($plugin_code = $xtPlugin->PluginCode('module_content.php:tpl_data')) ? eval($plugin_code) : false;
$page_data = $template->getTemplate('smarty', '/'._SRV_WEB_CORE.'forms/'.$tpl, $tpl_data);
?>