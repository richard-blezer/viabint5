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

if(DB_PREFIX!=''){
 $DB_PREFIX = DB_PREFIX . '_';
}else{
 define('DB_PREFIX','xt');
 $DB_PREFIX = DB_PREFIX . '_';
}

// Tables Cleaned
define('TABLE_CUSTOMERS_ADDRESSES', $DB_PREFIX.'customers_addresses');
define('TABLE_CATEGORIES', $DB_PREFIX.'categories');
define('TABLE_CATEGORIES_DESCRIPTION', $DB_PREFIX.'categories_description');
define('TABLE_CONFIGURATION', $DB_PREFIX.'config');
define('TABLE_CONFIGURATION_GROUP', $DB_PREFIX.'config_group');
define('TABLE_COUNTRIES', $DB_PREFIX.'countries');
define('TABLE_COUNTRIES_DESCRIPTION', $DB_PREFIX.'countries_description');
define('TABLE_CURRENCIES', $DB_PREFIX.'currencies');
define('TABLE_CUSTOMERS', $DB_PREFIX.'customers');
define('TABLE_CUSTOMERS_STATUS', $DB_PREFIX.'customers_status');
define('TABLE_CUSTOMERS_STATUS_DESCRIPTION', $DB_PREFIX.'customers_status_description');
define('TABLE_CAMPAIGNS', $DB_PREFIX.'campaigns');
define('TABLE_CUSTOMERS_BASKET', $DB_PREFIX.'customers_basket');
define('TABLE_FEED', $DB_PREFIX.'feed');
define('TABLE_LANGUAGES', $DB_PREFIX.'languages');
define('TABLE_LANGUAGE_CONTENT', $DB_PREFIX.'language_content');
define('TABLE_MANDANT_CONFIG', $DB_PREFIX.'stores');
define('TABLE_MANUFACTURERS', $DB_PREFIX.'manufacturers');
define('TABLE_MANUFACTURERS_DESCRIPTION', $DB_PREFIX.'manufacturers_info');
define('TABLE_ORDERS', $DB_PREFIX.'orders');
define('TABLE_ORDERS_PRODUCTS', $DB_PREFIX.'orders_products');
define('TABLE_ORDERS_STATUS_HISTORY', $DB_PREFIX.'orders_status_history');
define('TABLE_ORDERS_TOTAL', $DB_PREFIX.'orders_total');
define('TABLE_ORDERS_STATS', $DB_PREFIX.'orders_stats');
define('TABLE_ORDERS_SOURCE',$DB_PREFIX.'orders_source');
define('TABLE_PRODUCTS', $DB_PREFIX.'products');
define('TABLE_PRODUCTS_PRICE_SPECIAL',$DB_PREFIX.'products_price_special');
define('TABLE_PRODUCTS_PRICE_GROUP',$DB_PREFIX.'products_price_group_');
define('TABLE_PRODUCTS_DESCRIPTION', $DB_PREFIX.'products_description');
define('TABLE_PRODUCTS_TO_CATEGORIES', $DB_PREFIX.'products_to_categories');
define('TABLE_PRODUCTS_CROSS_SELL', $DB_PREFIX.'products_cross_sell');
define('TABLE_PRODUCTS_SERIAL',$DB_PREFIX.'products_serials');
define('TABLE_SEO_URL', $DB_PREFIX.'seo_url');
define('TABLE_SEO_STOP_WORDS',$DB_PREFIX.'seo_stop_words');
define('TABLE_CONTENT', $DB_PREFIX.'content');
define('TABLE_CONTENT_ELEMENTS', $DB_PREFIX.'content_elements');
define('TABLE_CONTENT_BLOCK', $DB_PREFIX.'content_block');
define('TABLE_CONTENT_TO_BLOCK', $DB_PREFIX.'content_to_block');
define('TABLE_SYSTEM_STATUS',$DB_PREFIX.'system_status');
define('TABLE_SYSTEM_STATUS_DESCRIPTION',$DB_PREFIX.'system_status_description');
define('TABLE_TAX_CLASS', $DB_PREFIX.'tax_class');
define('TABLE_TAX_RATES', $DB_PREFIX.'tax_rates');
define('TABLE_SESSIONS', $DB_PREFIX.'sessions2');
define('TABLE_SHIPPING_ZONES', $DB_PREFIX.'shipping_zones'); 
define('TABLE_PLUGIN_PRODUCTS', $DB_PREFIX.'plugin_products');
define('TABLE_PLUGIN_CODE', $DB_PREFIX.'plugin_code');
define('TABLE_PLUGIN_CONFIGURATION', $DB_PREFIX.'config_plugin');
define('TABLE_PLUGIN_SQL', $DB_PREFIX.'plugin_sql');
define('TABLE_CONFIGURATION_MULTI', $DB_PREFIX.'config_');
define('TABLE_MAIL_TEMPLATES', $DB_PREFIX.'mail_templates');
define('TABLE_MAIL_TEMPLATES_CONTENT', $DB_PREFIX.'mail_templates_content');
define('TABLE_MAIL_TEMPLATES_ATTACHMENT', $DB_PREFIX.'mail_templates_attachment');
define('TABLE_SHIPPING', $DB_PREFIX.'shipping');
define('TABLE_SHIPPING_DESCRIPTION', $DB_PREFIX.'shipping_description');
define('TABLE_SHIPPING_COST', $DB_PREFIX.'shipping_cost');
define('TABLE_PAYMENT', $DB_PREFIX.'payment');
define('TABLE_PAYMENT_DESCRIPTION', $DB_PREFIX.'payment_description');
define('TABLE_PAYMENT_COST', $DB_PREFIX.'payment_cost');
define('TABLE_CALLBACK_LOG',$DB_PREFIX.'callback_log');
define('TABLE_IMAGE_TYPE', $DB_PREFIX.'image_type');

define('TABLE_MEDIA', $DB_PREFIX.'media');
define('TABLE_MEDIA_DOWNLOAD_IP', $DB_PREFIX.'media_download_ip');  
define('TABLE_MEDIA_DESCRIPTION', $DB_PREFIX.'media_description');

define('TABLE_MEDIA_GALLERY', $DB_PREFIX.'media_gallery');
define('TABLE_MEDIA_GALLERY_DESCRIPTION', $DB_PREFIX.'media_gallery_description');
define('TABLE_MEDIA_TO_MEDIA_GALLERY', $DB_PREFIX.'media_to_media_gallery');
define('TABLE_MEDIA_LINK', $DB_PREFIX.'media_link');
define('TABLE_MEDIA_FILE_TYPES', $DB_PREFIX.'media_file_types');
define('TABLE_MEDIA_SYMLINK',$DB_PREFIX.'media_symlink');

define('TABLE_ADMIN_NAVIGATION', $DB_PREFIX.'acl_nav');
define('TABLE_SYSTEM_LOG',$DB_PREFIX.'system_log');

define('TABLE_ADMIN_ACL_AREA', $DB_PREFIX.'acl_area');
define('TABLE_ADMIN_ACL_AREA_PERMISSIONS', $DB_PREFIX.'acl_area_permissions');
define('TABLE_ADMIN_ACL_AREA_GROUPS', $DB_PREFIX.'acl_groups');
define('TABLE_ADMIN_ACL_AREA_USER', $DB_PREFIX.'acl_user');
define('TABLE_ADMIN_ACL_TASK', $DB_PREFIX.'acl_task');
define('TABLE_MEDIA_TO_PRODUCTS',$DB_PREFIX.'media_to_products');

define('TABLE_ORDERS_PRODUCTS_MEDIA',$DB_PREFIX.'orders_products_media');
define('TABLE_FAILED_LOGIN',$DB_PREFIX.'failed_login');

define('TABLE_CONFIGURATION_PAYMENT',$DB_PREFIX.'config_payment');

define('TABLE_CONTENT_PERMISSION',$DB_PREFIX.'content_permission');
define('TABLE_PRODUCTS_PERMISSION', $DB_PREFIX.'products_permission');
define('TABLE_CATEGORIES_PERMISSION', $DB_PREFIX.'categories_permission');
define('TABLE_MANUFACTURERS_PERMISSION', $DB_PREFIX.'manufacturers_permission');
define('TABLE_FEDERAL_STATES', $DB_PREFIX.'federal_states');
define('TABLE_FEDERAL_STATES_DESCRIPTION', $DB_PREFIX.'federal_states_description');
define('TABLE_SEARCH', $DB_PREFIX.'search');
define('TABLE_SALES_STATS', $DB_PREFIX.'sales_stats');

define('_SYSTEM_DEMO_MODE','false');
define('_SYSTEM_EXTENSION_WHITELIST','jpg,gif,png,jpeg,zip,exe,gz,pdf,doc,xls,rar,dmg,mp3,mp4,ogg,3gp,aac');

define('_SYSTEM_ALLOWED_MAINFILES','index.php,captcha.php,cronjob.php'); 

define('TABLE_PLUGIN_HISTORY',$DB_PREFIX.'plugin_history');
define ('TABLE_COUNTRIES_PERMISSION', $DB_PREFIX.'countries_permission');
define ('TABLE_MEDIA_LANGUAGES', $DB_PREFIX.'media_languages');
define ('TABLE_CRON', $DB_PREFIX.'cron');
define ('TABLE_DOWNLOAD_LOG', $DB_PREFIX.'download_log');
define ('TABLE_CATEGORIES_CUSTOM_LINK_URL',$DB_PREFIX.'categories_custom_link_url');
define ('TABLE_SEO_URL_REDIRECT', $DB_PREFIX.'seo_url_redirect');
define('TABLE_FAILED_PAGES',$DB_PREFIX.'failed_pages');

define('TABLE_PDF_MANAGER', $DB_PREFIX.'pdf_manager');
define('TABLE_PDF_MANAGER_CONTENT', $DB_PREFIX.'pdf_manager_content');
?>
