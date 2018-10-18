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
 # @version $Id: class.getProductSQL_query.php_F_Listing.php 6582 2013-11-22 17:44:11Z silviyap $
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

$check_pos = strstr($this->position, 'plugin_ms');$check_pos_s = strstr($this->position, 'getSearchData');
if (XT_MASTER_SLAVE_ACTIVE == 'true')
{
//
if (!$check_pos && !$check_pos_s && USER_POSITION != 'admin')
{
   $this->setSQL_COLS(', p.products_model AS model ');
   
   $this->setSQL_COLS(", CASE
							WHEN p.products_master_flag=1 THEN (SELECT SUM(ps.products_ordered)FROM ".TABLE_PRODUCTS." ps WHERE ps.products_master_model=model) 
							ELSE p.products_ordered
						END AS sort_ordered ");
						
}
}
?>