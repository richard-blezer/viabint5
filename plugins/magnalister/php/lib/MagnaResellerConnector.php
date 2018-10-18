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
 * $Id: MagnaResellerConnector.php 154 2012-12-14 17:53:17Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

define('ML_LOG_API_REQUESTS', true);
define('MAGNA_DEBUG', true);

require_once(DIR_MAGNALISTER_INCLUDES . 'lib/functionLib.php');

# Magnalister class
class MagnaResellerConnector extends MagnaConnector {
	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self($link);
		}
		return self::$instance;
	}
	
	protected function __construct() {
		parent::__construct();
		$this->magnaApiScript = 'API/Reseller/';
		$this->setSubsystem('Reseller');
	}
	
	public function updatePassPhrase() {
		
	}
	
	protected function finalizeRequest(&$requestFields) {
		
	}
	
	protected function preprocessResult($result, $response, &$timePerRequest) {
		if (!isset($result['Status'])) {
			$e = new MagnaException(
				html_entity_decode(ML_INTERNAL_INVALID_RESPONSE, ENT_NOQUOTES), 
				MagnaException::INVALID_RESPONSE, 
				$this->lastRequest, 
				(is_array($result) ? $result : $response),
				$timePerRequest['time']
			);
			MagnaError::gi()->addMagnaException($e);
			$timePerRequest['status'] = 'INVALID_RESPONSE';
			$this->timePerRequest[] = $timePerRequest;
			throw $e;
		}

		if ($result['Status'] == 'Error') {
			$msg = '';
			if (isset($result['Errors'])) {
				foreach ($result['Errors'] as $error) {
					if ($error['Level'] == 'Fatal') {
						$msg = $error['Message'];
						break;
					}
				}
			}
			$e = new MagnaException(
				($msg != '' ) ? $msg : ML_INTERNAL_API_CALL_UNSUCCESSFULL,
				MagnaException::NO_SUCCESS,
				$this->lastRequest,
				$result,
				$timePerRequest['time']
			);
			$timePerRequest['status'] = 'API_ERROR';
			$this->timePerRequest[] = $timePerRequest;
			MagnaError::gi()->addMagnaException($e);
			throw $e;
		}
		$timePerRequest['status'] = $result['Status'];
	}
	
}
