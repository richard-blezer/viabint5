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



class ListOfProducts
{

  /**
   * 
   * @var int $numberOfProducts
   * @access public
   */
  public $numberOfProducts;

  /**
   * 
   * @var array $products
   * @access public
   */
  public $products;

  /**
   * 
   * @var dateTime $dated
   * @access public
   */
  public $dated;

  /**
   * 
   * @var string $labelAcceptanceTermsAndConditions
   * @access public
   */
  public $labelAcceptanceTermsAndConditions;

  /**
   * 
   * @var string $labelAcceptanceLiabilityLimit
   * @access public
   */
  public $labelAcceptanceLiabilityLimit;

  /**
   * 
   * @var string $urlTermsAndConditions
   * @access public
   */
  public $urlTermsAndConditions;

  /**
   * 
   * @var int $netPriceCashOnDeliveryEurocent
   * @access public
   */
  public $netPriceCashOnDeliveryEurocent;

  /**
   * 
   * @var string $settlementType
   * @access public
   */
  public $settlementType;

  /**
   * 
   * @var string $urlHermesLogogram
   * @access public
   */
  public $urlHermesLogogram;

  /**
   * 
   * @var string $urlLiabilityInformations
   * @access public
   */
  public $urlLiabilityInformations;

  /**
   * 
   * @var string $urlPackagingGuidelines
   * @access public
   */
  public $urlPackagingGuidelines;

  /**
   * 
   * @var string $urlPortalB2C
   * @access public
   */
  public $urlPortalB2C;

  /**
   * 
   * @var string $vatInfo
   * @access public
   */
  public $vatInfo;

  /**
   * 
   * @var array $serviceChargeList
   * @access public
   */
  public $serviceChargeList;

  /**
   * 
   * @param int $numberOfProducts
   * @param array $products
   * @param dateTime $dated
   * @param string $labelAcceptanceTermsAndConditions
   * @param string $labelAcceptanceLiabilityLimit
   * @param string $urlTermsAndConditions
   * @param int $netPriceCashOnDeliveryEurocent
   * @param string $settlementType
   * @param string $urlHermesLogogram
   * @param string $urlLiabilityInformations
   * @param string $urlPackagingGuidelines
   * @param string $urlPortalB2C
   * @param string $vatInfo
   * @param array $serviceChargeList
   * @access public
   */
  public function __construct($numberOfProducts, $products, $dated, $labelAcceptanceTermsAndConditions, $labelAcceptanceLiabilityLimit, $urlTermsAndConditions, $netPriceCashOnDeliveryEurocent, $settlementType, $urlHermesLogogram, $urlLiabilityInformations, $urlPackagingGuidelines, $urlPortalB2C, $vatInfo, $serviceChargeList)
  {
    $this->numberOfProducts = $numberOfProducts;
    $this->products = $products;
    $this->dated = $dated;
    $this->labelAcceptanceTermsAndConditions = $labelAcceptanceTermsAndConditions;
    $this->labelAcceptanceLiabilityLimit = $labelAcceptanceLiabilityLimit;
    $this->urlTermsAndConditions = $urlTermsAndConditions;
    $this->netPriceCashOnDeliveryEurocent = $netPriceCashOnDeliveryEurocent;
    $this->settlementType = $settlementType;
    $this->urlHermesLogogram = $urlHermesLogogram;
    $this->urlLiabilityInformations = $urlLiabilityInformations;
    $this->urlPackagingGuidelines = $urlPackagingGuidelines;
    $this->urlPortalB2C = $urlPortalB2C;
    $this->vatInfo = $vatInfo;
    $this->serviceChargeList = $serviceChargeList;
  }

}
