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



class PropsOrder
{

  /**
   * 
   * @var string $orderNo
   * @access public
   */
  public $orderNo;

  /**
   * 
   * @var HermesAddress $receiver
   * @access public
   */
  public $receiver;

  /**
   * 
   * @var string $clientReferenceNumber
   * @access public
   */
  public $clientReferenceNumber;

  /**
   * 
   * @var string $parcelClass
   * @access public
   */
  public $parcelClass;

  /**
   * 
   * @var int $amountCashOnDeliveryEurocent
   * @access public
   */
  public $amountCashOnDeliveryEurocent;

  /**
   * 
   * @var boolean $includeCashOnDelivery
   * @access public
   */
  public $includeCashOnDelivery;

  /**
   * 
   * @var boolean $withBulkGoods
   * @access public
   */
  public $withBulkGoods;

  /**
   * 
   * @param string $orderNo
   * @param HermesAddress $receiver
   * @param string $clientReferenceNumber
   * @param string $parcelClass
   * @param int $amountCashOnDeliveryEurocent
   * @param boolean $includeCashOnDelivery
   * @param boolean $withBulkGoods
   * @access public
   */
  public function __construct($orderNo, $receiver, $clientReferenceNumber, $parcelClass, $amountCashOnDeliveryEurocent, $includeCashOnDelivery, $withBulkGoods)
  {
    $this->orderNo = $orderNo;
    $this->receiver = $receiver;
    $this->clientReferenceNumber = $clientReferenceNumber;
    $this->parcelClass = $parcelClass;
    $this->amountCashOnDeliveryEurocent = $amountCashOnDeliveryEurocent;
    $this->includeCashOnDelivery = $includeCashOnDelivery;
    $this->withBulkGoods = $withBulkGoods;
  }

}
