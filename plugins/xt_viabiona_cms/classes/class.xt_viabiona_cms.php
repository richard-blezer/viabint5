﻿<?php

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

function _getcmsfooter() 
{
    define("SUBDIR", "");  // shop subdirectory, change when moving to new domain or new subdirectory - Unterverzeichnis, in dem der Shop liegt, bei Umzug auf neue Domain oder neues Unterverzeichnis ändern !
    
    global $db,$language;
    
    if(DB_PREFIX!='') $DB_PREFIX = DB_PREFIX.'_';
	else
            {
            define('DB_PREFIX','xt');
	    $DB_PREFIX = DB_PREFIX . '_';
	    }
    $shopurl = $db->Execute("SELECT shop_http FROM ".$DB_PREFIX."stores WHERE shop_id = 1");
    $modrewritetrue = $db->Execute("SELECT config_value FROM ".$DB_PREFIX."config WHERE CONFIG_KEY = '_SYSTEM_MOD_REWRITE'");
    $lang_code = $language->code;

    $record = $db->Execute("SELECT a.categories_id,a.cms_showcatinfooter_status, b.categories_name FROM ".$DB_PREFIX."categories a INNER JOIN ".$DB_PREFIX."categories_description
                            b ON b.categories_id = a.categories_id AND b.language_code = '".$language->code."' WHERE a.cms_showcatinfooter_status = 1 ORDER BY  a.sort_order, b.categories_name");
    if($record->RecordCount() > 0)
        {
        $footercategories = array();
        while(!$record->EOF)
            {
            if (!empty($record->fields['categories_name'])) 
                {
                $footercategories['categories_name']=$record->fields['categories_name']; 
                $footercategories['categories_id']=$record->fields['categories_id'];
                $urlquery = "SELECT url_text FROM ".$DB_PREFIX."seo_url WHERE link_type = 2 AND language_code = '$lang_code' AND link_id = ".$footercategories['categories_id'];                   
                if ($modrewritetrue->fields['config_value'] == 'true') $urlrecord = $db->Execute($urlquery);
                    else $urlrecord->fields['url_text'] = 'index.php?page=categorie&cat='.$footercategories['categories_id'];
                $footercategories['categories_url']=$shopurl->fields['shop_http'].SUBDIR.'/'.$urlrecord->fields['url_text'];
                }
                $data[footercategories][] = $footercategories;
                $record->MoveNext();
            }
            $record->Close();
        }    

    $record = $db->Execute("SELECT a.products_id,a.cms_showprodinfooter_status, b.products_name FROM  ".$DB_PREFIX."products a INNER JOIN ".$DB_PREFIX."products_description 
                           b ON b.products_id = a.products_id AND b.language_code = '".$language->code."' WHERE a.cms_showprodinfooter_status = 1 ORDER BY  b.products_name");
    if($record->RecordCount() > 0)
        {
        $footerproducts = array();
        while(!$record->EOF)
            {
            if (!empty($record->fields['products_name'])) 
                {
                $footerproducts['products_name']=$record->fields['products_name']; 
                $footerproducts['products_id'] = $record->fields['products_id'];
                $urlquery = "SELECT url_text FROM ".$DB_PREFIX."seo_url WHERE link_type = 1 AND language_code = '$lang_code' AND link_id = ".$footerproducts['products_id'];  
                if ($modrewritetrue->fields['config_value'] == 'true') $urlrecord = $db->Execute($urlquery);
                    else $urlrecord->fields['url_text'] = 'index.php?page=product&info='.$footerproducts['products_id'];
                $footerproducts['products_url']=$shopurl->fields['shop_http'].SUBDIR.'/'.$urlrecord->fields['url_text'];
                }
                $data[footerproducts][] = $footerproducts;
                $record->MoveNext();
            }
            $record->Close();
        }    
return ($data);
}
?>