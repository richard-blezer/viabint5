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

$db->Execute("DELETE FROM `".TABLE_ADMIN_NAVIGATION."` WHERE `text` = 'xt_reviews'");
$db->Execute("UPDATE `".TABLE_PRODUCTS."` SET `products_average_rating` = 0, `products_rating_count` = 0");
$db->Execute("DROP TABLE IF EXISTS `".DB_PREFIX."_products_reviews_permission`;");
$db->Execute("DROP TABLE IF EXISTS `".DB_PREFIX."_products_reviews`;");
$db->Execute("DELETE FROM `".DB_PREFIX."_mail_templates_content` WHERE `tpl_id` = (SELECT tpl_id FROM `".DB_PREFIX."_mail_templates` WHERE tpl_type='review-notification-mail')");
$db->Execute("DELETE FROM `".DB_PREFIX."_mail_templates` WHERE `tpl_type` = 'review-notification-mail'");



