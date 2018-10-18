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
 * $Id
 *
 * (c) 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_HOOD_CATEGORIES.'` (
		`CategoryID` bigint(11) NOT NULL DEFAULT \'0\',
		`CategoryName` varchar(128) NOT NULL,
		`ParentID` bigint(11) NOT NULL DEFAULT \'0\',
		`LeafCategory` enum(\'0\',\'1\') NOT NULL DEFAULT \'1\',
		`StoreCategory` enum(\'0\',\'1\') NOT NULL DEFAULT \'0\',
		`InsertTimestamp` int(11) NOT NULL DEFAULT \'0\',
		`Fee` float NOT NULL,
		`FeeCurrency` varchar(5) NOT NULL,
		`Mode` varchar(20) DEFAULT NULL,
		PRIMARY KEY (`CategoryID`,`StoreCategory`)
	);
';

$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_HOOD_PROPERTIES.'` (
	  `mpID` int(8) unsigned NOT NULL DEFAULT \'0\',
	  `products_id` int(11) NOT NULL,
	  `products_model` varchar(64) NOT NULL,
	  `ItemID` varchar(12) DEFAULT NULL,
	  `PreparedTS` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
	  `StartTime` datetime DEFAULT NULL,
	  `Title` varchar(85) NOT NULL DEFAULT \'\',
	  `Subtitle` varchar(100) NOT NULL DEFAULT \'\',
	  `ShortDescription` text NOT NULL,
	  `Description` longtext NOT NULL,
	  `GalleryPictures` text NOT NULL,
	  `ConditionType` enum(\'new\',\'used\') NOT NULL,
	  `StartPrice` decimal(15,4) NOT NULL DEFAULT \'0.0000\',
	  `PrimaryCategory` int(10) NOT NULL,
	  `PrimaryCategoryName` varchar(128) NOT NULL,
	  `SecondaryCategory` int(10) NOT NULL,
	  `SecondaryCategoryName` varchar(128) NOT NULL,
	  `StoreCategory` bigint(11) DEFAULT \'0\',
	  `ListingType` enum(\'classic\',\'buyItNow\',\'shopProduct\') NOT NULL DEFAULT \'buyItNow\',
	  `ListingDuration` varchar(10) DEFAULT \'1\',
	  `noIdentifierFlag` enum(\'0\',\'1\',\'2\') NOT NULL DEFAULT \'0\',
	  `BestOfferEnabled` enum(\'0\',\'1\') DEFAULT \'0\',
	  `PaymentMethods` longtext NOT NULL,
	  `ShippingServiceOptions` longtext NOT NULL,
	  `Features` text NOT NULL,
	  `FSK` tinyint(4) NOT NULL DEFAULT \'-1\',
	  `USK` tinyint(4) NOT NULL DEFAULT \'-1\',
	  `Verified` enum(\'OK\',\'ERROR\',\'OPEN\',\'EMPTY\') NOT NULL DEFAULT \'OPEN\',
	  `Transferred` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
	  `deletedBy` enum(\'\',\'empty\',\'Sync\',\'Button\',\'expired\',\'notML\') NOT NULL,
	  `topPrimaryCategory` varchar(64) NOT NULL,
	  `topSecondaryCategory` varchar(64) NOT NULL,
	  `StoreCategory2` bigint(11) DEFAULT NULL,
	  `StoreCategory3` bigint(20) NOT NULL DEFAULT \'0\',
	  `Manufacturer` varchar(200) DEFAULT NULL,
	  `ManufacturerPartNumber` varchar(200) NOT NULL DEFAULT \'\',
	  UNIQUE KEY `mpID` (`mpID`,`products_id`,`products_model`)
	);
';