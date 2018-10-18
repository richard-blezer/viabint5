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


class ShippingStatus
{

  /**
   * 
   * @var string $shippingId
   * @access public
   */
  public $shippingId;

  /**
   * 
   * @var string $statusText
   * @access public
   */
  public $statusText;

  /**
   * 
   * @var dateTime $statusDateTime
   * @access public
   */
  public $statusDateTime;

  /**
   * 
   * @param string $shippingId
   * @param string $statusText
   * @param dateTime $statusDateTime
   * @access public
   */
  public function __construct($shippingId, $statusText, $statusDateTime)
  {
    $this->shippingId = $shippingId;
    $this->statusText = $statusText;
    $this->statusDateTime = $statusDateTime;
  }

}
