<?php
/*
 #########################################################################
#                       xt:Commerce VEYTON 4.0 Shopsoftware
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
# This file may not be redistributed in whole or significant part.
# Content of this file is Protected By International Copyright Laws.
#
# ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
#
# http://www.xt-commerce.com
#
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#
# @version $Id: class.products_search.php 4611 2011-03-30 16:39:15Z mzanier $
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

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_elefunds/classes/class.xt_elefunds.php';
$dummy = xt_elefunds::getElefundsObject()->createDonation();

$donation = xt_elefunds::getOrderDonation($orders_id);
if (!empty($donation)) {
	$donationObject = unserialize(base64_decode($donation['donation']));
	$donation_total_line = array();
	if (false !== $donationObject) {
		$donation_total_line['orders_id'] = $orders_id;
		$donation_total_line['orders_total_key'] = 'xt_elefunds_donation';
		$donation_total_line['orders_total_key_id'] = 1;
		$donation_total_line['orders_total_model'] = TEXT_XT_ELEFUNDS_DONATION;
		$donation_total_line['orders_total_name'] = TEXT_XT_ELEFUNDS_DONATION;
		$donation_total_line['orders_total_price'] = ($donationObject->getAmount() / 100);
		$donation_total_line['orders_total_tax'] = null;
		$donation_total_line['orders_total_tax_class'] = null;
		$donation_total_line['orders_total_quantity'] = 1;
		$donation_total_line['allow_tax'] = null;
		
		$total_array[] = $donation_total_line;
	}
}