<?php

/**
 * Die ShopgateConnectException-Klasse für die Ausnahmebehandlung von ShopgateConnect.
 * 
 * Die Ausnahme wird an Shopgate zurückgegeben.
 * @author Martin Weber (Shopgate GmbH)
 * @version 1.2
 */
class ShopgateConnectException extends Exception {
	const INVALID_USERNAME_OR_PASSWORD = 100;
	const EMPTY_CUSTOMER_NUMBER = 200;
	const EMPTY_FIRSTNAME = 201;
	const EMPTY_SURNAME = 202;
	const EMPTY_PHONE = 203;
	const EMPTY_MOBILE = 204;
	const EMPTY_CITY = 205;
	const EMPTY_ZIP = 206;
	const EMPTY_STREET = 207;
	const EMPTY_COMPANY = 208;
	const EMPTY_COUNTRY = 209;
	const EMPTY_GENDER = 210;
	const EMPTY_MAIL = 211;
	
	
	/**
	 * Der Konstruktor der ShopgateConnectException-Klasse.
	 * 
	 * @param string $message 	Ein Array mit allen Informationen der Bestellung.
	 * @param const string $code 	Der Fehlercode.
	 * @param string $user	 Optional ein Benutzer.
	 */
	public function __construct($message, $code, $user=null) {
		parent::__construct($message, $code);
		
		$error["error_code"] = $code;
		$error["error_message"] = $message;
		$error["post_data"] = $_POST;
		if($user)
			$error["user_data"] = get_object_vars($this);
		echo json_encode($error);
	}
}

/**
 * Die ShopgateShopCustomer-Klasse in der die Informationen zu einem Kunden gespeichert werden.
 * 
 * @author Martin Weber (Shopgate GmbH)
 * @version 1.0
 */
class ShopgateShopCustomer {
	private $customer_number = "";
	private $firstname = "";
	private $surname = "";
	private $gender = "";
	private $street = "";
	private $zip = "";
	private $city = "";
	private $country = "";
	private $phone = "";
	private $mail = "";
	private $mobile = "";
	private $company = "";
	
	const MALE = 0;
	const FEMALE = 1;

	/**
	 * Der Konstruktor der ShopgateShopCustomer-Klasse
	 * 
	 * @param mixed $data 	Ein Array oder ein String mit allen Kundeninformationen.
	 */
	public function __construct($data = null) {
		if($data) {
			if(is_string($data))
				$data = json_decode($data, true);
			if(is_array($data)) {
				foreach($data as $key=>$value)
					$this->$key = $value;
			}
		}
	}
	
	/**
	 * Set the UserId of the Shop
	 * 
	 * It can be the Customernumber, a eMail etc.
	 * @param string $value
	 */
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}
	/**
	 * Get the customernumber
	 * 
	 * It can be the customernumber, a eMail etc.
	 * @return string
	 */
	public function getCustomerNumber() {
		return $this->customer_number;
	}
	/**
	 * Set the firstname of the customer
	 * @param string $value
	 */
	public function setFirstName($value) {
		$this->firstname = $value;
	}
	/**
	 * Get the firstname of the customer
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstname;
	}
	/**
	 * Set the surname of the customer
	 * @param string $value
	 */
	public function setSurname($value) {
		$this->surname = $value;
	}
	/**
	 * Get the surname of the customer
	 * @return string
	 */
	public function getSurname() {
		return $this->surname;
	}
	/**
	 * Set gender of the customer
	 * 
	 * Use ShopgateShopCustomer::FEMALE
	 * or ShopgateShopCustomer::MALE
	 * as Value
	 * 
	 * @param int $value
	 */
	public function setGender($value) {
		if($value == self::FEMALE
			|| $value == self::MALE)
			$this->gender = $value;
	}
	/**
	 * Get gender of the customer
	 * @return int
	 */
	public function getGender() {
		return $this->gender;
	}
	/**
	 * Set the street of the customer
	 * @param string $value
	 */
	public function setStreet($value) {
		$this->street = $value;
	}
	/**
	 * Get the Street of the customer
	 * @return string
	 */
	public function getStreet() {
		return $this->street;
	}
	/**
	 * Set the Zip-Code of the customer
	 * @param string $value
	 */
	public function setZip($value) {
		$this->zip = $value;
	}
	/**
	 * Get the Zip-Code of the customer
	 * @return string
	 */
	public function getZip() {
		return $this->zip;
	}
	/**
	 * Set the city of the customer
	 * @param string $value
	 */
	public function setCity($value) {
		$this->city = $value;
	}
	/**
	 * Get the city of the customer
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}
	/**
	 * Set the country of the customer<br />
	 * <b>
	 * Use the International Standart Codes by ISO-3166<br/>
	 * http://www.iso.org/iso/english_country_names_and_code_elements
	 * </b>
	 * @param string $value
	 */
	public function setCountry($value) {
		$this->country = $value;
	}
	/**
	 * Get the country-code of the customer
	 * @return string
	 */
	public function getCoutry() {
		return $this->country;
	}
	/**
	 * Set the customers phonenumber 
	 * @param string $value
	 */
	public function setPhone($value) {
		$this->phone = $value;
	}
	/**
	 * Get the customers phonenumber
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}
	/**
	 * Set the customers mobile phonenumber
	 * @param string $value
	 */
	public function setMobile($value) {
		$this->mobile = $value;
	}
	/**
	 * Get the customers mobile phonenumber
	 * @return string
	 */
	public function getMobile() {
		return $this->mobile;
	}
	/**
	 * Set the e-Mail address 
	 * @param string $value
	 */
	public function setMail($value) {
		$this->mail = $value;
	}
	/**
	 * Get the e-Mail address 
	 * @return string
	 */
	public function getMail() {
		return $this->mail;
	}
	/**
	 * 
	 * Set the companies name of the customer
	 * @param string $value
	 */
	public function setCompany($value) {
		$this->company = $value;
	}
	/**
	 * Return the companies name of the customer
	 * @return string
	 */
	public function getCompany() {
		return $this->company;
	} 
	
	/**
	 * Das aktuelle Objekt in ein Array umwandeln
	 * 
	 * @return array
	 */
	public function toArray() {
		return get_object_vars($this);
	}
	/**
	 * Konvertiert das aktuelle Objekt in ein Json-Array
	 * @return string
	 */
	public function toJSON() {
		$data = $this->toArray();
		return json_encode($data);
	}
	
	/**
	 * Validiert eine Instanz der ShopgateShopCustomer-Klasse.
	 * 
	 * @throws ShopgateConnectException
	 * @return void
	 */
	private function validate() {
		$optional = array("phone", "mobile", "company");
		foreach(get_object_vars($this) as $key=>$value) {
			if(in_array($value, $optional))
				continue;
				
			if(empty($value)) {
				$message = "Missing field '$key'.";
				$_key = strtoupper($key);
				$code = constant("ShopgateConnectException::EMPTY_$_key");
				throw new ShopgateConnectException($message, $code, $this);
			}
		}
	} 
}

/**
 * Oberklasse der ShopgateConnectApi
 * 
 * @abstract
 * @author Martin Weber (Shopgate GmbH)
 * @version 1.0
 */
abstract class ShopgateConnectApi {
	private $userid = null;
	private $password = null;
	
	
	/**
	 * Der Konstruktor der ShopgateConnectApi-Klasse.
	 * @throws ShopgateConnectException
	 */
	public function __construct() {
		if(!isset($_POST["shopgate_remote_userid"]) || empty($_POST["shopgate_remote_userid"])
		 || !isset($_POST["shopgate_remote_password"])|| empty($_POST["shopgate_remote_password"]))
			throw new ShopgateConnectException("UserId or Password are not transferred.",
				ShopgateConnectException::INVALID_USERNAME_OR_PASSWORD);
				
		$this->userid = $_POST["shopgate_remote_userid"];
		$this->password = $_POST["shopgate_remote_password"];
	}
	
	/**
	 * Oberfunktion zum Abrufen von Benutzerdaten.
	 * 
	 * !!! OVERRIDE THIS METHOD !!!
	 * @abstract
	 * 
	 * @param string $user	 Der Loginname des Benutzers.
	 * @param string $pass	 Das Benutzerpasswort.
	 * @return ShopgateShopCustomer
	 */
	abstract public function getUser($user, $pass);

	/**
	 * Benutzerinformationen an Shopgate senden.
	 * 
	 * @return void
	 */
	public function send() {
		ob_clean();
		$user = $this->getUser($this->userid, $this->password);
		echo $user->toJSON();
	}
	
	/**
	 * Liefert Benutzerdaten zum Testen.
	 * 
	 * @access protected
	 * @return array $data	 Ein Array mit Testbenutzerdaten.
	 */
	protected function getTestUser($user, $pass) {
		$data = array();
		if($user === "ping" && $pass === "pong") {
			$data['customer_number'] = "ping0123";
			$data['name'] = "Max";
			$data['surname'] = "Mustermann";
			$data['email'] = "testuser@shopgate.com";
			$data['zip'] = "35510";
			$data['city'] = "Butzbach";
			$data['street'] = "Badborngasse 1a";
			$data['phone'] = "0603374700";
			$data['mobile'] = "06033747020";
		}
		return $data; 
	}
}

