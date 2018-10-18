<?php
include_once(dirname(__FILE__).'/../shopgate_library/shopgate.php');
require_once('class.shopgate_config_veyton.php');
$shopgateMobileHeader = '';
$shopgateJsHeader = '';
try {
	$shopgate_config = new ShopgateConfigVeyton();
	$shopgate_config->loadConfigFromDatabase();
	
	// instantiate and set up redirect class
	$shopgateBuilder = new ShopgateBuilder($shopgate_config);
	$redirector = $shopgateBuilder->buildRedirect();
		
	############################
	# redirector configuration #
	############################
	
	// set redirection url
	if (!empty($p_info) && ((!empty($p_info->data) && !empty($p_info->data['products_model'])) || !empty($p_info->pID))) {
		// product redirect
		if (!empty($p_info->pID)) {
			$model = $p_info->pID;
		} else {
			$model = $p_info->data["products_model"];
		}
		
		$shopgateJsHeader = $redirector->buildScriptItem($model);
	} else if (!empty($category) && !empty($category->current_category_id)) {
		// category redirect
		$shopgateJsHeader = $redirector->buildScriptCategory($category->current_category_id);
	} else {
		// default redirect
		$shopgateJsHeader = $redirector->buildScriptShop();
	}
} catch(Exception $e) {
	// never abort in front-end pages!
}


?>