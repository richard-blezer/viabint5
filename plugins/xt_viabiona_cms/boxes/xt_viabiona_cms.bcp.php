<?php

 /*
 ##########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware              #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
 #                                                                        #
 # Copyright 2012 xt:Commerce GmbH. All Rights Reserved.                  #
 # This file may not be redistributed in whole or significant part.       #
 # Content of this file is Protected By International Copyright Laws.     #
 #                                                                        #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~#
 #                                                                        #
 #                    http://www.xt-commerce.com                          # 
 #                                                                        #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
 #                                                                        #
 # @copyright xt:Commerce GmbH, www.xt-commerce.com                       #
 # viabiona CMS-Plugin, version 1.0.0, developer : Michael Garbs          #
 #                                                                        #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
 #                                                                        #
 # xt:Commerce GmbH, Eduard Bodem Gasse 6, 6020 Innsbruck, Austria        #
 #                                                                        # 
 #                     helpdesk@xt-commerce.com                           #
 #                                                                        #
 ##########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if(!isset($xtPlugin->active_modules['xt_viabiona_cms'])) 
    {$show_box = false;}
else 
    {
    include_once 'plugins/xt_viabiona_cms/classes/class.xt_viabiona_cms.php';
    $show_box = true;  
    $cat_main = $category->getCategoryBox(165);

    foreach ($cat_main as $key1=>$cat1) {
        $is_active = 0;
        foreach ($cat_main as $key2=>$cat2) {
            if ($cat2['level'] == '2' && $cat2['active'] == '1') {
                if ($cat1['categories_id'] == $cat2['parent_id']) {
                    $is_active = 1;
                }
            }
        }
        $cat_main[$key1]['is_sub_active'] = $is_active;
    }

    $cat1 = $category->getCategoryBox(19); 
	$cat2 = $category->getCategoryBox(230, true); 
	//$catthemen = $category->getCategoryBox(123);
    $tpl_data = _getcmsfooter();
    
    $tpl_data['cat_main'] = $cat_main;
    $tpl_data['cat1'] = $cat1; 
	$tpl_data['cat2'] = $cat2;
	//$tpl_data['catthemen'] = $catthemen;
    }
?>