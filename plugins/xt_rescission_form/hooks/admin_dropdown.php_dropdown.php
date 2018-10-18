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


// ADMIN: config for search mode

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($request['get'] == 'not_protected_content_blocks') {
	if (!isset($result)) $result = array();
	$result[] = array (
		'id' => '0',
		'text' => '- None -'
	);
	$query = "SELECT * FROM " . TABLE_CONTENT_BLOCK . " WHERE block_protected = '0'";
	$record = $db->Execute($query);
	if($record->RecordCount() > 0){
		while(!$record->EOF){
			$result[] = array (
				'id' => $record->fields['block_id'],
				'name' => $record->fields['block_tag'],
				'desc' => $record->fields['block_tag']
			);
			$record->MoveNext();
		}$record->Close();
	}
}

?>