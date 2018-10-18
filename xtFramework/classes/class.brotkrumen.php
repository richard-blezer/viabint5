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

class brotkrumen {

	public $krumen;
	
	public $snap;

	function brotkrumen() {
	}


	function _addItem($url, $name) {
		$this->krumen[] = array('name'=>$name,'url'=>$url);	
	}
	
	function _setSnapshot($url) {
		$_SESSION['brotkrumen'] = $url;
	}
	
	function _getSnapshot() {
		if (!isset($_SESSION['brotkrumen'])) {
			return false;
		} else {
			
			if(!is_string($_SESSION['brotkrumen'])) return false;
			
			$url = $_SESSION['brotkrumen'];
			unset($_SESSION['brotkrumen']);
			return $url;
			
		}
		
	}

	function _output() {
		$html = '<ul>';		
		foreach ($this->krumen as $key => $val) {
			$html .='<li><a href="'.$val['url'].'" title="'.$val['name'].'">'.$val['name'].'</a></li>'; 
		}		
		$html .= '</ul>';
		return $this->krumen;
	}
}