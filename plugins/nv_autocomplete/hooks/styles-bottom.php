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

if (NV_AUTOCOMPLETE_USE_OWN_CSS == 'true') {
  require_once(__DIR__ . '/../classes/nv_files.php');
  $xtMinify->add_resource(NvAutocompleteFiles::getFile('jqueryUiCss'), 150);
  $xtMinify->add_resource(NvAutocompleteFiles::getFile('nvAutocompleteCss'), 150);
}
