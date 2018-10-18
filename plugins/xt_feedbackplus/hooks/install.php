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
# @version $Id: install.php 6359 2013-06-21 13:37:52Z stefan $
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
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_PRODUCTS', DB_PREFIX . '_feedbackplus_campaigns_products');
define('TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS', DB_PREFIX . '_feedbackplus_campaigns_permissions');
define('TABLE_FEEDBACKPLUS_TEMPLATES', DB_PREFIX . '_feedbackplus_templates');
define('TABLE_FEEDBACKPLUS_LIFE_CIRCLES', DB_PREFIX . '_feedbackplus_life_circles');

$db->Execute("
CREATE TABLE IF NOT EXISTS  `".TABLE_FEEDBACKPLUS_CAMPAIGNS."` (
  `feedbackplus_campaign_id`                     INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `feedbackplus_campaign_name`                   VARCHAR(32)         NULL DEFAULT NULL,
  `feedbackplus_mail_class_reminder`                   VARCHAR(64)         NULL DEFAULT 'feedback_reminder',
  `feedbackplus_mail_class_success`                   VARCHAR(32)         NULL DEFAULT 'feedback_confirmation',
  `feedbackplus_campaign_status`                 TINYINT(3) UNSIGNED NULL DEFAULT '0',
  `feedbackplus_campaign_creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feedbackplus_campaign_start_date`             DATETIME            NULL DEFAULT NULL,
  `feedbackplus_campaign_expire_date`            DATETIME            NULL DEFAULT NULL,
  `feedbackplus_coupons_id`            INT(10)             NULL DEFAULT NULL,
  `feedbackplus_campaign_testing`            INT(1) UNSIGNED NULL DEFAULT '0',
  PRIMARY KEY (`feedbackplus_campaign_id`)
)
  COLLATE = 'utf8_general_ci'
  ENGINE = MyISAM");

$db->Execute("
CREATE TABLE IF NOT EXISTS  `".TABLE_FEEDBACKPLUS_CAMPAIGNS_CATEGORIES."` (
  `pk`                       INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `feedbackplus_campaign_id` INT(10) UNSIGNED    NULL DEFAULT NULL,
  `categories_id`            INT(10) UNSIGNED    NULL DEFAULT NULL,
  `allow`                    TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE INDEX `pk` (`pk`)
)
  COLLATE = 'utf8_general_ci'
  ENGINE = MyISAM");


$db->Execute("
CREATE TABLE IF NOT EXISTS  `".TABLE_FEEDBACKPLUS_CAMPAIGNS_PRODUCTS."` (
  `pk`                       INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `feedbackplus_campaign_id` INT(10) UNSIGNED    NULL DEFAULT NULL,
  `products_id`              INT(10) UNSIGNED    NULL DEFAULT NULL,
  `allow`                    TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE INDEX `pk` (`pk`),
  INDEX `pk_2` (`pk`)
)
  COLLATE = 'utf8_general_ci'
  ENGINE = MyISAM");

$db->Execute("
CREATE TABLE IF NOT EXISTS  `".TABLE_FEEDBACKPLUS_CAMPAIGNS_PERMISSIONS."` (
`pid` int(11) NOT NULL,
  `permission` tinyint(1) default '0',
  `pgroup` varchar(255) NOT NULL,
  PRIMARY KEY  (`pid`,`pgroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");


$db->Execute("
CREATE TABLE IF NOT EXISTS  `".TABLE_FEEDBACKPLUS_LIFE_CIRCLES."` (
  `feedback_life_circle_id`      INT(10)     NOT NULL AUTO_INCREMENT,
  `feedbackplus_campaign_id`                  INT(10)     NULL DEFAULT NULL,
  `order_id`                     INT(10)     NULL DEFAULT NULL,
  `order_customer_id`            INT(10)     NULL DEFAULT NULL,
  `order_customer_language_code` VARCHAR(2)  NULL DEFAULT NULL,
  `order_date_purchased`         DATETIME        NULL DEFAULT NULL,
  `reminder_send`         DATETIME        NULL DEFAULT NULL,
  `review_submited_date`         DATETIME        NULL DEFAULT NULL,
  `coupon_send_date`         DATETIME        NULL DEFAULT NULL,
  `feedback_send_status`         TINYINT(4)  NULL DEFAULT NULL,
  `coupons_token_id`                int(10)        NULL DEFAULT NULL,
  PRIMARY KEY (`feedback_life_circle_id`),
  UNIQUE KEY `order_id` (`order_id`)
)
  COLLATE = 'utf8_general_ci'
  ENGINE = MyISAM");

$db->Execute('INSERT INTO ' . TABLE_ADMIN_NAVIGATION . " (`pid` ,`text` ,`icon` ,`url_i` ,`url_d` ,`sortorder` ,`parent` ,`type` ,`navtype`) VALUES (NULL , 'xt_feedbackplus_campaigns', 'images/icons/user_comment.png', '&plugin=xt_feedbackplus', 'adminHandler.php', '4001', 'xt_reviews', 'I', 'W');");
$db->Execute('UPDATE ' . TABLE_ADMIN_NAVIGATION . " SET `type`='G' WHERE `text`='xt_reviews'");


$db->Execute("ALTER TABLE `" . DB_PREFIX . "_products_reviews` ADD `feedbackplus_life_circle_id` int(10) default NULL");
$db->Execute("ALTER TABLE `" . DB_PREFIX . "_products_reviews` ADD `feedbackplus_campaign_id` int(10) default NULL");

if (!$this->_FieldExists('feedback_reminder',TABLE_ORDERS))
    $db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD `feedback_reminder` INT( 1 ) NOT NULL DEFAULT '0';");

if (!$this->_FieldExists('feedback_hash',TABLE_ORDERS))
    $db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD `feedback_hash` VARCHAR( 64 ) NOT NULL DEFAULT '0';");

if (!$this->_FieldExists('feedback_life_circle_id',TABLE_ORDERS))
    $db->Execute("ALTER TABLE ".TABLE_ORDERS." ADD `feedback_life_circle_id` INT( 11 ) NOT NULL DEFAULT '0';");

if (!$this->_FieldExists('feedback_allowed_status',TABLE_PRODUCTS))
    $db->Execute("ALTER TABLE ".TABLE_PRODUCTS." ADD `feedback_allowed_status` INT( 1 ) NOT NULL DEFAULT '1';");


$langs = array('de','en');
$tpls = array('feedback_reminder','feedback_confirmation');
_installMailTemplatesFeedback($langs, $tpls);



function _installMailTemplatesFeedback($langs, $tpls) {
    global $db;

    $mail_dir = _SRV_WEBROOT.'plugins/xt_feedbackplus/installer/';

    foreach($tpls as $tpl)
    {
        $data = array(
            'tpl_type' => $tpl,
            'tpl_special' => '-1',
        );
        $c = (int) $db->GetOne("SELECT count(tpl_id) FROM ".TABLE_MAIL_TEMPLATES." where `tpl_type` = '".$data['tpl_type']."'");
        if ($c>0)
        {
            continue;
        }
        try {
            $db->AutoExecute(TABLE_MAIL_TEMPLATES ,$data);
        } catch (exception $e) {
            return $e->msg;
        }
        $tplId = $db->GetOne("SELECT `tpl_id` FROM `".TABLE_MAIL_TEMPLATES."` WHERE `tpl_type`='".$data['tpl_type']."'");

        foreach($langs as $lang)
        {
            $html = file_exists($mail_dir.'mails_'.$lang.'/'.$tpl.'_html.txt') ?  _getFileContent($mail_dir.'mails_'.$lang.'/'.$tpl.'_html.txt') : '';
            $txt  = file_exists($mail_dir.'mails_'.$lang.'/'.$tpl.'_txt.txt')  ?  _getFileContent($mail_dir.'mails_'.$lang.'/'.$tpl.'_txt.txt') : '';
            $subj = file_exists($mail_dir.'mails_'.$lang.'/'.$tpl.'_subject.txt')  ?  _getFileContent($mail_dir.'mails_'.$lang.'/'.$tpl.'_subject.txt') : '';

            $data = array(
                'tpl_id' => $tplId,
                'language_code' => $lang,
                'mail_body_html' => $html,
                'mail_body_txt' => $txt,
                'mail_subject' => $subj
            );
            try {
                $db->AutoExecute(TABLE_MAIL_TEMPLATES_CONTENT ,$data);
            } catch (exception $e) {
                return $e->msg;
            }
        }
    }
}

function _getFileContent($filename) {
    $handle = fopen($filename, 'rb');
    $content = fread($handle, filesize($filename));
    fclose($handle);
    return $content;

}
?>