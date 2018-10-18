<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

function _lang_mapping_($lang){
	$lang_mapping = array();
	$lang_mapping['de'] = 'de_DE';
	$lang_mapping['en'] = 'en_US';
	$lang_mapping['it'] = 'it_IT';
	
	if (!isset($lang_mapping[$lang])) return 'en_US';

	return $lang_mapping[$lang];
}
?>