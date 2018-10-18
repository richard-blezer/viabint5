<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

class jquery {

    var $mode;

    function __construct() {

        switch (_STORE_JQUERY_CDN) {
            case 'off':
                $this->mode='local';
                break;
            case 'Google CDN':
                $this->mode='cdn';
                break;
            case 'Microsoft CDN':
                $this->mode='cdn';
                break;
            case 'jQuery CDN':
                $this->mode='cdn';
                break;
        }

    }

	function _getCode() {

		switch (_STORE_JQUERY_CDN) {
			case 'off':
			//	echo $this->_getjQuery();
                return $this->_getjQuery();
				break;
			case 'Google CDN':
				echo $this->_getjQueryGoogleCDN();
				break;
			case 'Microsoft CDN':
				echo $this->_getjQueryMicrosoftCDN();
				break;
			case 'jQuery CDN':
				echo $this->_getjQueryCDN();
				break;
		}

	}

	function _getjQuery() {
		$jquery_url = _SYSTEM_BASE_URL . _SRV_WEB.'xtFramework/library/jquery/'._STORE_JQUERY_VERSION.'/jquery-'._STORE_JQUERY_VERSION.'.min.js';
		$jquerycode  = '<script type="text/javascript" src="'.$jquery_url.'"></script>'."\n";

        $jquerycode = 'xtFramework/library/jquery/'._STORE_JQUERY_VERSION.'/jquery-'._STORE_JQUERY_VERSION.'.min.js';

		return $jquerycode;
	}

	function _getjQueryGoogleCDN() {
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strtolower($_SERVER['HTTPS'])=='on')) {
			$jquerycode  = '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/' . _STORE_JQUERY_VERSION . '/jquery.min.js"></script>'."\n";
		} else {
			$jquerycode  = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/' . _STORE_JQUERY_VERSION . '/jquery.min.js"></script>'."\n";
		}
			
		return $jquerycode;
	}

	function _getjQueryMicrosoftCDN() {

		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strtolower($_SERVER['HTTPS'])=='on') || isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {

			$jquerycode  = '<script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-' . _STORE_JQUERY_VERSION . '.min.js"></script>'."\n";
		} else {
				
			$jquerycode  = '<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-' . _STORE_JQUERY_VERSION . '.min.js"></script>'."\n";
		}
			
		return $jquerycode;
	}

	function _getjQueryCDN() {
			
			if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strtolower($_SERVER['HTTPS'])=='on') || isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {

			$jquerycode  = '<script type="text/javascript" src="https://code.jquery.com/jquery-' . _STORE_JQUERY_VERSION . '.min.js"></script>'."\n";
		} else {
				
			$jquerycode  = '<script type="text/javascript" src="http://code.jquery.com/jquery-' . _STORE_JQUERY_VERSION . '.min.js"></script>'."\n";
		}
			
		return $jquerycode;

	}
	
	function _getCDNList() {
   			 		
		$result[] = array('id' =>'off', 'name' => 'off');
		$result[] = array('id' =>'Google CDN', 'name' => 'Google CDN (HTTP/HTTPS)');
		$result[] = array('id' =>'Microsoft CDN', 'name' => 'Microsoft CDN (HTTP/HTTPS)');
		$result[] = array('id' =>'jQuery CDN', 'name' => 'jQuery CDN (HTTP)');
		
		return $result;
	}
		
}
?>