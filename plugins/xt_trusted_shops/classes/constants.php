<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2014 xt:Commerce International Ltd. All Rights Reserved.
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

require_once _SRV_WEBROOT . 'conf/database.php';

define ('TABLE_TS_CERTIFICATES',  DB_PREFIX . '_trusted_shops_certificates');
define ('COL_TS_CERTS_ID',          'ID');
define ('COL_TS_CERTS_KEY',         'certificate_key');
// übernahme spalten aus trusted namen
define ('COL_TS_CERTS_STATUS',              'status');
define ('COL_TS_CERTS_RATING_ENABLED',      'ratingEnabled');
define ('COL_TS_CERTS_PROTECTION_ITEMS',    'protectionItems');
define ('COL_TS_CERTS_STATE',               'certificateState');
define ('COL_TS_CERTS_TYPE',                'certificateType');
define ('COL_TS_CERTS_WSLOGIN',             'wsLoginOK');
define ('COL_TS_CERTS_CURRENCIES',          'supportedCurrencies');
define ('COL_TS_CERTS_URL',                 'url');
define ('COL_TS_CERTS_LANG',                'shopLanguage');
// weitere spalten für display und config
define ('COL_TS_CERTS_SHOW_BADGE',          'show_badge');
define ('COL_TS_CERTS_SHOW_SEAL' ,          'show_seal');
define ('COL_TS_CERTS_SHOW_VIDEO',          'show_video');
define ('COL_TS_CERTS_SHOW_RATING',         'show_rating');
define ('COL_TS_CERTS_SHOW_BADGE_POS',      'show_badge_pos');
define ('COL_TS_CERTS_SHOW_RICH_SNIPPETS',  'show_rich_snippets');
define ('COL_TS_CERTS_RATE_LATER_AFTER',    'rate_later_after');

// um xtc's magic bei verarbeitung und darstellung von db-spalten müssen spalten für die view umbenannt werden
// bzw wg text konstanten
// COL_TS_CERTS_LANG würde ein sprachtab erzeugen
define ('VIEW_COL_TS_CERTS_LANG', 'ts_lng');
// url erzeugt TEXT_URL => 'Pluging Hersteller'
define ('VIEW_COL_TS_URL', 'ts_url');


define('TS_URL_CERTIFICATE_SHOW','http://www.trustedshops.com/shop/certificate.php?shop_id='); // admin link lang independent
define('TS_URL_SRVC_SHOP_STATUS','http://www.trustedshops.com/ts_services/checkShopStatus/');

define('TS_URL_RATING_DE','https://www.trustedshops.de/bewertung/info_TS_KEY.html');
define('TS_URL_RATING_EN','https://www.trustedshops.com/buyerrating/info_TS_KEY.html');
//define('TS_URL_RATING_EN-GB','https://www.trustedshops.co.uk/buyerrating/info_TS_KEY.html');
define('TS_URL_RATING_ES','https://www.trustedshops.es/evaluacion/info_TS_KEY.html');
define('TS_URL_RATING_FR','https://www.trustedshops.fr/evaluation/info_TS_KEY.html');
define('TS_URL_RATING_PL','https://www.trustedshops.pl/opinia/info_TS_KEY.html');


define('TS_VLD_CERT_KEY_LENGTH', 33);

define('TS_DEF_RATE_LATER_AFTER', 5);

define('TS_CERT_STATUS_CODE_SUCCESS', 'SUCCESS');

define('TS_SUCCESS', 'SUCCESS');
define('TS_ERROR_INVALID_ID', 'SUCCESS');
define('TS_ERROR_NOT_FOUND', 'SHOP_NOT_FOUND');
define('TS_ERROR_SERVICE', 'SERVICE_EXCEPTION');

define('TS_PARAM_CERT_ID', 'cert_id');
define('TS_PARAM_CERT_KEY', COL_TS_CERTS_KEY);

define('TS_CERT_STATE_PRODUCTION', 'PRODUCTION');
define('TS_CERT_STATE_INTEGRATION', 'INTEGRATION');
define('TS_CERT_STATE_TEST', 'TEST');

define('TS_CERT_TYPE_CLASSIC', 'CLASSIC');
define('TS_CERT_TYPE_EXCELLENCE', 'EXCELLENCE');
define('TS_CERT_TYPE_MIGRATION', 'C2E');
define('TS_CERT_TYPE_NO_PROTECT', 'NO_BUYER_PROTECTION');

define('TS_BADGE_SIZE_TEXT', 'text');
define('TS_BADGE_SIZE_DEFAULT', 'default');
define('TS_BADGE_SIZE_SMALL', 'small');
define('TS_BADGE_SIZE_REVIEWS', 'reviews');

define('TS_RICH_SNIPPET_MAX_RATINGS', 5);

if(!defined(SHOP_DOMAIN_HTTP))
{
    global $db;
    $sql = "SELECT `shop_domain` FROM ".TABLE_MANDANT_CONFIG." WHERE `shop_id`='$store_handler->shop_id'";
    $domain = $db->GetOne($sql);
    define('SHOP_DOMAIN_HTTP', $domain);
}
