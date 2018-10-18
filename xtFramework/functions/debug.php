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

// defined('_VALID_CALL') or die('Direct Access is not allowed.');

function __debug ($data, $text='') {
	if (defined ('__DEBUG_OUT_FILE') && is_writeable (__DEBUG_OUT_FILE)) {
		$f = fopen (__DEBUG_OUT_FILE, "a+");
		if(!empty($text)) {
			fwrite ($f, " ----- $text ------ \n");
		}
		fwrite ($f, var_export ($data, true));
		fwrite ($f, "\n\n");
		fclose ($f);
		return;
	}

	echo '<br />';
	echo '<br />';
	if(!empty($text))
	echo '<b>'.$text.':</b><br />';

	echo '<pre>';
	print_r($data);
	echo '</pre>';
	echo '<br />';
	echo '<HR SIZE=3>';
	echo '<br />';
}

?>