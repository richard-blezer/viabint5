<?php
if (
	(USER_POSITION != 'admin') && 
	isset($xtPlugin->active_modules['xt_customers_online_tracking']) && 
	($xtPlugin->active_modules['xt_customers_online_tracking'] == 'true') && 
	!($agent_check->isBot()) 

) {
	$shop = new multistore();
	$shop->determineStoreId();
	$stats_data = array(
		'sess_id' => session_id(),
		'shop_id' => (int)$shop->shop_id,
		'customers_id' => null,
		'customers_status' => null,
		'date_added' => date('Y-m-d h:i:s', strtotime('now')),
	);
	if (isset($_SESSION['registered_customer']) && $_SESSION['registered_customer']) {
		$stats_data['customers_id'] = (int)$_SESSION['customer']->customer_info['customers_id'];
		$stats_data['customers_status'] = (int)$_SESSION['customer']->customer_info['customers_status'];
	}
	
	$db->Execute("REPLACE INTO " . DB_PREFIX . '_customers_online_stats (sess_id, shop_id, customers_id, customers_status, date_added) 
	            VALUES ("'.join('","', $stats_data).'");');
}