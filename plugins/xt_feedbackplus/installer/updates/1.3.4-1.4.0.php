<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

define('TABLE_FEEDBACKPLUS_CAMPAIGNS', DB_PREFIX . '_feedbackplus_campaigns');

global $db;

$colExists = $db->GetOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='"._SYSTEM_DATABASE_DATABASE."' AND COLUMN_NAME='feedbackplus_campaign_testing' AND TABLE_NAME='".TABLE_FEEDBACKPLUS_CAMPAIGNS."'");
if (!$colExists)
{
    $db->Execute("ALTER TABLE `".TABLE_FEEDBACKPLUS_CAMPAIGNS."` ADD `feedbackplus_campaign_testing` int(1) UNSIGNED DEFAULT 0");
}