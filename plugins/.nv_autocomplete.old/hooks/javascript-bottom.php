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

$searchInput = str_replace(array('&apos;', '\\', '"', '&quot;'), array("'", '', '\\"', '\\"'), NV_AUTOCOMPLETE_SEARCH_INPUT);

$isMin41 = false;

if (version_compare(_SYSTEM_VERSION, '4.1.00', '>=')) {
	$isMin41 = true;
}

if (NV_AUTOCOMPLETE_USE_OWN_JQUERY == 'true' && !$isMin41) {
	echo '<script type="text/javascript" src="'. _SYSTEM_BASE_URL . _SRV_WEB . _SRV_WEB_PLUGINS . 'nv_autocomplete/js/jquery-1.9.1.min.js'
		.'"></script>';
}

//jQuery UI
$uiLink = '';

if (NV_AUTOCOMPLETE_USE_OWN_JQUERY_UI == 'true') {
	$uiLink = _SRV_WEB_PLUGINS . 'nv_autocomplete/js/jquery-ui-1.10.3.min.js';
	
	//Workaround. Because there is no sorting in xt_minify.class (Version: xt 4.1.00)
	//jQuery UI must be loaded before jQuery Mobile 
	if ($isMin41) {
		$xtMinifyJSArray = array();
		if(isset($xtMinify->resources['js'])) {
			$xtMinifyJSArray = $xtMinify->resources['js'];
			$xtMinify->resources['js'] = array();
		}
		$xtMinify->add_resource($uiLink, 10);
		if(!empty($xtMinifyJSArray)) {
			$xtMinify->resources['js'] = array_merge($xtMinify->resources['js'], $xtMinifyJSArray);
		}
	} else {
		echo '<script type="text/javascript" src="'. _SYSTEM_BASE_URL . _SRV_WEB . $uiLink .'"></script>';
	}
}

//nv_autocomplete.js Vars
echo '<script type="text/javascript">';

if (!$isMin41) {
	echo 'var NV_AUTOCOMPLETE_USE_OWN_JQUERY = "'.NV_AUTOCOMPLETE_USE_OWN_JQUERY.'";';
}

echo 'var base = "'._SYSTEM_BASE_URL . _SRV_WEB.'";
	var NV_AUTOCOMPLETE_SEARCH_INPUT = "' . $searchInput .'";
	var NV_AUTOCOMPLETE_DELAY = "'. NV_AUTOCOMPLETE_DELAY .'";
	var NV_AUTOCOMPLETE_MIN_LENGTH = "'. NV_AUTOCOMPLETE_MIN_LENGTH .'";
	</script>';