<?php
/*------------------------------------------------------------------------------
	$Id: affiliate_inventory.php 67 2011-10-07 16:54:32Z Standard $
	copyright (c) 2008 by Andreas Oberzier
	http://www.netz-designer.de
	projects@netz-designer.de
	---------------------------------------
	project: Affiliate-Plugin für xt:Commerce Enterprise
	
	This file may not be redistributed in whole or significant part.
------------------------------------------------------------------------------*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(!isset($_SESSION['affiliate_id'])) {
	$xtLink->_redirect($xtLink->_link(array()));
}

require_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'/nd_affiliate/classes/class.nd_affiliate_inventory.php';

$tpl_data = array();

switch($_GET['type']) {
	case 'i':
		$type = " AND inventory_image != ''";
		break;
	case 'h':
		$type = " AND inventory_html != ''";
		break;
	case 't':
		$type = " AND inventory_text != ''";
		break;
	default:
		$type = '';
		break;
}

if(AFFILIATE_AFFILIATE_GLOBAL == 'false') {
	$shop_clause = " AND affiliate_shop_id = '" . $store_handler->shop_id . "'";
}

$record = $db->Execute("SELECT *
						FROM " . TABLE_AFFILIATE_INVENTORY . "
						WHERE inventory_status = 1" .
						$type . 
						$shop_clause);

$inventory_array = array();

while(!$record->EOF) {
	$inventory = new nd_affiliate_inventory($record->fields['inventory_id']);
	$inventory->deliveryRef = $_SESSION['affiliate_id'];
	$inventory->deliveryType = $_GET['type'];
	$inventory_array[] = $inventory->drawInventoryCode();
	unset($inventory);
	$record->MoveNext();
}

$tpl_data['inventories'] = $inventory_array;

$tpl = 'affiliate_inventory.html';

$template = new Template();
$template->getTemplatePath($tpl, 'nd_affiliate', '', 'plugin');
$page_data = $template->getTemplate('nd_affiliate_affiliate', $tpl, $tpl_data);
?>