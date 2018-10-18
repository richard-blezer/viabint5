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



class propsOrderSaveAndPrintLabelPdfResponse
{

  /**
   * 
   * @var ShippingLabelPdf $propsOrderSaveAndPrintLabelPdfReturn
   * @access public
   */
  public $propsOrderSaveAndPrintLabelPdfReturn;

  /**
   * 
   * @param ShippingLabelPdf $propsOrderSaveAndPrintLabelPdfReturn
   * @access public
   */
  public function __construct($propsOrderSaveAndPrintLabelPdfReturn)
  {
    $this->propsOrderSaveAndPrintLabelPdfReturn = $propsOrderSaveAndPrintLabelPdfReturn;
  }

}
