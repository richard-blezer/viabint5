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

require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/debug.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/show_debug.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/is_data.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/build_define.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/date_short.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/merge_arrays.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/getPath.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/filter_text.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/strip_slashes.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/getSingleValue.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/get_array_with_keys.php';

if(USER_POSITION=='admin'){
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/get_table_fields.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/filter_text.inc.php';
require_once _SRV_WEBROOT._SRV_WEB_FRAMEWORK.'functions/empty_dataset.inc.php';
}

?>