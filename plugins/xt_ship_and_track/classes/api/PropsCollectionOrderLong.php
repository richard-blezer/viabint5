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



class PropsCollectionOrderLong
{

  /**
   * 
   * @var string $moreThan2ccm
   * @access public
   */
  public $moreThan2ccm;

  /**
   * 
   * @var float $volume
   * @access public
   */
  public $volume;

  /**
   * 
   * @var HermesAddress $collectionAdress
   * @access public
   */
  public $collectionAdress;

  /**
   * 
   * @var string $timeframe
   * @access public
   */
  public $timeframe;

  /**
   * 
   * @var string $collectionType
   * @access public
   */
  public $collectionType;

  /**
   * 
   * @var int $numberOfParcels
   * @access public
   */
  public $numberOfParcels;

  /**
   * 
   * @var dateTime $collectionDate
   * @access public
   */
  public $collectionDate;

  /**
   * 
   * @param string $moreThan2ccm
   * @param float $volume
   * @param HermesAddress $collectionAdress
   * @param string $timeframe
   * @param string $collectionType
   * @param int $numberOfParcels
   * @param dateTime $collectionDate
   * @access public
   */
  public function __construct($moreThan2ccm, $volume, $collectionAdress, $timeframe, $collectionType, $numberOfParcels, $collectionDate)
  {
    $this->moreThan2ccm = $moreThan2ccm;
    $this->volume = $volume;
    $this->collectionAdress = $collectionAdress;
    $this->timeframe = $timeframe;
    $this->collectionType = $collectionType;
    $this->numberOfParcels = $numberOfParcels;
    $this->collectionDate = $collectionDate;
  }

}
