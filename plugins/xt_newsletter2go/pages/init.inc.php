<?php
	include "../../../xtCore/main.php";
	$api_plugin = "xt_newsletter2go";
	$table_plugin_products = TABLE_PLUGIN_PRODUCTS;
	$table_plugin_code = TABLE_PLUGIN_CODE;
	$table_customers = TABLE_CUSTOMERS;

	$sql_plugin = "SELECT plugin_status
				   FROM $table_plugin_products
				   INNER JOIN $table_plugin_code
				   ON $table_plugin_products.plugin_id = $table_plugin_code.plugin_id
				   WHERE $table_plugin_code.plugin_code =? ";

	$plugin_status = (int)$db->GetOne($sql_plugin,array($api_plugin));
	$error = "";
	try {
		if (!is_int($plugin_status))
			throw new Exception("Plugin not found");
		if (!$plugin_status)
			throw new Exception("Plugin not installed");
	} catch (Exception $e) {
	    $error = $e->getMessage();
	}

	try {
		if (isset($_POST['user']) && isset($_POST['pass'])) {
			if (XT_NEWSLETTER2GO_API_USER == "" || XT_NEWSLETTER2GO_API_KEY == "")
				throw new Exception("No user and password in configuration");
			elseif (XT_NEWSLETTER2GO_API_USER != $_POST['user'] || XT_NEWSLETTER2GO_API_KEY != $_POST['pass'])
				throw new Exception("Api credentials are incorrect");
		}
		else
			throw new Exception("Api credentials incomplete");
	} catch (Exception $e) {
	    $error = $e->getMessage();
	}