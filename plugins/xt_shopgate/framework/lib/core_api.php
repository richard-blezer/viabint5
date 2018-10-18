<?php

/**
 * Oberklasse ShopgateCoreApi-Klasse.
 * 
 * @abstract 
 * 
 * @author Martin Weber (Shopgate GmbH)
 * @version 1.0
 * @package shopgate_framework
 */
abstract class ShopgateCoreApi extends ShopgateFrameworkCore {
	private $config;
	
	private $lastUrl;
	private $lastParams;
	private $lastResponse;
	
	
	/**
	 * Der Konstruktor der ShopgateCoreApi-Klasse. 
	 * Lädt die angegebene Config.
	 * 
	 * ShopgateConfig
	 */
	public function __construct() {
		$this->config = ShopgateConfig::validateAndReturnConfig();
	}
	
	
	/**
	 * Führt für alle abgeleiteten Klassen die Abfragen am Shopgate-Server durch. 
	 * 
	 * @access protected
	 * 
	 * @param function		Die aufgerufene Funktion.
	 * @param array $params1  	Die Parameter der aufgerufenen Funktion.
	 * @param $files
	 * 
	 * @throws ShopgateFrameworkException
	 */
	protected function _execute($function, $params1=array(), $files = null) {
		if(empty($this->config))
			$this->config = ShopgateConfig::validateAndReturnConfig();
		
		$url = $this->config["api_url"];

		if(!preg_match("/\/$/", $url)) $url.="/";
		$url .= $function;
		
		$this->lastUrl = $url;
		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "ShopgateFramework/" . SHOPGATE_FRAMEWORK_VERSION);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		// Daten per post übergeben
		curl_setopt($curl, CURLOPT_POST, true);
		
		$this->lastParams = $params1;
		$this->lastParams['apikey'] = $this->config["apikey"];
		$this->lastParams['customer_number'] = $this->config["customer_number"];
		$this->lastParams['shop_number'] = $this->config["shop_number"];

		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->lastParams);

		$response = curl_exec($curl);
		curl_close($curl);

		if (!$response) throw new ShopgateFrameworkException("No Connection to the Server");
		
		$this->lastResponse = json_decode($response, true);

		if(empty($this->lastResponse)) {
			throw new ShopgateFrameworkException('Error Parsing the Response \n'.print_r($response, true));
		}
		if($this->lastResponse['error'] != 0) {
			throw new ShopgateFrameworkException($this->lastResponse);
		}
		
		return $this->lastResponse;
	}
}

