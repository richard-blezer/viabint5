<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: magnalister.php 535 2014-11-08 12:06:06Z derpapst $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('E_RECOVERABLE_ERROR') OR define('E_RECOVERABLE_ERROR', 0x1000);
defined('E_DEPRECATED')        OR define('E_DEPRECATED',        0x2000);
defined('E_USER_DEPRECATED')   OR define('E_USER_DEPRECATED',   0x4000);
defined('PHP_INT_MAX')         OR define('PHP_INT_MAX',     2147483647); // for PHP < 5.0.5

/* Developer defines */
if (file_exists(dirname(__FILE__).'/magnadevconf.php')) {
	require_once(dirname(__FILE__).'/magnadevconf.php');
}

/**
 * Defines
 */
defined('MAGNA_DEBUG')         OR define('MAGNA_DEBUG', false);
defined('MAGNA_SHOW_WARNINGS') OR define('MAGNA_SHOW_WARNINGS', false);
defined('MAGNA_SHOW_FATAL')    OR define('MAGNA_SHOW_FATAL', false);
define('MAGNALISTER_PLUGIN', true);

$safe_mode = strtolower(ini_get('safe_mode'));
switch ($safe_mode) {
	case 'on':
	case 'yes':
	case 'true': {
		define('MAGNA_SAFE_MODE', true);
		break;
	}
	default: {
		define('MAGNA_SAFE_MODE', (bool)((int)$safe_mode));
		break;
	}
}
unset($safe_mode);

function magnaHandleFatalError() {
	$errorOccured = false;
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		$le = error_get_last();
		if (empty($le)) return;
		if (((E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING | 
		      E_DEPRECATED | E_USER_DEPRECATED | E_STRICT) & $le['type']) == 0
		) {
			echo '<pre>'.print_r(error_get_last(), true).'</pre>';
			$errorOccured = true;
		}
	} else {
		global $php_errormsg;
		if (empty($php_errormsg)) return;
		echo '<pre>'.$php_errormsg.'</pre>';
		$errorOccured = true;
	}
	if ($errorOccured) {
		if (version_compare(PHP_VERSION, '5.2.5', '>=')) {
			echo '<pre>'.print_r(debug_backtrace(false), true).'</pre>';
		} else {
			echo '<pre>'.print_r(debug_backtrace(), true).'</pre>';
		}
	}
}

if (MAGNA_DEBUG && (MAGNA_SHOW_WARNINGS || MAGNA_SHOW_FATAL)) {
	ini_set("display_errors", 1);
	register_shutdown_function('magnaHandleFatalError');
	if (version_compare(PHP_VERSION, '5.2.0', '<')) {
		ini_set('track_errors', 1);
	}
}

/* Allow setting a different Update-Paths */
if (isset($_GET['UPDATE_PATH'])) {
	$_SESSION['magna_UPDATE_PATH'] = ltrim(rtrim($_GET['UPDATE_PATH'], '/'), '/').'/';
} else if (!isset($_SESSION['magna_UPDATE_PATH'])) {
	$_SESSION['magna_UPDATE_PATH'] = 'update/';
}


define('FILENAME_MAGNALISTER', basename($_SERVER['SCRIPT_NAME']));
defined('MAGNA_SERVICE_URL') OR define('MAGNA_SERVICE_URL', 'http://api.magnalister.com/');
define('MAGNA_PUBLIC_SERVER', 'http://xtmulticonnect.com/');
define('DIR_MAGNALISTER_ABSOLUTE', dirname(__FILE__).'/');
define('MAGNA_UPDATE_PATH', $_SESSION['magna_UPDATE_PATH'].'veyton/');
defined('MAGNA_UPDATE_FILEURL') OR define('MAGNA_UPDATE_FILEURL', MAGNA_SERVICE_URL.MAGNA_UPDATE_PATH);
define('MAGNA_SUPPORT_URL', '<a href="'.MAGNA_PUBLIC_SERVER.'" title="'.MAGNA_PUBLIC_SERVER.'">'.MAGNA_PUBLIC_SERVER.'</a>');

$_backup = array (
	'REQUEST' => $_REQUEST,
	'GET'     => $_GET,
	'POST'    => $_POST,
	'COOKIE'  => $_COOKIE
);

if (!isset($_GET['ML_BOOTSTRAP_NO_OB'])) {
	ob_start();
}
require_once(dirname(__FILE__).'/../../xtFramework/admin/main.php');
require_once(dirname(__FILE__).'/../../conf/paths.php');
if (!isset($_GET['ML_BOOTSTRAP_NO_OB'])) {
	ob_end_clean();
}

$_REQUEST = $_backup['REQUEST'];
$_GET     = $_backup['GET'];
$_POST    = $_backup['POST'];
$_COOKIE  = $_backup['COOKIE'];

unset($_backup);

// safe admin loading
if (!$xtc_acl->isLoggedIn()) {
	die('login required');
}


if (MAGNA_DEBUG) {
	error_reporting(-1 & ~E_NOTICE & ~E_DEPRECATED);
}

define('MAGNA_PLUGIN_DIR', 'magnalister/');
define('_ML_WEB_ROOT',  str_replace('xtAdmin/', '', _SRV_WEB));
define('DIR_MAGNALISTER', _SRV_WEBROOT._SRV_WEB_PLUGINS.MAGNA_PLUGIN_DIR);

if (defined('_VALID_CALL') && !defined('_VALID_XTC')) {
	define('_VALID_XTC', true);
}

function __ml_useCURL($bl = null) {
	global $__ml_useCURL;
	
	$d = isset($_SESSION['ML_UseCURL']) && is_array($_SESSION['ML_UseCURL'])
		? $_SESSION['ML_UseCURL']
		: (isset($__ml_useCURL) && is_array($__ml_useCURL)
			? $__ml_useCURL
			: array ()
		);
	
	if (!isset($d['ForceCURL']) || !isset($d['UseCURL'])) {
		$d = array (
			'ForceCURL' => false,
			'UseCURL' => function_exists('curl_init')
		);
	}
	
	/* read */
	if ($bl === null) {
		if (defined('MAGNA_USE_CURL') && is_bool(MAGNA_USE_CURL)) {
			return MAGNA_USE_CURL;
		}
		if (isset($d['ForceCURL']) && ($d['ForceCURL'] === true)) {
			// READ ForceCURL === true
			return true;
		}
		if (isset($d['UseCURL']) && is_bool($d['UseCURL'])) {
			// READ UseCURL (bool)
			return $d['UseCURL'];
		}
		//echo "NO READ\n";
		return function_exists('curl_init');
		
	/* write */
	} else {
		if ($bl === 'ForceCURL') {
			$d['ForceCURL'] = true;
			$d['UseCURL'] = true;
		} else if ($d['ForceCURL'] !== true) {
			$d['UseCURL'] = (bool)$bl;
		}

		if (!empty($_SESSION)) {
			//echo "WRITE SESSION\n";
			$_SESSION['ML_UseCURL'] = $d;
		} else {
			//echo "WRITE GLOBAL\n";
			$__ml_useCURL = $d;
		}
		return $d['UseCURL'];
	}
}

function fileGetContentsPHP($path, &$warnings = null, $timeout = 10) {
	//echo __METHOD__."\n";
	if ($timeout > 0) {
		$context = stream_context_create(array(
			'http' => array('timeout' => $timeout)
		));
	} else {
		$context = null;
	}
	$timeout_ts = time() + $timeout;
	$next_try = false;
	
	ob_start();
	do {
		if ($next_try) usleep(rand(500000, 1500000));
		$return = file_get_contents($path, false, $context);
		$warnings = ob_get_contents();
		$next_try = true;
	} while ((false === $return) && (time() < $timeout_ts));
	ob_end_clean();
	
	return $return;
}

function fileGetContentsCURL($path, &$warnings = null, $timeout = 10, $forceSSLOff = false) {
	$useCURL = __ml_useCURL();
	if ($useCURL === false) {
		$warnings = 'cURL disabled';
		return false;
	}
	
	//echo __METHOD__."\n";
	if (!function_exists('curl_init') || (strpos($path, 'http') !== 0)) {
		return false;
	}
	$cURLVersion = curl_version();
	if (!is_array($cURLVersion) || !array_key_exists('version', $cURLVersion)) {
		return false;
	}
	
	$warnings = '';
	$ch = curl_init();
	
	$hasSSL = is_array($cURLVersion) && array_key_exists('protocols', $cURLVersion) && in_array('https', $cURLVersion['protocols']);
	if ($hasSSL && !$forceSSLOff) {
		$path = str_replace('http://', 'https://', $path);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		if (defined('MAGNA_CURLOPT_SSLVERSION')) {
			curl_setopt($ch, CURLOPT_SSLVERSION, MAGNA_CURLOPT_SSLVERSION);
		}
	}
	
	curl_setopt($ch, CURLOPT_URL, $path);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($timeout > 0) {
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	}
	//*
	$timeout_ts = time() + $timeout;
	$next_try = false;
	$return = false;
	
	do {
		//break;
		if ($next_try) usleep(rand(500000, 1500000));
		$return = curl_exec($ch);
		$next_try = true;
	} while (curl_errno($ch) && (time() < $timeout_ts));
	//*/
	if (curl_errno($ch) == CURLE_OPERATION_TIMEOUTED) {
		__ml_useCURL(false);
		$return = false;
	}
	
	$warnings = curl_error($ch);
	/*
	__ml_useCURL(false);
	$return = false;
	$warnings = 'Timeout';
	//*/
	
	if (!empty($return)) {
		__ml_useCURL('ForceCURL');
	}
	
	curl_close($ch);
	
	return $return;
}

function fileGetContents($path, &$warnings = null, $timeout = 10) {
	if (($contents = fileGetContentsCURL($path, $warnings, $timeout)) !== false) {
		return $contents;
	}
	return fileGetContentsPHP($path, $warnings, $timeout);
}

function echoDiePage($title, $content, $style = '') {
	echo '<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>magnalister :: '.$title.'</title>
		<style>
			body { max-width: 600px; padding: 20px; font: 12px sans-serif; line-height: 16px; color: #333334;}
			h1{ font-size: 130%; letter-spacing: -0.5px; }
			a { color: #E31A1C; text-decoration: none; }
			a:hover { text-decoration: underline; }
			'.$style.'
		</style>
	</head>
	<body>
		<h1>'.$title.'</h1>
		<p>'.$content.'</p>
		'.(isset($_SERVER['HTTP_REFERER']) 
			? (($_SESSION['selected_language'] == 'de')
				? '<a href="'.$_SERVER['HTTP_REFERER'].'" title="Zur&uuml;ck">Zur&uuml;ck</a>'
				: '<a href="'.$_SERVER['HTTP_REFERER'].'" title="Back">Back</a>'
			) 
			: ''
		).'
	</body>
</html>';
	exit();
}

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
	echoDiePage(
		(($_SESSION['selected_language'] == 'de') ? 'PHP Version zu alt' : 'PHP version too old'),
		(($_SESSION['selected_language'] == 'de') ?
			'Ihre PHP-Version ('.PHP_VERSION.') ist zu alt. Sie ben&ouml;tigen mindestens PHP Version 5.0 oder h&ouml;her.' :
		'Your PHP version ('.PHP_VERSION.') is too old. You need at least PHP version 5.0 or higher.'
		)
	);
}

/* Alles ueber diesem Kommentar muss PHP 4 kompartibel sein! */
if (MAGNA_SAFE_MODE && !file_exists(DIR_MAGNALISTER.'ClientVersion')) {
	echoDiePage(
		'Safe Mode '.(($_SESSION['selected_language'] == 'de') ? 'Beschr&auml;nkung aktiv' : 'Restriction active'),
		(($_SESSION['selected_language'] == 'de') ?
			'Die PHP Safe Mode Beschr&auml;nkung ist aktiv. Daher ist es nicht m&ouml;glich automatische Updates zu machen. Um den magnalister per 
			 hand zu akutalisieren, laden Sie sich bitte die aktuelle Version auf der 
			 <a href="'.MAGNA_PUBLIC_SERVER.'" title="magnalister Seite">magnalister Seite</a> herunter und entpacken Sie das Archiv ins Wurzelverzeichnis
			 Ihres Shops oder kontaktieren Sie alternativ Ihren Server-Administrator und bitten Sie ihn, den Safe Mode dauerhaft abzuschalten.' :
			'The PHP Save Mode restriction is active. That\'s why it is not possible to make automatic upgrades. To upgrade the magnalister manually please
			 download the current version from <a href="'.MAGNA_PUBLIC_SERVER.'" title="magnalister.com">magnalister.com</a> and extract the contents
			 of the zip archive into the root directory of your shop or contact your server administrator and ask if the Safe Mode Restriction can be 
			 switched off permanently.'
		)
	);
}

if (!MAGNA_SAFE_MODE && !is_writable(DIR_MAGNALISTER)) {
	echoDiePage(
		substr(DIR_MAGNALISTER, 1).' '.(($_SESSION['selected_language'] == 'de') ? 'kann nicht geschrieben werden' : 'is not writable'),
		(($_SESSION['selected_language'] == 'de') ?
			'Das Verzeichnis <tt>'.substr(DIR_MAGNALISTER, 1).'</tt> kann nicht vom Webserver geschrieben werden.<br/>
			 Dies ist allerdings zwingend notwendig um den magnalister verwenden zu k&ouml;nnen.' :
			'The directory <tt>'.substr(DIR_MAGNALISTER, 1).'</tt> is not writable by the webserver.<br/>
			 This is however required to use the magnalister.'
		)
	);
}

$requiredFiles = array (
	'magnalister.php',
	'veytonDefinesToXtc3.php',
	'init.php',
	'MagnaUpdater.php',
);

if (!MAGNA_SAFE_MODE && MAGNA_DEBUG && isset($_GET['PurgeFiles'])) {
	$_SESSION['MagnaPurge'] = ($_GET['PurgeFiles'] == 'true') ? true : false;
} else {
	if (MAGNA_SAFE_MODE || !MAGNA_DEBUG || !isset($_SESSION['MagnaPurge'])) {
		$_SESSION['MagnaPurge'] = false;
	}
}

if (!isset($_SESSION['selected_language'])) {
	$_SESSION['selected_language'] = 'de';
}

if (!MAGNA_SAFE_MODE) {
	foreach ($requiredFiles as $file) {
		$doDownload = (isset($_GET['update']) && ($_GET['update'] == 'true')) || ($_SESSION['MagnaPurge'] === true);
		$scriptPath = MAGNA_UPDATE_FILEURL.'magnalister/'.$file;
		if ($doDownload || !file_exists(DIR_MAGNALISTER.$file)) {
			$scriptContent = fileGetContents($scriptPath);
			if ($scriptContent === false) {
				echoDiePage(
					$scriptPath.' '.(
						($_SESSION['selected_language'] == 'de') ? 
							'kann nicht geladen werden' : 
							'can\'t be loaded'
					),
					(($_SESSION['selected_language'] == 'de') ?
						'Die Datei <tt>'.$scriptPath.'</tt> kann nicht heruntergeladen werden.' :
						'The File <tt>'.$scriptPath.'</tt> can not be downloaded.'
					)
				);
			}
			if (!defined('ML_DO_NOT_UPDATE')) {
				if (@file_put_contents(DIR_MAGNALISTER.$file, $scriptContent) === false) {
					echoDiePage(
						DIR_MAGNALISTER.$file.' '.(
							($_SESSION['selected_language'] == 'de') ? 
								'kann nicht gespeichert werden' : 
								'can\'t be loaded'
						),
						(($_SESSION['selected_language'] == 'de') ?
							'Die Datei <tt>'.DIR_MAGNALISTER.$file.'</tt> kann nicht gespeichert werden.' :
							'The File <tt>'.DIR_MAGNALISTER.$file.'</tt> can not be saved.'
						)
					);
				}
			} else {
				echo $scriptPath.' --> '.DIR_MAGNALISTER.$file.'<br>';
			}
		}
	}
}

/**
 * Magnalister Core
 */
include_once(DIR_MAGNALISTER.'init.php');
