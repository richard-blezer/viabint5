<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

// dropdownData der benachrichtigungsstatus uns store names
	
	require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.xt_tracking.php';
	require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.xt_ship_and_track.php';

switch ($request['get'])
{
	case 'tracking_shippers':
		$result = xt_tracking::getShippers();
		break;

	case 'hermes_parcel_class':
		$result = xt_ship_and_track::getParcelClassForUser();
		break;

	case 'hermes_stores':
		$result = xt_ship_and_track::getStores();
		break;

	case 'hermes_status':
		$result = xt_ship_and_track::getStatusCodes();
		break;
}
?>