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



class ServiceCharge
{

  /**
   * 
   * @var string $displayname
   * @access public
   */
  public $displayname;

  /**
   * 
   * @var int $amountEurocent
   * @access public
   */
  public $amountEurocent;

  /**
   * 
   * @var string $currency
   * @access public
   */
  public $currency;

  /**
   * 
   * @param string $displayname
   * @param int $amountEurocent
   * @param string $currency
   * @access public
   */
  public function __construct($displayname, $amountEurocent, $currency)
  {
    $this->displayname = $displayname;
    $this->amountEurocent = $amountEurocent;
    $this->currency = $currency;
  }

}
