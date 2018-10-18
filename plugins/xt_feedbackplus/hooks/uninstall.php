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
# @version $Id: uninstall.php 6359 2013-06-21 13:37:52Z stefan $
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
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_CUSTOMERS', DB_PREFIX . '_feedbackplus_campaigns_customers');
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_PRODUCTS', DB_PREFIX . '_feedbackplus_campaigns_products');
define('TABLE_FEEDBACKPLUS_LIFE_CIRCLES', DB_PREFIX . '_feedbackplus_life_circles');
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS', DB_PREFIX . '_feedbackplus_campaigns_permissions');
define('TABLE_FEEDBACKPLUS_LIFE_CIRCLES_STATUSES', DB_PREFIX . '_feedbackplus_life_circles_statuses');
define('TABLE_FEEDBACKPLUS_TEMPLATES', DB_PREFIX . '_feedbackplus_templates');


$db->Execute('DELETE FROM ' . TABLE_ADMIN_NAVIGATION . " WHERE text = 'xt_feedbackplus_campaigns'");
$db->Execute('UPDATE ' . TABLE_ADMIN_NAVIGATION . " SET `type`='I' WHERE `text`='xt_reviews'");

$db->Execute("DROP TABLE IF EXISTS " . TABLE_FEEDBACKPLUS_CAMPAIGNS);
$db->Execute("DROP TABLE IF EXISTS " . TABLE_FEEDBACKPLUS_CAMPAIGNS_CATEGORIES);
$db->Execute("DROP TABLE IF EXISTS " . TABLE_FEEDBACKPLUS_CAMPAIGNS_PRODUCTS);
$db->Execute("DROP TABLE IF EXISTS " . TABLE_FEEDBACKPLUS_LIFE_CIRCLES);
$db->Execute("DROP TABLE IF EXISTS " . TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS);

// delete tasks
$db->Execute("DELETE FROM ".TABLE_ADMIN_ACL_TASK." WHERE class='xt_feedbackplus_campaigns'");

$db->Execute("ALTER TABLE `" . DB_PREFIX . "_products_reviews` DROP COLUMN `feedbackplus_life_circle_id`");
$db->Execute("ALTER TABLE `" . DB_PREFIX . "_products_reviews` DROP COLUMN `feedbackplus_campaign_id`");


$emailTpls = $db->GetAll("SELECT `tpl_id` FROM ". TABLE_MAIL_TEMPLATES ." WHERE `tpl_type` LIKE 'feedback_reminder' ");
foreach($emailTpls as $tpl)
{
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES_CONTENT." WHERE `tpl_id` = ".$tpl['tpl_id']);
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES." WHERE `tpl_id` = ".$tpl['tpl_id']);
}
$emailTpls = $db->GetAll("SELECT `tpl_id` FROM ". TABLE_MAIL_TEMPLATES ." WHERE `tpl_type` LIKE 'feedback_confirmation' ");
foreach($emailTpls as $tpl)
{
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES_CONTENT." WHERE `tpl_id` = ".$tpl['tpl_id']);
    $db->Execute("DELETE FROM ".TABLE_MAIL_TEMPLATES." WHERE `tpl_id` = ".$tpl['tpl_id']);
}

if ($this->_FieldExists('feedback_reminder',TABLE_ORDERS))
    $db->Execute("ALTER TABLE ".TABLE_ORDERS." DROP `feedback_reminder`");
if ($this->_FieldExists('feedback_hash',TABLE_ORDERS))
    $db->Execute("ALTER TABLE ".TABLE_ORDERS." DROP `feedback_hash`");
if ($this->_FieldExists('feedback_life_circle_id',TABLE_ORDERS))
    $db->Execute("ALTER TABLE ".TABLE_ORDERS." DROP `feedback_life_circle_id`");
if ($this->_FieldExists('feedback_allowed_status',TABLE_PRODUCTS))
    $db->Execute("ALTER TABLE ".TABLE_PRODUCTS." DROP `feedback_allowed_status`");
?>