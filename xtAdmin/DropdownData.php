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
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

include '../xtFramework/admin/main.php';

include (_SRV_WEBROOT_ADMIN.'page_includes.php');
$request = array();

if($_GET)
$request = array_merge($request, $_GET);

if($_POST)
$request = array_merge($request, $_POST);

$dropdown = new getAdminDropdownData();

if ($request['systemstatus'])
$result = $dropdown->getSystemstatusDropdown($request['systemstatus']);

if ($request['get']) {
	switch ($request['get']) {
		// languages
		case "language_codes":
			$result = $dropdown->getLanguageCodes();
			break;
		case "ssl_options":
			$result = $dropdown->getSslOptions();
			break;
		case "language_classes":
			$result = $dropdown->getLanguageClasses();
			break;
		case "language_nondefines":
			$result = $dropdown->getLanguageNonDefines();
			break;
			// languages end
		case "mail_types":
			$result = $dropdown->getMailTypes();
			break;
			// status
		case "status_truefalse":
			$result = $dropdown->getTrueFalse();
			break;
        case "status_sandbox":
            $result = $dropdown->getLiveTest();
            break;
			// status
		case "download_status":
			$result = $dropdown->getDownloadStatus();
			break;
			// status
		case "conf_shippingtype":
			$result = $dropdown->getShippingType();
			break;
		case "conf_truefalse":
			$result = $dropdown->getConfTrueFalse();
			break;
		case "status_ascdesc":
			$result = $dropdown->getAscDesc();
			break;
			// status end
			// sort
		case "sort_defaults":
			$result = $dropdown->getSortDefaults();
			break;
		case "manufacturers":
			$result = $dropdown->getManufacturers();
			break;
		case "manufacturers_sort":
			$result = $dropdown->getManufacturersSort();
			break;
			// product
		case "product_template":
			$result = $dropdown->getProductTemplate();
			break;
		case "product_list_template":
			$result = $dropdown->getProductListTemplate();
			break;
			// product end
		case "conf_storelogo":
			$result = $dropdown->getStoreLogo();
			break;
		case "categories_template":
			$result = $dropdown->getCategoryTemplate();
			break;
		case "listing_template":
			$result = $dropdown->getProductListingTemplate();
			break;
		case "micropages":
			$result = $dropdown->getMicropages();
			break;
		case "customers_status":
			$result = $dropdown->getCustomersStatus();
			break;
			// tax
		case "tax_zones":
			$result = $dropdown->getTaxZones();
			break;
		case "tax_shipping_zones":
			$result = $dropdown->getTaxShippingZones();
			break;
		case "tax_classes":
			$result = $dropdown->getTaxClasses();
			break;
		case "category_sort":
			$result = $dropdown->getCategorySort();
			break;
		case "currencies":
			$result = $dropdown->getCurrencies();
			break;
		case "countries":
			$result = $dropdown->getCountries();
			break;
		case "stores":
			$result = $dropdown->getStores($request['current_store_id']);
			break;
		case "gender":
			$result = $dropdown->getGender();
			break;
                case "gender1":
                        $result[]  =  array('id' => '',
                         'name' => TEXT_EMPTY_SELECTION);
			$result2 = $dropdown->getGender();
                        foreach($result2 as $res){
                            $result[] = $res;
                        }
			break;
		case "address_types":
			$result = $dropdown->getAddressTypes();
			break;
		case "content_blocks":
			$result = $dropdown->getContentHooks();
			break;
		case "content_list":
			$result = $dropdown->getContentList();
			break;
		case "content_forms":
			$result = $dropdown->getContentForms();
			break;
		case "permission_areas":
			$result = $dropdown->getPermissionAreas();
			break;
		case "acl_group_list":
			$result = $dropdown->getACLGroupList();
			break;
		case "shipping_time":
			$result = $dropdown->getShippingTime();
			break;
		case "order_status":
			$result = $dropdown->getOrderStatus();
			break;
		case "admin_perm":
			$result = $dropdown->getAdminPerm();
			break;
		case "admin_rights":
			$result = $dropdown->getAdminRights();
			break;
		case "cat_tree":
			$result = $dropdown->getCatTree();
			break;
		case "file_types":
			$result = $dropdown->getFileTypes();
			break;
		case "image_classes":
			$result = $dropdown->getImageClasses();
			break;
		case "image_types":
			$result = $dropdown->getImageTypes();
			break;
		case "templateSets":
			$result = $dropdown->getTemplateSets();
			break;
		case "SimplePrefix":
			$result = $dropdown->getSimplePrefix();
			break;
		case "AdvancedPrefix":
			$result = $dropdown->getAdvancedPrefix();
			break;
		case "FieldTypes":
			$result = $dropdown->getFieldTypes();
			break;
		case "wysiwyg":
			$result = $dropdown->getWYSIWYG();
			break;
		case "upload_type":
			$result = $dropdown->getUploadType();
			break;
		case "payment_methods":
			$result = $dropdown->getPaymentMethods();
			break;
		case "shipping_methods":
			$result = $dropdown->getShippingMethods();
			break;
		case "language_xml":
			$result = $dropdown->getLanguageXML();
			break;
		case "export_tpls":
			$result = $dropdown->getExportTpls();
			break;
		case "payment_cost_types":
			$result = $dropdown->getPaymentCostTypes();
			break;
		case "jqversion":
			$result = $dropdown->getjQueryList();
			break;
		case "jqcdn":
			$result = $dropdown->getjQueryCDNList();
			break;
		case 'minify':
			$data = array(
				array('id' => 'minifymerge', 'name' => TEXT_SELECTION_MINIFYMERGE),
				array('id' => 'merge', 'name' => TEXT_SELECTION_MERGE),
				array('id' => 'single', 'name' => TEXT_SELECTION_SINGLE)
			);
			$result = $data;
			break;
		case "tplcolumns":
			$data=array(
				array('id' => 'one', 'name' => '1'),
				array('id' => 'two', 'name' => '2'),
				array('id' => 'three', 'name' => '3'),
				array('id' => 'four', 'name' => '4'),
				array('id' => 'five', 'name' => '5')
			);
			$result = $data;
			break;
		case "ext_languages":
//			global $db, $language, $filter;

			$data = array(
				array('id' => '', 'name' => TEXT_EMPTY_SELECTION, 'flag' => '')
			);

			foreach ($language->_getLanguageList('all') as $key => $val)
			{
				$data[] =  array(
					'id' => $val['id'],
					'name' => $val['name'].'('.$val['id'].')',
					'flag' => $val['id'].'_icon'
				);
			}
			$result = $data;
			break;
		case "captcha":
			$data = array(
				array('id' => TEXT_EMPTY_SELECTION, 'name' => TEXT_EMPTY_SELECTION),
				array('id' => 'Standard', 'name' => 'Standard')
			);

			// check reCaptcha is installed #CORE-160
			$plugin = new plugin();
			$result = $plugin->getPluginStatus('xt_recaptcha');
			if ($result)
			{
				$data[] =  array('id' => 'ReCaptcha', 'name' => 'ReCaptcha');
                        }
			$result = $data;
			break;
		case "vat_check":
			$data = array(
				array('id' => TEXT_EMPTY_SELECTION, 'name' => TEXT_EMPTY_SELECTION),
				array('id' => 'simple',	'name' => TEXT_VAT_CHECK_SIMPLE),
				array('id' => 'complex', 'name' => TEXT_VAT_CHECK_COMPLEX),
				array('id' => 'live', 'name' => TEXT_VAT_CHECK_LIVE)
			);
			$result = $data;
			break;
		case "date_format":
			$data = array(
				array('id' => 'dd.mm.yyyy', 'name' => 'dd.mm.yyyy'),
				array('id' => 'yyyy-mm-dd', 'name' => 'yyyy-mm-dd'),
				array('id' => 'mm/dd/yyyy', 'name' => 'mm/dd/yyyy')
			);

			($plugin_code = $xtPlugin->PluginCode('admin_dropdown.php:dropdown_date_format')) ? eval($plugin_code) : false;

			$result = $data;
			break;
               case "rating":
                    $star = '<img  src="images/icons/star.png">';
                    $data[] = array('id' => '1', 'name' => "1");
		    $data[] = array('id' => '2', 'name' => "2");
		    $data[] = array('id' => '3', 'name' => "3");
                    $data[] = array('id' => '4', 'name' => "4");
                    $data[] = array('id' => '5', 'name' => "5");
                    $result = $data;
                    break;
		case "export_type":
			$types = array(
				array('id' => '1', 'name'=> TEXT_PRODUCT),
				array('id' => '2', 'name'=> TEXT_ORDER),
				array('id' => '3', 'name'=> TEXT_SITEMAP)
			);
			$result = $types;
			break;
		case "export_encoding":
			$encoding = array(
				array('id' => 'UTF-8', 'name'=> 'UTF-8'),
				array('id' => 'ISO-8859-1', 'name'=> 'ISO-8859-1')
			);
			$result = $encoding;
			break;
		case "file_sort":
			$sort_by = array(
				array('id' => 'id_asc', 'name'=> 'id_asc'),
				array('id' => 'id_desc', 'name'=> 'id_desc'),
				array('id' => 'file_asc', 'name'=> 'file_asc'),
				array('id' => 'file_desc', 'name'=> 'file_desc')
			);
			$result = $sort_by;
			break;
		case "savebutton_position":
			$savebutton_position = array(
				array('id' => 'top', 'name'=> 'top'),
				array('id' => 'bottom', 'name'=> 'bottom'),
				array('id' => 'both', 'name'=> 'both')
			);
			$result = $savebutton_position;
			break;
		case "conf_mobile_switch_method":
			global $customers_status;
			$data = array(
				array('id' => 'auto', 'name' => TEXT_XT_MOBILE_SWITCH_METHOD_AUTO),
//				array('id' => 'semi', 'name' => TEXT_XT_MOBILE_SWITCH_METHOD_SEMIAUTO),
				array('id' => 'link', 'name' => TEXT_XT_MOBILE_SWITCH_METHOD_MANUEL)
			);
			$result = $data;
			break;
		case "backend_themes":
			$result = $dropdown->getBackendthemes();
			break;
		case "status_product":
			$result = $dropdown->getStatusProduct();
			break;
		case "master_slave_product":
			$result = $dropdown->getMasterSlaveProduct();
			break;
		case 'order_edit_coupons':
			require_once _SRV_WEBROOT.'xtFramework/classes/class.order_edit_edit_coupon.php';
			$_coupons = new order_edit_edit_coupon();
			$result = $_coupons->getCoupons();
			unset($_coupons);
			break;
		case 'coupon_type':
			$result = array(
				array('id' => 'fix', 'name' => TEXT_COUPON_TYPE_FIX),
				array('id' => 'percent', 'name' => TEXT_COUPON_TYPE_PERCENT),
				array('id' => 'freeshipping', 'name' => TEXT_COUPON_TYPE_FREESHIPPING)
			);
			break;
		case 'order_edit_address_type':
			$r = $dropdown->getAddressTypes();
			// default wird nicht übernommen
			$result = array($r[1], $r[2]);
			break;
		case 'order_edit_customer_addresses':
			require_once _SRV_WEBROOT.'xtFramework/classes/class.order_edit_edit_address.php';
			$_address = new order_edit_edit_address();
			$result = $_address->getCustomerAddresses();
			unset($_address);
			break;
		case 'order_edit_payment_methods':
			global $db, $language;
			$rs = $db->Execute('SELECT * FROM '.TABLE_PAYMENT.' p, '.TABLE_PAYMENT_DESCRIPTION." pd WHERE p.payment_id = pd.payment_id and pd.language_code = '".$language->code."'");
			$result = array();
			if ($rs->RecordCount() > 0)
			{
				while ( ! $rs->EOF)
				{
					$result[] = array(
						'id' => $rs->fields['payment_code'],
						'name' =>  $rs->fields['payment_name']
					);
					$rs->MoveNext();
				}
			}
			break;
		case 'order_sources':
			global $db,$language;

			$rs = $db->Execute('SELECT * FROM '.TABLE_ORDERS_SOURCE);
			$result = array(
				array('id' => 0, 'name' =>  TEXT_EMPTY_SELECTION)
			);

			if ($rs->RecordCount() > 0)
			{
				while ( ! $rs->EOF)
				{
					$code = $rs->fields['source_name'];
					$result[] = array(
						'id' => $rs->fields['source_id'],
						'name' => defined('TEXT_'.$code) ? constant('TEXT_'.$code) : $code
					);
					$rs->MoveNext();
				}
			}
			break;

        case "cron_type":
            $_cron = new xt_cron();
            $result = $_cron->getCronTypes();
            unset($_cron);
            break;

        case "cron_action":
            $_cron = new xt_cron();
            $result = $_cron->getCronActions();
            unset($_cron);
            break;

        case "hours":
            $result = array();
            foreach (range(0, 23) as $number) {
                $result[]=array('id'=>$number,'name'=>$number);
            }
            break;

        case "minutes":
            $result = array();
            foreach (range(0, 59) as $number) {
                $result[]=array('id'=>$number,'name'=>$number);
            }
            break;

        case "months":
            $result = array();
            foreach (range(0, 12) as $number) {
                $result[]=array('id'=>$number,'name'=>$number);
            }
            break;
        case "days":
            $result = array();
            foreach (range(0, 31) as $number) {
                $result[]=array('id'=>$number,'name'=>$number);
            }
            break;
		case "category_custom_link_type":
            $result = $dropdown->getCategoryCustomLinkType();
            break;
		case "seo_url_link_type":
			$result = $dropdown->getSeoUrlLinkType();
			break;
		case "category_custom_link_id":
			include_once _SRV_WEBROOT.'xtFramework/classes/class.custom_link.php';
			$cust_link = new custom_link();
			$cust_link->type = $_GET['custom_link_type'];
			$result = $cust_link->getCustomLinkTypeDate();
            break;
		case "filter_period":
			$filter_period = array(
				array('id' => 'day', 'name'=> TEXT_DAILY),
				array('id' => 'month', 'name'=> TEXT_MONTHLY),
				array('id' => 'year', 'name'=> TEXT_YEARLY)
			);
			$result = $filter_period;
			break;
		case "product_stats_display_type":
			$filter_period = array(
				array('id' => 'quantity_sold', 'name'=> TEXT_QUANTITY_SOLD),
				array('id' => 'amount', 'name'=> TEXT_AMOUNT),
			);
			$result = $filter_period;
			break;
		case "shopping_carts_type":
			$filter_period = array(
				array('id' => 'sc_added', 'name'=> TEXT_SHOPPING_CART_ADDED),
				array('id' => 'sc_checkout', 'name'=> TEXT_SHOPPING_CART_CHECKOUT),
				array('id' => 'sc_not_checkout', 'name'=> TEXT_SHOPPING_CART_NOT_CHECKOUT),
			);
			$result = $filter_period;
			break;
		case "shopping_carts_display_by":
			$filter_period = array(
				array('id' => 'carts_count', 'name'=> TEXT_CARTS_COUNT),
				array('id' => 'products_count', 'name'=> TEXT_PRODUCTS_COUNT),
			);
			$result = $filter_period;
			break;
		case "smtp_secure":
			$result = array(
					array('id' => 'tls', 'name'=> 'TLS'),
					array('id' => 'ssl', 'name'=> 'SSL'),
			);
			break;
		default:
			($plugin_code = $xtPlugin->PluginCode('admin_dropdown.php:dropdown',$request['plugin_code'])) ? eval($plugin_code) : false;
			if (isset($plugin_return_value)) return $plugin_return_value;
	}
}

if (isset($_GET['skip_empty']) && $_GET['skip_empty'] === 'true' && empty($result[0]['id'])) {
	array_shift($result);
}
if (isset($_POST['query']) && $_POST['query']!='') {
	$copy = $result;
	foreach ($result as $key => $value) {
		
		if (strpos(strtolower($value['name']), strtolower($_POST['query'])) === false) {
			unset($result[$key]);
		}
	}
	
	$result = array_values($result);
	if (empty($result)) {
		$result = $copy;
	}
}
//$result = $dropdown->matrixSort($result, 'name');
$obj = new stdClass();
$obj->totalCount = count($result);
$obj->topics = $result;
header('Content-Type: application/json; charset='._SYSTEM_CHARSET);
echo json_encode($obj);