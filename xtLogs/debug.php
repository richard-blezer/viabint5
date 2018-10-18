<?php
define('ADODB_ERROR_LOG_DEST',_SRV_WEBROOT.'xtLogs/db_error.log'); // destination to store sql errors
define('_SYSTEM_DEBUG_MANUALLY', 'true');// if set to 'true' all errors, warning and notice will be displayed
define('_SYSTEM_PHPLOG','true'); // when set to 'true' php errors will be logged in a file
define ('CSRF_PROTECTION','debug');