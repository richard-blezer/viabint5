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


$rowActions[] = array('iconCls' => 'xt_banktransfer_accounts', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_XT_BANKTRANSFER_ACCOUNTS);
if ($this->url_data['edit_id'])
    $js = "var edit_id = ".$this->url_data['edit_id']."; var edit_name = '".htmlentities($products_model)."';";
else
    $js = "var edit_id = record.id; var edit_name=record.get('products_model');";
$js .= "addTab('adminHandler.php?load_section=xt_banktransfer_accounts&plugin=xt_banktransfer&pg=overview&customers_id='+edit_id,'".TEXT_XT_BANKTRANSFER_ACCOUNTS."', 'product_serials'+edit_id)";

$rowActionsFunctions['xt_banktransfer_accounts'] = $js;

?>