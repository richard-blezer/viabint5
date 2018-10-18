<?php

function debug($was, $type="var_dump") {
	if($type==="var_dump") {
		echo "<pre>";
		var_dump($was);
		echo "</pre>";
	} else if($type==="print_r") {
		echo "<pre>";
		print_r($was);
		echo "</pre>";
	}
}

function profile() {
	debug("Memory: " . (memory_get_usage() / 1024) / 1024 . " MB \n", "print_r");
}

function sg_json_encode($array) {
	$string = "";
	if(function_exists("json_encode")) {
		$string = json_encode($array);
	} else {
		require_once dirname(__FILE__).'/../ext/JSON.php';
		$json = new Services_JSON();
		$string = $json->encode($array);
	}

	return $string;
}

function ShopgateErrorHandler($errno, $errstr, $errfile, $errline) {
	//no difference between excpetions and E_WARNING
	$msg = "Fatal PHP Error [Nr. $errno : $errfile / $errline] ";
	$msg .= "$errstr";

	$msg .= "\n". print_r(debug_backtrace(false));

	ShopgateFrameworkCore::logWrite($msg);

	return true;
}
//set_error_handler('ShopgateErrorHandler');

/**
 * Diese Excpetion wird in einem Fehlerfall vom Framework geworfen.
 * Alle Fehler werden im Log mitprotokolliert.
 *
 * @author Martin Weber
 * @version 1.0.$Rev: 591 $
 *
 */
class ShopgateFrameworkException extends Exception {
	public $lastResponse;
	
	/**
	 * Es sind nur Nachrichten als Text erlaubt
	 * @param string $message
	 */
	function __construct($message) {
		$this->lastResponse = $message;
		
		$btrace = debug_backtrace();
		$btrace = $btrace[1];
		$message = $btrace["class"]."::".$btrace["function"]."():".$btrace["line"]." - " . print_r($message, true);
		ShopgateFramework::logWrite($message);
		parent::__construct($message);
	}
}

define('SHOPGATE_BASE_DIR', realpath(dirname(__FILE__).'/../'));
define('SHOPGATE_FRAMEWORK_VERSION', "1.2.0");
define('SHOPGATE_FRAMEWORK_REVISION', "\$Rev: 591 $");

define('SHOPGATE_MOBILE_HEADER', 'http://static.shopgate.com/api/mobile_header.js');
define('SHOPGATE_ITUNES_URL', 'http://itunes.apple.com/de/app/shopgate-eine-app-alle-shops/id365287459?mt=8');

###################################################################################
# Config Datei
###################################################################################
if(!isset($shopgate_config)) {
	if (file_exists(SHOPGATE_BASE_DIR.'/myconfig.php')) {
		require_once SHOPGATE_BASE_DIR.'/myconfig.php';
	} else {
		require_once SHOPGATE_BASE_DIR.'/config.php';
	}
}

if (file_exists(SHOPGATE_BASE_DIR.'/devconfig.php')) {
	require_once SHOPGATE_BASE_DIR.'/devconfig.php';
}

if (isset($shopgate_config) && is_array($shopgate_config)) {
	try {
		ShopgateConfig::setConfig($shopgate_config, false);
	} catch (Exception $e) {
		$response = array(
			"is_error"=>true,
			"error"=>$e->getMessage(),
		);
		echo sg_json_encode($response);
		exit;
	}
} else {
	die("Die Config Datei ist ungültig!");
}
###################################################################################
# Include der anderen Dateien
###################################################################################

require_once SHOPGATE_BASE_DIR.'/lib/core_api.php';
require_once SHOPGATE_BASE_DIR.'/lib/item_api.php';
require_once SHOPGATE_BASE_DIR.'/lib/order_api.php';
require_once SHOPGATE_BASE_DIR.'/lib/connect_api.php';

###################################################################################
# Framework Klassen
###################################################################################


/**
 * Einstellungen für das Framework
 *
 * @author Daniel Aigner
 * @version 1.0.0
 *
 */
class ShopgateConfig {

	/**
	 * Die Standardeinstellungen.
	 *
	 * Die hier festgelegten Einstellungen werden aus der Datei
	 * config.php bzw. myconfig.php überschrieben und erweitert
	 *
	 * - api_url -> Die URL zum Shopgate-Server.
	 * - customer_number -> Die Kundennummer des Händleraccounts
	 * - apikey -> Der API-Key des Händlers. Dieser muss nach änderung angepasst werden.
	 * - shop_number -> Die Nummer des Shops.
	 * - server -> An welchen Server die Daten gesendet werden.
	 * - plugin -> Das PlugIn, welches verwendet werden soll.
	 * - plugin_language -> Spracheinstellung für das Plugin. Zur Zeit nur DE.
	 * - plugin_currency -> Währungseinstellung für das Plugin. Zur Zeit nur EUR.
	 * - plugin_root_dir -> Das Basisverzeichniss für das PlugIn.
	 * - enable_ping -> Ping erlaubt.
	 * - enable_get_shop_info -> Infos ueber das Shopsystem abholen
	 * - enable_http_alert -> Übergeben von bestelldaten erlaubt.
	 * - enable_connect -> Shopgate Connect erlaubt.
	 * - enable_get_items_csv -> Abholen der Produkt-CSV erlaubt.
	 * - enable_get_reviews_csv -> Abholen der Review-CSV erlaubt.
	 * - enable_get_pages_csv -> Abholen der Pages-CSV erlaubt.
	 * - enable_get_log_file -> Abholen der Log-Files erlaubt
	 * - generate_items_csv_on_the_fly -> Die CSV direkt beim Download erstellen
	 *
	 * @var array
	 */
	private static $config =  array(
		'api_url' => 'https://api.shopgate.com/shopgateway/api/',
		'customer_number' => 'THE_CUSTOMER_NUMBER',
		'shop_number' => 'THE_SHOP_NUMBER',
		'apikey' => 'THE_API_KEY',
		'server' => 'live',
		'plugin' => 'veyton',
		'plugin_language' => 'DE',
		'plugin_currency' => 'EUR',
		'plugin_root_dir' => "",
		'enable_ping' => true,
		'enable_get_shop_info' => true,
		'enable_http_alert' => true,
		'enable_connect' => true,
		'enable_get_items_csv' => true,
		'enable_get_reviews_csv' => true,
		'enable_get_pages_csv' => true,
		'enable_get_log_file' => true,
		'enable_app_redirect' => true,
		'generate_items_csv_on_the_fly' => true,
		'max_attributes' => 50,
		'use_custom_error_handler' => false,
	);

	/**
	 * Übergeben und überprüfen der Einstellungen.
	 *
	 * @param array $newConfig
	 */
	public static final function setConfig(array $newConfig, $validate = true) {
		if($validate) {
			self::validateConfig($newConfig);
		}
		self::$config = array_merge(self::$config, $newConfig);
	}

	/**
	 * Gibt das Konfigurations-Array zurück.
	 */
	public static final function validateAndReturnConfig() {
		try {
			self::validateConfig(self::$config);
		} catch (ShopgateFrameworkException $e) {  }
		
		return self::$config;
	}

	public static final function getConfig() {
		self::validateConfig(self::$config);
		
		return self::$config;
	}
	
	public static final function getPluginName() {
		return self::$config["plugin"];
	}

	/**
	 * Gibt den Pfad zur Error-Log-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 */
	public static final function getLogFilePath($type="error") {
		if($type==="access") {
			if(isset(self::$config['path_to_access_log_file'])) {
				return self::$config['path_to_access_log_file'];
			} else {
				return SHOPGATE_BASE_DIR.'/data/access.log';
			}
		} else {
			if(isset(self::$config['path_to_error_log_file'])) {
				return self::$config['path_to_error_log_file'];
			} else {
				return SHOPGATE_BASE_DIR.'/data/error.log';
			}
		}
	}

	/**
	 * Gibt den Pfad zur items-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 */
	public static final function getItemsCsvFilePath() {
		if(isset(self::$config['path_to_items_csv_file'])) {
			return self::$config['path_to_items_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/data/items.csv';
		}
	}

	/**
	 * Gibt den Pfad zur review-csv-Datei zurück
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden
	 */
	public static final function getReviewsCsvFilePath() {
		if(isset(self::$config['path_to_reviews_csv_file'])) {
			return self::$config['path_to_reviews_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/data/reviews.csv';
		}
	}

	/**
	 * Gibt den Pfad zur pages-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 */
	public static final function getPagesCsvFilePath() {
		if(isset(self::$config['path_to_pages_csv_file'])) {
			return self::$config['path_to_pages_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/data/pages.csv';
		}
	}

	/**
	 * Prüft, ob alle Pflichtfelder gesetzt sind und setzt die api_url.
	 *
	 * @param array $newConfig
	 * @throws ShopgateFrameworkException
	 */
	private static function validateConfig(array $newConfig) {
		//Pflichtfelder überprüfen
		if(!preg_match("/^\S+/", $newConfig['apikey'])){
			throw new ShopgateFrameworkException("Das Feld 'apikey' in der Konfiguration hat ein Falsches Format oder ist leer. Bitte prüfen Sie, das keine Leerzeichen vorhanden sind.");
		}

		if(!preg_match("/^\d{5,}$/", $newConfig['customer_number'])){
			throw new ShopgateFrameworkException("Das Feld 'customer_number' in der Konfiguration muss mindestens fünf Ziffern enthalten und darf keine Leerzechen enthalten.");
		}
		if(!preg_match("/^\d{5}$/", $newConfig['shop_number'])){
			throw new ShopgateFrameworkException("Das Feld 'shop_number' in der Konfiguration muss genau fünf Ziffern enthalten und darf keine Leerzeichen enthalten.");
		}

		////////////////////////////////////////////////////////////////////////
		// Server URL setzen
		////////////////////////////////////////////////////////////////////////
		if(!empty($newConfig["server"]) && $newConfig["server"] === "pg") {
			// Playground?
			self::$config["api_url"] = "https://api.shopgatepg.com/shopgateway/api/";
		} else if(!empty($newConfig["server"]) && $newConfig["server"] === "custom"
		&& !empty($newConfig["server_custom_url"])) {
			// Eigener Test-Server?
			self::$config["api_url"] = $newConfig["server_custom_url"];
		} else {
			// Live-Server?
			self::$config["api_url"] = "https://api.shopgate.com/shopgateway/api/";
		}
	}
}

/**
 * Das Herzstück des Frameworks. Alle Klassen werden ursprünglich von dieser
 * abstrakten Klasse abgeleitet. Sie stellt Funktionen wie z.B. das Loggen von
 * Fehlern zur Verfügung.
 *
 * @author Martin Weber
 * @version 1.0.0
 *
 */
abstract class ShopgateFrameworkCore {

	/**
	 * Der FileHandler für die Fehler-Log-Datei.
	 *
	 * @var resource
	 */
	protected static $errorLogFileHandler = null;
	/**
	 *
	 * Der FileHandler für die Zugriff-Log-Datei.
	 *
	 * @var resource
	 */
	protected static $accessLogFileHandler = null;

	/**
	 * Leitet die geloggten Daten an logWrite weiter
	 *
	 * @see lib/ShopgateFrameworkCore::logWrite($msg)
	 * @param string $msg
	 */
	public function log($msg, $type="error") {
		self::logWrite($msg, $type);
	}

	/**
	 * Schreibt die Nachricht in die Log-Datei.
	 * Wenn die Datei noch nicht existiert, wird diese
	 * automatisch erstellt.
	 *
	 * Der Speicherort dieser Datei ist data/shopgate_framework.log.
	 * Alternativ kann man einen Pfad in dr config.php angeben
	 * <code>
	 * $shopgate_config['path_to_log_file'] = "/path/to/file.log";
	 * </code>
	 *
	 * @param string $msg
	 */
	public static function logWrite($msg, $type="error") {
		$logFilePath = ShopgateConfig::getLogFilePath($type);
		$msg = gmdate('d-m-Y H:i:s: ').$msg."\n";
		if($type === "access") {
			if(!self::$accessLogFileHandler) {
				// Datei öffnen
				self::$accessLogFileHandler = fopen($logFilePath, 'a');
			}
			// In Datei schreiben
			fwrite(self::$accessLogFileHandler, $msg);
		}
		else {
			if(!self::$errorLogFileHandler) {
				// Datei öffnen
				self::$errorLogFileHandler = fopen($logFilePath, 'a');
			}
			// In Datei schreiben
			fwrite(self::$errorLogFileHandler, $msg);
		}
	}

	/**
	 * Sorgt am Ende für das Schließen der Log-Datei,
	 * falls diese noch offen sein sollte.
	 */
	public function __destruct() {
		if(self::$errorLogFileHandler) {
			// Datei schließen
			fclose(self::$errorLogFileHandler);
			self::$errorLogFileHandler = null;
		}
	}
}

/**
 * Das ShopgateFramework stellt die Funktionen der Shopgate-API zur Verfügung.
 *
 * @author Martin Weber
 * @version 1.0.0
 *
 */
class ShopgateFramework extends ShopgateFrameworkCore {
	/**
	 * Konfiguration des Frameworks.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Das Plugin für das jeweilige Shopping-System, das passende
	 * Plugin wird entsprechend der Config geladen.
	 *
	 * @var ShopgatePluginCore
	 */
	private $plugin;


	/**
	 * Die übergebenen POST- und GET-Parameter.
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Die erlaubten Funktionen, die aufgerufen werden können.
	 *
	 * @var array
	 */
	private  $actionWhitelist = array('ping', 'get_shop_info','http_alert', 'connect', 'get_items_csv',
		'get_reviews_csv', 'get_pages_csv', 'get_log_file');

	/**
	 * Die Daten, die zurück an Shopgate gehen. Dieses Array wird beim
	 * Beenden der Startfunktion als json-Array zurückgegeben
	 *
	 * @var array
	 */
	private $response = array();

	/**
	 * Dies ist der Einstiegspunkt des Frameworks. Es werden die Konfigurationen
	 * ausgelesen und gesetzt. Vor dem Aufrufen der eigentlichen Aktion wird
	 * geprüft, ob diese in der Konfiguration auch freigegeben wurde.
	 *
	 * Eventuell aufgetretene Fehler werden hier abgefangen und an den Server
	 * zurückgegeben.
	 *
	 * @throws ShopgateFrameworkException
	 */
	public function start() {
		try {
			// Config-Datei laden
			$this->config = ShopgateConfig::validateAndReturnConfig();

			// Plugin-Datei laden
			$this->plugin = ShopgatePluginCore::newInstance($this->config);

			// Übergebene Parameter importieren
			$this->params = $_REQUEST;
			// Action überprüfen und aufrufen
			if(empty($this->params['action'])) {
				throw new ShopgateFrameworkException('Get-Parameter "action" nicht übergeben');
			}

			$action = $this->params['action'];

			if(!in_array($action, $this->actionWhitelist)) {
				throw new ShopgateFrameworkException('Unbekannte Action: '.$action);
			} if($this->config['enable_'.$action] !== true) {
				throw new ShopgateFrameworkException('Action '.$action.' ist nicht in der Config-Datei erlaubt worden');
			}

			$actionCamelCase = $this->__toCamelCase($action);
				
			$this->{$actionCamelCase}();
		} catch (Exception $e) {

			// Abfangen einer beliebigen Excpetion innerhalb eines Plugins.
			// Der Fehler wird an den Serve zurückgegeben
			$this->response["is_error"] = true;
			$this->response["error"] = $e->getMessage();
		}

		// Setze noch die Framework-Version
		$this->response["version"] = SHOPGATE_FRAMEWORK_VERSION;
		$this->response["framework_revision"] = SHOPGATE_FRAMEWORK_REVISION;
		$this->response["plugin_revision"] = defined("SHOPGATE_PLUGIN_REVISION")?SHOPGATE_PLUGIN_REVISION:"Unbekannt";

		// Gib die Daten zurück an Shopgate
		header("HTTP/1.0 200 OK");
		echo sg_json_encode($this->response);

		return !isset($this->response["is_error"]);
	}

	/**
	 * Erzeugt aus get_items_csv => getItemsCSV
	 *
	 * @param string $str
	 * @param bool $capitalise_first_char
	 */
	private function __toCamelCase($str, $capitalise_first_char = false) {
		if($capitalise_first_char) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}

	/**
	 * Prüft ob der gegebene API-Key mit dem der Konfiguration übereinstimmt.
	 *
	 * @throws ShopgateFrameworkException
	 */
	private function __checkApiKey() {
		if(defined('DEBUG') && DEBUG == 1) return ;

		if(!isset($this->params['apikey'])) {
			header("HTTP/1.0 403 Forbidden");
			throw new ShopgateFrameworkException('Kein apikey übergeben');
		} elseif($this->params['apikey'] != $this->config['apikey']) {
			header("HTTP/1.0 403 Forbidden");
			throw new ShopgateFrameworkException('Der apikey ist falsch');
		}

		if(!isset($this->params['customer_number'])) {
			header("HTTP/1.0 403 Forbidden");
			throw new ShopgateFrameworkException('Keine customer_number übergeben');
		} elseif($this->params['customer_number'] != $this->config['customer_number']) {
			header("HTTP/1.0 403 Forbidden");
			throw new ShopgateFrameworkException('Die customer_number ist falsch');
		}
	}


	/****************************************
	 * Actions die Aufgerufen werden können 
	 ****************************************/

	/**
	 * Liefert mindestens einen "pong=OK" zurück.
	 *
	 * Wenn der API-Key und die Customer-Number stimmen, werden Informationen
	 * zum Server zurückgegeben. U.a, welche Server-Version und welche Plugins
	 * installiert sind.
	 */
	private function ping() {
		$this->response["pong"] = "OK";

		$this->__checkApiKey();

		// Statusmeldung ausgeben

		$this->response["extensions"] = get_loaded_extensions();
		$this->response["curl_version"] = curl_version();
		$this->response["php_version"] = phpversion();
		$this->response["configuration"] = $this->config;
	}

	/**
	 * Liefert Information ueber das verwendete Shopsystem zurueck
	 */
	private function getShopInfo() {
		$this->__checkApiKey();
		$Plugin = ShopgatePluginCore::newInstance($this->config);
		$info = $Plugin->startCreateShopInfo();
		if(!empty($info)){
			$this->response["shopinfo"] = $info;
		}else{
			$this->response["shopinfo"] = 'Keine Information über das Shopsystem verfügbar';
		}


	}
	/**
	 * Informiere das Framework über neue Meldungen wie z.B. eine neue
	 * Bestellung eingegangen.
	 *
	 * @throws ShopgateFrameworkException
	 */
	private function httpAlert() {
		//$this->__checkApiKey();

		$this->log("Bestellung mit folgenden Parametern wurde übergeben:\n".print_r($this->params,true), 'access');

		// Benachrichtigung über neue Bestellung oder sonstige Benachrichtigung
		if(!isset($this->params['order_number'])) {
			throw new ShopgateFrameworkException('http_alert aufgerufen, aber keine order_number übergeben');
		}

		$orderApi = new ShopgateOrderApi();
		// Neue Bestellung: Daten holen und im eigenen System speichern
		$order = $orderApi->getOrderDetails($this->params['order_number']);

		$this->plugin->saveOrder($order);
	}

	/**
	 * ShopgateConnect
	 * Verbindet einen ShopgateAccount mit einem ShopAccount.
	 *
	 * @throws ShopgateFrameworkException
	 */
	private function connect() {
		$this->__checkApiKey();

		// Shopgate-Connect
		// GET-Parameter: user, pass
		if(!isset($this->params['user'])) {
			throw new ShopgateFrameworkException('Parameter user nicht übergeben');
		} elseif(!isset($this->params['pass'])) {
			throw new ShopgateFrameworkException('Parameter pass nicht übergeben');
		}
		// Die Userdaten über das Plugin auslesen
		$userData = $this->plugin->getUserData($this->params['user'], $this->params['pass']);

		if(!is_object($userData) || get_class($userData) !== "ShopgateShopCustomer") {
			throw new ShopgateFrameworkException("Das zurückgegebene Format ist ungültig.");
		}
			
		// Daten als JSON zurückliefern
		$this->response["user_data"] = $userData->toArray();
	}

	/**
	 * Liefert die generierte items.csv-Datei an Shopgate zurück.
	 *
	 * Nach der Ausgabe wird das Skript sofort beendet.
	 *
	 * @throws ShopgateFrameworkException
	 */
	private function getItemsCsv() {

		$this->__checkApiKey();
		$generate_csv = $this->config["generate_items_csv_on_the_fly"];

		if(isset($this->params["generate_items_csv_on_the_fly"]))
		$generate_csv = $this->params["generate_items_csv_on_the_fly"];
			
		$fileName = ShopgateConfig::getItemsCsvFilePath();

		if($generate_csv) {
			// Plugin-Klasse initialisieren
			$Plugin = ShopgatePluginCore::newInstance($this->config);

			// CSV-Datei erstellen/updaten
			$Plugin->startCreateItemsCsv();
		}

		if(!file_exists($fileName)) {
			throw new ShopgateFrameworkException("Datei $fileName konnte nicht gefunden werden.");
		}

		// Inhalt der Datei zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="items.csv"');

		$fp = fopen($fileName, "r");

		if(!$fp) {
			throw new JobException('Konnte Datei nicht öffnen');
		}

		while($line = fgets($fp) )
		{
			echo $line;
		}//while end

		fclose($fp);

		exit;
	}
	/**
	 * Liefert die generierte reviews.csv Datei an Shopgate zurück.
	 *
	 * Nach der Ausgabe wird das Skript sofort beendet.
	 *
	 * @throws ShopgateFrameworkException
	 */
	private function getReviewsCsv() {
		$this->__checkApiKey();

		$fileName = ShopgateConfig::getReviewsCsvFilePath();

		$Plugin = ShopgatePluginCore::newInstance($this->config);
		$Plugin->startCreateReviewsCsv();

		if(!file_exists($fileName)) {
			throw new ShopgateFrameworkException("Datei $fileName nicht gefunden");
		}

		// Inhalt der Datei an den Browser zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="reviews.csv"');
		readfile($fileName);
		exit;
	}

	/**
	 * Liefert die generierte pages.csv-Datei an Shopgate zurück.
	 *
	 * Nach der Ausgabe wird das Skript sofort beendet.
	 *
	 * @throws ShopgateFrameworkException
	 */
	private function getPagesCsv() {
		$this->__checkApiKey();

		$fileName = ShopgateConfig::getPagesCsvFilePath();

		if(!file_exists($fileName)) {
			throw new ShopgateFrameworkException("Datei $fileName nicht gefunden");
		}

		// Inhalt der Datei an den Browser zurückgeben

		header("HTTP/1.0 200 OK");
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="pages.csv"');
		readfile($fileName);
		exit;
	}

	/**
	 * Parameter "kilobyte" muss gesetzt sein. Liefert dann die
	 * letzten x Kilobyte der Log-Datei zurück.
	 *
	 */
	private function getLogFile() {
		$this->__checkApiKey();

		$type = "error";
		if(isset($this->params['log_type'])) {
			$type = $this->params['log_type'];
		}

		if(!isset($this->params['kilobyte'])) {
			throw new ShopgateFrameworkException('Parameter kilobyte nicht übergeben');
		}


		$kilobyte = $this->params['kilobyte']; // Letzten x Kilobyte der Logdatei zurückgeben

		$filePath = ShopgateConfig::getLogFilePath($type);

		$bufferSize = floatval($kilobyte) * 1024;
		$returnStr = "";

		$fileSize = filesize($filePath);
		if($fileSize > $bufferSize) {
			$log = fopen($filePath,'r');
			$returnStr = (fseek($log, -1*$bufferSize, SEEK_END) == 0) ? fread($log, $bufferSize) : "Error reading the file $filePath.";
			fclose($log);
		} else {
			$returnStr = file_get_contents($filePath);
		}


		echo $returnStr;
		exit;
	}
}

/**
 * Die Grundfunktionalität eines Plugins
 *
 * Die Plugins müssen von dieser Klasse abgeleitet sein.
 *
 * <code>
 * class ShopgatePlugin extends ShopgatePluginCore {
 *	public function getUserData($user, $pass) {}
 *	public function saveOrder(ShopgateOrder $order) {}
 *	protected function createItemsCSV() {}
 *	protected function createReviewsCSV() {}
 *	protected function createPagesCSV() {}
 * }
 * </code>
 *
 * @author Martin Weber
 * @version 1.0.0
 */
abstract class ShopgatePluginCore extends ShopgateFrameworkCore {
	private $allowedEncodings = array(
		'UTF-8', 'ASCII', 'CP1252', 'ISO-8859-15', 'UTF-16LE','ISO-8859-1'
		);

		/**
		 * Die Handler für die Datei, in die geschrieben werden soll.
		 *
		 * @var resource
		 */
		private $fileHandle;
		/**
		 * Der Buffer.
		 *
		 * @var array
		 */
		private $buffer = array();
		/**
		 * Der aktuelle Füllstand des Buffers.
		 *
		 * @var int
		 */
		private $bufferCounter = 0;

		/**
		 * Die Konfiguration des Plugins.
		 *
		 * @var array
		 */
		protected $config;

		/**
		 * Wenn der Buffer größer als dieser Wert ist,
		 * werden alle Datensätze in die Datei geschrieben.
		 *
		 * @var int
		 */
		protected $bufferLimit = 100; // Gibt an, nach wievielen Zeilen in die CSV-Datei geschrieben werden soll

		/**
		 * Erzeuge eine neue Instanz des Plugins und gibt diese zurück.
		 *
		 * @param array $config
		 * @throws ShopgateFrameworkException
		 * @return ShopgatePluginCore
		 */
		public static function newInstance(array $config) {
			$pluginFile = SHOPGATE_BASE_DIR.'/plugins/plugin_'.$config['plugin'].'.inc.php';

			if(!file_exists($pluginFile)) {
				throw new ShopgateFrameworkException("Plugin-Datei $pluginFile nicht gefunden");
			}

			require_once $pluginFile;
			$Instance = new ShopgatePlugin();

			if(!$Instance->setConfig($config)) {
				throw new ShopgateFrameworkException("Config-Datei konnte nicht initialisiert werden");
			}

			//		if(!ini_set('error_log',$config["path_to_error_log_file"])) {
			//			$Instance->log("Can not change PHP-Error-Log location.");
			//		} else {
			//			error_reporting( E_ALL );
			//		}

			$_config = ShopgateConfig::validateAndReturnConfig();
			if(isset($config["use_custom_error_handler"]) && $config["use_custom_error_handler"]) {
				set_error_handler('ShopgateErrorHandler');
			}
				
			// Muss das Wort "true" in der überschriebenen startup() zurückgeben
			if($Instance->startup() !== true) {
				throw new ShopgateFrameworkException("Plugin konnte nicht initialisiert werden ");
			}

			return $Instance;
		}

		/**
		 * Speichert die Konfiguration.
		 *
		 * @param array $config
		 */
		public final function setConfig(array $config) {
			$this->config = $config;

			return true;
		}

		/**
		 * Wird beim Start aufgerufen. Funktion überschreiben um
		 * hier evtl. eigene Variablen zu initialisieren, oder die
		 * Verbindung zu einer Datenbank aufzubauen etc..
		 *
		 */
		public function startup() {

			return true;
		}

		/**
		 * Starte das Erstellen der ShopInfo
		 *
		 * @return unknown
		 */
		public function startCreateShopInfo(){
			$shopInfo = $this->createShopInfo();

			return $shopInfo;
		}
		/**
		 * Starte das Erstellen der items.csv.
		 *
		 * Stellt sicher, dass die Datei beschrieben werden kann und das der Buffer
		 * geleert wird.
		 *
		 * @throws ShopgateFrameworkException
		 */
		public final function startCreateItemsCsv() {

			$this->log('items.csv wird erstellt', "access");
			$timeStart = time();
			$filePath = ShopgateConfig::getItemsCsvFilePath();
			$this->fileHandle = @fopen($filePath, 'w');
			if(!$this->fileHandle) {
				throw new ShopgateFrameworkException("Datei $filePath konnte nicht geöffnet/erstellt werden");
			}

			$this->createItemsCsv(); // CSV-Datei mit Buffer schreiben

			$this->flushBuffer(); // Evtl. noch nicht gespeicherte Daten im Buffer schreiben

			fclose($this->fileHandle);
			$this->log('Fertig, items.csv wurde erfolgreich erstellt', "access");
			$duration = time() - $timeStart;
			$this->log("Dauer: $duration Sekunden", "access");
		}

		/**
		 * Starte das Erstellen der reviews.csv.
		 *
		 * Stellt sicher, dass die Datei beschrieben werden kann und das der Buffer
		 * geleert wird.
		 *
		 * @throws ShopgateFrameworkException
		 */
		public final function startCreateReviewsCsv() {
			$this->log('reviews.csv wird erstellt', 'access');
			$timeStart = time();

			$filePath = ShopgateConfig::getReviewsCsvFilePath();
			$this->fileHandle = @fopen($filePath, 'w');
			if(!$this->fileHandle) {
				$this->_error("Datei $filePath konnte nicht geöffnet/erstellt werden");
			}
			$this->buffer = array();
			try {
				$this->createReviewsCsv(); // CSV-Datei mit Buffer schreiben
				$this->flushBuffer(); // Evtl. noch nicht gespeicherte Daten im Buffer schreiben
			} catch (ShopgateFrameworkException $e) {
				$this->log("Fehler beim erstellen der ReviewCsv. ". print_r($e, true));
			}

			fclose($this->fileHandle);

			$this->log('Fertig, reviews.csv wurde erfolgreich erstellt', 'access');
			$duration = time() - $timeStart;
			$this->log("Dauer: $duration Sekunden", 'access');
		}

		/**
		 * Starte das Erstellen der pages.csv.
		 *
		 * Stellt sicher, dass die Datei beschrieben werden kann und das der Buffer
		 * geleert wird.
		 *
		 * @throws ShopgateFrameworkException
		 *
		 */
		public final function startCreatePagesCsv() {
			$this->log('pages.csv wird erstellt', 'access');
			$timeStart = time();

			$filePath = ShopgateConfig::getPagesCsvFilePath();
			$this->fileHandle = @fopen($filePath, 'w');
			if(!$this->fileHandle) {
				$this->_error("Datei $filePath konnte nicht geöffnet/erstellt werden");
			}

			try {
				$this->createPagesCsv(); // CSV-Datei mit Buffer schreiben
				$this->flushBuffer(); // Evtl. noch nicht gespeicherte Daten im Buffer schreiben
			} catch (ShopgateFrameworkException $e) {
				$this->log("Fehler beim erstellen der Pages-CSV. ". print_r($e, true));
			}

			fclose($this->fileHandle);

			$this->log('Fertig, pages.csv wurde erfolgreich erstellt', 'access');
			$duration = time() - $timeStart;
			$this->log("Dauer: $duration Sekunden", 'access');
		}



		/**
		 * Zeile in die CSV-Datei schreiben (gebuffert)
		 *
		 * @param array $itemArr
		 */
		protected final function addItem($itemArr) {
			// Item Buffern, evtl. Buffer schreiben
			$this->buffer[] = $itemArr;
			$this->bufferCounter++;

			if($this->bufferCounter > $this->bufferLimit
			|| isset($this->config["flush_buffer_size"]) && $this->config["flush_buffer_size"] <= $this->bufferCounter) {
				$this->flushBuffer();
			}
		}

		/**
		 * Wenn das Limit von $this->bufferLimit überschritten wurde, werden die
		 * Datensätze aus dem Buffer in die Datei geschrieben.
		 *
		 * Der Text wird vor dem Schreiben noch in UTF-8 konvertiert.
		 */
		private final function flushBuffer() {
			// Buffer leerschreiben
			$c = "\"";
			$string = '';

			if(empty($this->buffer) && ftell($this->fileHandle) == 0)
			throw new ShopgateFrameworkException("Buffer ist Leer");


			// Wenn noch am Anfang der CSV-Datei, schreibe die Kopfzeile
			if(ftell($this->fileHandle) == 0) {
				fputcsv($this->fileHandle, array_keys($this->buffer[0]), ';', '"');
			}

			// Schreibe jeden Datensatz nach $string
			foreach($this->buffer as $item) {
				// Konvertiere nach UTF-8
				if(function_exists("mb_convert_encoding")) {
					foreach($item as &$field)
					$field = mb_convert_encoding($field, "UTF-8", $this->allowedEncodings);
				}
					
				fputcsv($this->fileHandle, $item, ";", "\"");
			}

			$this->buffer = array(); // Leere den Buffer
			$this->bufferCounter = 0; // Setze zähler auf 
		}

		/**
		 * Schreibe die Fehlermeldung in das Log
		 * und werfe eine ShopgateFrameworkException
		 *
		 * @param string $msg	Die Fehlermeldung.
		 * @throws ShopgateFrameworkException
		 */
		protected final function _error($msg) {
			$this->log($msg);
			throw new ShopgateFrameworkException($msg);
		}

		/**
		 * Setze Anführngszeichen um array Elemente
		 *
		 * @param array $array Der Daten-array
		 * @return daten-array
		 * @throws ShopgateFrameworkException
		 */
		public function enquoteArray ($array)
		{
			if (!is_array($array)) {
				throw new ShopgateFrameworkException("Array parameter ist kein array");
			}

			foreach ($array as $k=>$v) {
				$array[$k] = $this->enquote ($v);
			}

			return $array;
		}

		/**
		 * Setze Anführngszeichen um einen String
		 *
		 * @param string $string Der String
		 * @return String
		 */
		public function enquote ($string)
		{
			return '"' . $string . '"';
		}

		protected function buildDefaultRow() {
			$row = array(
				/* responsible fields */
				'item_number' 				=> "",
				'item_name' 				=> "",
				'unit_amount' 				=> "",
				'description' 				=> "",
				'urls_images' 				=> "",
				'categories' 				=> "",
				'is_available' 				=> "1",
				'available_text' 			=> "",
				'manufacturer' 				=> "",
				'url_deeplink' 				=> "",
				/* additional fields */
				'properties'				=> "",
				'manufacturer_item_number' 	=> "",
				'currency' 					=> "",
				'tax_percent' 				=> "",
				'msrp' 						=> "",
				'shipping_costs_per_order' 	=> "",
				'additional_shipping_costs_per_unit' => "0",
				'basic_price' 				=> "",
				'use_stock' 				=> "0",
				'stock_quantity' 			=> "",
				'ean' 						=> "",
				'last_update' 				=> "",
				'tags' 						=> "",
				'sort_order' 				=> "",
				'marketplace' 				=> "1",
				'internal_order_info' 		=> "",
				'related_shop_item_numbers' => "",
				'age_rating' 				=> "",
				'weight' 					=> "",
				'block_pricing' 			=> "",
				/* parent/child relationship */
				'has_children' 				=> "0",
				'parent_item_number' 		=> "",
				'attribute_1' 				=> "",
				'attribute_2' 				=> "",
				'attribute_3' 				=> "",
				'attribute_4' 				=> "",
				'attribute_5' 				=> "",
				'attribute_6' 				=> "",
				'attribute_7' 				=> "",
				'attribute_8' 				=> "",
				'attribute_9' 				=> "",
				'attribute_10' 				=> "",
				/* options */
				'has_options' 				=> "0",
				'option_1' 					=> "",
				'option_1_values' 			=> "",				
				'option_2' 					=> "",
				'option_2_values' 			=> "",
				'option_3' 					=> "",
				'option_3_values' 			=> "",
				'option_4' 					=> "",
				'option_4_values' 			=> "",
				'option_5' 					=> "",
				'option_5_values' 			=> "",
				'option_6' 					=> "",
				'option_6_values' 			=> "",
				'option_7' 					=> "",
				'option_7_values' 			=> "",
				'option_8' 					=> "",
				'option_8_values' 			=> "",
				'option_9' 					=> "",
				'option_9_values' 			=> "",
				'option_10' 				=> "",
				'option_10_values' 			=> "",
				/* inputfields */
				'has_input_fields' 			=> 0,
				'input_field_1_type'		=> "",
				'input_field_1_label'		=> "",
				'input_field_1_infotext'	=> "",
				'input_field_1_required'	=> "",
				'input_field_1_add_amount'	=> "",
				'input_field_2_type'		=> "",
				'input_field_2_label'		=> "",
				'input_field_2_infotext'	=> "",
				'input_field_2_required'	=> "",
				'input_field_2_add_amount'	=> "",
				'input_field_3_type'		=> "",
				'input_field_3_label'		=> "",
				'input_field_3_infotext'	=> "",
				'input_field_3_required'	=> "",
				'input_field_3_add_amount'	=> "",
				'input_field_4_type'		=> "",
				'input_field_4_label'		=> "",
				'input_field_4_infotext'	=> "",
				'input_field_4_required'	=> "",
				'input_field_4_add_amount'	=> "",
				'input_field_5_type'		=> "",
				'input_field_5_label'		=> "",
				'input_field_5_infotext'	=> "",
				'input_field_5_required'	=> "",
				'input_field_5_add_amount'	=> "",
				'input_field_6_type'		=> "",
				'input_field_6_label'		=> "",
				'input_field_6_infotext'	=> "",
				'input_field_6_required'	=> "",
				'input_field_6_add_amount'	=> "",
				'input_field_7_type'		=> "",
				'input_field_7_label'		=> "",
				'input_field_7_infotext'	=> "",
				'input_field_7_required'	=> "",
				'input_field_7_add_amount'	=> "",
				'input_field_8_type'		=> "",
				'input_field_8_label'		=> "",
				'input_field_8_infotext'	=> "",
				'input_field_8_required'	=> "",
				'input_field_8_add_amount'	=> "",
				'input_field_9_type'		=> "",
				'input_field_9_label'		=> "",
				'input_field_9_infotext'	=> "",
				'input_field_9_required'	=> "",
				'input_field_9_add_amount'	=> "",
				'input_field_10_type'		=> "",
				'input_field_10_label'		=> "",
				'input_field_10_infotext'	=> "",
				'input_field_10_required'	=> "",
				'input_field_10_add_amount'	=> "",			
			);
				
			return $row;
		}

		///////////////////////////////////////////////////////////////////////////
		// Die Folgenden Funktionen müssen in der                                //
		// abgeleiteten Klasse implementiert werden                              //
		///////////////////////////////////////////////////////////////////////////

		/**
		 * Vergleicht $user und $pass mit den Daten in der Datenbank und gibt die
		 * Benutzerdaten als ShopgateShopCustomer-Objekt zurück.
		 *
		 *  Diese Funktion muss in der ShopgatePlugin-Klasse implementiert werden!
		 *
		 * @param String $user
		 * @param String $pass
		 * @return ShopgateShopCustomer
		 */
		public abstract function getUserData($user, $pass);

		/**
		 * <p>Diese Funktion speichert eine Bestellung in Ihre Datenbank. Das Object $order enthält alle
		 * relevanten Daten und die bestellten Artikel. Zudem werden auch Lieferanschrift,
		 * Rechnungsanschrift und Kundenanschrift mit übergeben.</p>
		 *
		 * <p>Die Produkte können über die Funktion $order->getOrderItems() als Array
		 * abgerufen werden. Jedes Element ist ein Objelt vom Typ ShopgateOrderItem,
		 * welches die Wichtigsten Informationen zu dem jeweiligen Produkt enthält.</p>
		 *
		 * <code>
		 * foreach($order->getOrderItems() as $orderItem) {
		 *
		 * }
		 * </code>
		 *
		 * <p>Die Addressdaten sind vom Typ ShopgateOrderAddress und enthalten jeweils die
		 * Kunden-, Liefer-, oder Rechnungsanschrift.</p>
		 * <ul>
		 * <li><b>Die Adresse des Kunden:</b><br/>
		 *        $order->getCustomerAddress();</li>
		 * <li><b>Die Lieferadresse:</b><br />
		 *        $order->getDeliveryAddress();</li>
		 * <li><b>Die Rechungsadresse:</b><br />
		 *        $order->getInvoiceAddress();</li>
		 * </ul>
		 *
		 * @param ShopgateOrder $order
		 */
		public abstract function saveOrder(ShopgateOrder $order);

		/**
		 * Diese Funktion soll die Daten aus der Datenbank laden und mittels der
		 * Funktion addItem() der CSV-Datei hinzufügen
		 *
		 * Die Dukumentation zum aufbau der CSV-Datei steht unter
		 * <a href="https://www.shopgate.com/csvdoc">https://www.shopgate.com/csvdoc</a>
		 *
		 * @throws ShopgateFrameworkException
		 * @example plugins/plugin_example.inc.php
		 */
		protected abstract function createItemsCsv();

		/**
		 * Erzeugt die CSV-Datei mit den Produktberwertungen
		 *
		 * @throws ShopgateFrameworkException
		 * @example plugins/plugin_example.inc.php
		 */
		protected abstract function createReviewsCsv();

		/**
		 * Erzeugt die CSV-Datei mit den Zusatztexten für Produkte
		 *
		 * @throws ShopgateFrameworkException
		 * @example plugins/plugin_example.inc.php
		 */
		protected abstract function createPagesCsv();

		/**
		 * Erstellt Informationen ueber das verwendete Shopsystem
		 *
		 * @throws ShopgateFrameworkException
		 * @example plugins/plugin_example.inc.php
		 */
		protected abstract function createShopInfo();
}

