<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db;


if (!$this->_FieldExists('ei_cat_tree_delimiter', DB_PREFIX.'_exportimport'))
{
    $db->Execute("ALTER TABLE  ".DB_PREFIX."_exportimport ADD `ei_cat_tree_delimiter` varchar(32) NOT NULL default '/';");
}