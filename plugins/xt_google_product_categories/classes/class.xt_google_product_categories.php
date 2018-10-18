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
 # @version $Id: class.xt_googleanalytics.php 4611 2011-03-30 16:39:15Z mzanier $
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

class google_product_categories {
    
    public function getCategories(){
        global $language;
        
        $cat_file = _SRV_WEBROOT.'plugins/xt_google_product_categories/taxonomies/taxonomy.'.XT_GOOGLE_PRODUCT_CATEGORIES_LANG.'.txt';
        $data = array();
        if(is_file($cat_file)){
            $lines = file($cat_file);
            $i = 1;
            foreach($lines as $line){
                $line = trim($line);
                $data[] = array('id' => $line, 'name' => $line, 'desc' => '');
                $i++;
            }
        }
        else{
            $temp_temp = 'text file is not found';
            $data[] = array('id' => $temp_temp, 'name' => $temp_temp, 'desc' => '');
        }
        
        return $data; 
    }
    
}
?>