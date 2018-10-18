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
 # @version $Id: class.filter.php 6060 2013-03-14 13:10:33Z mario $
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

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'library/htmlpurifier/HTMLPurifier.standalone.php';

class filter{

	var $_purifier = null;

	function __construct()
	{
		$config = $config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Allowed', '');
		$config->set('Cache.SerializerPath', _SRV_WEBROOT.'templates_c');
		$this->_purifier = new HTMLPurifier();
	}

	function _filterXSS($var, $urldecode = true) {
		$new_data = array();

		if (is_array($var))
		{
			$dontUrlDecodeKeys = array(
				'password',
				'customers_password_current',
				'customers_password',
				'customers_password_confirm',
				'passwd'
			);
			foreach ($var as $key => $element)
			{
				if(preg_match('/amp;/', $key)>0){
					$tmp_key = $key;
					unset($var[$key]);
					$key = str_replace('amp;', '', $tmp_key);
				}
				// filter key
				$cleaned_key = $this->_pageName($key);
				$urlDecode = !in_array($cleaned_key, $dontUrlDecodeKeys);
				$new_data[$cleaned_key] = $this->_filterXSS($element, $urlDecode);
			}
			return $new_data;
		}
		elseif (!is_array($var) && !is_object($var))
		{
			if ($urldecode)
			{
				$var = urldecode($var);
				$var =$this->_purifier->purify($var);
			}

			$var = $this->_nl2br($var);

			$var = preg_replace('#(<br\s?/?>)|(<[^>]+>)#i', '\\1', $var);

			$match='!<([A-Z]\w*)(?:\s* (?:\w+) \s* = \s* (?(?=["\']) (["\'])(?:.*?\2)+ | (?:[^\s]*) ) )* \s* (\s/)? >!ix';
			$var = preg_replace($match,'<\1\5>',$var);
			$var = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i',"",$var);

			// check if serverside security functions have been disabled (get_magic_quotes_gpc is set to ON per default)
			if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()==1) {
				$var = $this->_quote($var);
			}

			return $var;
		} else {
			// object
		}

	}

	function _nl2br($string)
	{
		return preg_replace("/(\r\n)+|(\n|\r)+/", "<br />", $string);
	}

	function _filter($string,$type = 'all') {

		switch ($type) {
			case 'int':
				return $this->_int($string);
				break;
			case 'char':
				return $this->_char($string);
				break;
			case 'pagename':
				return $this->_pageName($string);
				break;

			case 'lng':
				$string = $this->_char($string);
				return substr($string,0,2);
				break;
			case 'cur';
				$string = $this->_char($string);
				return substr($string,0,3);
				break;

			default:
			case 'all':
				return $this->_quote($string);
				break;
		}
	}

	function _int($var) {
		return (int)$var;
	}

	function _char($var) {
		$param ='/[^a-zA-Z]/';
		$var=preg_replace($param,'',$var);
		return $var;
	}

	/**
	 * filter everythin beside a-z A-Z 0-9 and _ -
	 *
	 * @param unknown_type $var
	 * @return unknown
	 */
	function _charNum($var) {
		$param ='/[^a-zA-Z0-9_-]/';
		$var=preg_replace($param,'',$var);
		return $var;
	}

	function _pageName($var) {
		$param ='/[^a-zA-Z0-9_-]/';
		$var=preg_replace($param,'',$var);
		return $var;
	}

	function _quote($var) {

		$var = stripslashes($var);
		$var = mysql_real_escape_string($var);
		return $var;
	}
}