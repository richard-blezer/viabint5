<?php





class HermesAddress
{

  /**
   * 
   * @var string $addressAdd
   * @access public
   */
  public $addressAdd;

  /**
   * 
   * @var string $email
   * @access public
   */
  public $email;

  /**
   * 
   * @var string $houseNumber
   * @access public
   */
  public $houseNumber;

  /**
   * 
   * @var string $countryCode
   * @access public
   */
  public $countryCode;

  /**
   * 
   * @var string $lastname
   * @access public
   */
  public $lastname;

  /**
   * 
   * @var string $city
   * @access public
   */
  public $city;

  /**
   * 
   * @var string $district
   * @access public
   */
  public $district;

  /**
   * 
   * @var string $postcode
   * @access public
   */
  public $postcode;

  /**
   * 
   * @var string $street
   * @access public
   */
  public $street;

  /**
   * 
   * @var string $telephoneNumber
   * @access public
   */
  public $telephoneNumber;

  /**
   * 
   * @var string $firstname
   * @access public
   */
  public $firstname;

  /**
   * 
   * @var string $telephonePrefix
   * @access public
   */
  public $telephonePrefix;

  /**
   * 
   * @param string $addressAdd
   * @param string $email
   * @param string $houseNumber
   * @param string $countryCode
   * @param string $lastname
   * @param string $city
   * @param string $district
   * @param string $postcode
   * @param string $street
   * @param string $telephoneNumber
   * @param string $firstname
   * @param string $telephonePrefix
   * @access public
   */
  public function __construct($addressAdd, $email, $houseNumber, $countryCode, $lastname, $city, $district, $postcode, $street, $telephoneNumber, $firstname, $telephonePrefix)
  {
    $this->addressAdd = $addressAdd;
    $this->email = $email;
    $this->houseNumber = $houseNumber;
    $this->countryCode = $countryCode;
    $this->lastname = $lastname;
    $this->city = $city;
    $this->district = $district;
    $this->postcode = $postcode;
    $this->street = $street;
    $this->telephoneNumber = $telephoneNumber;
    $this->firstname = $firstname;
    $this->telephonePrefix = $telephonePrefix;
  }

}
