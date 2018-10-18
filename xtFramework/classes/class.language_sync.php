<?php
/*
 #########################################################################
 #                       xt:Commerce  4.2 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.2 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
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

class language_sync {
	
	//const SERVICE_URL = "http://xtclng.local/api";
	const SERVICE_URL = "http://lng.xt-commerce.com/api";
	
	const SERVICE_GET_AVAILABLE_LANGUAGES_SUFFIX = "/post/getAvailableLanguages";
	const SERVICE_GET_LANGUAGE_PACK = "/get/getLanguagePack";
	const SERVICE_GET_PLUGIN_TRANSLATIONS = "/get/getPluginTranslations";
	
	public $position = null;
	public $default_language = _STORE_LANGUAGE;
	
	protected $_table = TABLE_LANGUAGE_CONTENT;
	protected $_table_lang = null;
	protected $_table_seo = null;
	protected $_master_key = 'language_content_id';
	
	protected $_connectTimeout = 5;
	protected $_timeout = 20;
	
	/**
	 * Set curl connect timeout
	 * @param unknown $seconds
	 * @return language_sync
	 */
	public function setConnectTimeout($seconds) {
		$this->_connectTimeout = $seconds;
		return $this;
	}
	
	/**
	 * Set curl timeout
	 * @param unknown $seconds
	 * @return language_sync
	 */
	public function setTimeout($seconds) {
		$this->_timeout = $seconds;
		return $this;
	}
	
	/**
	 * Set position
	 * @param string $position
	 * @return language_sync
	 */
	public function setPosition($position) {
		$this->position = $position;
		return $this;
	}
	
	/**
	 * Get params for admin area
	 * @return multitype:string number multitype:string
	 */
	public function _getParams() {
		$params = array();

		$header['language'] = array('type' => 'dropdown', 'url' => self::SERVICE_URL . self::SERVICE_GET_AVAILABLE_LANGUAGES_SUFFIX);
		
		$params['header']         = $header;
		$params['master_key']     = $this->_master_key;
		$params['default_sort']   = 'language_key';
 		$params['languageTab']    = 0;
 		$params['PageSize']       = 50;


		return $params;
	}
	
	/**
	 * Get record
	 * @param number $id
	 * @return boolean|stdClass
	 */
	public function _get($id = 0) {
		global $xtPlugin, $db, $language;
	
		if ($this->position != 'admin') {
			return false;
		}
	
		$data = array();
		$data['language'] = '';
	
		$obj = new stdClass();
		$obj->totalCount = 0;
		$obj->data = array($data);
		return $obj;
	}
	
	/**
	 * Set new record
	 * @param unknown $data
	 * @param string $set_type
	 * @return stdClass
	 */
	public function _set($data, $set_type = 'edit') {
		$obj = new stdClass;
		$obj->success = true;
		
		if (!empty($data['language'])) {
			$params = array();
			$params['TranslationCode'] = array($data['language']);
			$params['ShopVersion'] = array(_SYSTEM_VERSION);
			$params['TranslationPlugin'] = "StoreTranslations";
			$params['ExportFormat'] = 1;
			
			$url = self::SERVICE_URL . self::SERVICE_GET_LANGUAGE_PACK . "?" . http_build_query($params);
			$content = $this->makeRequest($url);
			
			if (!$content || empty($content)) {
				$obj->success = false;
			} else {
				
				$downloadsFolder = _SRV_WEBROOT . "media" . DIRECTORY_SEPARATOR . "lang_downloads";
				
				if (!is_dir($downloadsFolder)) {
					mkdir($downloadsFolder, 0755);
				}
				
				$downloadFile = $downloadsFolder . DIRECTORY_SEPARATOR . "{$data['language']}_content.yml";
				
				if (file_exists($downloadFile)) {
					unlink($downloadFile);
				}
				
				file_put_contents($downloadFile, $content);
			}
		}
		
		return $obj;
	}
	
	/**
	 * Download latest plugin translations
	 * @param string $pluginName
	 * @return stdClass
	 */
	public function downloadPluginTranslations($pluginName) {
		$obj = new stdClass;
		$obj->success = true;
		
		$params = array();
		//$params['TranslationCode'] = array($data['language']);
		$params['ShopVersion'] = array(_SYSTEM_VERSION);
		$params['TranslationPlugin'] = "$pluginName";
		$params['ExportFormat'] = 2;
				
		$url = self::SERVICE_URL . self::SERVICE_GET_PLUGIN_TRANSLATIONS . "?" . http_build_query($params);
		$content = $this->makeRequest($url);
				
		if (!$content || empty($content)) {
			$obj->success = false;
		} else {
		
			$downloadsFolder = _SRV_WEBROOT . "plugins" . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . "installer";
		
			if (!is_dir($downloadsFolder)) {
				mkdir($downloadsFolder, 0755);
			}
		
			$downloadFile = $downloadsFolder . DIRECTORY_SEPARATOR . $pluginName . "_lng.xml";
			
			$xmlArray = XML_unserialize($content);
			if (is_writable($downloadFile)){
				if (isset($xmlArray['xtcommerceplugin']['language_content']['phrase'])) {
					if (file_exists($downloadFile))   {
						unlink($downloadFile);
					}
					file_put_contents($downloadFile, $content);
				}
			}
		}
		
		return $obj;
	}
	
	/**
	 * Make http response
	 * @param string $url
	 * @return mixed
	 */
	protected function makeRequest($url, $params = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT , "XT:Commerce Platform");
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		
		if (!empty($params)) {
			curl_setopt($ch, CURLOPT_POST , 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS , $params);
		}
		
		$response = curl_exec($ch);
		
		if (!$response) {
			// @TODO Log error
			//die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}
		
		if(curl_errno($ch))
		{
			// 28 -timeout
			$response = false;
		}
		curl_close($ch);
		
		return $response;
	}
	
}