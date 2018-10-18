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
# @version $Id: class.auto_cross_sell.php 4611 2011-03-30 16:39:15Z mzanier $
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

class cleancache{
	
	/**
	 * 
	 */
	public function cleancache(){
		
	}
	
	/**
	 * @param unknown_type $type
	 */
	public function cleanTemplateCached($type=1){
		$this->type = $type;
		switch ($type){
			case '1':
				$this->cleanCategoryCache();
				$this->cleanFeedCache();
                $this->cleanCSSCache();
                $this->cleanJSCache();
                $this->cleanSEOCache();
                $this->clearTemplatesCache();
				break;
			case '2':
				$this->cleanFeedCache();
				break;
			case '3':
				$this->cleanCategoryCache();
				break;
            case '4':
                $this->cleanCSSCache();
                break;
            case '5':
                $this->cleanJSCache();
                break;
            case '6':
            	$this->clearTemplatesCache();
            	break;
        }
		
		$this->addLogs();
		$this->updateLastRun($type);
	}
	
	/**
	 * 
	 */
	public function clearTemplatesCache() {
		$dir = _SRV_WEBROOT.'templates_c/';
		
		$files = $this->getTemplateCacheFiles();
		
		foreach ($files as $k=>$v){
			$filename = $dir.$v;
			unlink($filename);
		}
	}
	
	/**
	 * 
	 */
	public function cleanFeedCache(){
		$dir = _SRV_WEBROOT.'cache/';
		
		$files = array('xt_newpluginfeed.xml','xt_newsfeed.xml','xt_toppluginsfeed.xml');

		foreach ($files as $k=>$v){
			$filename = $dir.$v;
			if (file_exists($filename))     unlink($filename);
		}	

	}

    public function cleanCSSCache() {

        $dir = _SRV_WEBROOT.'cache/';
        $files = $this->getCSSFiles();

        if (is_array($files)) {
            foreach ($files as $key => $val) {
                if (file_exists($dir.$val)) {
                    unlink($dir.$val);
                }
            }
        }

    }

    public function cleanJSCache() {

        $dir = _SRV_WEBROOT.'cache/';
        $files = $this->getJsFiles();

        if (is_array($files)) {
            foreach ($files as $key => $val) {
                if (file_exists($dir.$val)) {
                    unlink($dir.$val);
                }
            }
        }
    }

    public function cleanSEOCache() {

        $dir = _SRV_WEBROOT.'cache/';
        $files = $this->getSEOFiles();

        if (is_array($files)) {
            foreach ($files as $key => $val) {
                if (file_exists($dir.$val)) {
                    unlink($dir.$val);
                }
            }
        }
    }
	
	/**
	 * 
	 */
	public function cleanCategoryCache(){
		global $template;
	
		$template->content_smarty->clear_all_cache();
	}
	
	/**
	 * 
	 */
	public function addLogs(){
		global $db;
		
		$data = array();

		$data['type'] = $this->type;
		$data['change_trigger'] = 'user';
		$data['last_run'] = date('Y-m-d h:i:s');
		$data['date_added'] = date('Y-m-d h:i:s');
		$data['last_modified'] = date('Y-m-d h:i:s');
		
		$db->AutoExecute(TABLE_CLEANCACHE_LOGS,$data,'INSERT');
	}
	
	/**
	 * @param unknown_type $type
	 */
	public function updateLastRun($type){
		global $db;
		
		$db->Execute('UPDATE '.TABLE_CLEANCACHE.' SET last_run = '.$db->DBTimeStamp(time()).' WHERE id = "'.$type. '"');	
	}

    /**
     *
     */
    private function getCSSFiles(){
        $hDir = _SRV_WEBROOT.'cache/';
        if ($handle = opendir($hDir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && strpos($file,'.css') !== false) {
                    $FilesArray[] =  $file;
                }
            }
            closedir($handle);
        }
        return $FilesArray;
    }
    
    /**
     * 
     * @return array
     */
    private function getTemplateCacheFiles() {
    	$hDir = _SRV_WEBROOT.'templates_c/';
    	if ($handle = opendir($hDir)) {
    		while (false !== ($file = readdir($handle))) {
    			if ($file != "." && $file != ".." && strpos($file,'.php') !== false) {
    				$FilesArray[] =  $file;
    			}
    		}
    		closedir($handle);
    	}
    	return $FilesArray;
    }

    /**
     *
     */
    private function getJsFiles(){
        $hDir = _SRV_WEBROOT.'cache/';
        if ($handle = opendir($hDir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && strpos($file,'.js') !== false) {
                    $FilesArray[] =  $file;
                }
            }
            closedir($handle);
        }
        return $FilesArray;
    }

    private function getSEOFiles(){
        $hDir = _SRV_WEBROOT.'cache/';
        if ($handle = opendir($hDir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && strpos($file,'seo_optimization') !== false) {
                    $FilesArray[] =  $file;
                }
            }
            closedir($handle);
        }
        return $FilesArray;
    }
}
?>