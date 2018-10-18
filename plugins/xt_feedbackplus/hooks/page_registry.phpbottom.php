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
 # @version $Id: page_registry.phpbottom.php 6060 2013-03-14 13:10:33Z mario $
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

define('TABLE_FEEDBACKPLUS_CAMPAIGNS', DB_PREFIX . '_feedbackplus_campaigns');
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_CATEGORIES', DB_PREFIX . '_feedbackplus_campaigns_categories');
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS', DB_PREFIX . '_feedbackplus_campaigns_permissions');
define('TABLE_FEEDBACKPLUS_LIFE_CIRCLES', DB_PREFIX . '_feedbackplus_life_circles');

define('PAGE_FEEDBACKPLUS', _SRV_WEB_PLUGINS.'xt_feedbackplus/pages/page.feedbackplus.php');
define('PAGE_FEEDBACK', _SRV_WEB_PLUGINS.'xt_feedbackplus/pages/page.feedbackplus_review.php');
require _SRV_WEBROOT.'plugins/xt_feedbackplus/classes/class.xt_feedbackplus.php';

?>