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
 * $Id: class.magnalister.php 199 2013-03-15 10:03:31Z tim.neumann $
 *
 * (c) 2011 - 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

//error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
error_reporting(error_reporting(E_ALL) | E_WARNING);

if (ini_get('safe_mode')) {
	define('MAGNA_SAFE_MODE', true);
} else {
	define('MAGNA_SAFE_MODE', false);
}

define('MAGNA_SERVICE_URL', 'http://api.magnalister.com/');
define('MAGNA_PUBLIC_SERVER', 'http://xtmulticonnect.com/');
define('DIR_MAGNALISTER_ABSOLUTE', dirname(__FILE__).'/');
define('DIR_MAGNALISTER', _SRV_WEBROOT._SRV_WEB_PLUGINS.'magnalister/');
define('MAGNA_UPDATE_PATH', 'update/veyton/');
define('MAGNA_UPDATE_FILEURL', MAGNA_SERVICE_URL.MAGNA_UPDATE_PATH);
define('MAGNA_SUPPORT_URL', '<a href="'.MAGNA_PUBLIC_SERVER.'" title="'.MAGNA_PUBLIC_SERVER.'">'.MAGNA_PUBLIC_SERVER.'</a>');

class magnalister extends plugin {
	var $limit = 0; // keine Ahnung wofuer das benoetigt wird, aber ohne gibt's jede Menge Notices
    var $data = array();
    
	var $position = null;
	
	function __construct() {
		$this->initMagna();
	}
	
	private function echoDiePage($title, $content, $style = '') {
		echo '
	    <style>'.(($style === false) ? '' :'
			div#magna {
				padding: 5px;
			}
	    	h1 { font-size: 130%; }
	    	'.$style).'
	    </style>
	    <div id="magna">
	    <h1>'.$title.'</h1>
		    <p>'.$content.'</p>
	    </div>';
	}
	
	private function fileGetContents($path, &$warnings = null, $timeout = -1) {
		if (function_exists('curl_init') && (strpos($path, 'http') !== false)) {
			$warnings = '';
		    $ch = curl_init();
	
		    curl_setopt($ch, CURLOPT_URL, $path);
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    if ($timeout > 0) {
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			}
		    $return = curl_exec($ch);
		    if (curl_errno($ch) == CURLE_OPERATION_TIMEOUTED) {
		    	$return = false;
		    }
		   	$warning = curl_error($ch);
		    curl_close($ch);
	
		    return $return;
		}

		if ($timeout > 0) {
			$context = stream_context_create(
				array('http' => 
				    array(
				        'method'  => 'GET',
				        'timeout' => $timeout
				    )
				)
			);
		} else {
			$context = null;
		}
		ob_start();
		$return = file_get_contents($path, false, $context);
		$warnings = ob_get_contents();
		ob_end_clean();
	
		if ($warnings == '') {
			return $return;
		}

		return false;
	}
	
    function setPosition ($position) {
        $this->position = $position;
    }
    
    function _getParams() {
    	$params = array(
    		'master_key' => 'id',
    	);

        return $params;
    }
    
    function _get($ID = 0) {
    	$obj = new stdClass();
		$obj->totalCount = 0;
		$obj->data = array(array('a' => 'b'));
		return $obj;
    }
    
    function _set($data, $set_type='edit') {
    	$obj = new stdClass();
    	return $obj;
    }
    
    function _unset($id = 0) {
    	return false;
    }

	private function initMagna() { 
		if (MAGNA_SAFE_MODE && !file_exists(DIR_MAGNALISTER.'ClientVersion')) {
			$this->echoDiePage(
				'Safe Mode '.(($_SESSION['selected_language'] == 'de') ? 'Beschr&auml;nkung aktiv' : 'Restriction active'),
				(($_SESSION['selected_language'] == 'de') ?
			    	'Die PHP Safe Mode Beschr&auml;nkung ist aktiv. Daher ist es nicht m&ouml;glich automatische Updates zu machen. Um den magnalister per 
			    	 hand zu akutalisieren, laden Sie sich bitte die aktuelle Version auf der 
			    	 <a href="'.MAGNA_PUBLIC_SERVER.'" title="mangalister Seite">mangalister Seite</a> herunter und entpacken Sie das Archiv ins Wurzelverzeichnis
			    	 Ihres Shops oder kontaktieren Sie alternativ Ihren Server-Administrator und bitten Sie ihn, den Safe Mode dauerhaft abzuschalten.' :
			    	'The PHP Save Mode restriction is active. That\'s why it is not possible to make automatic upgrades. To upgrade the mangalister manually please
			    	 download the current version from <a href="'.MAGNA_PUBLIC_SERVER.'" title="mangalister.com">mangalister.com</a> and extract the contents
			    	 of the zip archive into the root directory of your shop or contact your server administrator and ask if the Safe Mode Restriction can be 
			    	 switched off permanently.'
			    )
			);
			return;
		}
		
		if (!MAGNA_SAFE_MODE && !is_writable(DIR_MAGNALISTER)) {
			$this->echoDiePage(
				substr(_ML_WEB_ROOT.DIR_MAGNALISTER, 1).' '.(($_SESSION['selected_language'] == 'de') ? 'kann nicht geschrieben werden' : 'is not writable'),
				(($_SESSION['selected_language'] == 'de') ?
			    	'Das Verzeichnis <tt>'.substr(_ML_WEB_ROOT.str_replace(_SRV_WEBROOT, '', DIR_MAGNALISTER), 1).'</tt> kann nicht vom Webserver geschrieben werden.<br/>
			    	 Dies ist allerdings zwingend notwendig um den magnalister verwenden zu k&ouml;nnen.' :
			    	'The directory <tt>'.substr(_ML_WEB_ROOT.str_replace(_SRV_WEBROOT, '', DIR_MAGNALISTER), 1).'</tt> is not writable by the webserver.<br/>
			    	 This is however required to use the magnalister.'
			    )
			);
			return;
		}
		$requiredFiles = array (
			'magnalister.php'
		);
		
		if (!MAGNA_SAFE_MODE) {
			foreach ($requiredFiles as $file) {
				$scriptPath = MAGNA_UPDATE_FILEURL.'magnalister/'.$file;
				if (!file_exists(DIR_MAGNALISTER.$file)) {
					$scriptContent = $this->fileGetContents($scriptPath);
					if ($scriptContent === false) {
						$this->echoDiePage(
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
						return;
					}
				
					if (@file_put_contents(DIR_MAGNALISTER.$file, $scriptContent) === false) {
						$this->echoDiePage(
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
						return;
					}
				}
			}
		}
		
		$requiredFiles[] = '.htaccess';
		if (!file_exists(DIR_MAGNALISTER.'.htaccess')) {
			@file_put_contents(DIR_MAGNALISTER.'.htaccess', "<Files *.php>\n    Order Deny,Allow\n    Allow from all\n</Files>");
		}
		
		foreach ($requiredFiles as $file) {
			if (!file_exists(DIR_MAGNALISTER.$file)) {
				$this->echoDiePage(
					DIR_MAGNALISTER.$file.' '.(
						($_SESSION['selected_language'] == 'de') ? 
							'nicht verf&uuml;gbar' : 
							'not available'
					),
					(($_SESSION['selected_language'] == 'de') ?
				    	'Die Datei <tt>'.DIR_MAGNALISTER.$file.'</tt> ist nicht verf&uuml;gbar. 
				    	 Diese Datei ist jedoch zwingend notwendig f&uuml;r den Betrieb des magnalisters. 
				    	 Bitte laden Sie sich den magnalister f&uuml;r xt:Commerce Veyton von '.MAGNA_SUPPORT_URL.' 
				    	 herunter und folgen genau der Installationsanleitung. ' :
				    	'The File <tt>'.DIR_MAGNALISTER.$file.'</tt> is not available.
				    	 This file is however required to run magnalister.
				    	 Please download the magnalister for xt:Commerce Veyton from '.MAGNA_SUPPORT_URL.' 
				    	 and follow exactly the installation instructions.'
				    )
				);
				return;
			}
		}

		echo '<iframe style="width: 100%; height: 100%; border: none; margin: 0; padding: 0;" src="../plugins/magnalister/magnalister.php"></iframe>';
		return true;
	}
}
