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

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.sql_query.php';

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.timer.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.LogHandler.php';
$logHandler = new LogHandler();
//$logHandler->timer_start();
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.permissions.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.item_permissions.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.language_content.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.language.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.stock.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.popup.php';
$language = new language();
$language->_setLocale();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.client_detect.php';
$client_detect = new client_detect;
$client_detect->_getEnvironment();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/smarty/Smarty.class.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'library/smarty/SmartyValidate.class.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.template.php';

include _SRV_WEBROOT . _SRV_WEB_CORE . 'page_registry.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.page_handler.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.box_handler.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.subpage_handler.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.form.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.xt_cron.php';
$form = new form();

if (!is_object($_SESSION['customer'])) {
    $_SESSION['customer'] = new customer();
}

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.customers_status.php';
$customers_status = new customers_status();

define('_CUST_STATUS_SHOW_PRICE', $customers_status->customers_status_show_price);
define('_CUST_STATUS_FSK18', $customers_status->customers_fsk18);
define('_CUST_STATUS_FSK18_DISPLAY', $customers_status->customers_fsk18_display);

if (($customers_status->customers_status_template != '') && ($client_detect->mobile===false)) {
    define('_STORE_TEMPLATE', $customers_status->customers_status_template);
} elseif ($client_detect->mobile === true) { // mobile template switch
    if ($customers_status->customers_status_mobile_template != '') {
        define('_STORE_TEMPLATE', $customers_status->customers_status_mobile_template);
    } else {
        define('_STORE_TEMPLATE', _STORE_DEFAULT_MOBILE_TEMPLATE);
    }
} else {
    define('_STORE_TEMPLATE', _STORE_DEFAULT_TEMPLATE);
}
$template = new Template();

$currency = new currency();
$currency->_checkCurrencyData();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.tax.php';
$tax = new tax();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.links.php';
$xtLink = new xtLink();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.xt_minify.php';
$xtMinify = new xt_minify();

include_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'classes/class.logo.php';

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.image.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaData.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaImages.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.MediaFiles.php';
$mediaImages = new MediaImages();
$mediaFiles = new MediaFiles();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.product_sql_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.product.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.seo_regenerate.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.products_list.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.category_sql_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.nested_set.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.category.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.manufacturer_sql_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.manufacturer.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.split_page.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.seo.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.email.php';

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.brotkrumen.php';
$brotkrumen = new brotkrumen();

define('SYSTEM_STOCK_TRAFFIC', 'true');
define('SYSTEM_SHIPPING_STATUS', 'true');

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.system_status.php';
$system_status = new system_status();


$manufacturer = new manufacturer();
$product = new product();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.price.php';
$price = new price($customers_status->customers_status_id, $customers_status->customers_status_master);

$page_data = array();

$seo = new seo_modRewrite();
($plugin_code = $xtPlugin->PluginCode('store_main_handler.php:seo')) ? eval($plugin_code) : false;
if (USER_POSITION != 'admin') $seo_return = $seo->_lookUpforUrl();
$system_status->system_status();


$tmp_page = '';
$tmp_page_action = '';

// UBo ++
if ((_SYSTEM_MOD_REWRITE == 'true') && (_SYSTEM_MOD_REWRITE_DEFAULT == 'true')) {
    $ru_lang = '';
    $ru_page = '';
    $ru_page_action = '';
    $ru_file_ext = (trim(_SYSTEM_SEO_FILE_TYPE) != '') ? _SYSTEM_SEO_FILE_TYPE : 'html';

    $ru = $_SERVER['REQUEST_URI'];
    $ru = $seo->_cleanUpUrl($ru);
    if (is_array($ru)) {
        $ru = $ru['url'];
    }
    list($ru,) = preg_split('/\?/', $ru, 2);
    $arr_ru = preg_split('%/%', $ru);
    if (_SYSTEM_SEO_URL_LANG_BASED == 'true') {
        // Sprache speichern
        $ru_lang = array_shift($arr_ru);
    }

    if ((count($arr_ru) == 1) or ((count($arr_ru) == 2) and (trim($arr_ru[1]) == ''))) { //[LANG_CODE]/[PAGE]
        $ru_pathinfo = pathinfo($arr_ru[0]);
        if (($ru_pathinfo['extension'] == '') or (($ru_pathinfo['extension'] == $ru_file_ext))) {
            $ru_page = $ru_pathinfo['filename'];
        }
        $tmp_page = $ru_page;

    } else if (count($arr_ru) == 2) { //[LANG_CODE]/[PAGE]/PAGE_ACTION.HTML
        $ru_page = $arr_ru[0];
        $ru_pathinfo = pathinfo($arr_ru[1]);
        if (($ru_pathinfo['extension'] == '') or (($ru_pathinfo['extension'] == $ru_file_ext))) {
            $ru_page_action = $ru_pathinfo['filename'];
        }
        $tmp_page = $ru_page;
        $tmp_page_action = $ru_page_action;
    }

    if (_SYSTEM_SEO_URL_LANG_BASED == 'true') {
        // sprache behandeln
        if ($ru_lang != '')
            if ((($ru_lang != $language->code) && ($ru_lang != $_SESSION['selected_language'])) && ($language->_checkStore($ru_lang, $store_handler->shop_id) == true)) {
                $language = new language($ru_lang);
                $_SESSION['selected_language'] = $ru_lang;
                $link_array = array('lang_code' => $ru_lang, 'params' => $xtLink->_getParams(array('action', 'new_lang')));
                // ermittelte Werte
                if (trim($tmp_page) != '') {
                    $link_array['page'] = $tmp_page;
                }
                if (trim($tmp_page_action) != '') {
                    $link_array['paction'] = $tmp_page_action;
                }
                // $_GET['page'] könnte per HOOK:class.seo.php:_lookUpforUrl_switch  gesetzt sein
                if (trim($_GET['page']) != '') {
                    $link_array['page'] = $_GET['page'];
                }
                // $_GET['page_action'] könnte per HOOK:class.seo.php:_lookUpforUrl_switch  gesetzt sein
                if (trim($_GET['page_action']) != '') {
                    $link_array['paction'] = $_GET['page_action'];
                }
                ($plugin_code = $xtPlugin->PluginCode('form_handler.php:change_lang_bottom')) ? eval($plugin_code) : false;
                //$xtLink->_redirect($xtLink->_link($link_array));
//               }
            }
    }
    if (($tmp_page != '') && ($_GET['page'] == '')) { // $_GET['page'] könnte per HOOK:class.seo.php:_lookUpforUrl_switch  gesetzt sein
        $_GET['page'] = $tmp_page;
    }
    if (($tmp_page_action != '') && ($_GET['page_action'] == '')) { // $_GET['page_action'] könnte per HOOK:class.seo.php:_lookUpforUrl_switch  gesetzt sein
        $_GET['page_action'] = $tmp_page_action;
    }

}
// UBO --

$language->_getLanguageContent(USER_POSITION);


if (isset($_POST['page'])) {
    $tmp_page = $_POST['page'];
} elseif (isset($_GET['page'])) {
    $tmp_page = $_GET['page'];
}

if (isset($_POST['page_action'])) {
    $tmp_page_action = $_POST['page_action'];
} elseif (isset($_GET['page_action'])) {
    $tmp_page_action = $_GET['page_action'];
}


$page_data = array('page' => $tmp_page, 'page_action' => $tmp_page_action);


$page = new page($page_data);
// checking if page available
if ($tmp_page != $page->page_name && USER_POSITION != 'admin' && $tmp_page != '') {
    // redirect to 404
    if (_SYSTEM_MOD_REWRITE_404 == 'true') {
        $seo->faultHandler(404);
    } else {
		$seo->Log404page();
        $tmp_link = $xtLink->_link(array('page' => 'index'));
        $xtLink->_redirect($tmp_link);
    }
}

if (($page->page_name=='404') && (USER_POSITION != 'admin') && (!defined('XT_WIZARD_STARTED'))&& ( (strpos($_SERVER['PHP_SELF'],'cronjob.php') === false))){
     $seo->Log404page();
}

if (isset ($_GET['info'])) {
    $current_product_id = (int)$_GET['info'];
    $current_category_id = _getSingleValue(array('value' => 'categories_id', 'table' => TABLE_PRODUCTS_TO_CATEGORIES, 'key' => 'products_id', 'key_val' => $current_product_id, 'key_where' => ' and master_link=1'));
}

if (isset ($_GET['cat'])) {
    $current_category_id = (int)$_GET['cat'];
}

if (isset ($_GET['mnf'])) {
    $current_manufacturer_id = (int)$_GET['mnf'];
}

if (isset ($_GET['coID'])) {
    $current_content_id = (int)$_GET['coID'];
}

$category = new category($current_category_id);

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.info_handler.php';
$info = new info();


require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.content_sql_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.content.php';
$_content = new content();


require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.shipping_link.php';
$system_shipping_link = new system_shipping_link();

if (!is_object($_SESSION['cart'])) {
    $_SESSION['cart'] = new cart();
}
$_SESSION['cart']->_refresh();

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.order.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.shipping_sql_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.shipping.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.payment_sql_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.payment.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.checkout.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.search_query.php';
require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.search.php';

if (!empty($current_product_id))
    $p_info = new product($current_product_id, 'full', '', '', 'product_info');

require_once _SRV_WEBROOT . _SRV_WEB_FRAMEWORK . 'classes/class.meta_tags.php';
$meta_tags = new meta_tags();

($plugin_code = $xtPlugin->PluginCode('store_main_handler.php:bottom')) ? eval($plugin_code) : false;
?>