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

function _buildDefine($db_handler, $tablename, $key = 'config_key', $value = 'config_value', $requirement = false)
{
    if ($requirement != '') {
        $requirement = " WHERE " . $requirement . "";
    }

    if ($tablename == TABLE_LANGUAGE_CONTENT) {
        $record = $db_handler->CacheExecute(_CACHETIME_LANGUAGE_CONTENT, "SELECT " . $key . ", " . $value . " FROM " . $tablename . " " . $requirement . " ");
    } else {
        $record = $db_handler->Execute("SELECT " . $key . ", " . $value . " FROM " . $tablename . " " . $requirement . " ");
    }

    while (!$record->EOF) {
        if (!defined(strtoupper($record->fields[$key]))) {
            if ($tablename == TABLE_LANGUAGE_CONTENT) {


                $constValue = html_entity_decode(stripslashes($record->fields[$value]), ENT_COMPAT, 'UTF-8');
            } else {
                $constValue = $record->fields[$value];
            }
            if (USER_POSITION == 'admin'){
                $constValue = str_replace(array('"', '\''), array('&quot;', '&apos;'), $constValue);
            }
            define(strtoupper($record->fields[$key]), $constValue);
        }
        $record->MoveNext();
    }
    $record->Close();
}

?>