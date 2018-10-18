<?php

function loadCustomersOnline(stdClass $response) {
	global $db,$store_handler,$system_status, $filter;

	$stores = $store_handler->getStores();

	$data = array();

	$storeIds = array();

	$now = strtotime('now');
	$date = date('h:i:s', $now);
	
	foreach ($stores as $sdata) {
		$query = sprintf("SELECT COUNT(*) as count FROM " . DB_PREFIX . "_customers_online_stats 
		              WHERE shop_id=? AND date_added >='" . date('Y-m-d h:i:s', strtotime('-1 hour')) . "'");
		$storeIds[] = $sdata['id'];

		$rs = $db->Execute($query,array($sdata['id']));


		$data[$date][$sdata['id']] = $rs->fields['count'];
		$data[$date]['date'] = $date;
	}

	foreach ($data as $date => &$salesData) {
		foreach ($storeIds as $id) {
			if (!isset($salesData[$id])) {
				$salesData[$id] = '0';
			}
		}
		$total = 0;
		foreach ($salesData as $key => $count) {
			if (!is_numeric($key)) {
				continue;
			}
			$total += $count;
		}

		$data[$date]['total'] = sprintf("%d", $total);
		ksort($salesData);
	}

	if (empty($data)) {
		foreach ($stores as $sdata) {
			$data[] = array($sdata['id'] => 0, 'date' => date('Y-m-d', strtotime('now')));
		}
	}

	// Sort by date
	ksort($data);
	// Reset data
	$data = array_values($data);
	$response->topics = $data;
	$response->totalCount = count($data);
}

function loadCustomersOnlineByStore(stdClass $response) {
	global $db,$store_handler,$system_status, $filter;
	
	$store_names = array();
	$stores = $store_handler->getStores();
	
	foreach ($stores as $store_data) {
		$store_names[$store_data['id']] = $store_data['text'];
	}
	
	$data = array();
	
	$storeIds = array();
	
	foreach ($stores as $sdata) {

		$query = sprintf("SELECT COUNT(*) as count, shop_id FROM " . DB_PREFIX . "_customers_online_stats 
		  WHERE shop_id=? AND date_added >='" . date('Y-m-d h:i:s', strtotime('-1 hour')) . "' GROUP BY shop_id");
	
		$storeIds[] = $sdata['id'];
			
		$rs = $db->Execute($query,array($sdata['id']));
	
		while (!$rs->EOF) {
			$data[] = array('store_total' => $rs->fields['count'], 'store_name' => $store_names[$rs->fields['shop_id']]);
			$rs->MoveNext();
		}$rs->Close();
	}
	
	if (empty($data)) {
		foreach ($stores as $sdata) {
			$data[] = array('store_total' => 0, 'store_name' => $store_names[$sdata['id']]);
		}
	}
	
	$response->topics = $data;
	$response->totalCount = count($data);
}

function garbage_collect() {
	global $db;
	$num = rand(1, 100);

	if ($num < 5) {
		$db->Execute("DELETE FROM " . DB_PREFIX . "_customers_online_stats WHERE date_added < '" . date('Y-m-d h:i:s', strtotime('-1 hour')) . "'");
	}
}

garbage_collect();
switch ($this->_chartType) {
	case 'customers_online':
		loadCustomersOnline($obj);
		break;
		
	case 'customers_online_by_store':
		loadCustomersOnlineByStore($obj);
		break;
}