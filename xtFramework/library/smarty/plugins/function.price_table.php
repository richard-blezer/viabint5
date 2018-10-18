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

function smarty_function_price_table($params, & $smarty) {
	global $xtPlugin,$p_info,$price, $tax;

	if ((int)$params['pid'] > 0) {
		$tmp_p = new product($params['pid']);
	}
	

	if (!is_object($p_info) && !is_object($tmp_p)) return false;
		
	if (is_object($p_info) && is_array($p_info->data['group_price']['prices'])) {
		$price_matrix = $p_info->data['group_price']['prices'];
	}
	if (is_object($p_info) && is_array($tmp_p->data['group_price']['prices'])) {
		$price_matrix = $tmp_p->data['group_price']['prices'];
	}
	
	if (is_array($price_matrix)) {

		// calculate taxes
		$group_prices = array();
		for ($i = 0, $n = sizeof($price_matrix); $i < $n; $i ++) {
			if ($price_matrix[$i]['qty'] == 1) {
				$qty = $price_matrix[$i]['qty'];
				if (isset($price_matrix[$i +1]['qty']))
				$qty = $price_matrix[$i]['qty'].'-'. ($price_matrix[$i +1]['qty'] - 1);
			} else {
				$qty = ' >= '.$price_matrix[$i]['qty'];
				if (isset($price_matrix[$i +1]['qty']))
				$qty = $price_matrix[$i]['qty'].'-'. ($price_matrix[$i +1]['qty'] - 1);
			}
			$g_price = $price_matrix[$i]['price'];
            if ($i==0) $base_price = $g_price; 

            $saving = 0;
            if ($i>0 && $g_price!=0 && $base_price!=0) {
                $saving = 100-($g_price/$base_price*100);
                $saving = round($saving,1);
            }
            
            $g_price = $price->_AddTax($g_price, $p_info->data['products_tax_rate']);
			$g_price=array('plain'=>$g_price,'formated'=>$price->_StyleFormat($g_price));
			
			if ($p_info->data['products_vpe_status'] == 1) {
				$pricePerUnit_num = $price->_StyleFormat($g_price['plain']/$p_info->data['products_vpe_value']);
				$perUnitHeader_str = TEXT_SHIPPING_BASE_PER.'&nbsp;'.$p_info->data['base_price']['vpe']['name'];

				$group_prices[] = array ('QTY' => $qty, 'PRICE' => $g_price,'saving'=>$saving, 'pricePerUnit'=>$pricePerUnit_num, 'perUnitHeader'=>$perUnitHeader_str);
			}
			else {
				$group_prices[] = array ('QTY' => $qty, 'PRICE' => $g_price,'saving'=>$saving);
			}
			
		}
		$template = $price->_Format(array('prices'=>$group_prices,'format'=>true,'format_type'=>'graduated-table'));
		echo $template['formated'];

	} else {
		return false;
	}
}
?>