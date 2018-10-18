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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$header['products_master_model'] = array(
    'type' => 'dropdown', // you can modyfy the auto type
    'url' => 'DropdownData.php?get=products_model&plugin_code=xt_master_slave');

$header['products_model_old'] = array('type' => 'hidden');
$header['products_option_master_price'] = array('type' => 'dropdown', 'url' => 'DropdownData.php?get=master_price_view&plugin_code=xt_master_slave');

$header['products_image_from_master'] = array('type' => 'status');
$header['products_master_slave_order'] = array('type' => 'text','width'=>100);

$groupingMaster_slave = 'master_slave';
$grouping['products_master_model'] = array('position' => $groupingMaster_slave);
$grouping['products_master_flag'] = array('position' => $groupingMaster_slave);
$grouping['products_option_master_price'] = array('position' => $groupingMaster_slave);
$grouping['products_image_from_master'] = array('position' => $groupingMaster_slave);
$grouping['products_master_slave_order'] = array('position' => $groupingMaster_slave);
?>