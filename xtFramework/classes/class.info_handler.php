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

class info{

	public $info = false;
	public $info_content = '';

	public $info_data = array();
	public $default_info_type = 'error';

	function info(){
		global $xtPlugin;

		if (isset($_SESSION['info_handler'])){
			$this->info_data = $_SESSION['info_handler']['data'];
			$this->_showInfo($_SESSION['info_handler']['store']);
			unset($_SESSION['info_handler']);
		}

		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:info_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;
	}

	function _addInfoSession($info_message, $info_type='', $pos='store'){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:_addInfoSession_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($info_type=='')
		$info_type = $this->default_info_type;

		if($info_type=='error'){
			$this->info_data['error'][] = array('info_message'=>$info_message);
		}elseif($info_type=='warning'){
			$this->info_data['warning'][] = array('info_message'=>$info_message);
		}elseif($info_type=='success'){
			$this->info_data['success'][] = array('info_message'=>$info_message);
		}elseif($info_type=='info'){
			if(!empty($this->info_data['info'])){
				if(!in_array(array('info_message'=>$info_message), $this->info_data['info']))
					$this->info_data['info'][] = array('info_message'=>$info_message);
			}
			else{
				$this->info_data['info'][] = array('info_message'=>$info_message);
			}
		}

		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:_addInfoSession_top_bottom')) ? eval($plugin_code) : false;

		$_SESSION['info_handler']['pos'] = $pos;
		$_SESSION['info_handler']['data'] = $this->info_data;

	}

	function _addInfo($info_message, $info_type='', $pos='store'){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:_addInfo_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if($info_type=='')
		$info_type = $this->default_info_type;

		if($info_type=='error'){
			$this->info_data['error'][] = array('info_message'=>$info_message);
		}elseif($info_type=='warning'){
			$this->info_data['warning'][] = array('info_message'=>$info_message);
		}elseif($info_type=='success'){
			$this->info_data['success'][] = array('info_message'=>$info_message);
		}elseif($info_type=='info'){
			if(!empty($this->info_data['info'])){
				if(!in_array(array('info_message'=>$info_message), $this->info_data['info']))
					$this->info_data['info'][] = array('info_message'=>$info_message);
			}
			else{
				$this->info_data['info'][] = array('info_message'=>$info_message);
			}
		}

		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:_addInfo_bottom')) ? eval($plugin_code) : false;

		$this->_showInfo($pos);
	}

	function _showInfo($pos){
		global $xtPlugin, $template;

		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:_showInfo_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$template = new Template();

		if($pos=='admin'){
			$template->_setTemplate('xtAdmin');
		}

		$tpl_data = array(
			'error'=>$this->info_data['error'],
			'warning'=>$this->info_data['warning'],
			'success'=>$this->info_data['success'],
			'info'=>$this->info_data['info']
		);
		$tpl = 'info_handler.html';
		($plugin_code = $xtPlugin->PluginCode('class.info_handler.php:_showInfo_tpl_data')) ? eval($plugin_code) : false;
		$this->info_content = $template->getTemplate('smarty', '/'._SRV_WEB_CORE.'pages/'.$tpl, $tpl_data);

	}
	
	function _unsetInfo($info_type='', $pos='store'){
        if($info_type=='')
            $info_type = $this->default_info_type;
        unset($this->info_data[$info_type]);
    }
}