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



class ParcelFormat
{

  /**
   * 
   * @var string $parcelClass
   * @access public
   */
  public $parcelClass;

  /**
   * 
   * @var int $shortestPlusLongestEdgeCmMax
   * @access public
   */
  public $shortestPlusLongestEdgeCmMax;

  /**
   * 
   * @var int $shortestPlusLongestEdgeCmMin
   * @access public
   */
  public $shortestPlusLongestEdgeCmMin;

  /**
   * 
   * @var int $thridEdgeCmMax
   * @access public
   */
  public $thridEdgeCmMax;



  /**
   * 
   * @param string $parcelClass
   * @param int $shortestPlusLongestEdgeCmMax
   * @param int $shortestPlusLongestEdgeCmMin
   * @param int $thridEdgeCmMax
   * @param float $weightMinKg
   * @param float $weigthMaxKg
   * @access public
   */
  public function __construct($parcelClass, $shortestPlusLongestEdgeCmMax, $shortestPlusLongestEdgeCmMin, $thridEdgeCmMax)
  {
    $this->parcelClass = $parcelClass;
    $this->shortestPlusLongestEdgeCmMax = $shortestPlusLongestEdgeCmMax;
    $this->shortestPlusLongestEdgeCmMin = $shortestPlusLongestEdgeCmMin;
    $this->thridEdgeCmMax = $thridEdgeCmMax;
  }

}
