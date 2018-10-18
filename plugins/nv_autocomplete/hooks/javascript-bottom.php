<?php
/*
* netvise Autocomplete Search
* #################################
* netvise - Sean Nicholas Dieterle
* Classen-Kappelmann-Straße 26
* 50931 Köln
* www.netvise.de
* info@netvise.de
*/

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once(__DIR__ . '/../classes/nv_files.php');
if (NV_AUTOCOMPLETE_USE_OWN_JQUERY_UI == 'true') {
	$xtMinify->add_resource(NvAutocompleteFiles::getFile('jqueryUi'), 100);
}
$xtMinify->add_resource(NvAutocompleteFiles::getFile('nvAutocomplete'), 120);

//nv_autocomplete.js Vars
$searchInput = str_replace(array('&apos;', '\\', '"', '&quot;'), array("'", '', '\\"', '\\"'), NV_AUTOCOMPLETE_SEARCH_INPUT);
echo '
	<script type="text/javascript">
		var base = "'._SYSTEM_BASE_URL . _SRV_WEB.'";
		var NV_AUTOCOMPLETE_SEARCH_INPUT = "' . $searchInput .'";
		var NV_AUTOCOMPLETE_DELAY = "'. NV_AUTOCOMPLETE_DELAY .'";
		var NV_AUTOCOMPLETE_MIN_LENGTH = "'. NV_AUTOCOMPLETE_MIN_LENGTH .'";
	</script>';
