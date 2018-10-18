<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: ajax.php 132 2012-10-19 12:39:38Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

function pipethough ($str, $doit = false) {
	if ($doit) echo $str;
	return $str;
}
if (isset($_GET['request'])) {
	$request = $_GET['request'];

	if ($request == 'refresh') {
		$key = $_GET['key'];

		if ($key == 'product_name') {
			$r = MagnaDB::gi()->fetchArray(
				'SELECT `products_name` FROM `'.TABLE_PRODUCTS_DESCRIPTION.'` '.
				'WHERE `products_id`= \''.(int)$magnaDB->escape($_GET['id'])."' ".' '.
				'AND `language_code` = \''.$_SESSION['selected_language']."' LIMIT 0,1"
			);
			$r = $r[0]['products_name'];
			echo json_encode(array('result' => $r));

		} else if ($key == 'product_description') {
			$r = MagnaDB::gi()->fetchArray(pipethough(
				'SELECT `products_description` FROM `'.TABLE_PRODUCTS_DESCRIPTION.'` '.
				'WHERE `products_id`= \''.(int)$magnaDB->escape($_GET['id'])."' ".' '.
				'AND `language_code` = \''.$magnaDB->escape($_GET['selected_language'])."' LIMIT 0,1"
			), false);
			$r = $r[0]['products_description'];
			echo json_encode(array('result' => $r));

		} else if ($key == 'product_price') {
			$r = MagnaDB::gi()->fetchRow(pipethough(
				'SELECT `products_price`, `products_tax_class_id` FROM `'.TABLE_PRODUCTS.'` '.
				'WHERE `products_id`= \''.(int)MagnaDB::gi()->escape($_GET['id'])."'"
			), false);
			
			$cprice = new SimplePrice($r['products_price'], $magnaConfig['db']['general.Currency']);
			if (!(isset($_GET['netto']) && ($_GET['netto'] == 'true'))) {
				$cprice->addTaxByTaxID($r['products_tax_class_id']);
			}
			$finalPrice = $cprice->calculateCurr()->roundPrice()->getPrice();
			
			$r = number_format($finalPrice, 2, ',', '');
			echo json_encode(array('result' => $r));
		}
	}

} else if (isset($_POST['request'])) {
	$request = $_POST['request'];
	
}
