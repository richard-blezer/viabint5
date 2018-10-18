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
 # @version $Id: class.product.php__getParams_row_actions.php 6578 2013-11-15 17:15:39Z silviyap $
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

$rowActions[] = array('iconCls' => 'products_to_attributes', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_PRODUCTS_TO_ATTRIBUTES);
if ($this->url_data['edit_id'])
    $js = "var edit_id = " . $this->url_data['edit_id'] . ";";
else
    $js = "var edit_id = record.id;";

$extF = new ExtFunctions();
$js .= $extF->_RemoteWindow("TEXT_PRODUCTS_TO_ATTRIBUTES", "TEXT_PRODUCTS", "adminHandler.php?plugin=xt_master_slave&load_section=product_to_attributes&pg=getTreePanel&products_id='+edit_id+'", '', array(), 800, 600) . ' new_window.show();';

$rowActionsFunctions['products_to_attributes'] = $js;

	
?>