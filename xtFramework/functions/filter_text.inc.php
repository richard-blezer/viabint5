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

function _filterText($data, $type='full'){
    global $link_params;

    if (!$link_params['edit_id']) { // not for edit page
        $_search = array("'", '"');
        $_replace = array("&acute;", '&quot;');
        $data = str_replace($_search,$_replace, $data);
    }
		if($type=='full'){
			$data = addslashes($data);
		}

		$data = preg_replace("/\n/","\\n",$data);
		$data = preg_replace("/\r/","\\r",$data);
		$data = preg_replace("/\t/","\\t",$data);

		// f?r den admin, falls ein user script tags verwendet: (ak/mb)
		$data = preg_replace ('/<\/script>/i', '<\\/script>', $data);

	return $data;
}
?>