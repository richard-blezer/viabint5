<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.tracking.php';

$template = new Template();
$template->getTemplatePath('order-tracking.tpl.html', 'xt_ship_and_track', '', 'plugin');

global $tpl_data;
if (!$tpl_data) $tpl_data = array();
$trackingInfos = array('tracking_infos' => tracking::getTrackingForOrder($tpl_data['order_data']['orders_id']));
$tpl_data = array_merge($trackingInfos, $tpl_data);
$html = $template->getTemplate('xt_ship_and_track', 'order-tracking.tpl.html', $tpl_data). PHP_EOL;

echo $html;

?>