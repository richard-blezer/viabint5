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



class DeliveryDestination
{

  /**
   * 
   * @var string $exclusions
   * @access public
   */
  public $exclusions;

  /**
   * 
   * @var string $countryCode
   * @access public
   */
  public $countryCode;

  /**
   * 
     * @var float $weightMinKg
     * @access public
     */
    public $weightMinKg;

    /**
     *
     * @var float $weigthMaxKg
     * @access public
     */
    public $weigthMaxKg;

  /**
   * 
   * @param string $exclusions
   * @param string $countryCode
   * @access public
   */
  public function __construct($exclusions, $countryCode, $weightMinKg, $weigthMaxKg)
  {
    $this->exclusions = $exclusions;
    $this->countryCode = $countryCode;
    $this->weightMinKg = $weightMinKg;
    $this->weigthMaxKg = $weigthMaxKg;
  }

}
