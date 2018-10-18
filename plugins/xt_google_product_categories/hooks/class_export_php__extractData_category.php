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
 # @version $Id: class_export_php__extractData_category.php 4611 2011-03-30 16:39:15Z mzanier $
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

if(empty($data_array['google_product_cat']) || $data_array['google_product_cat']==NULL || $data_array['google_product_cat']=='New'){
    
    //get standard categorie for multishop
    $c_rs=$db->Execute("SELECT config_value FROM ".TABLE_PLUGIN_CONFIGURATION." WHERE shop_id = '".$this->data['feed_store_id']."' and config_key ='XT_GOOGLE_PRODUCT_CATEGORIES_CAT'");
    if ($c_rs->RecordCount()==1) {
        $google_productcategory = $c_rs->fields['config_value'];
    }
    else{
        $google_productcategory = XT_GOOGLE_PRODUCT_CATEGORIES_CAT;
    }
    
    if ($rs->RecordCount()==1) {
        $g_rs=$db->Execute("SELECT google_product_cat FROM ".TABLE_CATEGORIES." WHERE google_product_cat <> 'New' AND google_product_cat <> '' and google_product_cat is not NULL and categories_id ='".$rs->fields['categories_id']."'");
        if ($g_rs->RecordCount()==1) {
            $data_array['google_productcategory'] = $g_rs->fields['google_product_cat'];
        }
        else{
            $data_array['google_productcategory'] = $google_productcategory;
        }
    } 
    else{
        $data_array['google_productcategory'] = $google_productcategory;
    }  
}
else{
    $data_array['google_productcategory'] = $data_array['google_product_cat'];
}
?>