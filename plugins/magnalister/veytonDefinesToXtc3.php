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
 * $Id: init.php 37 2012-04-22 20:04:10Z derpapst $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/* DEFINES */

define('DEFAULT_CURRENCY', _STORE_CURRENCY);

/* Server defines */
$__SYSTEM_BASE_HTTP = defined('_SYSTEM_BASE_HTTP') ? _SYSTEM_BASE_HTTP : '';
define('HTTP_SERVER', empty($__SYSTEM_BASE_HTTP) ? _SYSTEM_BASE_HTTPS : _SYSTEM_BASE_HTTP);
define('HTTPS_SERVER', _SYSTEM_BASE_HTTPS);
define('ENABLE_SSL', _SYSTEM_SSL);
define('HTTP_CATALOG_SERVER', _SYSTEM_BASE_HTTP);
define('HTTPS_CATALOG_SERVER', _SYSTEM_BASE_HTTPS);
unset($__SYSTEM_BASE_HTTP);

$web_dir = _SRV_WEB;
if (strstr($web_dir, _SRV_WEB_PLUGINS.MAGNA_PLUGIN_DIR)) {
	$web_dir = str_replace(_SRV_WEB_PLUGINS.MAGNA_PLUGIN_DIR, '', $web_dir);
}
define('SHOP_URL_POPUP_IMAGES', _SYSTEM_BASE_HTTP.$web_dir._SRV_WEB_IMAGES._DIR_INFO);

define('_SRV_SERVER_ROOT', $web_dir);

/*  */
define('DIR_WS_INCLUDES', 'includes/'); //for includingpath application_bottom.php
define('DIR_WS_CATALOG', $web_dir); 	//dont know what this stand for  but.... 
define('DIR_WS_CLASSES', '');			//important for black magic 

/*  */
define('DIR_MAGNA_MODULES', '');	//simon todo  (only ebay needs this)
define('DIR_MAGNA_LANGUAGES','');	//simon todo  (only ebay needs this)

/*  */
define('DIR_FS_DOCUMENT_ROOT', 	(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'].'/' : '/'));
define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT);

/*  */
defined('_DIR_POPUP') OR define('_DIR_POPUP', 'popup/');
define('SHOP_URL', HTTP_SERVER);
define('SHOP_FS_PRODUCT_IMAGES',  _SRV_WEBROOT._SRV_WEB_IMAGES._DIR_ORG);
define('SHOP_FS_CATEGORY_IMAGES', _SRV_WEBROOT._SRV_WEB_IMAGES._DIR_ORG);
define('SHOP_FS_POPUP_IMAGES', _SRV_WEBROOT._SRV_WEB_IMAGES._DIR_POPUP);

/* TBD */
define('SHOP_FS_MANUFACTURES_IMAGES', '');

define('DIR_WS_IMAGES', 'media/images/thumb/');
define('DIR_WS_CATALOG_IMAGES', DIR_WS_CATALOG.'/media/images/thumb/');

define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG.'media/images/thumb/');
define('DIR_FS_CATALOG_ORIGINAL_IMAGES', DIR_FS_CATALOG_IMAGES);
define('DIR_FS_CATALOG_POPUP_IMAGES', DIR_FS_CATALOG.'media/images/popup/');
define('DIR_WS_CATALOG_POPUP_IMAGES','/media/images/popup/');
/// media/images/org
/*
		if (file_exists(SHOP_FS_PRODUCT_IMAGES.$fName)) {
			$imagePath = SHOP_FS_PRODUCT_IMAGES;
			$cachePath = DIR_MAGNALISTER_IMAGECACHE.'product_';
		} else if (file_exists(SHOP_FS_CATEGORY_IMAGES.$fName)) {
			$imagePath = SHOP_FS_CATEGORY_IMAGES;
			$cachePath = DIR_MAGNALISTER_IMAGECACHE.'category_';
		}
*/

/* ACHTUNG: Klasse php/lib/MagnaDB stellt selbst verbindung her. Optimalerweise sollte wrapper von veyton genutzt werden! */
define('DB_SERVER', _SYSTEM_DATABASE_HOST);
define('DB_SERVER_USERNAME', _SYSTEM_DATABASE_USER);
define('DB_SERVER_PASSWORD', _SYSTEM_DATABASE_PWD);
define('DB_DATABASE', _SYSTEM_DATABASE_DATABASE);

function mlVeytonLangIdToCode($id) {
	if (!isset($_SESSION['magnalister']['caches']['langIdToCode'])) {
		$_SESSION['magnalister']['caches']['langIdToCode'] = array();
	}
	if (!isset($_SESSION['magnalister']['caches']['langIdToCode'][$id])) {
		$_SESSION['magnalister']['caches']['langIdToCode'][$id] = MagnaDB::gi()->fetchOne('
			SELECT code FROM '.TABLE_LANGUAGES.' WHERE languages_id = "'.((int)$id).'"
		');
	}
	return $_SESSION['magnalister']['caches']['langIdToCode'][$id];
}
