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
 # @version $Id: cart_tpl_form.php 4896 2011-11-18 10:28:20Z mzanier $
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

if (isset($xtPlugin->active_modules['xt_master_slave']) && $request['get'] === 'xt_reviews_master_slave')
{
	$result = array(
		array('id' => 'default', 'name' => 'Default', 'desc' => 'Reviews displayed where they are written'),
		array('id' => 'master_only', 'name' => 'Master only', 'desc' => 'All reviews are displayed in master only'),
		array('id' => 'slave_only', 'name' => 'Slave only', 'desc' => 'Only slave products display reviews'),
		array('id' => 'master_all', 'name' => 'Master shows all (+slaves)', 'desc' => 'Master products display all reviews, while slaves only display their own')
	);
}