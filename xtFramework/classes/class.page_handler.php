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

class page{

	var $default_page = 'index';
	//var $default_action = 'default';

	function page($data=''){
		global $xtPlugin, $filter,$xtLink;

		$_page = $filter->_filter($data['page'],'pagename');
		$_page_action = $filter->_filter($data['page_action'],'pagename');

		($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:page_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		if(empty($_page)){
            $org_page = $_SERVER['REQUEST_URI'];
            $page_url_data = $this->_cleanUpUrl($org_page);
            
            if(!isset($_SESSION['selected_language']))
                $_SESSION['selected_language'] = _STORE_LANGUAGE;
            
            if($page_url_data['url']=='' || $page_url_data['url']==$_SESSION['selected_language'] || $page_url_data['url']==$_SESSION['selected_language'].'/' || $page_url_data['url']==$_SESSION['selected_language'].'/index' || $page_url_data['url']==$_SESSION['selected_language'].'/index.php' || $page_url_data['url']=='index' || $page_url_data['url']=='index.php'){
                $_page = $this->default_page;
            }else{
                if (_SYSTEM_MOD_REWRITE_404=='true') {
                    $_page = '404';
                }else{
                    $_page = $this->default_page;
                }
            }
			
		}
        
		$page_loaded = $this->_getPage($_page);
		if(empty($page_loaded)){
			$_page = $this->default_page;
		}

		$this->loaded_page = $page_loaded;
		$this->page_name = $_page;
		$this->page_action = $_page_action;
		
		$xtLink->ClearUPFromDuplicate($_SERVER['REQUEST_URI']);
		
		($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:page_bottom')) ? eval($plugin_code) : false;
	}

	function _getPage($data){
		global $xtPlugin;

		($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:_getPage_top')) ? eval($plugin_code) : false;
		if(isset($plugin_return_value))
		return $plugin_return_value;

		$data = 'PAGE_'.strtoupper($data);

		if(defined($data)){
			return constant($data);
		}else{
			return false;
		}

	}
    
    function _cleanUpUrl($page){
        global $xtPlugin, $xtLink;

        ($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:_cleanUpUrl_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
        return $plugin_return_value;

        if(_SRV_WEB != '/'){
            $page = str_replace(_SRV_WEB,'',$page);
        }else{
            $page = substr($page, 1);
        }

        if ($_SERVER['QUERY_STRING']!='')
        $page = str_replace('?'.$_SERVER['QUERY_STRING'],'',$page);

        if(_SYSTEM_SEO_FILE_TYPE!=''){
            $page_clean = $this->_cleanUrlFromFileType($page, '.'._SYSTEM_SEO_FILE_TYPE);
        }else{
            $page_clean = $page;
        }

        ($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:_cleanUpUrl_page_cleaner')) ? eval($plugin_code) : false;

        $page_array= array('url'=>$page, 'url_clean'=>$page_clean);

        ($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:_cleanUpUrl_bottom')) ? eval($plugin_code) : false;

        return $page_array;
    }

    function _cleanUrlFromFileType($page, $value){
        global $xtPlugin;

        ($plugin_code = $xtPlugin->PluginCode('class.page_handler.php:_cleanUrlFromFileType_top')) ? eval($plugin_code) : false;
        if(isset($plugin_return_value))
            return $plugin_return_value;

        if(preg_match('/'.$value.'/', $page)){

            $start_pos_val = strpos($page, $value);
            $page = substr($page, 0, $start_pos_val);

            return $page;
        }else{
            return $page;
        }
    }
}