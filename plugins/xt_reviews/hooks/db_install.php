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

$db->Execute("INSERT INTO ".TABLE_ADMIN_NAVIGATION." (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_reviews', 'images/icons/user_comment.png', '&plugin=xt_reviews', 'adminHandler.php', '4000', 'shop', 'I', 'W');");
$db->Execute("UPDATE ".DB_PREFIX."_products SET products_average_rating = 0, products_rating_count=0");

$sql = 'CREATE TABLE IF NOT EXISTS `'.DB_PREFIX."_products_reviews` (
  `review_id` int(11) NOT NULL auto_increment,
  `products_id` int(11) NOT NULL,
  `customers_id` int(11) NOT NULL,
  `orders_id` int(11) NOT NULL default '0',
  `review_rating` int(1) NOT NULL,
  `review_date` datetime NOT NULL,
  `review_status` int(1) NOT NULL default '0',
  `language_code` char(2) NOT NULL,
  `review_text` text,
  `review_title` text,
  `review_source` varchar(64) default NULL,
  `admin_comment` text default NULL,
  PRIMARY KEY  (`review_id`),
  KEY `products_id_review_status` (`products_id`,`review_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$db->Execute($sql);

$sql = 'CREATE TABLE IF NOT EXISTS `'.DB_PREFIX."_products_reviews_permission` (
  `pid` int(11) NOT NULL,
  `permission` tinyint(1) DEFAULT '0',
  `pgroup` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`,`pgroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$db->Execute($sql);
$db->Execute("
   INSERT INTO `".DB_PREFIX."_mail_templates` (`tpl_type`, `tpl_special`, `email_from`, `email_from_name`, `email_reply`, `email_reply_name`, `email_forward`) 
    VALUES('review-notification-mail', '0', '"._CORE_DEBUG_MAIL_ADDRESS."', '', '"._CORE_DEBUG_MAIL_ADDRESS."', '', '');
"); 

$db->Execute("
    INSERT INTO `".DB_PREFIX."_mail_templates_content` (`tpl_id`, `language_code`, `mail_body_html`, `mail_body_txt`, `mail_subject`) 
    VALUES((SELECT tpl_id FROM `".DB_PREFIX."_mail_templates` WHERE tpl_type='review-notification-mail'), 'de', '<p>Eine neue Bewertung wartet auf Freischaltung</p>\r\n <p>{txt key=TEXT_ORDER_NUMBER}: {\$orders_id} </p> \r\n <p>{txt key=TEXT_REVIEW_DATE}: {\$review_date} </p>\r\n<p>{txt key=TEXT_REVIEW_STATUS}: {\$review_status} </p>\r\n<p>{txt key=TEXT_REVIEW_LANGUAGE_CODE}: {\$language_code} </p>\r\n<p>{txt key=TEXT_REVIEW_TEXT}: {\$review_text} </p>\r\n<p>{txt key=TEXT_REVIEW_RATING}:  {\$review_rating}</p>\r\n','<p>Eine neue Bewertung wartet auf Freischaltung</p>\r\n <p>{txt key=TEXT_ORDER_NUMBER}: {\$orders_id} </p> \r\n <p>{txt key=TEXT_REVIEW_DATE}: {\$review_date} </p>\r\n<p>{txt key=TEXT_REVIEW_STATUS}: {\$review_status} </p>\r\n<p>{txt key=TEXT_REVIEW_LANGUAGE_CODE}: {\$language_code} </p>\r\n<p>{txt key=TEXT_REVIEW_TEXT}: {\$review_text} </p>\r\n<p>{txt key=TEXT_REVIEW_RATING}:  {\$review_rating}</p>\r\n', 'Neue Produktbewertung');
");
$db->Execute("
    INSERT INTO `".DB_PREFIX."_mail_templates_content` (`tpl_id`, `language_code`, `mail_body_html`, `mail_body_txt`, `mail_subject`) 
    VALUES((SELECT tpl_id FROM `".DB_PREFIX."_mail_templates` WHERE tpl_type='review-notification-mail'), 'en', '<p>A client submitted a new review</p>\r\n <p>{txt key=TEXT_ORDER_NUMBER}: {\$orders_id} </p> \r\n <p>{txt key=TEXT_REVIEW_DATE}: {\$review_date} </p>\r\n<p>{txt key=TEXT_REVIEW_STATUS}: {\$review_status} </p>\r\n<p>{txt key=TEXT_REVIEW_LANGUAGE_CODE}: {\$language_code} </p>\r\n<p>{txt key=TEXT_REVIEW_TEXT}: {\$review_text} </p>\r\n<p>{txt key=TEXT_REVIEW_RATING}:  {\$review_rating}</p>\r\n','<p>Eine neue Bewertung wartet auf Freischaltung</p>\r\n <p>{txt key=TEXT_ORDER_NUMBER}: {\$orders_id} </p> \r\n <p>{txt key=TEXT_REVIEW_DATE}: {\$review_date} </p>\r\n<p>{txt key=TEXT_REVIEW_STATUS}: {\$review_status} </p>\r\n<p>{txt key=TEXT_REVIEW_LANGUAGE_CODE}: {\$language_code} </p>\r\n<p>{txt key=TEXT_REVIEW_TEXT}: {\$review_text} </p>\r\n<p>{txt key=TEXT_REVIEW_RATING}:  {\$review_rating}</p>\r\n', 'New product review');
");
