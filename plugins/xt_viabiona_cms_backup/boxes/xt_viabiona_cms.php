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
    $catalpha = $category->getCategoryBox(14);
    $catgrps = $category->getCategoryBox(19); 
	$catpdgrps = $category->getCategoryBox(20);
    $tpl_data[] = _getcmsfooter();
    
    
    $tpl_data['alpha'] = $catalpha;
    $tpl_data['kundengrps'] = $catgrps; 
	$tpl_data['productgrps'] = $catpdgrps;
    //__debug($tpl_data);
    }
?>