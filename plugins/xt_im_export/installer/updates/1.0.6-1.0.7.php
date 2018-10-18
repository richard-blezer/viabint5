<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

global $db;


if ($this->_FieldExists('ei_limit', DB_PREFIX.'_exportimport'))
{
    $db->Execute("ALTER IGNORE TABLE ".DB_PREFIX."_exportimport MODIFY `ei_limit` int(11) NOT NULL default '100';");
}