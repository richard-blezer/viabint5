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

class NvAutocompleteFiles {
  public static function getFile($name) {
    $files = array(
      'jqueryUi' => _SRV_WEB_PLUGINS . 'nv_autocomplete/js/jquery-ui.js',
      'nvAutocomplete' => _SRV_WEB_PLUGINS .'nv_autocomplete/js/nv_autocomplete.js',
      'jqueryUiCss' => _SRV_WEB_PLUGINS .'nv_autocomplete/css/jquery-ui.css',
      'nvAutocompleteCss' => _SRV_WEB_PLUGINS .'nv_autocomplete/css/nv-autocomplete.css'
    );

    return $files[$name];
  }
}
