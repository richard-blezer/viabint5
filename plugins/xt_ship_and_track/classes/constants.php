<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
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

// SHIPPER
define ('TABLE_SHIPPER',  DB_PREFIX . '_shipper');
define ('COL_SHIPPER_ID_PK',  'id');
define ('COL_SHIPPER_CODE',  'shipper_code');
define ('COL_SHIPPER_NAME',  'shipper_name');
define ('COL_SHIPPER_TRACKING_URL',  'shipper_tracking_url');
define ('COL_SHIPPER_API_ENABLED',  'shipper_api_enabled');
define ('COL_SHIPPER_ENABLED',  'shipper_enabled');

// tracking
define ('TABLE_TRACKING',  DB_PREFIX . '_tracking');
define ('COL_TRACKING_ID_PK',  'id');
define ('COL_TRACKING_CODE',  'tracking_code');
define ('COL_TRACKING_ORDER_ID',  'tracking_order_id');
define ('COL_TRACKING_STATUS_ID',  'tracking_status_id');
define ('COL_TRACKING_SHIPPER_ID',  'tracking_shipper_id');
define ('COL_TRACKING_ADDED',  'tracking_added');

// view tracking
define ('VIEW_TRACKING',  DB_PREFIX . '_v_tracking');

// trackin status
define ('TABLE_TRACKING_STATUS',  DB_PREFIX . '_tracking_status');
define ('COL_TRACKING_STATUS_ID_PK',  'id');
define ('COL_TRACKING_SHIPPER_ID',  'tracking_shipper_id');
define ('COL_TRACKING_STATUS_CODE',  'tracking_status_code');
define ('COL_TRACKING_STATUS_DESC_SHORT',  'tracking_status_desc_short');
define ('COL_TRACKING_STATUS_DESC_LONG',  'tracking_status_desc_long');

// hermes orders
define ('TABLE_HERMES_ORDER',  DB_PREFIX . '_hermes_order');
define ('COL_HERMES_ID_PK',  'id');
define ('COL_HERMES_ORDER_NO',  'hermes_order_no'); // entspricht tracking code
define ('COL_HERMES_XT_ORDER_ID',  'xt_orders_id'); //
define ('COL_HERMES_SHIPPING_ID',  'hermes_shipping_id');
define ('COL_HERMES_STATUS',  'hermes_status');
define ('COL_HERMES_PARCEL_CLASS',  'parcel_class');
define ('COL_HERMES_AMOUNT_CASH_ON_DELIVERY',  'hermes_amount_cash_on_delivery');
define ('COL_HERMES_BULK_GOOD',  'hermes_bulk_good');
define ('COL_HERMES_COLLECT_DATE',  'collect_date');
define ('COL_HERMES_TS_CREATED',  'hermes_ts_created');

// hermes settings
define ('TABLE_HERMES_SETTINGS',  DB_PREFIX . '_hermes_settings');
define ('COL_HERMES_SETTINGS_PK',  'id');
define ('COL_HERMES_USER',  'hermes_user');
define ('COL_HERMES_PWD',  'hermes_pwd');
define ('COL_HERMES_SANDBOX',  'hermes_sandbox');
define ('COL_HERMES_SETTINGS_HINT',  'hermes_settings_hint');


// hermes collect request
define ('TABLE_HERMES_COLLECT',  DB_PREFIX . '_hermes_collect');
define ('COL_HERMES_COLLECT_ID_PK',  'id');
define ('COL_HERMES_COLLECT_NO',  'collect_request_no');