<?php
define('ADODB_ERROR_LOG_TYPE',3); // 
define('ADODB_ERROR_LOG_DEST',_SRV_WEBROOT.'xtLogs/db_error.log'); // destination to store sql errors

define('_SYSTEM_DEBUG_MANUALLY', 'false');// if set to 'true' all errors, warning and notice will be displayed
if((defined('_SYSTEM_DEBUG') && _SYSTEM_DEBUG=='true') || (_SYSTEM_DEBUG_MANUALLY=='true')){
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		ini_set("display_errors", "1");
		define('_SYSTEM_DEBUG_FINAL', 'true');
	}else{
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		ini_set("display_errors", "0");
		define('_SYSTEM_DEBUG_FINAL', 'false');
	}
define('_SYSTEM_PHPLOG','false'); // when set to 'true' php errors will be logged in a file

/* when CSRF_PROTECTION set to 'true' perform check for admin session security key (ASSK) and dies; 
   when set to 'debug' checks ASSK but only log error
   when set to 'false' skip the ASSK check
 */
define ('CSRF_PROTECTION','false');
 
/* if CHECK_STORE_ID_EXISTS=true, will perform check for store_id field in a table. When set to FALSE the store_id won't be checked */
define ('CHECK_STORE_ID_EXISTS','true'); 
?>