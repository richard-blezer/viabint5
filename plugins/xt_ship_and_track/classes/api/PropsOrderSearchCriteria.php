<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
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



class PropsOrderSearchCriteria
{

  /**
   * 
   * @var string $identNo
   * @access public
   */
  public $identNo;

  /**
   * 
   * @var string $orderNo
   * @access public
   */
  public $orderNo;

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
   * @var dateTime $from
   * @access public
   */
  public $from;

  /**
   * 
   * @var dateTime $to
   * @access public
   */
  public $to;

  /**
   * 
   * @var string $postcode
   * @access public
   */
  public $postcode;

  /**
   * 
   * @var string $countryCode
   * @access public
   */
  public $countryCode;

  /**
   * 
   * @var string $clientReferenceNumber
   * @access public
   */
  public $clientReferenceNumber;

  /**
   * 
   * @var string $ebayNumber
   * @access public
   */
  public $ebayNumber;

  /**
   * 
   * @var array $status
   * @access public
   */
  public $status;

  /**
   * 
   * @param string $identNo
   * @param string $orderNo
   * @param string $lastname
   * @param string $city
   * @param dateTime $from
   * @param dateTime $to
   * @param string $postcode
   * @param string $countryCode
   * @param string $clientReferenceNumber
   * @param string $ebayNumber
   * @param array $status
   * @access public
   */
  public function __construct($identNo, $orderNo, $lastname, $city, $from, $to, $postcode, $countryCode, $clientReferenceNumber, $ebayNumber, $status)
  {
    $this->identNo = $identNo;
    $this->orderNo = $orderNo;
    $this->lastname = $lastname;
    $this->city = $city;
    $this->from = $from;
    $this->to = $to;
    $this->postcode = $postcode;
    $this->countryCode = $countryCode;
    $this->clientReferenceNumber = $clientReferenceNumber;
    $this->ebayNumber = $ebayNumber;
    $this->status = $status;
  }

}
