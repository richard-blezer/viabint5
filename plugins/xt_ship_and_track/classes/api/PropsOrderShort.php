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



class PropsOrderShort
{

  /**
   * 
   * @var string $orderNo
   * @access public
   */
  public $orderNo;

  /**
   * 
   * @var string $shippingId
   * @access public
   */
  public $shippingId;

  /**
   * 
   * @var dateTime $creationDate
   * @access public
   */
  public $creationDate;

  /**
   * 
   * @var string $parcelClass
   * @access public
   */
  public $parcelClass;

  /**
   * 
   * @var string $status_text
   * @access public
   */
  public $status_text;

  /**
   * 
   * @var int $status
   * @access public
   */
  public $status;

  /**
   * 
   * @var string $firstname
   * @access public
   */
  public $firstname;

  /**
   * 
   * @var string $lastname
   * @access public
   */
  public $lastname;

  /**
   * 
   * @var string $postcode
   * @access public
   */
  public $postcode;

  /**
   * 
   * @var string $city
   * @access public
   */
  public $city;

  /**
   * 
   * @var string $countryCode
   * @access public
   */
  public $countryCode;

  /**
   * 
   * @var int $bulkGoodsServiceAmount
   * @access public
   */
  public $bulkGoodsServiceAmount;

  /**
   * 
   * @param string $orderNo
   * @param string $shippingId
   * @param dateTime $creationDate
   * @param string $parcelClass
   * @param string $status_text
   * @param int $status
   * @param string $firstname
   * @param string $lastname
   * @param string $postcode
   * @param string $city
   * @param string $countryCode
   * @param int $bulkGoodsServiceAmount
   * @access public
   */
  public function __construct($orderNo, $shippingId, $creationDate, $parcelClass, $status_text, $status, $firstname, $lastname, $postcode, $city, $countryCode, $bulkGoodsServiceAmount)
  {
    $this->orderNo = $orderNo;
    $this->shippingId = $shippingId;
    $this->creationDate = $creationDate;
    $this->parcelClass = $parcelClass;
    $this->status_text = $status_text;
    $this->status = $status;
    $this->firstname = $firstname;
    $this->lastname = $lastname;
    $this->postcode = $postcode;
    $this->city = $city;
    $this->countryCode = $countryCode;
    $this->bulkGoodsServiceAmount = $bulkGoodsServiceAmount;
  }

}
