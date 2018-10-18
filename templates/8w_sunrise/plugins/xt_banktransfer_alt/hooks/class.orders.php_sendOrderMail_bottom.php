<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
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
defined('_VALID_CALL') or die('Direct Access is not allowed.');

// check if xt_banktransfer payment method
if ($this->order_data['payment_code']=='xt_banktransfer' && XT_BANKTRANSFER_SEND_MANDATE=='true') {
    include_once _SRV_WEBROOT._SRV_WEB_PLUGINS.'xt_banktransfer/classes/class.xt_banktransfer.php';
    $xt_banktransfer = new xt_banktransfer;
    $pdf_mandat=$xt_banktransfer->getMandate($this->order_data['orders_id']);
    if ($pdf_mandat!=false) $ordermail->AddStringAttachment($pdf_mandat, 'sepa_mandat_' . $this->order_data['orders_id'] . '.pdf');
}
?>