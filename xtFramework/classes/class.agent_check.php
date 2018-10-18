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

class agent_check{
    
    var $browser_list = array('Boxely', 'Gecko', 'GtkHTML', 'HTMLayout', 'KHTML', 'NetFront', 'NetSurf', 'Presto', 'Prince XML','Robin','Tasman','Trident','Tkhtml','WebKit','Blink','XEP','Hubbub','iCab');
    
	function isBot() {

        if($this->isBrowser()=='true') return false;

		$bot_file = _SRV_WEBROOT.'xtFramework/library/bots/bots.txt';

		if (!file_exists($bot_file)) return 'false';

		$bots = array();

		$bf = fopen( $bot_file, "r" ) ;

		while (!feof($bf)) {
			$bots[] = trim(fgets($bf, 4096));
		}
		fclose($bf);

		$bot_ID = strtolower($_SERVER['HTTP_USER_AGENT']);
		$bot_ID2 = strtolower(getenv("HTTP_USER_AGENT"));

		foreach ($bots as $key => $val) {
			if (strstr($bot_ID, $val) or strstr($bot_ID2, $val)) {
                return 'true';
            }

		}
		return 'false';
	}
    
    function isBrowser(){
        $bot_ID = strtolower($_SERVER['HTTP_USER_AGENT']);
        $bot_ID2 = strtolower(getenv("HTTP_USER_AGENT"));
        
        foreach ($this->browser_list as $key => $val) {

			if (strstr($bot_ID, strtolower($val)) or strstr($bot_ID2, strtolower($val))) {
                return 'true';
            } 
		}
        
        return 'false';
    }
}