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

class extJS {
	function extJS () {
		
	}
	function _add_Method_JS_Funktion ($name, $parameters = '', $position = 0) {
		$string = '.'.$name;
		$string .= '('.$parameters.')';
		$this->_setTMP_JS_String($string, $position);
	}
	
	function _call_Method_JS_Function ($name, $position = 0) {
		$string = $name;
		$this->_setTMP_JS_String($string, $position);
	}
	function _setTMP_JS_String ($string, $position = 0) {
		$this->TMP_JS_String[$position] .= $string;
		
	}
	function _getTMP_JS_String($position = 0) {
		$string = $this->TMP_JS_String[$position];
		$this->TMP_JS_String[$position] = '';
		return $string;
	}
	function _setJS_String($string) {
		$this->JS_String .= $string;
	}
	function _getJS_String() {
		return $this->JS_String;
	}
	
	function setVar ($name, $value = '') {
		$string = '
		var '.$name;
		if ($value)
			$string .= ' = '.$value.'';
		return $string.'
		';		
	}
	function setVarValue ($name, $value = '') {
		$string = '
		'.$name;
			$string .= ' = '.$value.'';
		return $string.'
		';		
	}
	
	function WindowLocation ($location = '') {
		$string = 'window.location = "'.$location.'"';
		return $string;		
	}
	
	function jsFunction ($name, $content, $parameters = '') {
		$string = ' function '.$name.'('.$parameters.') { 
				  '.$content.' } ';
		return $string;		
	}
	
}

?>