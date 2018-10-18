<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db;

if (!$this->_FieldExists('ei_store_id', DB_PREFIX.'_exportimport'))
{
    $db->Execute("ALTER TABLE  ".DB_PREFIX."_exportimport ADD `ei_store_id` int(11) NOT NULL;");
}