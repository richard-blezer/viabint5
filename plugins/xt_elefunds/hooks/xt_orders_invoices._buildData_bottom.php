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

global $bd;
$rc = $db->Execute("Select * FROM ".DB_PREFIX . "_elefunds_transactions WHERE orders_id = '".$orderData["order_data"]["orders_id"]."'");
if ($rc->RecordCount()>0){
    $facade = xt_elefunds::getElefundsObject();
    $data['invoice']['invoice_comment'] .= "\n" . $facade->getReceiptDisclaimer() . "\n";   
}
