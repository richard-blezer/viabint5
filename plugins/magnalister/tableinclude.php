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
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */	

define('_DB_PREFIX_',DB_PREFIX.'_');
define('TABLE_ADDRESS', _DB_PREFIX_ .'customers_addresses');//define('TABLE_ADDRESS', _DB_PREFIX_ .'address');
define('TABLE_ADDRESS_BOOK', 'address_book');
define('TABLE_SYSTEM_STATUS', _DB_PREFIX_.'system_status');
//define('TABLE_CATEGORIES', _DB_PREFIX_ .'category'); //allready defined     
//define('TABLE_CATEGORIES_DESCRIPTION', 'ps_category_lang'); //allready defined 
//define('TABLE_COUNTRIES',  _DB_PREFIX_ . 'country');
define('TABLE_COUNTRIES_LANG', _DB_PREFIX_ . 'country_lang');
//define('TABLE_CURRENCIES', _DB_PREFIX_ . 'currency'); //allready defined     
//define('TABLE_LANGUAGES', _DB_PREFIX_ . 'lang');//allready defined
define('TABLE_COUNTRIES_DESCRIPTION','countries_description');     
//define('TABLE_MANUFACTURERS', _DB_PREFIX_ . 'manufacturer');//allready defined     
define('TABLE_CART', _DB_PREFIX_ .'cart');
define('TABLE_CARRIER', _DB_PREFIX_ .'carrier');
//define('TABLE_CUSTOMERS', _DB_PREFIX_ .'customer');//allready defined     
//define('TABLE_CUSTOMERS_INFO', 'customers_info');//allready defined     
//define('TABLE_CUSTOMERS_STATUS', _DB_PREFIX_.'customers_status');//allready defined     
//define('TABLE_ORDERS', _DB_PREFIX_ .'orders');//allready defined     
define('TABLE_CUSTOMERS_STATUS_DESCRIPTION', 'customers_status_description'); 
define('TABLE_ORDERS_STATUS', _DB_PREFIX_ . 'orders_status_history');//define('TABLE_ORDERS_STATUS', _DB_PREFIX_ . 'order_state_lang');
define('TABLE_STATUTS_DESCRIPTION', _DB_PREFIX_.'system_status_description');
//define('TABLE_ORDERS_STATUS_HISTORY',  _DB_PREFIX_ . 'order_history');//allready defined     
define('TABLE_ORDERS_DETAIL',  _DB_PREFIX_ . 'orders_products');//define('TABLE_ORDERS_DETAIL',  _DB_PREFIX_ . 'order_detail');
//define('TABLE_PRODUCTS', _DB_PREFIX_ . 'product');//allready defined     
//define('TABLE_PRODUCTS_ATTRIBUTES', _DB_PREFIX_ . 'product_attribute');//allready defined     
define('TABLE_PRODUCTS_ATTRIBUTES_COMBINE', _DB_PREFIX_ . 'product_attribute_combination');
define('TABLE_ATTRIBUTES', _DB_PREFIX_ .'plg_products_attributes');
define('TABLE_ATTRIBUTES_DESCRIPTION', _DB_PREFIX_ . 'plg_products_attributes_description');
define('TABLE_ATTRIBUTES_GROUP_LANG', _DB_PREFIX_ . 'attribute_group_lang');    
define('TABLE_PRODUCTS_IMAGES', _DB_PREFIX_ . 'image');
//define('TABLE_PRODUCTS_TO_CATEGORIES', _DB_PREFIX_ . 'category_product');//allready defined     
define('TABLE_SHIPPING_STATUS',_DB_PREFIX_ . 'carrier_lang');
define('TABLE_SPECIALS', 'specials');
define('TABLE_TAX_GROUP_TO_TAX',_DB_PREFIX_ . 'tax_rule');
//define('TABLE_TAX_RATES', _DB_PREFIX_ . 'tax');//allready defined     
define('TABLE_TAX_RULES_GROUP',_DB_PREFIX_ . 'tax_rules_group');
