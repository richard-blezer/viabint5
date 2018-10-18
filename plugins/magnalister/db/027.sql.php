<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();


# Hitmeister-Modul
$queries[] = "
CREATE TABLE IF NOT EXISTS `".TABLE_MAGNA_HITMEISTER_PREPARE."` (
  `mpID` int(11) NOT NULL,
  `products_id` int(11) NOT NULL,
  `products_model` varchar(255) NOT NULL,
  `mp_category_id` varchar(9) NOT NULL DEFAULT '0',
  `mp_category_name` varchar(127) DEFAULT '',
  `condition_id` int(4) NOT NULL DEFAULT '100',
  `shippingtime` char(1) NOT NULL DEFAULT 'b',
  `is_porn` int(1) NOT NULL DEFAULT '0',
  `age_rating` int(3) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `PreparedTS` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `UniqueEntry` (`mpID`,`products_id`,`products_model`),
  KEY `mpID` (`mpID`),
  KEY `products_id` (`products_id`),
  KEY `products_model` (`products_model`)
) ENGINE=MyISAM;
";
