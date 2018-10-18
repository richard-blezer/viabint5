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

$language = new language();
$lang_list = $language->_getLanguageList('store');


$language_links = $language->getLanguageSwitchLinks($lang_list);

foreach ($lang_list as $key=>$lang) {
    if (isset($language_links[$lang['code']])) {
        if(_SYSTEM_MOD_REWRITE == 'true'){
            $lang_list[$key]['link']=$language_links[$lang['code']];
        }else{
            $lang_list[$key]['link']=$language_links[$lang['code']].'&action=change_lang&new_lang='.$lang['code'];
        }

    }
}


if(count($lang_list) > 1){
	$tpl_data = array('lang_data'=>$lang_list, 'selected_lang'=>$_SESSION['selected_language']);
	$show_box = true;
}else{
	$show_box = false;
}
?>