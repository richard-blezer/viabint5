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

class box{

	var $loaded_box;

	function box($data=''){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.box_handler.php:box_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($data)){
			return false;
		}

		$this->loaded_box = $this->_getBox($data);
		($plugin_code = $xtPlugin->PluginCode('class.box_handler.php:box_bottom')) ? eval($plugin_code) : false;
	}

	function _getBox($data){
		global $xtPlugin;

		if(empty($data['type'])){
			$type = 'core';
		}else{
			$type = $data['type'];
		}

        //check status of plugin
        if(!array_key_exists($data['name'],$xtPlugin->active_modules) && $data['type'] == 'user'){
            return false;
        }
        
		($plugin_code = $xtPlugin->PluginCode('class.box_handler.php:_getBox_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$name = $data['name'].'.php';

		if(!empty($data['name']))
		$plugin_name = $data['name'].'/';


		if($type=='core'){
			$path = _SRV_WEBROOT._SRV_WEB_CORE.'boxes/';
		}elseif($type=='user'){
			$path = _SRV_WEBROOT._SRV_WEB_PLUGINS.$plugin_name.'boxes/';
		}else{
			$path = $type;
		}

		if (!file_exists($path . $name)) {
			return false;
		}else{
			return $path.$name;
		}
	}
}