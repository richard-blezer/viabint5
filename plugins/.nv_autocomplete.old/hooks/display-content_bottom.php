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

if(version_compare(_SYSTEM_VERSION, '4.1.00', '>=')) {
	echo '<script type="text/javascript" src="'. _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_PLUGINS
		. 'nv_autocomplete/js/4.1/nv_autocomplete.min.js"></script>';
} else {
	echo '<script type="text/javascript" src="'. _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_PLUGINS
	. 'nv_autocomplete/js/4.0/nv_autocomplete.min.js"></script>';
}