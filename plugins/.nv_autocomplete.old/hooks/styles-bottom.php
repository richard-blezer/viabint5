<?php
/*
* netvise Autocomplete Search
* #################################
* netvise - Dieterle und Frauen GbR
* Waldring 1
* 61381 Friedrichsdorf
* www.netvise.de
* info@netvise.de
*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$cssLink = '';

if (NV_AUTOCOMPLETE_CSS == '') {
	$cssLink = _SRV_WEB_PLUGINS . 'nv_autocomplete/css/jquery-ui-1.10.3.min.css';
} elseif (NV_AUTOCOMPLETE_CSS != 'none') {
	$cssLink = NV_AUTOCOMPLETE_CSS;
}

if (version_compare(_SYSTEM_VERSION, '4.1.00', '>=')) {
	$xtMinify->add_resource($cssLink, 150);
} else {
	echo '<link href="'. _SYSTEM_BASE_URL . _SRV_WEB . $cssLink .'" rel="stylesheet" type="text/css" />';
}