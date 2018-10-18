<?php defined('_VALID_CALL') or die('Direct Access is not allowed.');

/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/

if(_SYSTEM_PARSE_TIME == 'false' && XT_8WORKS_SUNRISE_PARSE_TIME == 'true'){
	global $logHandler;
	
	$cacheNote = 'aus';
	$cacheClass = '';
	if (XT_8WORKS_SUNRISE_CACHEMODE == 'true') {
		$cacheNote = 'an';
		$cacheClass = ' no-cache';
	}
	
	echo '<div class="template-info'.$cacheClass.'">';
	$logHandler->parseTime(true);
	echo '<div class="mode">Redakteur-Mode: '.$cacheNote.'</div>';
	echo '</div>';
}

?>